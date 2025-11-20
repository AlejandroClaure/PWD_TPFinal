<?php
include_once __DIR__ . '/../../estructura/cabecera.php';
include_once __DIR__ . '/../../../Control/AbmProducto.php';
include_once __DIR__ . '/../../../Control/AbmMenu.php';

$tipo = 'sub';
$idPadre = 94;

$abmProducto = new AbmProducto();

// Obtener todos los productos
$todos = $abmProducto->listar();
$productos = [];

// Ruta generada (descripcion de menu)
$generadaRuta = 'celulares/samsung.php';

// Prefijo de categoría/subcategoría (ej: celulares_iphone_)
$prefijoCategoria = strtolower(str_replace('.php', '', $generadaRuta));
$prefijoCategoria = str_replace('/', '_', $prefijoCategoria) . '_';

// ===============================
//  FUNCIONES AUXILIARES
// ===============================
function normalizar_nombre_img($nombre) {
    $tmp = trim($nombre);
    // pasar a minúsculas
    $tmp = mb_strtolower($tmp, 'UTF-8');
    // quitar acentos
    $tmp = iconv('UTF-8', 'ASCII//TRANSLIT', $tmp);
    if ($tmp === false) {
        // fallback simple
        $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N'];
        $tmp = strtr($nombre, $map);
        $tmp = strtolower($tmp);
    }
    // eliminar todo lo que no sea letra/número/espacio
    $tmp = preg_replace('/[^a-z0-9 ]+/', '', $tmp);
    // espacios a guión bajo
    $tmp = preg_replace('/\s+/', '_', trim($tmp));
    return $tmp;
}

/**
 * Detecta la carpeta física de imágenes y la URL pública base.
 * Devuelve un array: ['dir' => '/ruta/fisica/', 'baseUrl' => '/ruta/para/url/']
 */
function detectar_imagenes_ruta_base() {
    $candidatos = [
        // posibles rutas relativas desde este archivo
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
        dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR,
    ];

    // Añadir intento usando DOCUMENT_ROOT si está disponible y proyecto en subcarpeta
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
        // intenta rutas comunes (ajusta 'PWD_TPFinal' si tu carpeta raíz tiene otro nombre)
        $candidatos[] = $docRoot . DIRECTORY_SEPARATOR . 'PWD_TPFinal' . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
        $candidatos[] = $docRoot . DIRECTORY_SEPARATOR . 'Vista' . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
    }

    // evaluar candidatos
    foreach ($candidatos as $cand) {
        $real = @realpath($cand);
        if ($real && is_dir($real)) {
            // intentar generar base URL a partir de DOCUMENT_ROOT
            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
                if (strpos($real, $docRoot) === 0) {
                    $urlPath = str_replace(DIRECTORY_SEPARATOR, '/', substr($real, strlen($docRoot)));
                    // asegurar leading slash
                    if ($urlPath === '' || $urlPath[0] !== '/') $urlPath = '/' . $urlPath;
                    // asegurar trailing slash
                    if (substr($urlPath, -1) !== '/') $urlPath .= '/';
                    return ['dir' => $real . DIRECTORY_SEPARATOR, 'baseUrl' => $urlPath];
                }
            }
            // si no se puede derivar a partir de document_root, usar ruta relativa presumida
            // intentar suponer que la carpeta 'Vista/imagenes' se expone en /PWD_TPFinal/Vista/imagenes/
            $guessUrl = '/PWD_TPFinal/Vista/imagenes/';
            return ['dir' => $real . DIRECTORY_SEPARATOR, 'baseUrl' => $guessUrl];
        }
    }

    // fallback: usar la constante global IMG_URL si existe (sin comprobar)
    $gImg = $GLOBALS['IMG_URL'] ?? '/PWD_TPFinal/Vista/imagenes/';
    $gDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR;
    return ['dir' => $gDir, 'baseUrl' => $gImg];
}

