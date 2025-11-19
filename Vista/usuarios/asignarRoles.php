<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';

$session = new Session();
$usuario = $session->getUsuario();

$abmUsuarioRol = new AbmUsuarioRol();
$rolesAdmin = $abmUsuarioRol->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("Administrador", $rolesAdmin)) {
    echo "<div class='alert alert-danger m-5'>Acceso denegado.</div>";
    include_once dirname(__DIR__, 2) . '/estructura/pie.php';
    exit;
}

$id = $_GET["id"];

// Usuario
$abmUsuario = new AbmUsuario();
$u = $abmUsuario->buscar(["idusuario" => $id])[0];

// Roles existentes
$abmRol = new AbmRol();
$rolesDisponibles = $abmRol->buscar(null);

// Roles del usuario
$rolesUser = $abmUsuarioRol->rolesDeUsuario($id);
?>

<div class="container mt-5 pt-4">
    <h2>Asignar Roles a <?= $u->getUsNombre(); ?></h2>

    <form action="accionAsignarRoles.php" method="POST" class="mt-3">

        <input type="hidden" name="idusuario" value="<?= $id ?>">

        <?php foreach ($rolesDisponibles as $r): ?>
            <div class="form-check">
                <input 
                    class="form-check-input"
                    type="checkbox"
                    name="roles[]"
                    value="<?= $r->getIdRol(); ?>"
                    <?= in_array($r->getRoDescripcion(), $rolesUser) ? "checked" : "" ?>
                >

                <label class="form-check-label">
                    <?= $r->getRoDescripcion(); ?>
                </label>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-primary mt-3">Guardar Roles</button>
        <a href="panelUsuarios.php" class="btn btn-secondary mt-3">Volver</a>

    </form>
</div>

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
