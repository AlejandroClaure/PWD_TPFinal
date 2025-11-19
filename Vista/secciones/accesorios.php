<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';

$abmProducto = new AbmProducto();
$productos = [];

// Obtener todos los productos
$allProductos = $abmProducto->listar();

// Filtrado por primera palabra
foreach ($allProductos as $prod) {
    $firstWord = explode(' ', trim($prod->getProNombre()))[0];
    if ($firstWord === 'Accesorios') {
        $productos[] = $prod;
    }
}

// Para categorías principales, agregar también productos de subcategorías
if ('raiz' === 'raiz') {
    // Buscar subcategorías del menú padre
    include_once dirname(__DIR__, 3) . '/Control/AbmMenu.php';
    $abmMenu = new AbmMenu();
    $hijos = $abmMenu->buscar(['idpadre' =>  ?? null]);
    foreach ($hijos as $h) {
        foreach ($allProductos as $prod) {
            $firstWord = explode(' ', trim($prod->getProNombre()))[0];
            if ($firstWord === $h->getMeNombre()) {
                $productos[] = $prod;
            }
        }
    }
}
?>

<div class="container mt-4 pt-4">
    <h1 class="mb-4">Accesorios</h1>
    <div class="row g-3">
        <?php if (empty($productos)): ?>
            <p class="text-muted">No hay productos cargados en esta sección.</p>
        <?php else: ?>
            <?php foreach ($productos as $prod): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <img src="<?= $GLOBALS['IMG_URL']; ?>productos/<?= $prod->getProNombre(); ?>.jpg"
                             class="card-img-top"
                             alt="<?= $prod->getProNombre(); ?>">
                        <div class="card-body">
                            <h5><?= $prod->getProNombre(); ?></h5>
                            <p class="text-success fs-4 fw-bold">$<?= $prod->getProDetalle(); ?></p>
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

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>