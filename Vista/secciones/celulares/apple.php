<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/configuracion.php";
include_once $GLOBALS['VISTA_PATH'] . "estructura/cabecera.php";

$abmProducto = new AbmProducto();
$productos = $abmProducto->listar();

$prefijoCategoria = "celulares_apple_";  // ej: celulares_samsung_

function limpiarNombreProducto($nombreBD, $prefijo) {
    // Quitamos el prefijo completo
    $nombreLimpio = str_replace($prefijo, "", $nombreBD);
    
    // Quitamos todo lo que venga después del último "_"
    // Ej: Samsung_Galaxy_A55_5G_azul → Samsung Galaxy A55 5G azul
    $partes = explode("_", $nombreLimpio);
    $nombreReal = end($partes); // toma el último segmento
    
    // Capitalizamos bien
    $nombreReal = ucwords(strtolower($nombreReal));
    
    // Reemplazos comunes para que quede lindo
    $nombreReal = str_replace(
        [" De ", " Del ", " Con ", " Para "],
        [" de ", " del ", " con ", " para "],
        $nombreReal
    );
    
    return trim($nombreReal);
}

$imgBaseUrl = $GLOBALS['VISTA_URL'] . "imagenes/productos/";
$imgDir     = $GLOBALS['VISTA_PATH'] . "imagenes/productos/";

$productosFiltrados = [];
foreach ($productos as $p) {
    $nombreBD = strtolower($p->getProNombre());
    
    // Solo incluir si empieza exactamente con el prefijo
    if (!str_starts_with($nombreBD, $prefijoCategoria)) {
        continue;
    }
    
    $nombreReal = limpiarNombreProducto($nombreBD, $prefijoCategoria);

    $productosFiltrados[] = [
        "obj"        => $p,
        "nombreReal" => $nombreReal,
        "nombreImg"  => preg_replace('/[^a-z0-9]+/', '_', strtolower($nombreReal))
    ];
}
?>

<div class="container mt-5 pt-4">
    <h1 class="mb-4"><?= htmlspecialchars("Apple") ?></h1>

    <?php if (empty($productosFiltrados)): ?>
        <div class="text-center py-5">
            <p class="lead text-muted">No hay productos disponibles en esta sección aún.</p>
            <a href="<?= $GLOBALS['VISTA_URL'] ?>producto/producto.php" class="btn btn-outline-primary">
                Volver a la tienda
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($productosFiltrados as $prod):
                $p = $prod["obj"];
                $nombreReal = $prod["nombreReal"];
                $imagenBD = $p->getProImagen();
                $imagenURL = (!$imagenBD || !file_exists($imgDir . $imagenBD))
                    ? $imgBaseUrl . "no-image.jpeg"
                    : $imgBaseUrl . $imagenBD;

                $precio = (float)$p->getProPrecio();
                $stock  = (int)$p->getProCantStock();
            ?>
                <div class="col">
                    <div class="card h-100 shadow-sm hover-shadow transition">
                        <img src="<?= $imagenURL ?>" class="card-img-top" alt="<?= htmlspecialchars($nombreReal) ?>" style="height: 200px; object-fit: contain; background: #f8f9fa;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fs-6 fw-bold"><?= htmlspecialchars($nombreReal) ?></h5>
                            <p class="text-success fw-bold fs-4 mt-2">$<?= number_format($precio, 0, ',', '.') ?></p>
                            
                            <p class="text-muted small grow">
                                <?= substr(htmlspecialchars($p->getProDetalle()), 0, 100) ?>...
                            </p>

                            <div class="mt-auto">
                                <p class="text-muted small mb-2">Stock: <strong><?= $stock ?></strong></p>
                                <a href="<?= $GLOBALS['VISTA_URL'] ?>compra/accion/accionAgregarItemCarrito.php?id=<?= $p->getIdProducto() ?>"
                                   class="btn btn-warning w-100 <?= $stock <= 0 ? 'disabled' : '' ?>">
                                    <?= $stock > 0 ? 'Agregar al carrito' : 'Sin stock' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once $GLOBALS['VISTA_PATH'] . "estructura/pie.php"; ?>