<?php
require_once "../../configuracion.php";
$session = new Session();

// Debe ser admin o vendedor
if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No autorizado");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID producto no especificado.");
}

$abm = new AbmProducto();
$producto = $abm->buscarPorId($id);

if (!$producto) {
    die("Producto no encontrado.");
}

// Si es vendedor, solo puede editar sus propios productos
if ($session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    if ($producto->getIdUsuario() != $session->getUsuario()->getIdUsuario()) {
        die("No puede editar productos de otros usuarios.");
    }
}

include_once "../estructura/cabecera.php";
?>

<div class="container mt-4">
    <h2>Editar Producto</h2>

    <form action="accionEditar.php" method="post">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="pronombre" 
                value="<?= $producto->getProNombre() ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Detalle</label>
            <textarea class="form-control" name="prodetalle" required><?= $producto->getProDetalle() ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="procantstock" 
                value="<?= $producto->getProCantStock() ?>" required>
        </div>

        <button class="btn btn-success">Guardar cambios</button>
        <a href="listarMisProductos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include_once "../estructura/pie.php"; ?>
