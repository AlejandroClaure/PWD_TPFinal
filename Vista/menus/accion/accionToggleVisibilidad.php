<?php
include_once dirname(__DIR__, 3) . "/configuracion.php";
include_once dirname(__DIR__, 3) . "/Control/AbmMenu.php";

$id = $_GET["idmenu"] ?? null;

if (!$id) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$abm = new AbmMenu();
$abm->toggleVisibilidad($abm,$id);

