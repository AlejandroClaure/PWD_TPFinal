<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraEstado.php';

$session = new Session();
if (!$session->activa() || !$session->tieneRol('admin')) {
    exit;
}

$idCompra = intval($_POST['idcompra'] ?? 0);
$nuevoEstado = intval($_POST['nuevoestado'] ?? 0);

if ($idCompra > 0 && in_array($nuevoEstado, [2,3,4])) {
    $abm = new AbmCompraEstado();
    $abm->cambiarEstadoCompra($idCompra, $nuevoEstado);
}

header("Location: ../verCompraAdmin.php?id=" . $idCompra);
exit;