<?php
// Vista/compra/verCompraCliente.php

include_once '../../configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraItem.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraEstado.php';

$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php");
    exit;
}

$idCompra = intval($_GET['id'] ?? 0);
if ($idCompra <= 0) {
    header("Location: carrito.php");
    exit;
}

$abmCompra = new AbmCompra();
$compra = $abmCompra->buscar(['idcompra' => $idCompra])[0] ?? null;

if (!$compra || $compra->getObjUsuario()->getIdUsuario() !== $session->getUsuario()->getIdUsuario()) {
    echo "<div class='alert alert-danger'>Acceso denegado o compra no encontrada.</div>";
    exit;
}

$items = (new AbmCompraItem())->buscar(['idcompra' => $idCompra]);
$estadoActual = (new AbmCompraEstado())->obtenerEstadoActual($idCompra);
$estadoTexto = $estadoActual ? $estadoActual->getObjCompraEstadoTipo()->getCeTDescripcion() : 'desconocido';

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5">
    <h2>Mi Compra #<?= $compra->getIdCompra() ?></h2>
    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($compra->getCoFecha())) ?></p>
    <p><strong>Estado:</strong> 
        <span class="badge bg-success fs-6"><?= ucfirst($estadoTexto) ?></span>
    </p>

    <div class="card">
        <div class="card-body">
            <h5>Productos</h5>
            <?php $total = 0; foreach ($items as $item): 
                $p = $item->getObjProducto();
                $subtotal = $p->getProPrecio() * $item->getCiCantidad();
                $total += $subtotal;
            ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <strong><?= htmlspecialchars($p->getProNombre()) ?></strong><br>
                        <small>Cantidad: <?= $item->getCiCantidad() ?></small>
                    </div>
                    <div>$<?= number_format($subtotal, 0, ',', '.') ?></div>
                </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between mt-3 fs-5 fw-bold">
                <span>Total</span>
                <span class="text-success">$<?= number_format($total, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <a href="carrito.php" class="btn btn-outline-primary mt-3">Volver al carrito</a>
</div>

<?php include_once '../estructura/pie.php'; ?>