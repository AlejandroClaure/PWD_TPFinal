<?php
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

$session = new Session();

// Si no está logueado o no es cliente → al login
if (!$session->activa() || !$session->tieneRol('cliente')) {
    header("Location: {$GLOBALS['VISTA_URL']}login/login.php");
    exit;
}

$idCompra = (int)($_GET['id'] ?? 0);

if ($idCompra <= 0) {
    header("Location: {$GLOBALS['VISTA_URL']}verCompraCliente.php?msg=error_id");
    exit;
}

$abm = new AbmCompra();
$ok  = $abm->cancelarCompra($idCompra);

$msg = $ok ? 'cancel_ok' : 'cancel_fail';
header("Location: {$GLOBALS['VISTA_URL']}detalleCompra.php?id={$idCompra}&msg={$msg}");
exit;