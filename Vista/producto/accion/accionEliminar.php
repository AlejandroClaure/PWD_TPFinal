<?php
require_once "../../configuracion.php";

$session = new Session();
if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No autorizado.");
}

$idproducto = $_POST["idproducto"];

$abm = new AbmProducto();
$producto = $abm->buscarPorId($idproducto);

// Vendedor no puede borrar productos ajenos
if ($session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    if ($producto->getIdUsuario() != $session->getUsuario()->getIdUsuario()) {
        die("No autorizado.");
    }
}

$abm->eliminar($idproducto);

header("Location: listarMisProductos.php");
exit;
