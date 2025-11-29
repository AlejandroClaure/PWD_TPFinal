<?php
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

$session = new Session();
if (!$session->activa()) { header("Location: {$GLOBALS['VISTA_URL']}login/login.php?error=2"); exit; }

$abm = new AbmCompra();
$abm->vaciarCarritoUsuario($session->getUsuario()->getIdUsuario());

header("Location: {$GLOBALS['VISTA_URL']}compra/carrito.php?vaciado=1");
exit;