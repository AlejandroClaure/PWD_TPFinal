<?php
// acciones/carrito/accionAgregarItemCarrito.php
include_once dirname(__DIR__, 3) . '/configuracion.php';
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

$idProducto = intval($_GET['id'] ?? 0);
$cantidad   = intval($_GET['cantidad'] ?? 1);

if ($idProducto <= 0 || $cantidad <= 0) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "producto/producto.php?error=1");
    exit;
}

$abm = new AbmCompraItem();
$ok = $abm->agregarProducto($usuarioId, $idProducto, $cantidad);

$redirect = $_GET['redirect'] ?? 'compra/carrito.php';
header("Location: " . $GLOBALS['VISTA_URL'] . $redirect . ($ok ? "?ok=1" : "?error=3"));
exit;