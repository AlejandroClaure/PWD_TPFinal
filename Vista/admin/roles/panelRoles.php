<?php
// CONFIGURACIÓN (subir 3 niveles)
// /Vista/admin/roles/panelRoles.php → raíz del proyecto
include_once dirname(__DIR__, 3) . '/configuracion.php';

// Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

// Si no está logueado, fuera
if (!$usuario) {
    header("Location: ../../login/login.php");
    exit;
}

// Verificar si es admin
$abmUR = new AbmUsuarioRol();
$rolesUsuario = $abmUR->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("admin", $rolesUsuario)) {
    header("Location: ../../error/noAutorizado.php");
    exit;
}

// Listas necesarias
$abmRol = new AbmRol();
$listaRoles = $abmRol->listar();

$abmUsuario = new AbmUsuario();
$listaUsuarios = $abmUsuario->buscar([]);

?>

<?php include_once dirname(__DIR__, 2) . "/estructura/cabecera.php"; ?>
<!-- cabecera está en /Vista/estructura -->

<div class="container mt-5 pt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Administración de Roles</h2>
        <a href="<?= $GLOBALS['BASE_URL']; ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

    <!-- CREAR ROL -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-primary text-white">Crear nuevo rol</div>
        <div class="card-body">
            <form action="accion/crearRol.php" method="POST">
                <div class="input-group">
                    <input type="text" name="rodescripcion" class="form-control" placeholder="Nombre del rol" required>
                    <button class="btn btn-success">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTA ROLES -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-dark text-white">Roles existentes</div>
        <div class="card-body">
            <table class="table table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($listaRoles as $rol): ?>
                    <tr>
                        <td><?= $rol->getIdRol(); ?></td>
                        <td><?= $rol->getRoDescripcion(); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                onclick="editarRol(<?= $rol->getIdRol(); ?>, '<?= $rol->getRoDescripcion(); ?>')">
                                <i class="fa fa-edit"></i> Editar
                            </button>

                            <a href="accion/eliminarRol.php?idrol=<?= $rol->getIdRol(); ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Seguro que deseas eliminar este rol?')">
                                <i class="fa fa-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

    <!-- ASIGNAR ROLES -->
    <div class="card mb-5 shadow">
        <div class="card-header bg-secondary text-white">Asignar rol a usuario</div>
        <div class="card-body">

            <form action="accion/asignarRol.php" method="POST" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Usuario</label>
                    <select name="idusuario" class="form-select" required>
                        <?php foreach ($listaUsuarios as $u): ?>
                            <option value="<?= $u->getIdUsuario(); ?>">
                                <?= $u->getUsNombre(); ?> (<?= $u->getUsMail(); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Rol</label>
                    <select name="idrol" class="form-select" required>
                        <?php foreach ($listaRoles as $r): ?>
                            <option value="<?= $r->getIdRol(); ?>"><?= $r->getRoDescripcion(); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">
                        <i class="fa fa-plus"></i> Asignar
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalEditarRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="accion/editarRol.php" method="POST">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="idrol" id="edit-idrol">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="rodescripcion" id="edit-desc" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar cambios
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
function editarRol(id, desc) {
    document.getElementById("edit-idrol").value = id;
    document.getElementById("edit-desc").value = desc;
    new bootstrap.Modal(document.getElementById('modalEditarRol')).show();
}
</script>

<?php include_once dirname(__DIR__, 2) . "/estructura/pie.php"; ?>
