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
    // Si viene un WHERE manual en forma de string → devolver directo
    if (is_string($param)) {
        return Menu::listar($param);
    }

    $where = "true";

    if (isset($param["idmenu"])) {
        $where .= " AND idmenu=" . intval($param["idmenu"]);
    }

    if (isset($param["idpadre"])) {
        $where .= " AND idpadre=" . intval($param["idpadre"]);
    }

    if (isset($param["menombre"])) {
        $where .= " AND menombre LIKE '%" . addslashes($param["menombre"]) . "%'";
    }

    // 🔥 Filtro correcto para DATETIME
    if (isset($param["medeshabilitado"])) {
        $valor = addslashes($param["medeshabilitado"]);
        
        // Si el valor es NULL
        if ($valor === null || strtoupper($valor) === "NULL") {
            $where .= " AND medeshabilitado IS NULL";
        } else {
            // Usamos fecha exacta
            $where .= " AND medeshabilitado = '$valor'";
        }
    }

    return Menu::listar($where);
}


    private function seteadosCamposClaves($params)
    {
        return isset($params["idmenu"]);
    }

    /**
     * Elimina un menú completo con toda su jerarquía (archivos, roles, hijos, BD)
     */
    public function eliminarMenuCompleto($idMenu)
    {
        $idMenu = (int)$idMenu;
        if ($idMenu <= 0) return false;

        $abmMenuRol       = new AbmMenuRol();
        $carpetaSecciones = $GLOBALS['VISTA_PATH'] . "secciones/";

        // === FUNCIÓN RECURSIVA INTERNA (sin problemas de $this ni referencias) ===
        $eliminarRecursivo = null; // Declaramos antes
        $eliminarRecursivo = function ($id) use (&$eliminarRecursivo, $abmMenuRol, $carpetaSecciones) {
            $self = $this; // Capturamos $this correctamente

            // 1. Eliminar hijos primero
            $hijos = $self->buscar(['idpadre' => $id]);
            foreach ($hijos as $hijo) {
                $eliminarRecursivo($hijo->getIdMenu()); // Ahora sí funciona
            }

            // 2. Obtener menú actual
            $menuArr = $self->buscar(['idmenu' => $id]);
            if (empty($menuArr)) return;
            $menu = $menuArr[0];

            // 3. Eliminar permisos de roles
            $roles = $abmMenuRol->buscar(['idmenu' => $id]);
            foreach ($roles as $rol) {
                $abmMenuRol->baja([
                    'idmenu' => $id,
                    'idrol'  => $rol->getIdRol() ?? $rol->getIdrol() ?? null
                ]);
            }

            // 4. Eliminar archivo físico
            $ruta = $menu->getMeDescripcion() ?: $menu->getMeLink();
            if ($ruta) {
                $archivo = $carpetaSecciones . ltrim($ruta, '/');
                if (is_file($archivo)) {
                    @unlink($archivo);
                }
                // Eliminar carpeta vacía
                $dir = dirname($archivo);
                if ($dir !== rtrim($carpetaSecciones, '/\\') && is_dir($dir)) {
                    $contenido = array_diff(scandir($dir), ['.', '..']);
                    if (empty($contenido)) {
                        @rmdir($dir);
                    }
                }
            }

            // 5. Eliminar registro del menú
            $self->baja(['idmenu' => $id]);
        };

        // Ejecutar
        $eliminarRecursivo($idMenu);
        return true;
    }
    public function baja($param)
    {
        $exito = false;
        if (isset($param['idmenu'])) {
            $menu = new Menu();
            $menu->setIdMenu($param['idmenu']);
            if ($menu->cargar()) {
                $exito = $menu->eliminar();
            }
        }
        return $exito;
    }

    /**
     * Devuelve todos los menús que el usuario puede ver según sus roles
     * @param array $roles Array con nombres de roles (ej: ['admin', 'cliente'])
     * @return array Lista de objetos Menu
     */
    public function obtenerMenuPorRoles($roles)
    {
        if (empty($roles)) {
            return [];
        }

        // Convertir roles a string para el IN
        $rolesEscapados = array_map('addslashes', $roles);
        $rolesIn = "'" . implode("','", $rolesEscapados) . "'";

        // Primero obtenemos los idrol correspondientes
        $sqlRoles = "SELECT idrol FROM rol WHERE rodescripcion IN ($rolesIn)";
        $bd = new BaseDatos();
        $idRoles = [];
        if ($bd->Ejecutar($sqlRoles) > 0) {
            while ($row = $bd->Registro()) {
                $idRoles[] = $row['idrol'];
            }
        }

        if (empty($idRoles)) {
            return [];
        }

        $idRolesIn = implode(',', $idRoles);

        // Ahora buscamos los menús que tienen al menos uno de esos roles
        $sql = "SELECT DISTINCT m.*
            FROM menu m
            LEFT JOIN menurol mr ON m.idmenu = mr.idmenu
            WHERE m.medeshabilitado IS NULL
              AND (mr.idrol IN ($idRolesIn) OR mr.idrol IS NULL)
            ORDER BY m.idpadre, m.menombre";

        $menus = [];
        if ($bd->Ejecutar($sql) > 0) {
            while ($row = $bd->Registro()) {
                $menu = new Menu();
                $menu->setear(
                    $row['idmenu'],
                    $row['menombre'],
                    $row['melink'],
                    $row['medescripcion'],
                    $row['idpadre'] ? $this->buscar(['idmenu' => $row['idpadre']])[0] ?? null : null,
                    $row['medeshabilitado']
                );
                $menus[] = $menu;
            }
        }

        return $menus;
    }

    /* ============================================================
       ===============  GENERAR ARCHIVO PHP COMPLETO ==============
       ============================================================ */
    private function generarContenidoPHP($ruta, $menombre)
    {
        // Convertimos la ruta web (ej: celulares/samsung.php) en prefijo de BD
        $rutaSinPhp = str_replace(".php", "", $ruta);
        $partesRuta = explode("/", $rutaSinPhp);

        // Construimos el prefijo exacto como aparece en proNombre
        // Ej: celulares → celulares_
        // Ej: celulares/samsung → celulares_samsung_
        $prefijoBD = "";
        foreach ($partesRuta as $i => $parte) {
            $prefijoBD .= $parte;
            if ($i < count($partesRuta) - 1) {
                $prefijoBD .= "_";  // solo guion bajo entre niveles
            }
        }
        $prefijoBD = strtolower($prefijoBD) . "_";

        return <<<PHP
<?php
require_once \$_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/configuracion.php";
include_once \$GLOBALS['VISTA_PATH'] . "estructura/cabecera.php";

\$abmProducto = new AbmProducto();
\$productos = \$abmProducto->listar();

\$prefijoCategoria = "$prefijoBD";  // ej: celulares_samsung_

function limpiarNombreProducto(\$nombreBD, \$prefijo) {
    // Quitamos el prefijo completo
    \$nombreLimpio = str_replace(\$prefijo, "", \$nombreBD);
    
    // Quitamos todo lo que venga después del último "_"
    // Ej: Samsung_Galaxy_A55_5G_azul → Samsung Galaxy A55 5G azul
    \$partes = explode("_", \$nombreLimpio);
    \$nombreReal = end(\$partes); // toma el último segmento
    
    // Capitalizamos bien
    \$nombreReal = ucwords(strtolower(\$nombreReal));
    
    // Reemplazos comunes para que quede lindo
    \$nombreReal = str_replace(
        [" De ", " Del ", " Con ", " Para "],
        [" de ", " del ", " con ", " para "],
        \$nombreReal
    );
    
    return trim(\$nombreReal);
}

\$imgBaseUrl = \$GLOBALS['VISTA_URL'] . "imagenes/productos/";
\$imgDir     = \$GLOBALS['VISTA_PATH'] . "imagenes/productos/";

\$productosFiltrados = [];
foreach (\$productos as \$p) {
    \$nombreBD = strtolower(\$p->getProNombre());
    
    // Solo incluir si empieza exactamente con el prefijo
    if (!str_starts_with(\$nombreBD, \$prefijoCategoria)) {
        continue;
    }
    
    \$nombreReal = limpiarNombreProducto(\$nombreBD, \$prefijoCategoria);

    \$productosFiltrados[] = [
        "obj"        => \$p,
        "nombreReal" => \$nombreReal,
        "nombreImg"  => preg_replace('/[^a-z0-9]+/', '_', strtolower(\$nombreReal))
    ];
}
?>

<div class="container mt-5 pt-4">
    <h1 class="mb-4"><?= htmlspecialchars("$menombre") ?></h1>

    <?php if (empty(\$productosFiltrados)): ?>
        <div class="text-center py-5">
            <p class="lead text-muted">No hay productos disponibles en esta sección aún.</p>
            <a href="<?= \$GLOBALS['VISTA_URL'] ?>producto/producto.php" class="btn btn-outline-primary">
                Volver a la tienda
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach (\$productosFiltrados as \$prod):
                \$p = \$prod["obj"];
                \$nombreReal = \$prod["nombreReal"];
                \$imagenBD = \$p->getProImagen();
                \$imagenURL = (!\$imagenBD || !file_exists(\$imgDir . \$imagenBD))
                    ? \$imgBaseUrl . "no-image.jpeg"
                    : \$imgBaseUrl . \$imagenBD;

                \$precio = (float)\$p->getProPrecio();
                \$stock  = (int)\$p->getProCantStock();
            ?>
                <div class="col">
                    <div class="card h-100 shadow-sm hover-shadow transition">
                        <img src="<?= \$imagenURL ?>" class="card-img-top" alt="<?= htmlspecialchars(\$nombreReal) ?>" style="height: 200px; object-fit: contain; background: #f8f9fa;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fs-6 fw-bold"><?= htmlspecialchars(\$nombreReal) ?></h5>
                            <p class="text-success fw-bold fs-4 mt-2">$<?= number_format(\$precio, 0, ',', '.') ?></p>
                            
                            <p class="text-muted small grow">
                                <?= substr(htmlspecialchars(\$p->getProDetalle()), 0, 100) ?>...
                            </p>

                            <div class="mt-auto">
                                <p class="text-muted small mb-2">Stock: <strong><?= \$stock ?></strong></p>
                                <a href="<?= \$GLOBALS['VISTA_URL'] ?>compra/accion/accionAgregarItemCarrito.php?id=<?= \$p->getIdProducto() ?>"
                                   class="btn btn-warning w-100 <?= \$stock <= 0 ? 'disabled' : '' ?>">
                                    <?= \$stock > 0 ? 'Agregar al carrito' : 'Sin stock' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once \$GLOBALS['VISTA_PATH'] . "estructura/pie.php"; ?>
PHP;
    }


    //PROBANDO METODO

    // dentro de class AbmMenu { ... }

