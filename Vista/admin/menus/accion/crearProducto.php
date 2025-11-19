<?php
include_once dirname(__DIR__, 4) . '/configuracion.php';
include_once dirname(__DIR__, 4) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 4) . '/Control/AbmMenu.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

if (!$usuario) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

// Datos del formulario
$pronombre = trim($_POST['pronombre'] ?? '');
$prodetalle = trim($_POST['prodetalle'] ?? '');
$procantstock = intval($_POST['procantstock'] ?? 0);
$categoria = trim($_POST['categoria'] ?? '');

if ($pronombre === '' || $prodetalle === '' || $categoria === '' || $procantstock < 0) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

// Subida de imagen
$imagenNombre = null;
if (!empty($_FILES['proimagen']['name'])) {
    $ext = strtolower(pathinfo($_FILES['proimagen']['name'], PATHINFO_EXTENSION));
    $imagenNombre = preg_replace('/[^a-zA-Z0-9_-]/', '', $pronombre) . "." . $ext;
    $destino = dirname(__DIR__, 3) . "/img/productos/" . $imagenNombre;
    move_uploaded_file($_FILES['proimagen']['tmp_name'], $destino);
}

// Instancias de control
$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Buscar menú de la categoría
$menus = $abmMenu->buscar(['menombre' => $categoria]);

if (empty($menus)) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$menuActual = $menus[0];

// ================================
// ARMAR CADENA COMPLETA DE CATEGORÍAS
// ================================
$cadenaCategorias = [];
$menuTemp = $menuActual;

while ($menuTemp) {
    $cadenaCategorias[] = strtolower($menuTemp->getMeNombre());
    $menuTemp = $menuTemp->getObjMenuPadre();
}

$cadenaCategorias = array_reverse($cadenaCategorias);

// Prefijo estilo: "celulares_iphone_ "
$prefijo = implode('_', $cadenaCategorias) . '_ ';

// Nombre real guardado en BD
$pronombreBD = $prefijo . $pronombre;

// ================================
// CREAR PRODUCTO
// ================================
$datos = [
    'pronombre' => $pronombreBD,
    'prodetalle' => $prodetalle,
    'procantstock' => $procantstock,
    'idusuario' => $usuario->getIdUsuario(),
    'proimagen' => $imagenNombre
];

$abmProducto->crear($datos);

header("Location: ../gestionMenus.php?ok=1");
exit;
?>
