<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';

$session = new Session();
$usuario = $session->getUsuario();

// Verificar admin
$abmUsuarioRol = new AbmUsuarioRol();
$roles = $abmUsuarioRol->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("Administrador", $roles)) {
    echo "<div class='alert alert-danger m-5'>Acceso denegado.</div>";
    include_once dirname(__DIR__, 2) . '/estructura/pie.php';
    exit;
}

$id = $_GET["id"];

$abmU = new AbmUsuario();
$u = $abmU->buscar(["idusuario" => $id])[0];
?>

<div class="container mt-5 pt-4">
    <h2>Editar Usuario</h2>

    <form action="accionEditarUsuario.php" method="POST" class="mt-3">

        <input type="hidden" name="idusuario" value="<?= $u->getIdUsuario(); ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="usnombre" value="<?= $u->getUsNombre(); ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mail</label>
            <input type="email" name="usmail" value="<?= $u->getUsMail(); ?>" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="panelUsuarios.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
