<?php
include_once '../../configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraItem.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraEstado.php';

$session = new Session();
if (!$session->activa() || !$session->tieneRol('admin')) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php");
    exit;
}

$idCompra = intval($_GET['id'] ?? 0);
if ($idCompra <= 0) {
    header("Location: listarCompras.php");
    exit;
}

$abmCompra = new AbmCompra();
$compra = $abmCompra->buscar(['idcompra' => $idCompra])[0] ?? null;
if (!$compra) {
    header("Location: listarCompras.php?error=1");
    exit;
}

$abmItem = new AbmCompraItem();
$abmEstado = new AbmCompraEstado();

$items = $abmItem->buscar(['idcompra' => $idCompra]);
$estadoActual = $abmEstado->obtenerEstadoActual($idCompra);
$estadoDesc = $estadoActual ? $estadoActual->getObjCompraEstadoTipo()->getCeTDescripcion() : 'desconocido';

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5">
    <h2>Detalle de Compra #<?= $compra->getIdCompra() ?></h2>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($compra->getObjUsuario()->getUsNombre()) ?></p>
    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i:s', strtotime($compra->getCoFecha())) ?></p>
    <p><strong>Estado actual:</strong> 
        <span class="badge bg-primary"><?= ucfirst($estadoDesc) ?></span>
    </p>

    <h4 class="mt-4">Productos</h4>
    <table class="table">
        <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php $total = 0; foreach ($items as $item):
                $prod = $item->getObjProducto();
                $subtotal = $prod->getProPrecio() * $item->getCiCantidad();
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($prod->getProNombre()) ?></td>
                <td><?= $item->getCiCantidad() ?></td>
                <td>$<?= number_format($prod->getProPrecio(), 0, ',', '.') ?></td>
                <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-success">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td><strong>$<?= number_format($total, 0, ',', '.') ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Cambiar estado -->
    <?php if (!in_array($estadoDesc, ['enviada', 'cancelada'])): ?>
    <div class="mt-4">
        <form action="accion/cambiarEstadoCompra.php" method="post" class="d-inline">
            <input type="hidden" name="idcompra" value="<?= $compra->getIdCompra() ?>">
            <select name="nuevoestado" class="form-select d-inline w-auto" required>
                <option value="">Cambiar estado...</option>
                <option value="2">Aceptada</option>
                <option value="3">Enviada</option>
                <option value="4">Cancelar compra</option>
            </select>
            <button type="submit" class="btn btn-primary ms-2">Actualizar</button>
        </form>
    </div>
    <?php endif; ?>

    <a href="listarCompras.php" class="btn btn-secondary mt-3">Volver</a>
</div>

<?php include_once '../estructura/pie.php'; ?>