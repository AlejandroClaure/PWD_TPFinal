<?php
require_once '../../../../configuracion.php';

$rol = new Rol();
$rol->setRoDescripcion($_POST['rodescripcion']);
$rol->insertar();

header("Location: ../panelRoles.php");
exit;
