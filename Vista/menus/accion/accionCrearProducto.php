<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';
include_once dirname(__DIR__, 3) . '/Control/AbmMenu.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';

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
$pronombre      = trim($_POST['pronombre'] ?? '');
$prodetalle      = trim($_POST['prodetalle'] ?? '');
$procantstock    = intval($_POST['procantstock'] ?? 0);
$categoria       = trim($_POST['categoria'] ?? '');

if ($pronombre === '' || $prodetalle === '' || $categoria === '' || $procantstock < 0) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

// ========================================
// Subida de imagen
// ========================================
$imagenNombre = null;

if (!empty($_FILES['proimagen']['name'])) {
    $ext = strtolower(pathinfo($_FILES['proimagen']['name'], PATHINFO_EXTENSION));
    $imagenNombre = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $pronombre)) . "." . $ext;

    $rutaCarpeta = __DIR__ . '/../../../imagenes/';
    if (!is_dir($rutaCarpeta)) mkdir($rutaCarpeta, 0777, true);

    $destino = $rutaCarpeta . $imagenNombre;

    if (!move_uploaded_file($_FILES['proimagen']['tmp_name'], $destino)) {
        header("Location: ../gestionMenus.php?ok=0");
        exit;
    }
}

// ========================================
// Controladores
// ========================================
$abmProducto = new AbmProducto();
$abmMenu     = new AbmMenu();

// Buscar menú de la categoría seleccionada
$menus = $abmMenu->buscar(['menombre' => $categoria]);
if (empty($menus)) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$menuActual = $menus[0];

// ========================================
// Armar prefijo jerárquico para el nombre
// ========================================
$cadenaCategorias = [];
$menuTemp = $menuActual;

while ($menuTemp) {
    $cadenaCategorias[] = strtolower($menuTemp->getMeNombre());
    $menuTemp = $menuTemp->getObjMenuPadre();
}

$cadenaCategorias = array_reverse($cadenaCategorias);

// Prefijo: padre[_sub]_  
$prefijo = implode('_', $cadenaCategorias) . '_';

// Nombre final para la BD
$pronombreBD = $prefijo . $pronombre;

// ========================================
// Crear producto
// ========================================
$datos = [
    'pronombre'     => $pronombreBD,
    'prodetalle'    => $prodetalle,
    'procantstock'  => $procantstock,
    'idusuario'     => $usuario->getIdUsuario(),
    'proimagen'     => $imagenNombre,
    'categoria'     => $categoria
];

$abmProducto->crear($datos);

// Volver a la gestión de menú con éxito
header("Location: ../gestionMenus.php?ok=1");
exit;
?>
