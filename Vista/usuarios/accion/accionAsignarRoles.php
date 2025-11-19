<?php
require_once '../../../configuracion.php';

$idusuario = $_POST["idusuario"];
$rolesEnviados = $_POST["roles"] ?? [];

$abmUsuarioRol = new AbmUsuarioRol();

// Primero borro todos los roles del usuario
$rolesActuales = $abmUsuarioRol->rolesDeUsuario($idusuario);

foreach ($rolesActuales as $desc) {
    // buscar id del rol
    $rol = (new AbmRol())->buscar(["rodescripcion" => $desc])[0];
    $abmUsuarioRol->quitarRol($idusuario, $rol->getIdRol());
}

// Agregar los nuevos
foreach ($rolesEnviados as $idrol) {
    $abmUsuarioRol->asignarRol($idusuario, $idrol);
}

header("Location: panelUsuarios.php?roles=1");
exit;
