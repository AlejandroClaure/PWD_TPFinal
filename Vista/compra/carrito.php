<?php
include_once '../../configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmProducto.php';

session_start();

$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito =& $_SESSION['carrito'];

// ==========================
// Funciones auxiliares
// ==========================
function normalizar_nombre_img($nombre) {
    $tmp = trim($nombre);
    $tmp = mb_strtolower($tmp, 'UTF-8');
    $tmp = iconv('UTF-8', 'ASCII//TRANSLIT', $tmp) ?: $tmp;
    $tmp = preg_replace('/[^a-z0-9 ]+/', '', $tmp);
    $tmp = preg_replace('/\s+/', '_', trim($tmp));
    return $tmp;
}

function detectar_imagenes_ruta_base() {
    $candidatos = [
        dirname(__DIR__, 2) . '/imagenes/',
        dirname(__DIR__) . '/imagenes/',
        dirname(__DIR__, 3) . '/Vista/imagenes/',
        dirname(__DIR__, 4) . '/Vista/imagenes/',
    ];
    foreach ($candidatos as $cand) {
        $real = @realpath($cand);
        if ($real && is_dir($real)) {
            return ['dir' => $real . '/', 'baseUrl' => '/PWD_TPFinal/Vista/imagenes/'];
        }
    }
    $gImg = $GLOBALS['IMG_URL'] ?? '/PWD_TPFinal/Vista/imagenes/';
    $gDir = dirname(__DIR__, 2) . '/imagenes/';
    return ['dir' => $gDir, 'baseUrl' => $gImg];
}

$imgInfo = detectar_imagenes_ruta_base();
$imgDir = rtrim($imgInfo['dir'], '/') . '/';
$imgBaseUrl = rtrim($imgInfo['baseUrl'], '/') . '/';

// ==========================
// Modificar cantidad
// ==========================
if (isset($_GET['id']) && isset($_GET['accion'])) {
    $idProducto = $_GET['id'];
    $accion = $_GET['accion'];

    $abmProducto = new AbmProducto();
    $producto = $abmProducto->buscarPorId($idProducto);
    $stock = (int)$producto->getProCantStock();

    if ($accion === 'sumar') {
        if (!isset($carrito[$idProducto])) {
            $carrito[$idProducto] = [
                'nombre' => $producto->getProNombre(),
                'precio' => (float)str_replace(['$', ','], '', $producto->getProDetalle()),
                'cantidad' => 0
            ];
        }
        if ($carrito[$idProducto]['cantidad'] < $stock) {
            $carrito[$idProducto]['cantidad']++;
        }
    } elseif ($accion === 'restar') {
        if (isset($carrito[$idProducto])) {
            $carrito[$idProducto]['cantidad']--;
            if ($carrito[$idProducto]['cantidad'] <= 0) {
                unset($carrito[$idProducto]);
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==========================
// Eliminar producto
// ==========================
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    if (isset($carrito[$idEliminar])) {
        unset($carrito[$idEliminar]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ==========================
// Totales
// ==========================
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += floatval($item['precio']) * intval($item['cantidad']);
}
$envio = $subtotal > 0 ? 5000 : 0;
$total = $subtotal + $envio;

?>

<?php include_once '../estructura/cabecera.php'; ?>

<main class="mt-5 pt-5">
<div class="container mt-5">
    <h3>Mi carrito</h3>

    <?php if (empty($carrito)): ?>
        <p class="text-muted">No hay productos en el carrito.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrito as $id => $item): 
                        $abmProducto = new AbmProducto();
                        $productoObj = $abmProducto->buscarPorId($id);
                        $stock = (int)$productoObj->getProCantStock();

                        // Nombre limpio
                        $nombreBD = $productoObj->getProNombre();
                        $partes = explode('_', $nombreBD);
                        $nombreReal = end($partes);
                        $nombreVisible = str_replace('_', ' ', $nombreReal);

                        // Imagen pequeña
                        $nombreImg = normalizar_nombre_img($nombreReal);
                        $extensiones = ['jpg','jpeg','png','webp'];
                        $imagenURL = $imgBaseUrl . 'no-image.jpeg';
                        foreach ($extensiones as $ext) {
                            if (file_exists($imgDir . $nombreImg . '.' . $ext)) {
                                $imagenURL = $imgBaseUrl . $nombreImg . '.' . $ext;
                                break;
                            }
                        }
                    ?>
                    <tr>
                        <td class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($imagenURL); ?>" 
                                 alt="<?= htmlspecialchars($nombreVisible); ?>" 
                                 style="width:50px; height:auto; margin-right:10px; object-fit:cover;">
                            <?= htmlspecialchars($nombreVisible); ?>
                        </td>
                        <td>$<?= number_format(floatval($item['precio']), 2, ',', '.'); ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="?id=<?= $id; ?>&accion=restar" class="btn btn-sm btn-secondary me-1">-</a>
                                <?= intval($item['cantidad']); ?>
                                <a href="?id=<?= $id; ?>&accion=sumar" 
                                   class="btn btn-sm btn-secondary ms-1 <?= $item['cantidad'] >= $stock ? 'disabled' : ''; ?>">+</a>
                            </div>
                            <small class="text-muted">Stock: <?= $stock; ?></small>
                        </td>
                        <td>$<?= number_format(floatval($item['precio']) * intval($item['cantidad']), 2, ',', '.'); ?></td>
                        <td><a href="?eliminar=<?= $id; ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-end">
            <p><strong>Subtotal:</strong> $<?= number_format($subtotal, 2, ',', '.'); ?></p>
            <p><strong>Envío:</strong> $<?= number_format($envio, 2, ',', '.'); ?></p>
            <p class="fs-4"><strong>Total:</strong> $<?= number_format($total, 2, ',', '.'); ?></p>

            <a href="../compra/finalizarCompra.php" class="btn btn-success btn-lg">
                Finalizar Compra
            </a>
        </div>
    <?php endif; ?>
</div>
</main>

<?php include_once '../estructura/pie.php'; ?>
