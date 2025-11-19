<?php
include_once '../../configuracion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar sesión
$session = new Session();

// Incluir AbmProducto
include_once $GLOBALS['CONTROL_PATH'] . 'AbmProducto.php';
$abmProducto = new AbmProducto();
$productos = $abmProducto->listar();

$imgBaseUrl = $GLOBALS['IMG_URL'] ?? '/PWD_TPFinal/Vista/imagenes/';
$imgDir     = dirname(__DIR__) . '/imagenes/'; 
?>

<div class="row g-4">
<?php if (empty($productos)): ?>
    <p class="text-muted text-center">No hay productos cargados.</p>
<?php else: ?>
    <?php foreach ($productos as $prod): ?>
        <?php
        // Nombre completo del producto en BD: celulares_iphone_NombreProducto
        $nombreCompleto = $prod->getProNombre();
        $partes = explode('_', $nombreCompleto);
        
        // Nombre real del producto (último segmento)
        $nombreProductoReal = end($partes);
        $nombreProductoReal = str_replace(' ', '_', $nombreProductoReal);

        // Buscar imagen en varios formatos
        $extensiones = ['jpg', 'jpeg', 'png', 'webp'];
        $imagenURL = $imgBaseUrl . 'no-image.jpeg'; // default

        foreach ($extensiones as $ext) {
            if (file_exists($imgDir . $nombreProductoReal . '.' . $ext)) {
                $imagenURL = $imgBaseUrl . $nombreProductoReal . '.' . $ext;
                break;
            }
        }

        // Nombre visible
        $nombreVisible = $nombreProductoReal;

        // Precio como float
        $precio = str_replace(['$', ','], '', $prod->getProDetalle());
        $precio = (float)$precio;

        // Stock
        $stock = (int)$prod->getProCantStock();
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
