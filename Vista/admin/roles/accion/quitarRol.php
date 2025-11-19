<?php
require_once '../../../../configuracion.php';

$abmUR = new AbmUsuarioRol();
$abmUR->quitarRol($_GET['idusuario'], $_GET['idrol']);

header("Location: ../panelRoles.php");
exit;
