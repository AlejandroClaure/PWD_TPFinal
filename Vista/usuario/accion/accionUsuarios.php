<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../configuracion.php';

$accion = $_REQUEST['accion'] ?? null;
$controller = new GestionUsuariosControl();
$controller->accionUsuarios($accion,$controller);
