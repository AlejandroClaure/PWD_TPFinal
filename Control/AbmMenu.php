<?php
// Control/AbmMenu.php

// Asegurarnos de cargar la configuración (define $GLOBALS['VISTA_PATH'], MODELO_PATH, CONTROL_PATH, etc.)
require_once dirname(__DIR__) . '/configuracion.php';

// Cargar modelos y ABMs que usamos
// Aquí asumimos que configuracion.php define $GLOBALS['MODELO_PATH'] y $GLOBALS['CONTROL_PATH']
// Si no lo hace, usamos rutas relativas seguras.
if (!empty($GLOBALS['MODELO_PATH'])) {
    require_once $GLOBALS['MODELO_PATH'] . 'Menu.php';
} else {
    require_once dirname(__DIR__) . '/Modelo/Menu.php';
}

if (!empty($GLOBALS['CONTROL_PATH'])) {
    // AbmMenuRol suele estar en Control/AbmMenuRol.php
    require_once $GLOBALS['CONTROL_PATH'] . 'AbmMenuRol.php';
} else {
    require_once __DIR__ . '/AbmMenuRol.php';
}

class AbmMenu
{

    /* ============================================================
       ===============  CREAR NUEVO MENÚ  ==========================
       ============================================================ */
    public function crearMenu($param)
    {
        $menombre  = trim($param["menombre"] ?? "");
        $tipo      = $param["tipo"] ?? "raiz";
        $idPadre   = $param["idpadre"] ?? null;

        if ($menombre === "") return false;

        /* ------------------------------
           GENERAR SLUG
        --------------------------------*/
        $slug = strtolower(trim($menombre));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, "-");

        /* ------------------------------
           RUTAS (web + física)
        --------------------------------*/
        if ($tipo === "raiz") {
            $ruta      = $slug . ".php";
            $rutaFisica = "secciones/" . $slug . ".php";
        } else {
            $padre = $this->buscar(["idmenu" => $idPadre])[0];
            $padreRuta = str_replace(".php", "", $padre->getMeLink());
            $ruta      = $padreRuta . "/" . $slug . ".php";
            $rutaFisica = "secciones/" . $ruta;
        }

        $fullPath = $GLOBALS['VISTA_PATH'] . $rutaFisica;

        /* ------------------------------
           CREAR CARPETAS
        --------------------------------*/
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        /* ------------------------------
           CREAR CONTENIDO DEL ARCHIVO
        --------------------------------*/
        $contenido = $this->generarContenidoPHP($ruta, $menombre);

        file_put_contents($fullPath, $contenido);

        /* ------------------------------
           INSERTAR EN BD
        --------------------------------*/
        $objMenu = new Menu();
        $objMenu->setear(
            null,
            $menombre,
            $ruta,
            ($tipo === "sub" ? $idPadre : null),
            $ruta,
            0
        );

        $idNuevoMenu = $objMenu->insertar();

        /* ------------------------------
           PERMISOS PARA TODOS LOS ROLES (1–5)
        --------------------------------*/
        $abmMenuRol = new AbmMenuRol();
        foreach ([1, 2, 3, 4, 5] as $r) {
            $abmMenuRol->alta(["idmenu" => $idNuevoMenu, "idrol" => $r]);
        }

