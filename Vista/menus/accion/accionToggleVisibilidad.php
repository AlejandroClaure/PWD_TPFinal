<?php
include_once dirname(__DIR__, 3) . "/configuracion.php";
include_once dirname(__DIR__, 3) . "/Control/AbmMenu.php";

$id = $_GET["idmenu"] ?? null;

if (!$id) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$abm = new AbmMenu();
$menuArr = $abm->buscar(["idmenu" => $id]);

if (empty($menuArr)) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$menu = $menuArr[0];

// Detectar correctamente si está deshabilitado
$estaDeshabilitado = ($menu->getMeDeshabilitado() !== "0000-00-00 00:00:00");

// Toggle
$nuevoEstado = $estaDeshabilitado ? 0 : 1;

// Datos completos para modificar
$datos = [
    "idmenu"        => $menu->getIdMenu(),
    "menombre"      => $menu->getMeNombre(),
    "melink"        => $menu->getMeLink(),
    "medescripcion" => $menu->getMeDescripcion(),
    "idpadre"       => $menu->getObjMenuPadre() ? $menu->getObjMenuPadre()->getIdMenu() : null,
    "medeshabilitado" => $nuevoEstado
];

$abm->modificar($datos);

header("Location: ../gestionMenus.php?toggle=1");
exit;
