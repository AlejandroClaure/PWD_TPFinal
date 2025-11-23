<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/configuracion.php";
include_once $GLOBALS['VISTA_PATH'] . "estructura/cabecera.php";

$abmProducto = new AbmProducto();

$prefijoCategoria = strtolower(str_replace("/", "_", str_replace(".php","", "celulares/samsung.php"))) . "_";

$productos = $abmProducto->listar();

function normalizar_nombre_img($nombre) {
    $tmp = mb_strtolower(trim($nombre), "UTF-8");
    $tmp = iconv("UTF-8","ASCII//TRANSLIT", $tmp) ?: $tmp;
    $tmp = preg_replace("/[^a-z0-9 ]+/", "", $tmp);
    return preg_replace("/\s+/", "_", trim($tmp));
}

$imgBaseUrl = $GLOBALS['VISTA_URL'] . "imagenes/productos/";
$imgDir     = $GLOBALS['VISTA_PATH'] . "imagenes/productos/";

$productosFiltrados = [];
foreach ($productos as $p) {
    $nombreBD = strtolower($p->getProNombre());
    if (!str_starts_with($nombreBD, $prefijoCategoria)) continue;

    $partes = explode("_", $nombreBD);
    $nombreReal = end($partes);

    $productosFiltrados[] = [
        "obj"        => $p,
        "nombreReal" => $nombreReal,
        "nombreImg"  => normalizar_nombre_img($nombreReal)
    ];
}
?>

<div class='container mt-4 pt-4'>
    <h1 class='mb-4'><?= htmlspecialchars("Samsung") ?></h1>

    <div class='row g-3'>
        <?php if (empty($productosFiltrados)): ?>
            <p class='text-muted'>No hay productos en esta sección.</p>
        <?php else: ?>
            <?php foreach ($productosFiltrados as $prod):
                $p = $prod["obj"];
                $nombreReal = $prod["nombreReal"];

                $imagenBD = $p->getProImagen();
                $imagenURL = (!$imagenBD || !file_exists($imgDir . $imagenBD))
                                ? $imgBaseUrl . "no-image.jpeg"
                                : $imgBaseUrl . $imagenBD;

                $precio = (float) $p->getProPrecio();
                $stock  = (int) $p->getProCantStock();
            ?>
            <div class='col-md-4 col-lg-3'>
                <div class='card shadow-sm h-100'>
                    <img src='<?= $imagenURL ?>' class='card-img-top' alt='<?= htmlspecialchars($nombreReal) ?>'>
                    <div class='card-body'>
                        <h5 class='card-title'><?= htmlspecialchars($nombreReal) ?></h5>
                        <p class='text-success fw-bold fs-5'>$<?= number_format($precio, 2, ',', '.') ?></p>

                        <p class='small text-muted'>
                            <?= nl2br(htmlspecialchars($p->getProDetalle())) ?>
                        </p>

                        <p class='text-muted'>Stock: <?= $stock ?></p>

                        <a href='<?= $GLOBALS['VISTA_URL'] ?>compra/accion/accionAgregarItemCarrito.php?id=<?= $p->getIdProducto() ?>'
                           class='btn btn-warning w-100 <?= $stock <= 0 ? "disabled" : "" ?>'>
                           <?= $stock > 0 ? "Agregar al carrito" : "Sin stock" ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include_once $GLOBALS['VISTA_PATH'] . "estructura/pie.php"; ?>