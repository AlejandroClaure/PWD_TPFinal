<?php
include_once '../../configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraEstado.php';

$session = new Session();
if (!$session->activa() || !$session->tieneRol('admin')) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php");
    exit;
}

$abmCompra = new AbmCompra();
$abmEstado = new AbmCompraEstado();

$todasLasCompras = $abmCompra->buscar([]); // todas las compras

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5">
    <h2>Gestión de Compras</h2>

    <?php if (empty($todasLasCompras)): ?>
        <p class="text-muted">No hay compras registradas.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado Actual</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todasLasCompras as $compra): 
                        $estado = $abmEstado->obtenerEstadoActual($compra->getIdCompra());
                        $tipoEstado = $estado ? $estado->getObjCompraEstadoTipo()->getCeTDescripcion() : 'desconocido';
                        $items = (new AbmCompraItem())->buscar(['idcompra' => $compra->getIdCompra()]);
                        $total = 0;
                        foreach ($items as $item) {
                            $prod = $item->getObjProducto();
                            $total += $prod->getProPrecio() * $item->getCiCantidad();
                        }
                    ?>
                    <tr>
                        <td><strong>#<?= $compra->getIdCompra() ?></strong></td>
                        <td><?= htmlspecialchars($compra->getObjUsuario()->getUsNombre()) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($compra->getCoFecha())) ?></td>
                        <td>
                            <span class="badge 
                                <?= $tipoEstado=='iniciada'?'bg-warning':
                                    ($tipoEstado=='aceptada'?'bg-primary':
                                    ($tipoEstado=='enviada'?'bg-success':'bg-danger')) ?>">
                                <?= ucfirst($tipoEstado) ?>
                            </span>
                        </td>
                        <td>$<?= number_format($total, 0, ',', '.') ?></td>
                        <td>
                            <a href="verCompra.php?id=<?= $compra->getIdCompra() ?>" class="btn btn-sm btn-info">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../estructura/pie.php'; ?>