        return $idNuevoMenu;
    }

    /* ============================================================
   ===============  EDITAR / RENOMBRAR MENÚ ====================
   ============================================================ */
    public function editarMenu($param)
    {
        $idmenu = $param["idmenu"] ?? null;
        $menombreNuevo = trim($param["menombre"] ?? "");
        $tipo = $param["tipo"] ?? "raiz";
        $idPadre = $param["idpadre"] ?? null;

        if (!$idmenu || $menombreNuevo === "") return false;

        $menu = $this->buscar(["idmenu" => $idmenu])[0] ?? null;
        if (!$menu) return false;

        // 🔹 Generar nuevo slug
        $slug = strtolower(trim($menombreNuevo));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, "-");

        // 🔹 Nueva ruta
        if ($tipo === "raiz") {
            $nuevaRuta = $slug . ".php";
        } else {
            $padre = $this->buscar(["idmenu" => $idPadre])[0] ?? null;
            $padreRuta = $padre ? str_replace(".php", "", $padre->getMeLink()) : '';
            $nuevaRuta = $padreRuta . "/" . $slug . ".php";
        }

        $rutaActualFull = $GLOBALS['VISTA_PATH'] . "secciones/" . $menu->getMeLink();
        $rutaNuevaFull  = $GLOBALS['VISTA_PATH'] . "secciones/" . $nuevaRuta;

        // 🔹 Renombrar archivo físico
        if (file_exists($rutaActualFull)) {
            $dirNueva = dirname($rutaNuevaFull);
            if (!is_dir($dirNueva)) mkdir($dirNueva, 0777, true);
            rename($rutaActualFull, $rutaNuevaFull);
        }

        // 🔹 Evitar ser padre de sí mismo
        if ($idPadre == $idmenu) $idPadre = null;

        // 🔹 Actualizar objeto Menu
        $menu->setMeNombre($menombreNuevo);
        $menu->setMeLink($nuevaRuta);
        $menu->setMeDescripcion($nuevaRuta);
        $menu->setMeDeshabilitado(null);
        $menu->setObjMenuPadre($tipo === "sub" && $idPadre != $idmenu ? $this->buscar(["idmenu" => $idPadre])[0] ?? null : null);

        $exito = $menu->modificar();
        if (!$exito) error_log("Error al modificar menú: " . $menu->getMensajeOperacion());

        // 🔹 Actualizar submenús recursivamente
        $this->actualizarSubmenusRuta($menu);

        return $exito;
    }


    /* ============================================================
       ===============  RENOMBRADO RECURSIVO =======================
       ============================================================ */
    private function actualizarSubmenusRuta($menuPadre)
    {
        $hijos = $this->buscar(["idpadre" => $menuPadre->getIdMenu()]);
        if (empty($hijos)) return;

        $rutaPadre = str_replace(".php", "", $menuPadre->getMeLink());

        foreach ($hijos as $hijo) {
            $slugHijo = strtolower(trim($hijo->getMeNombre()));
            $slugHijo = preg_replace('/[^a-z0-9]+/', '-', $slugHijo);
            $slugHijo = trim($slugHijo, '-');

            $nuevaRuta = $rutaPadre . "/" . $slugHijo . ".php";

            $rutaActualFull = $GLOBALS['VISTA_PATH'] . "secciones/" . $hijo->getMeLink();
            $rutaNuevaFull  = $GLOBALS['VISTA_PATH'] . "secciones/" . $nuevaRuta;

            if (file_exists($rutaActualFull)) {
                $dirNueva = dirname($rutaNuevaFull);
                if (!is_dir($dirNueva)) mkdir($dirNueva, 0777, true);
                rename($rutaActualFull, $rutaNuevaFull);
            }

            // Actualizar en BD
            $hijo->setMeLink($nuevaRuta);
            $hijo->setMeDescripcion($nuevaRuta);
            $hijo->modificar();

            // Actualizar título dentro del archivo
            if (file_exists($rutaNuevaFull)) {
                $contenido = file_get_contents($rutaNuevaFull);
                $contenido = preg_replace(
                    '/<h1 class=\'mb-4\'>.*?<\/h1>/',
                    "<h1 class='mb-4'>" . htmlspecialchars($hijo->getMeNombre()) . "</h1>",
                    $contenido
                );
                file_put_contents($rutaNuevaFull, $contenido);
            }

            // Recursivo hacia abajo
            $this->actualizarSubmenusRuta($hijo);
        }
    }

    /* ============================================================
       ===============  BORRAR MENÚ ===============================
       ============================================================ */
    public function eliminarMenu($param)
    {
        if (!$this->seteadosCamposClaves($param)) return false;

        $obj = new Menu();
        $obj->setIdMenu($param["idmenu"]);
        return $obj->eliminar();
    }

    /* ============================================================
       ===============  BUSCAR ====================================
       ============================================================ */
    public function buscar($param)
    {
        $where = "true";

        if (isset($param["idmenu"])) $where .= " AND idmenu=" . intval($param["idmenu"]);
        if (isset($param["idpadre"])) $where .= " AND idpadre=" . intval($param["idpadre"]);
        if (isset($param["menombre"])) $where .= " AND menombre LIKE '%" . addslashes($param["menombre"]) . "%'";

        return Menu::listar($where);
    }

    private function seteadosCamposClaves($params)
    {
        return isset($params["idmenu"]);
    }

    /* ============================================================
       ===============  GENERAR ARCHIVO PHP COMPLETO ==============
       ============================================================ */
    private function generarContenidoPHP($ruta, $menombre)
    {
        return <<<PHP
<?php
require_once \$_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/configuracion.php";
include_once \$GLOBALS['VISTA_PATH'] . "estructura/cabecera.php";

\$abmProducto = new AbmProducto();

\$prefijoCategoria = strtolower(str_replace("/", "_", str_replace(".php","", "$ruta"))) . "_";

\$productos = \$abmProducto->listar();

function normalizar_nombre_img(\$nombre) {
    \$tmp = mb_strtolower(trim(\$nombre), "UTF-8");
    \$tmp = iconv("UTF-8","ASCII//TRANSLIT", \$tmp) ?: \$tmp;
    \$tmp = preg_replace("/[^a-z0-9 ]+/", "", \$tmp);
    return preg_replace("/\\s+/", "_", trim(\$tmp));
}

\$imgBaseUrl = \$GLOBALS['VISTA_URL'] . "imagenes/productos/";
\$imgDir     = \$GLOBALS['VISTA_PATH'] . "imagenes/productos/";

\$productosFiltrados = [];
foreach (\$productos as \$p) {
    \$nombreBD = strtolower(\$p->getProNombre());
    if (!str_starts_with(\$nombreBD, \$prefijoCategoria)) continue;

    \$partes = explode("_", \$nombreBD);
    \$nombreReal = end(\$partes);

    \$productosFiltrados[] = [
        "obj"        => \$p,
        "nombreReal" => \$nombreReal,
        "nombreImg"  => normalizar_nombre_img(\$nombreReal)
    ];
}
?>

<div class='container mt-4 pt-4'>
    <h1 class='mb-4'><?= htmlspecialchars("$menombre") ?></h1>

    <div class='row g-3'>
        <?php if (empty(\$productosFiltrados)): ?>
            <p class='text-muted'>No hay productos en esta sección.</p>
        <?php else: ?>
            <?php foreach (\$productosFiltrados as \$prod):
                \$p = \$prod["obj"];
                \$nombreReal = \$prod["nombreReal"];

                \$imagenBD = \$p->getProImagen();
                \$imagenURL = (!\$imagenBD || !file_exists(\$imgDir . \$imagenBD))
                                ? \$imgBaseUrl . "no-image.jpeg"
                                : \$imgBaseUrl . \$imagenBD;

                \$precio = (float) \$p->getProPrecio();
                \$stock  = (int) \$p->getProCantStock();
            ?>
            <div class='col-md-4 col-lg-3'>
                <div class='card shadow-sm h-100'>
                    <img src='<?= \$imagenURL ?>' class='card-img-top' alt='<?= htmlspecialchars(\$nombreReal) ?>'>
                    <div class='card-body'>
                        <h5 class='card-title'><?= htmlspecialchars(\$nombreReal) ?></h5>
                        <p class='text-success fw-bold fs-5'>\$<?= number_format(\$precio, 2, ',', '.') ?></p>

                        <p class='small text-muted'>
                            <?= nl2br(htmlspecialchars(\$p->getProDetalle())) ?>
                        </p>

                        <p class='text-muted'>Stock: <?= \$stock ?></p>

                        <a href='<?= \$GLOBALS['VISTA_URL'] ?>compra/accion/accionModificarCarrito.php?id=<?= \$p->getIdProducto() ?>'
                           class='btn btn-warning w-100 <?= \$stock <= 0 ? "disabled" : "" ?>'>
                           <?= \$stock > 0 ? "Agregar al carrito" : "Sin stock" ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include_once \$GLOBALS['VISTA_PATH'] . "estructura/pie.php"; ?>
PHP;
    }
}
