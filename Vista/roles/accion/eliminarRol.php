<?php
require_once '../../../configuracion.php';

$rol = new Rol();
$rol->setIdRol($_GET['idrol']);
$rol->eliminar();

header("Location: ../panelRoles.php");
exit;
