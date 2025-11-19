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

// Variables dinámicas
$tipo = '__TIPO__';
$idPadre = __IDPADRE__;

$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Obtener productos
$todos = $abmProducto->listar();
$productos = [];

// __RUTA__ es reemplazado dinámicamente
$generadaRuta = '__RUTA__';

// Prefijo de categoría
$prefijoCategoria = strtolower(str_replace('.php', '', $generadaRuta));
$prefijoCategoria = str_replace('/', '_', $prefijoCategoria) . '_';

// Filtrar productos de la categoría principal
foreach ($todos as $p) {
    $nombreProducto = strtolower($p->getProNombre());
    if (str_starts_with($nombreProducto, $prefijoCategoria)) {
        $productos[] = $p;
    }
}

// Incluir productos de subcategorías si existen
$idPadreActual = $tipo === 'raiz' ? null : $idPadre;
$hijos = $abmMenu->buscar(['idpadre' => $idPadreActual]);
foreach ($hijos as $hijo) {
    foreach ($todos as $p) {
        $firstWord = explode(' ', trim($p->getProNombre()))[0];
        if ($firstWord === $hijo->getMeNombre()) {
            $productos[] = $p;
        }
    }
}

// Rutas de imágenes
$imgBaseUrl = $GLOBALS['IMG_URL'] ?? '/PWD_TPFinal/Vista/imagenes/';
$imgDir     = dirname(__DIR__, 2) . '/imagenes/';
?>

<div class="container mt-4 pt-4">
    <h1 class="mb-4"><?php echo htmlspecialchars('REPLACEMENOMBRE'); ?></h1>
    <div class="row g-3">
        <?php if (empty($productos)): ?>
            <p class="text-muted">No hay productos cargados en esta sección.</p>
        <?php else: ?>
            <?php foreach ($productos as $prod): ?>
                <?php
                $partes = explode('_', $prod->getProNombre());
                $nombreVisible = end($partes);

                // Buscar imagen
                $baseName = str_replace(' ', '_', $prod->getProNombre());
                if (file_exists($imgDir . $baseName . '.jpg')) {
                    $imagenURL = $imgBaseUrl . $baseName . '.jpg';
                } elseif (file_exists($imgDir . $baseName . '.jpeg')) {
                    $imagenURL = $imgBaseUrl . $baseName . '.jpeg';
                } else {
                    $imagenURL = $imgBaseUrl . 'no-image.jpeg';
                }

                // Precio y stock
                $precio = str_replace(['$', ','], '', $prod->getProDetalle());
                $precio = (float)$precio;
                $stock  = (int)$prod->getProCantStock();
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <img src="<?= htmlspecialchars($imagenURL, ENT_QUOTES); ?>"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($nombreVisible, ENT_QUOTES); ?>"
                             onerror="this.src='<?= $imgBaseUrl; ?>no-image.jpeg';">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($nombreVisible); ?></h5>
                            <p class="text-success fw-bold fs-5">
                                $<?= number_format($precio, 2, ',', '.'); ?>
                            </p>
                            <p class="text-muted">
                                Stock: <?= $stock; ?>
                            </p>
                            <a href="<?= $GLOBALS['VISTA_URL'] ?? '/PWD_TPFinal/Vista/'; ?>compra/accion/agregarCarrito.php?id=<?= $prod->getIdProducto(); ?>"
                               class="btn btn-warning w-100 <?= $stock <= 0 ? 'disabled' : ''; ?>">
                                <i class="fa fa-shopping-cart"></i> 
                                <?= $stock > 0 ? 'Agregar al carrito' : 'Sin stock'; ?>
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

// ================================
//  Reemplazos dinámicos
// ================================
$contenido = str_replace('__SALTO__', $saltoRuta, $contenido);
$contenido = str_replace('__RUTA__', $ruta, $contenido);
$contenido = str_replace('REPLACEMENOMBRE', addslashes($menombre), $contenido);
$contenido = str_replace('__TIPO__', $tipo, $contenido);
$contenido = str_replace('__IDPADRE__', is_numeric($idPadre) ? $idPadre : 'null', $contenido);

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
