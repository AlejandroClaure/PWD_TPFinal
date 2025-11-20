<?php
require_once "../../configuracion.php";

$session = new Session();

if (!$session->activa()) {
    header("Location: login.php");
    exit;
}

$usuario = $session->getUsuario();
$roles = $_SESSION['roles'] ?? [];

// Obtener menús dinámicos según roles
$abmMenu = new AbmMenu();
$menus = $abmMenu->obtenerMenuPorRoles($roles);

include_once "../estructura/cabecera.php";
?>

<div class="container mt-5">

    <h2 class="mb-3">
        Bienvenido, <?= htmlspecialchars($usuario->getUsNombre()); ?> 👋
    </h2>

    <p class="text-muted">
        Tus roles: <strong><?= implode(", ", $roles); ?></strong>
    </p>

    <hr>

    <!-- ACCESOS RÁPIDOS -->
    <h4 class="mt-4">Accesos rápidos</h4>
    <div class="row mt-3">
        <!-- ADMIN -->
        <?php if (in_array("admin", $roles)): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Administración</h5>
                        <p class="card-text">Gestión completa del sistema.</p>
                        <a href="../admin/panelAdmin.php" class="btn btn-primary w-100">Entrar</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- VENDEDOR -->
        <?php if (in_array("vendedor", $roles)): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Mis Productos</h5>
                        <p class="card-text">Gestioná tus publicaciones.</p>
                        <a href="../productos/listarMisProductos.php" class="btn btn-success w-100">Ver productos</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- CLIENTE -->
        <?php if (in_array("cliente", $roles)): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Catálogo</h5>
                        <p class="card-text">Explorá los productos disponibles.</p>
                        <a href="../producto/producto.php" class="btn btn-info w-100">Ver catálogo</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Mi Cuenta</h5>
                        <p class="card-text">Gestioná tus datos personales.</p>
                        <a href="../usuarios/editarUsuario.php" class="btn btn-primary w-100">Editar mi cuenta</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- MENÚS DINÁMICOS -->
    <h4 class="mt-5">Menús</h4>
    <div class="row mt-3">
        <?php if (empty($menus)): ?>
            <p class="text-muted">No tenés menús asignados.</p>
        <?php endif; ?>

        <?php foreach ($menus as $menu): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($menu->getMeNombre()); ?></h5>
                        <!--  <p class="card-text"><?= htmlspecialchars($menu->getMeDescripcion()); ?></p>-->

                        <?php if (!empty($menu->getMeDescripcion())): ?>
                            <a href="<?= $GLOBALS['VISTA_URL'] . 'secciones/' . htmlspecialchars($menu->getMeDescripcion()); ?>" class="btn btn-outline-primary w-100">
                                Ir al menú
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                Sin enlace configurado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="../login/cerrarSesion.php" class="btn btn-danger mt-4">Cerrar sesión</a>

</div>

<?php include_once "../estructura/pie.php"; ?>
