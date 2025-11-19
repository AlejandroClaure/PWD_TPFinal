<?php
include_once dirname(__DIR__, 4) . "/configuracion.php";
include_once dirname(__DIR__, 4) . "/Control/AbmMenu.php";

$id = $_GET["idmenu"] ?? null;

if (!$id) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$abm = new AbmMenu();
$menu = $abm->buscar(["idmenu" => $id])[0];

$nuevoEstado = ($menu->getMeDeshabilitado() == 1) ? 0 : 1;

$datos = [
    "idmenu" => $menu->getIdMenu(),
    "menombre" => $menu->getMeNombre(),
    "medescripcion" => $menu->getMeDescripcion(),
    "idpadre" => $menu->getObjMenuPadre() ? $menu->getObjMenuPadre()->getIdMenu() : null,
    "medeshabilitado" => $nuevoEstado
];

$abm->modificacion($datos);

header("Location: ../gestionMenus.php?toggle=1");
exit;
