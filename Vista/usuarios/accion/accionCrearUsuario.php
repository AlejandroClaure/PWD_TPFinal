<?php
require_once '../../../configuracion.php';

$abm = new AbmUsuario();

$param = [
    "usnombre" => $_POST["usnombre"],
    "usmail"   => $_POST["usmail"],
    "uspass"   => $_POST["uspass"]
];

$abm->alta($param);

// Redirige al panel
header("Location: ../panelUsuarios.php?ok=1");
exit;