// Detectar rutas
$imgInfo = detectar_imagenes_ruta_base();
$imgDir = rtrim($imgInfo['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$imgBaseUrl = rtrim($imgInfo['baseUrl'], '/') . '/';

// ===============================
//  FILTRO + NOMBRE REAL CORRECTO
// ===============================
foreach ($todos as $p) {

    $nombreBD = strtolower($p->getProNombre());

    // Debe pertenecer a esta categoría
    if (!str_starts_with($nombreBD, $prefijoCategoria)) continue;

    // Partes del nombre BD
    $partes = explode('_', $nombreBD);

    // Siempre el último segmento es el nombre real
    $nombreReal = end($partes);

    // Normalizar para imagen
    $nombreImg = normalizar_nombre_img($nombreReal);

    $productos[] = [
        'obj'        => $p,
        'nombreReal' => $nombreReal,
        'nombreImg'  => $nombreImg
    ];
}

// Preparar log
$logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$logFile = $logDir . 'imagenes.log';
if (!file_exists($logFile)) file_put_contents($logFile, "=== LOG DE BUSQUEDA DE IMAGENES ===\n\n");

?>

<div class="container mt-4 pt-4">
    <h1 class="mb-4"><?= htmlspecialchars('Samsung'); ?></h1>

    <div class="row g-3">
        <?php if (empty($productos)): ?>
            <p class="text-muted">No hay productos cargados en esta sección.</p>

        <?php else: ?>

            <?php foreach ($productos as $prod):

                $p = $prod['obj'];
                $nombreReal = $prod['nombreReal'];
                $nombreImg  = $prod['nombreImg'];

                // Log inicial por producto
                file_put_contents($logFile,
                    "\n----- Producto: {$nombreReal} -----\n".
                    "Nombre base generado: {$nombreImg}\n".
                    "Buscando en dir: {$imgDir}\n".
                    "Base URL candidata: {$imgBaseUrl}\n",
                    FILE_APPEND
                );

                // Buscar imagen
                $extensiones = ['jpg', 'jpeg', 'png', 'webp'];
                $imagenURL = $imgBaseUrl . 'no-image.jpeg';
                $found = false;

                foreach ($extensiones as $ext) {
                    $archivo = $nombreImg . '.' . $ext;
                    $rutaFisica = $imgDir . $archivo;

                    file_put_contents($logFile, "Probando: {$rutaFisica} ... ", FILE_APPEND);

                    if (file_exists($rutaFisica)) {
                        // construir URL pública a partir de imgBaseUrl + archivo
                        $imagenURL = $imgBaseUrl . $archivo;
                        $found = true;
                        file_put_contents($logFile, "ENCONTRADO\n", FILE_APPEND);
                        break;
                    } else {
                        file_put_contents($logFile, "no existe\n", FILE_APPEND);
                    }
                }

                if (!$found) {
                    file_put_contents($logFile, ">> Imagen NO encontrada, usando no-image.jpeg\n", FILE_APPEND);
                }

                // Precio y stock
                $precio = (float) str_replace(['$', ','], '', $p->getProDetalle());
                $stock  = (int)  $p->getProCantStock();
            ?>

            <div class="col-md-4 col-lg-3">
                <div class="card shadow-sm h-100 producto-img">
                    <img src="<?= htmlspecialchars($imagenURL, ENT_QUOTES); ?>"
                         class="card-img-top producto-img"
                         alt="<?= htmlspecialchars($nombreReal, ENT_QUOTES); ?>"
                         onerror="this.src='<?= htmlspecialchars($imgBaseUrl . 'no-image.jpeg', ENT_QUOTES); ?>';">

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($nombreReal); ?></h5>

                        <p class="text-success fw-bold fs-5">
                            $<?= number_format($precio, 2, ',', '.'); ?>
                        </p>

                        <p class="text-muted">Stock: <?= $stock; ?></p>

                        <a href="<?= $GLOBALS['VISTA_URL'] ?? '/PWD_TPFinal/Vista/'; ?>compra/accion/agregarCarrito.php?id=<?= $p->getIdProducto(); ?>"
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

<?php include_once __DIR__ . '/../../estructura/pie.php'; ?>