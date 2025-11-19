<?php
include_once __DIR__ . '/../estructura/cabecera.php';
include_once __DIR__ . '/../../Control/AbmProducto.php';
include_once __DIR__ . '/../../Control/AbmMenu.php';

$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Obtener productos
$todos = $abmProducto->listar();
$productos = [];

// celulares.php es reemplazado dinámicamente al generar el archivo (ej: "celulares/iphone.php" o "celulares.php")
$generadaRuta = 'celulares.php';

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
    <h1 class="mb-4"><?php echo htmlspecialchars('Celulares'); ?></h1>
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

<?php include_once __DIR__ . '/../estructura/pie.php'; ?>