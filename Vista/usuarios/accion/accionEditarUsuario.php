<?php
require_once '../../../configuracion.php';

$abm = new AbmUsuario();

$param = [
    "idusuario" => $_POST["idusuario"],
    "usnombre" => $_POST["usnombre"],
    "usmail"   => $_POST["usmail"]
];

// Modifica SIN tocar contraseña
$abm->modificacion($param);

header("Location: ../panelUsuarios.php?edit=1");
exit;
