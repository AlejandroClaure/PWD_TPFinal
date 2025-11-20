<?php

include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/Session.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';

// Mostrar todos los errores durante desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);


//      VALIDAR SESIÓN
$session = new Session();
if (!$session->activa()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
    exit;
}


//      RECIBIR DATOS
$idProducto = $_GET['id'] ?? null;
$cantidad   = $_POST['cantidad'] ?? 1; // si quieres permitir seleccionar cantidad

if (!$idProducto || !is_numeric($idProducto)) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "producto/albumProductos.php?error=1");
    exit;
}


//      OBTENER PRODUCTO
$abmProducto = new AbmProducto();
$producto = $abmProducto->buscarPorId($idProducto);

if (!$producto || $producto->getProDeshabilitado()) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "producto/albumProductos.php?error=2");
    exit;
}


//      INICIALIZAR CARRITO EN SESSION
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}


//      AGREGAR PRODUCTO AL CARRITO
$carrito =& $_SESSION['carrito'];

// Si el producto ya existe en el carrito, sumar cantidad
if (isset($carrito[$idProducto])) {
    $carrito[$idProducto]['cantidad'] += $cantidad;
} else {
    $carrito[$idProducto] = [
        'idproducto' => $producto->getIdProducto(),
        'nombre'     => $producto->getProNombre(),
        'precio'     => $producto->getProDetalle(),
        'cantidad'   => $cantidad,
    ];
}


//      REDIRECCIONAR AL CARRITO
header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?ok=1");
exit;
