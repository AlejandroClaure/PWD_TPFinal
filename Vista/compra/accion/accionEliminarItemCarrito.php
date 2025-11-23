<?php
// acciones/carrito/accionEliminarItemCarrito.php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $  $GLOBALS['CONTROL_PATH'] . 'AbmCompraItem.php';

session_start();

$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}

$usuario = $session->getUsuario();
$usuarioId = $usuario->getIdUsuario();

$idProducto = intval($_GET['id'] ?? 0);
if ($idProducto <= 0) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?error=1");
    exit;
}

$abm = new AbmCompraItem();
$eliminado = $abm->eliminarProducto($usuarioId, $idProducto);

$redirect = $_GET['redirect'] ?? 'compra/carrito.php';
header("Location: " . $GLOBALS['VISTA_URL'] . $redirect . ($eliminado ? "?ok=2" : "?error=2"));
exit;