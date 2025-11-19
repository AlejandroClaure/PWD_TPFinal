<?php
// ============================
//      CONFIGURACIÓN
// ============================
include_once dirname(__DIR__, 4) . '/configuracion.php';
include_once dirname(__DIR__, 4) . '/Control/AbmMenu.php';

$abmMenu = new AbmMenu();

$menombre = trim($_POST["menombre"] ?? "");
$tipo = $_POST["tipo"] ?? "raiz";
$idPadre = $_POST["idpadre"] ?? null;

if ($menombre === "") {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

// Normalizar nombre
$menombre = ucfirst($menombre);

// Crear slug seguro
$slug = strtolower(trim($menombre));
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, "-");

// Nombre archivo final
$ruta = $slug . ".php";

// Si es submenú, meter dentro del padre
if ($tipo === "sub" && $idPadre) {
    $padre = $abmMenu->buscar(['idmenu' => $idPadre])[0];
    $padreSlug = strtolower(str_replace(".php", "", $padre->getMeDescripcion()));
    $ruta = $padreSlug . "/" . $slug . ".php";
}

// Ruta física final
$carpetaSecciones = dirname(__DIR__, 4) . "/Vista/secciones/";
$fullPath = $carpetaSecciones . $ruta;

// Crear carpeta si no existe
$dir = dirname($fullPath);
if (!is_dir($dir)) mkdir($dir, 0777, true);

// =====================================
//   CALCULAR CUÁNTOS ../ NECESITA
// =====================================
$nivelProfundidad = substr_count($ruta, "/");
$saltoRuta = str_repeat("../", $nivelProfundidad);

// ===============================
//  ARCHIVO PHP GENERADO (nowdoc)
// ===============================
$contenido = <<<'PHP'
<?php
include_once __DIR__ . '/__SALTO__../estructura/cabecera.php';
include_once __DIR__ . '/__SALTO__../../Control/AbmProducto.php';
include_once __DIR__ . '/__SALTO__../../Control/AbmMenu.php';

$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Obtener productos
$todos = $abmProducto->listar();
$productos = [];

// __RUTA__ es reemplazado dinámicamente al generar el archivo (ej: "celulares/iphone.php" o "celulares.php")
$generadaRuta = '__RUTA__';

// Convertir ruta de archivo a prefijo de categoría: quitar extensión y transformar "/" por "_", y añadir "_" final
$prefijoCategoria = strtolower(str_replace('.php', '', $generadaRuta));
$prefijoCategoria = str_replace('/', '_', $prefijoCategoria) . '_';

// Filtrar productos que empiecen con el prefijo completo (case-insensitive)
foreach ($todos as $p) {
    $nombreProducto = strtolower($p->getProNombre());
    if (str_starts_with($nombreProducto, $prefijoCategoria)) {
        $productos[] = $p;
    }
}
?>
<div class="container mt-4 pt-4">
    <h1 class="mb-4"><?php echo htmlspecialchars('REPLACEMENOMBRE'); ?></h1>
    <div class="row g-3">
        <?php if (empty($productos)): ?>
            <p class="text-muted">No hay productos cargados en esta sección.</p>
        <?php else: ?>
            <?php foreach ($productos as $prod): ?>
                <?php
                // Mostrar solo lo que está después del último "_" (nombre visible)
                $partes = explode('_', $prod->getProNombre());
                $nombreVisible = end($partes);
                ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <img src="<?= $GLOBALS['IMG_URL']; ?>productos/<?= $prod->getProNombre(); ?>.jpg"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($nombreVisible, ENT_QUOTES); ?>">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($nombreVisible); ?></h5>
                            <p class="text-success fs-4 fw-bold">$<?= htmlspecialchars($prod->getProDetalle()); ?></p>
                            <a href="<?= $GLOBALS['VISTA_URL']; ?>compra/accion/agregarCarrito.php?id=<?= $prod->getIdProducto(); ?>"
                               class="btn btn-warning w-100">
                               <i class="fa fa-shopping-cart"></i> Agregar al carrito
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/__SALTO__../estructura/pie.php'; ?>
PHP;

// Reemplazos seguros dentro del contenido generado
$contenido = str_replace('__SALTO__', $saltoRuta, $contenido);
$contenido = str_replace('__RUTA__', $ruta, $contenido);
// Reemplazamos también el nombre mostrado (evita inyección de variables en el nowdoc)
$contenido = str_replace('REPLACEMENOMBRE', addslashes($menombre), $contenido);

// Guardar archivo físico
file_put_contents($fullPath, $contenido);

// ============================
//     GUARDAR EN DB
// ============================
$datos = [
    "idmenu" => null,
    "menombre" => $menombre,
    "medescripcion" => $ruta,
    "idpadre" => ($tipo === "sub") ? $idPadre : null,
    "medeshabilitado" => 0
];

$idNuevoMenu = $abmMenu->alta($datos);

// Permisos para todos
include_once dirname(__DIR__, 4) . '/Control/AbmMenuRol.php';
$abmMenuRol = new AbmMenuRol();
$roles = [1, 2, 3, 4, 5];
foreach ($roles as $r) {
    $abmMenuRol->alta(["idmenu" => $idNuevoMenu, "idrol" => $r]);
}

header("Location: ../gestionMenus.php?ok=1");
exit;
?>
