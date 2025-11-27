<?php
// Vista/menus/accion/accionEliminarMenu.php

include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmMenu.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';

// === Seguridad: solo admin puede eliminar menús ===

$session = new Session();
$abmMenu = new AbmMenu();
$abmMenu->eliminarMenus($session);