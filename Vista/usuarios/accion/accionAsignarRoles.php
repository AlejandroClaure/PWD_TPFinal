<?php
require_once '../../../configuracion.php';

$idusuario = $_POST["idusuario"];
$rolesEnviados = $_POST["roles"] ?? [];

$abmUsuarioRol = new AbmUsuarioRol();
$abmUsuarioRol->accionAsignarRoles($abmUsuarioRol, $rolesEnviados, $idusuario);

