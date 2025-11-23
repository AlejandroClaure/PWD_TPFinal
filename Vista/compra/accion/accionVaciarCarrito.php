<?php
// Vista/compra/accion/accionVaciarCarrito.php

// Subimos 3 niveles para llegar a la raíz del proyecto
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompraItem.php';

session_start();

// Verificar sesión
$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}

$usuarioId = $session->getUsuario()->getIdUsuario();

// Vaciar carrito
$abm = new AbmCompraItem();
$abm->vaciarCarrito($usuarioId); 

// Redirigir
header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?vaciado=1");
exit;