<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';

$session = new Session();
if (!$session->getUsuario() || !(new AbmUsuarioRol())->esAdmin($session->getUsuario()->getIdUsuario())) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

$abm = new AbmProducto();
$producto = $abm->buscarPorId($id);

if (!$producto) {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

$exito = $producto->getProDeshabilitado()
    ? $abm->habilitar($id)
    : $abm->eliminar($id);

header("Location: ../../menus/gestionMenus.php?toggle=1");
exit;