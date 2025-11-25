<?php
require_once "../../../configuracion.php";

session_start();

$session = new Session();
if (!$session->activa()) die("No login");

$usuario  = $session->getUsuario();
$idUsuario = $usuario->getIdUsuario();
// 2) Controladores
$abmCompra = new AbmCompra();
$abmItem   = new AbmCompraItem();
$abmEstado = new AbmCompraEstado();
// 3) Crear compra 
$compra = $abmCompra->alta([
    "cofecha" => date('Y-m-d H:i:s'),
    "idusuario" => $idUsuario
]);
if (!$compra) die("Error al crear compra.");
$idCompraNueva = $compra->getIdCompra();
// 4) Transferir items del carrito
$ok = $abmItem->transferirCarritoACompra($idUsuario, $idCompraNueva);
if (!$ok) die("Error al transferir los items.");
// 5) Obtener items reales para PDF
$itemsCompra = $abmItem->buscar(['idcompra' => $idCompraNueva]);
// 6) PDF comprobante
$rutaPDF = $abmCompra->generarComprobantePDF($compra, $itemsCompra);
// 7) Estado = iniciada
$abmEstado->alta([
    "idcompra" => $idCompraNueva,
    "idcompraestadotipo" => COMPRA_ESTADO_INICIADA,
    "cefechaini" => date("Y-m-d H:i:s")
]);
// 8) Vaciar carrito origen
$abmItem->vaciarCarrito($idUsuario);
$_SESSION['carrito'] = [];
// 9) Redirigir
header("Location: ../compra_exitosa.php?id=$idCompraNueva");
exit;
