<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../configuracion.php';
require_once '../../Modelo/Venta.php';
require_once $GLOBALS['CONTROL_PATH'] . 'Session.php';

$session = new Session();
if (!$session->activa()) header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: No se encontró el número de operación."; exit;
}

$idventa = intval($_GET['id']);
$usuario = $session->getUsuario();
$rutaPDF = $GLOBALS['ROOT_PROYECTO'] . "Archivos/ventas/comprobante_pedido_$idventa.pdf";

include_once '../estructura/cabecera.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="text-success">✔ ¡Compra iniciada con éxito!</h2>
    <p class="lead">Estimado/a <b><?= htmlspecialchars($usuario->getUsNombre()) ?></b>,</p>
    <p>Su pedido ha sido registrado correctamente. Número de operación: <b><?= $idventa ?></b></p>
    <hr>
    <?php if (file_exists($rutaPDF)): ?>
        <a class="btn btn-primary btn-lg mt-3" href="<?= $GLOBALS['BASE_URL'] ?>Archivos/ventas/comprobante_pedido_<?= $idventa ?>.pdf" target="_blank">
            📄 Descargar Comprobante (PDF)
        </a>
    <?php else: ?>
        <div class="alert alert-danger">❌ No se encontró el comprobante en el servidor.</div>
    <?php endif; ?>
    <br><br>
    <a class="btn btn-secondary btn-lg" href="<?= $GLOBALS['BASE_URL'] ?>index.php">Volver al inicio</a>
</div>

<?php include_once '../estructura/pie.php'; ?>
