<?php
// Traemos la config general y las clases que necesitamos
include_once dirname(__DIR__,3) . '/configuracion.php';
include_once $GLOBALS['CONTROL_PATH'] . 'Session.php';
include_once $GLOBALS['CONTROL_PATH'] . 'AbmCompra.php';

// Creamos el objeto sesión (ya está iniciada por configuracion.php)
$session = new Session();

// Si el usuario no está logueado → lo mandamos al login con error 2
if (!$session->activa()) {
    header("Location: {$GLOBALS['VISTA_URL']}login/login.php?error=2");
    exit;                                 // cortamos todo acá
}

// Sacamos los parámetros que vienen por GET (id del producto, cantidad y a dónde volver)
$idProducto = (int)($_GET['id'] ?? 0);     // si no viene id → 0
$cantidad   = (int)($_GET['cantidad'] ?? 1); // si no viene cantidad → 1
$redirect   = $_GET['redirect'] ?? 'compra/carrito.php'; // página a la que vuelve después

// Validación rápida: si el id o la cantidad son inválidos → error en la ficha del producto
if ($idProducto <= 0 || $cantidad <= 0) {
    header("Location: {$GLOBALS['VISTA_URL']}producto/producto.php?error=1");
    exit;
}

// Instanciamos el ABM del carrito y ejecutamos la acción (acá está toda la magia)
$abm = new AbmCompra();
$ok  = $abm->agregarAlCarrito(
        $session->getUsuario()->getIdUsuario(),  // id del usuario logueado
        $idProducto,
        $cantidad
);

// Armamos el parámetro que va en la URL (?ok=1 o ?error=3)
$param = $ok ? 'ok=1' : 'error=3';

// Redirigimos al lugar que nos pidió el usuario (o al carrito por defecto)
header("Location: {$GLOBALS['VISTA_URL']}{$redirect}?{$param}");
exit;   // chau, misión cumplida