public function modificar(array $params)
{
    // Necesitamos idmenu
    if (empty($params['idmenu'])) return false;
    $id = intval($params['idmenu']);

    // Cargar objeto menu
    $menu = new Menu();
    $menu->setIdMenu($id);
    if (!$menu->cargar()) {
        return false;
    }

    // Actualizar campos permitidos (si vienen)
    if (isset($params['menombre'])) {
        $menu->setMeNombre(trim($params['menombre']));
    }
    if (array_key_exists('medescripcion', $params)) {
        $menu->setMeDescripcion($params['medescripcion']);
    }
    if (array_key_exists('medeshabilitado', $params)) {
        // Permitimos 0/null/1
        $val = $params['medeshabilitado'];
        // Normalizar: si viene '' o null -> null, si viene 1 o '1' -> 1, else 0
        if ($val === '' || $val === null) {
            $menu->setMeDeshabilitado(null);
        } else {
            $menu->setMeDeshabilitado(intval($val) === 1 ? 1 : 0);
        }
    }

    // idpadre: si viene, actualizar objeto padre (o setear null)
    if (array_key_exists('idpadre', $params)) {
        $idpadre = $params['idpadre'] === null || $params['idpadre'] === '' ? null : intval($params['idpadre']);
        if ($idpadre && $idpadre !== $menu->getIdMenu()) {
            $padreArr = $this->buscar(['idmenu' => $idpadre]);
            if (!empty($padreArr)) {
                $menu->setObjMenuPadre($padreArr[0]);
            } else {
                $menu->setObjMenuPadre(null);
            }
        } else {
            $menu->setObjMenuPadre(null);
        }
    }

    // Guardar cambios
    $exito = $menu->modificar();
    if (!$exito) {
        error_log("AbmMenu::modificar fallo: " . $menu->getMensajeOperacion());
    }
    return $exito;
}

}
