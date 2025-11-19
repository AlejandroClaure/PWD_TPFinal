<?php
require_once "../../configuracion.php";
$session = new Session();

if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No autorizado");
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID no especificado.");

$abm = new AbmProducto();
$producto = $abm->buscarPorId($id);

if (!$producto) die("Producto inexistente.");

if ($session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    if ($producto->getIdUsuario() != $session->getUsuario()->getIdUsuario()) {
        die("No puede eliminar productos de otros usuarios.");
    }
}

include_once "../estructura/cabecera.php";
?>

<div class="container mt-4">
    <div class="alert alert-danger">
        <h4>¿Eliminar producto?</h4>
        <p><strong><?= $producto->getProNombre() ?></strong></p>
    </div>

    <form action="accionEliminar.php" method="post">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">

        <button class="btn btn-danger">Sí, eliminar</button>
        <a href="listarMisProductos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include_once "../estructura/pie.php"; ?>
