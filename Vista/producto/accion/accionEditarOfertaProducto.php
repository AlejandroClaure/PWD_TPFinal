<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';

$session = new Session();
if (!$session->getUsuario() || !(new AbmUsuarioRol())->esAdmin($session->getUsuario()->getIdUsuario())) {
    exit;
}

$id = intval($_POST['idproducto'] ?? 0);
$oferta = intval($_POST['prooferta'] ?? 0);
$finOferta = $_POST['profinoffer'] ?? null;
if ($finOferta === '') $finOferta = null;

if ($id <= 0) {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

$abm = new AbmProducto();
$producto = $abm->buscarPorId($id);

$datos = [
    'idproducto'   => $id,
    'pronombre'    => $producto->getProNombre(),
    'prodetalle'   => $producto->getProDetalle(),
    'proprecio'    => $producto->getProPrecio(),
    'procantstock' => $producto->getProCantStock(),
    'prooferta'    => $oferta,
    'profinoffer'  => $finOferta,
    'idusuario'    => $producto->getIdUsuario(),
    'proimagen'    => $producto->getProimagen()
];

$exito = $abm->modificar($datos);
header("Location: ../../menus/gestionMenus.php?ok=" . ($exito ? 1 : 0));
exit;