<?php
require_once "../../configuracion.php";
$session = new Session();

// Solo admin o vendedor
if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No autorizado");
}

$id = intval($_GET['id'] ?? 0);
if (!$id) die("ID producto no especificado.");

$abm = new AbmProducto();
$producto = $abm->buscarPorId($id);
if (!$producto) die("Producto no encontrado.");

// Vendedor solo puede editar sus productos
if ($session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    if ($producto->getIdUsuario() != $session->getUsuario()->getIdUsuario()) {
        die("No puede editar productos de otros usuarios.");
    }
}

include_once "../estructura/cabecera.php";
?>

<div class="container mt-4">
    <h2>Editar Producto</h2>

    <form action="accion/accionEditarProducto.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="pronombre" 
                   value="<?= htmlspecialchars($producto->getProNombre()) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Detalle</label>
            <textarea class="form-control" name="prodetalle" required><?= htmlspecialchars($producto->getProDetalle()) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" class="form-control" name="proprecio" 
                   value="<?= $producto->getProPrecio() ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="procantstock" 
                   value="<?= $producto->getProCantStock() ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Oferta (%)</label>
            <input type="number" class="form-control" name="prooferta" 
                   value="<?= $producto->getProoferta() ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Fin de oferta</label>
            <input type="datetime-local" class="form-control" name="profinoffer" 
                   value="<?= $producto->getProFinOffer() ? date('Y-m-d\TH:i', strtotime($producto->getProFinOffer())) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" class="form-control" name="proimagen">
            <?php if ($producto->getProimagen()): ?>
                <img src="<?= $GLOBALS['VISTA_URL'] . 'imagenes/' . $producto->getProimagen() ?>" 
                     alt="Imagen" class="img-thumbnail mt-2" width="150">
            <?php endif; ?>
        </div>

        <button class="btn btn-success">Guardar cambios</button>
        <a href="listarMisProductos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include_once "../estructura/pie.php"; ?>
