<?php
require_once "../../configuracion.php";

$session = new Session();
if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No autorizado.");
}

$idproducto = $_POST["idproducto"];
$abm = new AbmProducto();

// Obtener producto actual
$producto = $abm->buscarPorId($idproducto);

// Validación: un vendedor NO puede editar productos ajenos
if ($session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    if ($producto->getIdUsuario() != $session->getUsuario()->getIdUsuario()) {
        die("No tiene permiso para editar este producto.");
    }
}

// Actualizar datos
$param = [
    "idproducto"   => $idproducto,
    "pronombre"    => $_POST["pronombre"],
    "prodetalle"   => $_POST["prodetalle"],
    "procantstock" => $_POST["procantstock"],
    "idusuario"    => $producto->getIdUsuario() // no se cambia
];

$abm->modificar($param);

header("Location: listarMisProductos.php");
exit;
