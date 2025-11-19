<?php
require_once "../../configuracion.php";

$session = new Session();

// Si no hay sesión activa → volver al login
if (!$session->activa()) {
    header("Location: ../login/login.php");
    exit;
}

$usuario = $session->getUsuario();
$roles = $_SESSION['roles'] ?? [];

// Solo los admins pueden entrar aquí
if (!in_array("admin", $roles)) {
    header("Location: ../login/paginaSegura.php");
    exit;
}

include_once "../estructura/cabecera.php";
?>

<div class="container mt-5">

    <h2 class="mb-3">Panel de Administración</h2>

    <p class="text-muted">
        Bienvenido <strong><?= $usuario->getUsNombre(); ?></strong> — Rol: <strong>Administrador</strong>
    </p>

    <hr>

    <div class="row mt-4">

        <!-- GESTIÓN DE USUARIOS -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">Alta, baja y modificación de usuarios del sistema.</p>
                    <a href="gestionUsuarios.php" class="btn btn-primary w-100">Administrar Usuarios</a>
                </div>
            </div>
        </div>

        <!-- GESTIÓN DE ROLES -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Roles</h5>
                    <p class="card-text">Configurar roles y permisos asociados.</p>
                    <a href="roles/panelRoles.php" class="btn btn-success w-100">Administrar Roles</a>
                </div>
            </div>
        </div>

        <!-- GESTIÓN DE MENÚS -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Menús dinámicos</h5>
                    <p class="card-text">
                        Crear, editar y organizar el menú visible según los roles asignados.
                    </p>
                    <a href="menus/gestionMenus.php" class="btn btn-info w-100">Administrar Menús</a>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <a href="../login/paginaSegura.php" class="btn btn-outline-secondary">Volver</a>
        <a href="../login/cerrarSesion.php" class="btn btn-danger float-end">Cerrar sesión</a>
    </div>

</div>

<?php include_once "../estructura/pie.php"; ?>
