<?php
require_once "../../configuracion.php";
$session = new Session();

if (!$session->tieneRol("vendedor") && !$session->tieneRol("admin")) {
    die("No tiene permiso para ver esta página.");
}

$usuario = $session->getUsuario();
$abm = new AbmProducto();

$productos = ($session->tieneRol("admin"))
    ? $abm->listar()
    : $abm->listarPorUsuario($usuario->getIdUsuario());

include_once "../estructura/cabecera.php";
?>

<div class="container mt-4">
    <h2><?= $session->tieneRol("admin") ? "Todos los productos" : "Mis productos" ?></h2>

    <a href="nuevoProducto.php" class="btn btn-primary mb-3">Nuevo producto</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Detalle</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= $p->getProNombre() ?></td>
                    <td><?= $p->getProDetalle() ?></td>
                    <td><?= $p->getProCantStock() ?></td>
                    <td>
                        <a href="editarProducto.php?id=<?= $p->getIdProducto() ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="eliminarProducto.php?id=<?= $p->getIdProducto() ?>" class="btn btn-sm btn-danger">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once "../estructura/pie.php"; ?>
