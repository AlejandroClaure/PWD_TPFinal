<?php
require_once '../../../configuracion.php';

$id = $_GET["id"];

$abm = new AbmUsuario();
$abm->baja(["idusuario" => $id]);

header("Location: panelUsuarios.php?del=1");
exit;
