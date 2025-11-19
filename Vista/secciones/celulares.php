<?php
include_once __DIR__ . '/../estructura/cabecera.php';
include_once __DIR__ . '/../../Control/AbmProducto.php';
include_once __DIR__ . '/../../Control/AbmMenu.php';

// Variables dinámicas
$tipo = 'raiz';
$idPadre = null;

$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Obtener productos
$todos = $abmProducto->listar();
$productos = [];

// celulares.php es reemplazado dinámicamente
$generadaRuta = 'celulares.php';

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
    <h1 class="mb-4"><?php echo htmlspecialchars('Celulares'); ?></h1>
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

<?php include_once __DIR__ . '/../estructura/pie.php'; ?>