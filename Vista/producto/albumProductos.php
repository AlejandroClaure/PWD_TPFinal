<?php
include_once '../../configuracion.php';
include_once '../../configuracion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar sesión
$session = new Session();

// Incluir AbmProducto
include_once $GLOBALS['CONTROL_PATH'] . 'AbmProducto.php';
$abmProducto = new AbmProducto();
$productos = $abmProducto->listar();


// Funciones auxiliares
function normalizar_nombre_img($nombre) {
    $tmp = trim($nombre);
    // pasar a minúsculas
    $tmp = mb_strtolower($tmp, 'UTF-8');
    // quitar acentos
    $tmp = iconv('UTF-8', 'ASCII//TRANSLIT', $tmp);
    if ($tmp === false) {
        $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'];
        $tmp = strtr($nombre, $map);
        $tmp = strtolower($tmp);
    }
    // eliminar caracteres no alfanuméricos
    $tmp = preg_replace('/[^a-z0-9 ]+/', '', $tmp);
    // espacios a guión bajo
    $tmp = preg_replace('/\s+/', '_', trim($tmp));
    return $tmp;
}

function detectar_imagenes_ruta_base() {
    $candidatos = [
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
    ];

    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
        $candidatos[] = $docRoot . DIRECTORY_SEPARATOR . 'PWD_TPFinal' . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
        $candidatos[] = $docRoot . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
    }

    foreach ($candidatos as $cand) {
        $real = @realpath($cand);
        if ($real && is_dir($real)) {
            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
                if (strpos($real, $docRoot) === 0) {
                    $urlPath = str_replace(DIRECTORY_SEPARATOR, '/', substr($real, strlen($docRoot)));
                    if ($urlPath === '' || $urlPath[0] !== '/') $urlPath = '/' . $urlPath;
                    if (substr($urlPath, -1) !== '/') $urlPath .= '/';
                    return ['dir' => $real . DIRECTORY_SEPARATOR, 'baseUrl' => $urlPath];
                }
            }
            return ['dir' => $real . DIRECTORY_SEPARATOR, 'baseUrl' => '/PWD_TPFinal/Vista/imagenes/'];
        }
    }

    $gImg = $GLOBALS['IMG_URL'] ?? '/PWD_TPFinal/Vista/imagenes/';
    $gDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
    return ['dir' => $gDir, 'baseUrl' => $gImg];
}


// Detectar rutas
$imgInfo = detectar_imagenes_ruta_base();
$imgDir = rtrim($imgInfo['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$imgBaseUrl = rtrim($imgInfo['baseUrl'], '/') . '/';
?>

<div class="row g-4">
<?php if (empty($productos)): ?>
    <p class="text-muted text-center">No hay productos cargados.</p>
<?php else: ?>
    <?php foreach ($productos as $prod):
        // Nombre completo en BD
        $nombreCompleto = $prod->getProNombre();
        $partes = explode('_', $nombreCompleto);

        // Último segmento → nombre real
        $nombreReal = end($partes);

        // Nombre para mostrar al usuario
        $nombreVisible = str_replace('_', ' ', $nombreReal);

        // Nombre para imagen
        $nombreImg = normalizar_nombre_img($nombreReal);

        // Buscar imagen
        $extensiones = ['jpg', 'jpeg', 'png', 'webp'];
        $imagenURL = $imgBaseUrl . 'no-image.jpeg';
        foreach ($extensiones as $ext) {
            if (file_exists($imgDir . $nombreImg . '.' . $ext)) {
                $imagenURL = $imgBaseUrl . $nombreImg . '.' . $ext;
                break;
            }
        }

        // Precio y stock
        $precio = (float) str_replace(['$', ','], '', $prod->getProDetalle());
        $stock  = (int) $prod->getProCantStock();
    ?>
    <div class="col-md-4 col-lg-3">
        <div class="card shadow-sm h-100 product-card">
            <img src="<?= htmlspecialchars($imagenURL, ENT_QUOTES); ?>"
                 class="card-img-top producto-img"
                 alt="<?= htmlspecialchars($nombreVisible, ENT_QUOTES); ?>"
                 onerror="this.src='<?= $imgBaseUrl; ?>no-image.jpeg';">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($nombreVisible); ?></h5>
                <p class="text-success fw-bold fs-5">
                    $<?= number_format($precio, 2, ',', '.'); ?>
                </p>
                <p class="text-muted">Stock: <?= $stock; ?></p>
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