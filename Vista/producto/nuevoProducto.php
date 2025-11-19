<?php
require_once "../../configuracion.php";
$session = new Session();

if (!$session->tieneRol("vendedor")) {
    die("No autorizado.");
}

include_once "../estructura/cabecera.php";
?>

<div class="container mt-4">
    <h2>Nuevo producto</h2>

    <form method="post" action="accion/accionNuevoProducto.php">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="pronombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Detalle</label>
            <textarea name="prodetalle" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="procantstock" class="form-control" required>
        </div>

        <button class="btn btn-success">Guardar producto</button>
    </form>
</div>

<?php include_once "../estructura/pie.php"; ?>
