<?php
include_once '../../configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraItem.php';

session_start();

$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}

$usuario = $session->getUsuario();
$usuarioId = $usuario->getIdUsuario();

// Cargar carrito desde BD
$abmItem = new AbmCompraItem();
list($compra, $carrito) = $abmItem->obtenerCompraYCarrito($usuarioId);

// Guardar en sesión (útil para otras vistas rápidas)
$_SESSION['carrito'] = $carrito;

// Calcular totales
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 30000 ? 0 : ($subtotal > 0 ? 5000 : 0); // envío gratis si supera $30.000 (opcional)
$total = $subtotal + $envio;

$imgDir = $GLOBALS['VISTA_PATH'] . "imagenes/productos/";
$imgBaseUrl = $GLOBALS['IMG_URL'] . "productos/";


include_once '../estructura/cabecera.php';
?>

<main class="mt-5 pt-5">
<div class="container mt-5">
    <h2 class="mb-4">
        <i class="bi bi-cart3"></i> Mi Carrito 
        <?php if (!empty($carrito)): ?>
            <span class="badge bg-primary"><?= count($carrito) ?> producto<?= count($carrito)>1?'s':'' ?></span>
        <?php endif; ?>
    </h2>

    <?php if (empty($carrito)): ?>
        <!----------------------------------- CARRITO VACÍO ----------------------------------->
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <p class="lead mt-3">Tu carrito está vacío</p>
            <a href="../producto/producto.php" class="btn btn-primary btn-lg">Ir a Comprar</a>
        </div>

    <?php else: ?>
        <!----------------------------------- CARRITO CON PRODUCTOS ----------------------------------->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Precio</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrito as $id => $item): 
                        $imagenURL = (!empty($item['imagen']) && file_exists($imgDir . $item['imagen']))
                            ? $imgBaseUrl . $item['imagen']
                            : $imgBaseUrl . "no-image.jpeg";
                        $puedeSumar = $item['cantidad'] < $item['stock'];
                    ?>
                    <tr>
                        <!-- Producto -->
                        <td class="d-flex align-items-center">
                            <img src="<?= $imagenURL ?>" alt="<?= htmlspecialchars($item['nombre']) ?>" 
                                 class="me-3 rounded" style="width:70px; height:70px; object-fit:cover;">
                            <div>
                                <strong><?= htmlspecialchars($item['nombre']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($item['detalle']) ?></small>
                            </div>
                        </td>

                        <!-- Precio unitario -->
                        <td class="text-center">
                            $<?= number_format($item['precio'], 0, ',', '.') ?>
                        </td>

                        <!-- Cantidad + controles -->
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="accion/accionRestarStockCarrito.php?id=<?= $id ?>"
                                   class="btn btn-outline-secondary btn-sm">–</a>
                                
                                <span class="btn btn-light btn-sm fw-bold"><?= $item['cantidad'] ?></span>
                                
                                <a href="accion/accionSumarStockCarrito.php?id=<?= $id ?>"
                                   class="btn btn-outline-secondary btn-sm <?= !$puedeSumar ? 'disabled' : '' ?>"
                                   title="<?= !$puedeSumar ? 'Sin stock disponible' : 'Sumar uno' ?>">+</a>
                            </div>
                            <div class="text-muted small mt-1">
                                Stock: <?= $item['stock'] ?>
                                <?= !$puedeSumar ? ' <span class="text-danger">(máximo)</span>' : '' ?>
                            </div>
                        </td>

                        <!-- Subtotal -->
                        <td class="text-end fw-bold">
                            $<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?>
                        </td>

                        <!-- Eliminar -->
                        <td class="text-center">
                            <a href="accion/accionEliminarItemCarrito.php?id=<?= $id ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar este producto del carrito?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!----------------------------------- RESUMEN DE COMPRA ----------------------------------->
        <div class="card mt-4">
            <div class="card-body">
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-borderless">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">$<?= number_format($subtotal, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Envío <?= $envio == 0 ? '<span class="text-success">(Gratis!)</span>' : '' ?></td>
                                <td class="text-end">$<?= number_format($envio, 0, ',', '.') ?></td>
                            </tr>
                            <?php if ($envio > 0): ?>
                            <tr>
                                <td colspan="2" class="text-end text-muted small">
                                    ¡Envío gratis a partir de $30.000!
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="fs-4 fw-bold">
                                <td>Total a pagar</td>
                                <td class="text-end text-success">$<?= number_format($total, 0, ',', '.') ?></td>
                            </tr>
                        </table>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="../producto/producto.php" class="btn btn-outline-dark btn-lg">
                                Seguir comprando
                            </a>
                            <a href="../compra/accion/accionFinalizarCompra.php" class="btn btn-success btn-lg px-5">
                                Finalizar Compra
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--- Opción de vaciar carrito (opcional) -->
        <div class="text-center mt-4">
            <a href="accion/accionVaciarCarrito.php" 
               class="text-danger small" 
               onclick="return confirm('¿Vaciar todo el carrito?')">
                Vaciar carrito
            </a>
        </div>
    <?php endif; ?>
</div>
</main>

<?php include_once '../estructura/pie.php'; ?>