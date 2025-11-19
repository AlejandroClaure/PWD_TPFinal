<?php
require_once "../../configuracion.php";

$session = new Session();
if (!$session->tieneRol("vendedor")) {
    die("No autorizado.");
}

$param = [
    "pronombre" => $_POST["pronombre"],
    "prodetalle" => $_POST["prodetalle"],
    "procantstock" => $_POST["procantstock"],
    "idusuario" => $session->getUsuario()->getIdUsuario()
];

$abm = new AbmProducto();
$abm->crear($param);

header("Location: listarMisProductos.php");
