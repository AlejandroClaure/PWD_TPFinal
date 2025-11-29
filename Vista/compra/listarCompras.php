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

// 1. Obtenemos TODAS las compras
$todasLasCompras = $abmCompra->buscar([]);

// 2. Detectamos cuál es la compra más antigua (menor ID) de cada usuario → esa es el carrito
$carritoPorUsuario = []; // [idusuario => idcompra del carrito]

foreach ($todasLasCompras as $compra) {
    $idUsuario = $compra->getObjUsuario()->getIdUsuario();
    $idCompra  = $compra->getIdCompra();

    // Si no tengo ninguna registrada aún o esta es más antigua que la que tenía
    if (!isset($carritoPorUsuario[$idUsuario]) || $idCompra < $carritoPorUsuario[$idUsuario]) {
        $carritoPorUsuario[$idUsuario] = $idCompra;
    }
}

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5">
    <h2>Gestión de Compras</h2>

    <?php if (empty($todasLasCompras)): ?>
        <div class="alert alert-info">No hay compras registradas.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Compra</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado Actual</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todasLasCompras as $compra):
                        $idCompra  = $compra->getIdCompra();
                        $idUsuario = $compra->getObjUsuario()->getIdUsuario();

                        // OCULTAR la compra que funciona como carrito (la de menor ID por usuario)
                        if (isset($carritoPorUsuario[$idUsuario]) && $carritoPorUsuario[$idUsuario] === $idCompra) {
                            continue;
                        }

                        // Obtener estado actual
                        $estadoObj = $abmEstado->obtenerEstadoActual($idCompra);
                        $tipoEstado = $estadoObj 
                            ? $estadoObj->getObjCompraEstadoTipo()->getCeTDescripcion() 
                            : 'desconocido';

                        // Calcular total
                        $items = (new AbmCompraItem())->buscar(['idcompra' => $idCompra]);
                        $total = 0;
                        foreach ($items as $item) {
                            $producto = $item->getObjProducto();
                            $total += $producto->getProPrecio() * $item->getCiCantidad();
                        }
                    ?>
                        <tr>
                            <td><strong>#<?= $idCompra ?></strong></td>
                            <td><?= htmlspecialchars($compra->getObjUsuario()->getUsNombre()) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($compra->getCoFecha())) ?></td>
                            <td>
                                <span class="badge
                                    <?= $tipoEstado === 'iniciada' ? 'bg-warning text-dark' :
                                        ($tipoEstado === 'aceptada' ? 'bg-primary' :
                                        ($tipoEstado === 'enviada' ? 'bg-success' :
                                        ($tipoEstado === 'cancelada' ? 'bg-danger' : 'bg-secondary')))
                                    ?>">
                                    <?= ucfirst($tipoEstado) ?>
                                </span>
                            </td>
                            <td>$<?= number_format($total, 0, ',', '.') ?></td>
                            <td>
                                <a href="verCompraAdmin.php?id=<?= $idCompra ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver detalle
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