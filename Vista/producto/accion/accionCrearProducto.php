<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';

$session = new Session();
$abm = new AbmProducto();
$abm-> crearProducto($session);

