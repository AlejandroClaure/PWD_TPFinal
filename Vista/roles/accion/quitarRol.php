<?php
// Vista/admin/roles/accion/quitarRol.php

ob_clean();
session_start();

// Subir 4 niveles para llegar a la raíz
require_once dirname(__DIR__, 3) . '/configuracion.php';

// Verificar sesión y rol de admin CORRECTAMENTE
$session = new Session();
$usuario = $session->getUsuario();

if (!$usuario) {
    header("Location: ../../login/login.php");
    exit;
}

// Obtener los roles del usuario logueado (tu método real)
$abmUR = new AbmUsuarioRol();
$rolesUsuario = $abmUR->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("admin", $rolesUsuario)) {
    header("Location: ../../../error/noAutorizado.php");
    exit;
}

// Procesar el quitar rol
$idusuario = $_POST['idusuario'] ?? null;
$idrol     = $_POST['idrol'] ?? null;

if ($idusuario && $idrol) {
    $exito = $abmUR->quitarRol($idusuario, $idrol);
    $_SESSION['mensaje'] = $exito 
        ? "Rol quitado correctamente." 
        : "Error al quitar el rol.";
} else {
    $_SESSION['mensaje'] = "Faltan datos para quitar el rol.";
}

// Volver al panel
header("Location: ../panelRoles.php");
exit;