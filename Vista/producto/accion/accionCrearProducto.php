<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';

$session = new Session();
$usuario = $session->getUsuario();

if (!$usuario) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$datos = [
    'pronombre'     => $_POST['pronombre'] ?? '',
    'proprecio'     => $_POST['proprecio'] ?? 0,
    'procantstock'  => $_POST['procantstock'] ?? 0,
    'prodetalle'    => $_POST['prodetalle'] ?? '',
    'categoria'     => $_POST['categoria'] ?? '',
    'idusuario'     => $usuario->getIdUsuario(),
    'proimagen'     => $_FILES['proimagen'] ?? null
];

$abmProducto = new AbmProducto();

if ($abmProducto->crear($datos)) {
    header("Location: ../../menus/gestionMenus.php?ok=1");
} else {
    header("Location: ../../menus/gestionMenus.php?ok=0");
}
exit;
