<?php
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

// Slug seguro
$slug = strtolower(trim($menombre));
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, "-");

// Generar ruta PHP
$ruta = $slug . ".php";
if ($tipo === "sub" && $idPadre) {
    $padre = $abmMenu->buscar(['idmenu' => $idPadre])[0];
    $padreSlug = strtolower(str_replace(".php", "", $padre->getMeDescripcion()));
    $ruta = $padreSlug . "/" . $slug . ".php";
}

// Crear archivo de sección
$carpetaSecciones = dirname(__DIR__, 4) . "/Vista/secciones/";
$fullPath = $carpetaSecciones . $ruta;
$dir = dirname($fullPath);
if (!is_dir($dir)) mkdir($dir, 0777, true);

// Contenido del archivo generado
$contenido = <<<HTML
<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';

\$abmProducto = new AbmProducto();
\$productos = [];

// Obtener todos los productos
\$allProductos = \$abmProducto->listar();

// Filtrado por primera palabra
foreach (\$allProductos as \$prod) {
    \$firstWord = explode(' ', trim(\$prod->getProNombre()))[0];
    if (\$firstWord === '{$menombre}') {
        \$productos[] = \$prod;
    }
}

// Para categorías principales, agregar también productos de subcategorías
if ('$tipo' === 'raiz') {
    // Buscar subcategorías del menú padre
    include_once dirname(__DIR__, 3) . '/Control/AbmMenu.php';
    \$abmMenu = new AbmMenu();
    \$hijos = \$abmMenu->buscar(['idpadre' => $idNuevoMenu ?? null]);
    foreach (\$hijos as \$h) {
        foreach (\$allProductos as \$prod) {
            \$firstWord = explode(' ', trim(\$prod->getProNombre()))[0];
            if (\$firstWord === \$h->getMeNombre()) {
                \$productos[] = \$prod;
            }
        }
    }
}
?>

<div class="container mt-4 pt-4">
    <h1 class="mb-4">{$menombre}</h1>
    <div class="row g-3">
        <?php if (empty(\$productos)): ?>
            <p class="text-muted">No hay productos cargados en esta sección.</p>
        <?php else: ?>
            <?php foreach (\$productos as \$prod): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <img src="<?= \$GLOBALS['IMG_URL']; ?>productos/<?= \$prod->getProNombre(); ?>.jpg"
                             class="card-img-top"
                             alt="<?= \$prod->getProNombre(); ?>">
                        <div class="card-body">
                            <h5><?= \$prod->getProNombre(); ?></h5>
                            <p class="text-success fs-4 fw-bold">$<?= \$prod->getProDetalle(); ?></p>
                            <a href="<?= \$GLOBALS['VISTA_URL']; ?>compra/accion/agregarCarrito.php?id=<?= \$prod->getIdProducto(); ?>"
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

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
HTML;

// Guardar archivo
file_put_contents($fullPath, $contenido);

// Guardar menú en BD
$datos = [
    "idmenu" => null,
    "menombre" => $menombre,
    "medescripcion" => $ruta,
    "idpadre" => ($tipo === "sub") ? $idPadre : null,
    "medeshabilitado" => 0
];

$idNuevoMenu = $abmMenu->alta($datos);

// Asignar a todos los roles
include_once dirname(__DIR__, 4) . '/Control/AbmMenuRol.php';
$abmMenuRol = new AbmMenuRol();
$roles = [1, 2, 3, 4, 5];
foreach ($roles as $r) {
    $abmMenuRol->alta(["idmenu" => $idNuevoMenu, "idrol" => $r]);
}

// Redirigir
header("Location: ../gestionMenus.php?ok=1");
exit;
?>
