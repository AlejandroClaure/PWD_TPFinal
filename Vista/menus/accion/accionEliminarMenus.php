<?php
// Vista/menus/accion/accionEliminarMenu.php

include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmMenu.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';

// === Seguridad: solo admin puede eliminar menús ===

$session = new Session();
$abmMenu = new AbmMenu();
$resultado = $abmMenu->accionEliminarMenus($session);
// Decido la redirección según resultado
if (!$resultado['estado']) {

    switch ($resultado['error']) {
        case 'no_sesion':
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php");
            break;

        case 'no_admin':
            header("Location: ../gestionMenus.php?error=permiso");
            break;

        case 'id_invalido':
            header("Location: ../gestionMenus.php?error=id");
            break;
    }

    exit;
}

// Si todo salió bien
header("Location: ../gestionMenus.php?ok=1");
exit;