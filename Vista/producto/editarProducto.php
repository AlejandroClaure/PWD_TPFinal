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

    <!-- Editar Nombre y Detalle -->
    <form action="accion/accionEditarNombreProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="pronombre" value="<?= $producto->getProNombre() ?>" required>
        </div>
        <button class="btn btn-success">Actualizar Nombre</button>
        </form>
        <form action="accion/accionEditarDetalleProducto.php" method="post" class="mb-3">
        <div class="mb-3">
            <label class="form-label">Detalle</label>
            <textarea class="form-control" name="prodetalle" required><?= $producto->getProDetalle() ?></textarea>
        </div>
        <button class="btn btn-success">Actualizar Detalle</button>
    </form>

    <!-- Editar Precio -->
    <form action="accion/accionEditarPrecioProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" class="form-control" name="proprecio" value="<?= $producto->getProPrecio() ?>" required>
        </div>
        <button class="btn btn-success">Actualizar Precio</button>
    </form>

    <!-- Editar Stock -->
    <form action="accion/accionEditarStockProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="procantstock" value="<?= $producto->getProCantStock() ?>" required>
        </div>
        <button class="btn btn-success">Actualizar Stock</button>
    </form>

    <!-- Aumentar Stock -->
    <form action="accion/accionAumentarStockProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Aumentar Stock</label>
            <input type="number" class="form-control" name="cantidad" value="1" min="1" required>
        </div>
        <button class="btn btn-primary">Aumentar Stock</button>
    </form>

    <!-- Reducir Stock -->
    <form action="accion/accionReducirStockProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Reducir Stock</label>
            <input type="number" class="form-control" name="cantidad" value="1" min="1" required>
        </div>
        <button class="btn btn-danger">Reducir Stock</button>
    </form>

    <!-- Editar Oferta -->
    <form action="accion/accionEditarOfertaProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <div class="mb-3">
            <label class="form-label">Oferta (%)</label>
            <input type="number" class="form-control" name="prooferta" value="<?= $producto->getProOferta() ?>" min="0" max="100">
        </div>
        <button class="btn btn-warning">Actualizar Oferta</button>
    </form>

    <!-- Toggle Visibilidad -->
    <form action="accion/accionToggleVisibilidadProducto.php" method="post" class="mb-3">
        <input type="hidden" name="idproducto" value="<?= $producto->getIdProducto() ?>">
        <button class="btn btn-secondary">
            <?= $producto->getProDeshabilitado() ? "Habilitar Producto" : "Deshabilitar Producto" ?>
        </button>
    </form>

    <a href="listarMisProductos.php" class="btn btn-outline-secondary">Volver</a>
</div>

<?php include_once "../estructura/pie.php"; ?>