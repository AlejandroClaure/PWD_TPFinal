<?php
require_once dirname(__DIR__, 3) . '/configuracion.php';

$session = new Session();
$usuario = $session->getUsuario();

$abmUR = new AbmUsuarioRol();

$resultado = $abmUR->accionQuitarRol($abmUR,$usuario);

// Si hay error específico
if (!$resultado['estado']) {
    header("Location: ../panelRoles.php?error=1");
    exit;
}

// Si todo salió bien
header("Location: ../panelRoles.php?ok=1");
exit;