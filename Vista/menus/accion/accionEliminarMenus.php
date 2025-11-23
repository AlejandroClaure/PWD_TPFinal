<?php
// Vista/menus/accion/accionEliminarMenu.php

include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmMenu.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';

// === Seguridad: solo admin puede eliminar menús ===
session_start();
$session = new Session();
if (!$session->activa() || !$session->tieneRol('admin')) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php");
    exit;
}

// === Obtener ID y validar ===
$idmenu = intval($_GET['idmenu'] ?? 0);
if ($idmenu <= 0) {
    header("Location: ../gestionMenus.php?error=1");
    exit;
}

// === Ejecutar eliminación completa (todo en el ABM) ===
$abmMenu = new AbmMenu();
$exito = $abmMenu->eliminarMenuCompleto($idmenu);

// === Redirigir con mensaje ===
header("Location: ../gestionMenus.php?" . ($exito ? "ok=1" : "error=2"));
exit;