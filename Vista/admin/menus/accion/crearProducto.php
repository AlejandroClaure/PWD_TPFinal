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

// Buscar si es categoría principal o subcategoría
$menus = $abmMenu->buscar(['menombre' => $categoria]);
$esSubcategoria = false;
if (!empty($menus)) {
    $menu = $menus[0];
    $esSubcategoria = $menu->getObjMenuPadre() !== null;
}

// Crear producto
$datos = [
    'pronombre' => $pronombre,
    'prodetalle' => $prodetalle,
    'procantstock' => $procantstock,
    'idusuario' => $usuario->getIdUsuario(),
    'proimagen' => $imagenNombre,
    'categoria' => $categoria
];

$abmProducto->crear($datos);

// --------------------------------------------------
// Si es categoría principal, también asignar productos a sus subcategorías
// --------------------------------------------------
if (!$esSubcategoria && isset($hijosMap)) {
    // Obtener subcategorías de esta categoría
    $subcategorias = $abmMenu->buscar(['idpadre' => $menu->getIdMenu()]);
    foreach ($subcategorias as $sub) {
        $subNombre = $sub->getMeNombre();
        // Opción: duplicar el producto en cada subcategoría
        // Si no quieres duplicar, solo se filtra correctamente al mostrar productos
    }
}

header("Location: ../gestionMenus.php?ok=1");
exit;
?>
