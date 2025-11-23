<?php
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

$idUsuario = $session->getUsuario()->getIdUsuario();
$idCompra = intval($_GET['id'] ?? 0);

$abmCompra = new AbmCompra();
$abmEstado = new AbmCompraEstado();
$abmItem   = new AbmCompraItem();

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5">

<?php
// ----------------------------------------------------------------------
// 1) SI NO SE RECIBE ID → MOSTRAR LISTA DE TODAS LAS COMPRAS DEL CLIENTE
// ----------------------------------------------------------------------
if ($idCompra <= 0):

    $compras = $abmCompra->buscar(['idusuario' => $idUsuario]);
?>
    <h2 class="mb-4">Mis Compras</h2>

    <?php if (empty($compras)): ?>
        <div class="alert alert-info">Todavía no realizaste ninguna compra.</div>

    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Compra</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($compras as $c):
                $estado = $abmEstado->obtenerEstadoActual($c->getIdCompra());
                $estadoTexto = $estado ? $estado->getObjCompraEstadoTipo()->getCeTDescripcion() : 'desconocido';
            ?>
                <tr>
                    <td><?= $c->getIdCompra() ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($c->getCoFecha())) ?></td>
                    <td><?= ucfirst($estadoTexto) ?></td>
                    <td>
                        <a class="btn btn-primary btn-sm"
                           href="verCompraCliente.php?id=<?= $c->getIdCompra() ?>">
                            Ver detalles
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php
// ----------------------------------------------------------------------
// 2) SI SE RECIBE ID → MOSTRAR DETALLES DE UNA COMPRA
// ----------------------------------------------------------------------
else:

    $compra = $abmCompra->buscar(['idcompra' => $idCompra])[0] ?? null;

    if (!$compra || $compra->getObjUsuario()->getIdUsuario() !== $idUsuario) {
        echo "<div class='alert alert-danger'>Acceso denegado o compra no encontrada.</div>";
        include_once '../estructura/pie.php';
        exit;
    }

    $items = $abmItem->buscar(['idcompra' => $idCompra]);
    $estadoActual = $abmEstado->obtenerEstadoActual($idCompra);
    $estadoTexto = $estadoActual ? $estadoActual->getObjCompraEstadoTipo()->getCeTDescripcion() : 'desconocido';
?>
    <h2>Mi Compra #<?= $compra->getIdCompra() ?></h2>
    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($compra->getCoFecha())) ?></p>
    <p><strong>Estado:</strong>
        <span class="badge bg-success fs-6"><?= ucfirst($estadoTexto) ?></span>
    </p>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Productos</h5>

            <?php
            $total = 0;
            foreach ($items as $item):
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

    <a href="verCompraCliente.php" class="btn btn-secondary">← Volver a Mis Compras</a>

<?php endif; ?>

</div>

<?php include_once '../estructura/pie.php'; ?>
