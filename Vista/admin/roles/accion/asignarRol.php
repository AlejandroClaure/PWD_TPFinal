<?php
require_once '../../../../configuracion.php';

$abmUR = new AbmUsuarioRol();
$abmUR->asignarRol($_POST['idusuario'], $_POST['idrol']);

header("Location: ../panelRoles.php");
exit;
