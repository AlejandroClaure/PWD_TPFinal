<?php
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

$session = new Session();
if (!$session->activa() || !$session->tieneRol('admin')) {
    exit; 
}

$idCompra    = (int)($_POST['idcompra'] ?? 0);
$nuevoEstado = (int)($_POST['nuevoestado'] ?? 0);

$abm = new AbmCompra();
$abm->cambiarEstadoCompra($idCompra, $nuevoEstado);

header("Location: {$GLOBALS['VISTA_URL']}compra/verCompraAdmin.php?id={$idCompra}");
exit;