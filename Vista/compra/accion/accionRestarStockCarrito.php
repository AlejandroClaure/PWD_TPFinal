<?php
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

$session = new Session();
if (!$session->activa()) { header("Location: {$GLOBALS['VISTA_URL']}login/login.php?error=2"); exit; }

$idProducto = (int)($_GET['id'] ?? 0);
$redirect   = $_GET['redirect'] ?? 'compra/carrito.php';

if ($idProducto <= 0) {
    header("Location: {$GLOBALS['VISTA_URL']}compra/carrito.php?error=1");
    exit;
}

$abm = new AbmCompra();
$abm->restarCantidad($session->getUsuario()->getIdUsuario(), $idProducto);

header("Location: {$GLOBALS['VISTA_URL']}{$redirect}");
exit;