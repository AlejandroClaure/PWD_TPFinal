<?php
include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';

$session = new Session();
$usuario = $session->getUsuario();

// Verificar si está logueado y es ADMIN
$abmUsuarioRol = new AbmUsuarioRol();
$rolesUsuario = $abmUsuarioRol->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("Administrador", $rolesUsuario)) {
    echo "<div class='alert alert-danger m-5'>Acceso denegado. No tienes permisos.</div>";
    include_once dirname(__DIR__, 2) . '/estructura/pie.php';
    exit;
}

// Obtener usuarios
$abmUsuario = new AbmUsuario();
$listaUsuarios = $abmUsuario->buscar(null);
?>

<div class="container mt-5 pt-4">
    <h2 class="text-center mb-4">Panel de Usuarios</h2>
    
    <div class="mb-3">
        <a href="crearUsuario.php" class="btn btn-success">
            <i class="fa fa-plus"></i> Crear Usuario
        </a>
        <a href="<?= $GLOBALS['BASE_URL']; ?>" class="btn btn-secondary">
            ← Volver al Inicio
        </a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Mail</th>
                <th>Roles</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($listaUsuarios as $u): 
            $roles = $abmUsuarioRol->rolesDeUsuario($u->getIdUsuario());
        ?>
            <tr>
                <td><?= $u->getIdUsuario(); ?></td>
                <td><?= $u->getUsNombre(); ?></td>
                <td><?= $u->getUsMail(); ?></td>
                <td><?= implode(", ", $roles); ?></td>

                <td>
                    <a href="editarUsuario.php?id=<?= $u->getIdUsuario(); ?>" class="btn btn-warning btn-sm">
                        Editar
                    </a>
                    <a href="eliminarUsuario.php?id=<?= $u->getIdUsuario(); ?>" class="btn btn-danger btn-sm">
                        Eliminar
                    </a>
                    <a href="asignarRoles.php?id=<?= $u->getIdUsuario(); ?>" class="btn btn-info btn-sm">
                        Roles
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
