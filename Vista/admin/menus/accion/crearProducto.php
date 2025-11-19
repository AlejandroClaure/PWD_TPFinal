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

// ========================================
// Datos del formulario
// ========================================
$pronombre = trim($_POST['pronombre'] ?? '');
$prodetalle = trim($_POST['prodetalle'] ?? '');
$procantstock = intval($_POST['procantstock'] ?? 0);
$categoria = trim($_POST['categoria'] ?? '');

if ($pronombre === '' || $prodetalle === '' || $categoria === '' || $procantstock < 0) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

// ========================================
// SUBIDA DE IMAGEN
// ========================================
$imagenNombre = null;

if (!empty($_FILES['proimagen']['name'])) {

    // Extensión del archivo
    $ext = strtolower(pathinfo($_FILES['proimagen']['name'], PATHINFO_EXTENSION));

    // Nombre seguro del archivo
    $imagenNombre = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $pronombre)) . "." . $ext;

    // Carpeta de destino real
    $rutaCarpeta = __DIR__ . '/../../../imagenes/';

    // Crear carpeta si no existe
    if (!is_dir($rutaCarpeta)) {
        mkdir($rutaCarpeta, 0777, true);
    }

    // Ruta completa de destino
    $destino = $rutaCarpeta . $imagenNombre;

    // Mover archivo
    if (!move_uploaded_file($_FILES['proimagen']['tmp_name'], $destino)) {
        header("Location: ../gestionMenus.php?ok=0");
        exit;
    }
}

// ========================================
// CONTROLADORES
// ========================================
$abmProducto = new AbmProducto();
$abmMenu = new AbmMenu();

// Buscar el menú de la categoría seleccionada
$menus = $abmMenu->buscar(['menombre' => $categoria]);

if (empty($menus)) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$menuActual = $menus[0];

// ========================================
// ARMAR CADENA COMPLETA DE CATEGORÍAS
// ========================================
$cadenaCategorias = [];
$menuTemp = $menuActual;

while ($menuTemp) {
    $cadenaCategorias[] = strtolower($menuTemp->getMeNombre());
    $menuTemp = $menuTemp->getObjMenuPadre();
}

$cadenaCategorias = array_reverse($cadenaCategorias);

// Formato: celulares_iphone_
$prefijo = implode('_', $cadenaCategorias) . '_ ';

// Nombre final que se guarda en BD
$pronombreBD = $prefijo . $pronombre;

// ========================================
// CREAR PRODUCTO
// ========================================
$datos = [
    'pronombre' => $pronombre,
    'prodetalle' => $prodetalle,
    'procantstock' => $procantstock,
    'idusuario' => $usuario->getIdUsuario(),
    'proimagen' => $imagenNombre,
    'categoria' => $categoria
];

$abmProducto->crear($datos);

// Volver a alta sin salir del sistema
header("Location: ../gestionMenus.php?ok=1");
exit;
?>
