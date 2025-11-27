<?php
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';
$session = new Session();
$abmCompra = new AbmCompra();
$abmCompra->cambioEstadoCompra($session); // Le pasamos la sesión y listo