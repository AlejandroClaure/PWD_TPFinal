<?php
require_once '../../../configuracion.php';

$rol = new Rol();
$rol->setIdRol($_POST['idrol']);
$rol->setRoDescripcion($_POST['rodescripcion']);
$rol->modificar();

header("Location: ../panelRoles.php");
exit;
