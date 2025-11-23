<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';
include_once dirname(__DIR__, 3) . '/Control/AbmProducto.php';

$id = intval($_POST['idproducto'] ?? 0);
$cantidad = intval($_POST['cantidad'] ?? 1);

if ($id > 0 && $cantidad > 0) {
    $abm = new AbmProducto();
    $producto = $abm->buscarPorId($id);
    if ($producto && $producto->getProCantStock() >= $cantidad) {
        $nuevoStock = $producto->getProCantStock() - $cantidad;
        $datos = [
            'idproducto'   => $id,
            'pronombre'    => $producto->getProNombre(),
            'prodetalle'   => $producto->getProDetalle(),
            'proprecio'    => $producto->getProPrecio(),
            'procantstock' => $nuevoStock,
            'idusuario'    => $producto->getIdUsuario(),
            'proimagen'    => $producto->getProimagen()
        ];
        $abm->modificar($datos);
    }
}
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../../menus/gestionMenus.php"));
exit;