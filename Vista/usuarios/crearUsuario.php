<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';

$session = new Session();
$usuario = $session->getUsuario();

// Verificar rol Administrador
$abmUsuarioRol = new AbmUsuarioRol();
$rolesUsuario = $abmUsuarioRol->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("Administrador", $rolesUsuario)) {
    echo "<div class='alert alert-danger m-5'>Acceso denegado.</div>";
    include_once dirname(__DIR__, 2) . '/estructura/pie.php';
    exit;
}

?>

<div class="container mt-5 pt-4">
    <h2>Crear Usuario</h2>

    <form action="accionCrearUsuario.php" method="POST" class="mt-4">

        <div class="mb-3">
            <label class="form-label">Nombre de usuario</label>
            <input type="text" name="usnombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mail</label>
            <input type="email" name="usmail" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="uspass" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Crear Usuario</button>
        <a href="panelUsuarios.php" class="btn btn-secondary">Volver</a>
    </form>
</div>

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
