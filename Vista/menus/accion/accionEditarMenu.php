<?php
require_once dirname(__DIR__, 3) . '/configuracion.php';
require_once $GLOBALS['CONTROL_PATH'] . 'AbmMenu.php';

$abmMenu = new AbmMenu();
// Validación básica
if (empty($_POST['idmenu']) || trim($_POST['menombre']) === "") {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

$exito = $abmMenu->editarMenu($_POST);

header("Location: ../../menus/gestionMenus.php?ok=" . ($exito ? 1 : 0));
exit;
