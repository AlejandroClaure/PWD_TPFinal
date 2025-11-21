<?php
require_once '../../../configuracion.php';

$abm = new AbmUsuario();

// Tomo datos del formulario
$param = [];
$param["usnombre"] = $_POST["usnombre"];
$param["usmail"]   = $_POST["usmail"];
$param["uspass"]   = $_POST["uspass"];

// Registrar usuario 
$idUsuario = $abm->registrar($param);

if ($idUsuario !== false) {

    // Asigno rol cliente = idrol 2
    $abmUR = new AbmUsuarioRol();
    $abmUR->asignarRol($idUsuario, 2);

    // Redirección si todo salió bien
    header("Location: " . $GLOBALS['BASE_URL'] . "Vista/login/login.php?ok=1");
    exit;
}

// Si algo falló, envío error
header("Location: " . $GLOBALS['BASE_URL'] . "Vista/login/registro.php?error=1");
exit;
