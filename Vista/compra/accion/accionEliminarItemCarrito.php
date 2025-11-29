<?php
// Cargamos config y clases necesarias
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

$session = new Session();

// Si no hay sesión → al login con error
if (!$session->activa()) {
    header("Location: {$GLOBALS['VISTA_URL']}login/login.php?error=2");
    exit;
}

$idProducto = (int)($_GET['id'] ?? 0);                    // producto a eliminar
$redirect   = $_GET['redirect'] ?? 'compra/carrito.php'; // a dónde volver

// Si no viene id válido → error en el carrito
if ($idProducto <= 0) {
    header("Location: {$GLOBALS['VISTA_URL']}compra/carrito.php?error=1");
    exit;
}

$abm = new AbmCompra();
$ok  = $abm->eliminarDelCarrito($session->getUsuario()->getIdUsuario(), $idProducto);

$param = $ok ? '?ok=2' : '?error=2';
header("Location: {$GLOBALS['VISTA_URL']}{$redirect}{$param}");
exit;