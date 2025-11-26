<?php
require_once '../../../configuracion.php';

error_log("Datos recibidos: " . print_r($_POST, true));

$auth = new AbmAuth();
$resultado = $auth->registrarYLogin($_POST);

if ($resultado === true) {
    error_log("Entró a crear usuario");
    header("Location: ../login.php?ok=1");
    exit;
}

if ($resultado === "email_duplicado") {
    header("Location: ../login.php?email_duplicado=1");
    exit;
}

// Error general
error_log("Error al crear el usuario");
header("Location: ../login.php?error=1");
exit;