<?php
include_once dirname(__DIR__, 2) . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

if (!$usuario) {
    header("Location: ../../login/login.php");
    exit;
}

$abmUR = new AbmUsuarioRol();
$rolesUsuario = $abmUR->rolesDeUsuario($usuario->getIdUsuario());

if (!in_array("admin", $rolesUsuario)) {
    header("Location: ../../error/noAutorizado.php");
    exit;
}

$abmRol = new AbmRol();
$listaRoles = $abmRol->listar();

$abmUsuario = new AbmUsuario();
$listaUsuarios = $abmUsuario->buscar([]);
?>

<?php include_once dirname(__DIR__, 1) . "/estructura/cabecera.php"; ?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Administración de Roles</h2>
        <a href="<?= $GLOBALS['VISTA_URL']; ?>panelAdmin.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- CREAR ROL -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-primary text-white">Crear nuevo rol</div>
        <div class="card-body">
            <form action="accion/accionCrearRol.php" method="POST">
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
                                    onclick="editarRol(<?= $rol->getIdRol(); ?>, '<?= htmlspecialchars($rol->getRoDescripcion()); ?>')">
                                    <i class="fa fa-edit"></i> Editar
                                </button>
                                <a href="accion/accionEliminarRol.php?idrol=<?= $rol->getIdRol(); ?>"
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
            <form action="accion/accionAsignarRol.php" method="POST" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Usuario</label>
                    <select name="idusuario" class="form-select" required>
                        <?php foreach ($listaUsuarios as $u): ?>
                            <option value="<?= $u->getIdUsuario(); ?>">
                                <?= htmlspecialchars($u->getUsNombre()); ?> (<?= htmlspecialchars($u->getUsMail()); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Rol</label>
                    <select name="idrol" class="form-select" required>
                        <?php foreach ($listaRoles as $r): ?>
                            <option value="<?= $r->getIdRol(); ?>"><?= htmlspecialchars($r->getRoDescripcion()); ?></option>
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

    <!-- QUITAR ROLES -->
    <div class="card mb-5 shadow">
        <div class="card-header bg-danger text-white">
            <i class="fa fa-user-minus"></i> Quitar rol a usuario
        </div>
        <div class="card-body">
            <form id="formQuitarRol" action="accion/accionQuitarRol.php" method="POST" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Usuario</label>
                    <select id="selectUsuarioQuitar" name="idusuario" class="form-select" required>
                        <option value="">Seleccione un usuario...</option>
                        <?php foreach ($listaUsuarios as $u): ?>
                            <option value="<?= $u->getIdUsuario(); ?>">
                                <?= htmlspecialchars($u->getUsNombre()); ?> (<?= htmlspecialchars($u->getUsMail()); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-bold">Rol a quitar</label>
                    <select id="selectRolQuitar" name="idrol" class="form-select" required disabled>
                        <option value="">Primero seleccione un usuario</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" id="btnQuitarRol" class="btn btn-danger w-100" disabled>
                        <i class="fa fa-minus"></i> Quitar rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ROL -->
<div class="modal fade" id="modalEditarRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="accion/accionEditarRol.php" method="POST">
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

<!-- SCRIPTS -->
 <script>
function editarRol(id, descripcion) {
    document.getElementById('edit-idrol').value = id;
    document.getElementById('edit-desc').value = descripcion;

    // Mostrar modal Bootstrap
    let modal = new bootstrap.Modal(document.getElementById('modalEditarRol'));
    modal.show();
}
</script>

<script>
document.getElementById('selectUsuarioQuitar').addEventListener('change', function () {
    const idusuario = this.value;
    const selectRol = document.getElementById('selectRolQuitar');
    const btn = document.getElementById('btnQuitarRol');

    selectRol.innerHTML = '<option value="">Cargando...</option>';
    selectRol.disabled = true;
    btn.disabled = true;

    if (!idusuario) {
        selectRol.innerHTML = '<option value="">Seleccione un usuario</option>';
        return;
    }

    // Ruta correcta usando BASE_URL + la ubicación real del archivo
   const url = '<?= $GLOBALS['BASE_URL']; ?>Vista/roles/accion/rolesDeUsuario.php?idusuario=' + idusuario;


    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('Error HTTP: ' + r.status);
            return r.json();
        })
        .then(data => {
            selectRol.innerHTML = '<option value="">Seleccione rol a quitar</option>';

            if (data && data.length > 0) {
                data.forEach(rol => {
                    const opt = document.createElement('option');
                    opt.value = rol.idrol;
                    opt.textContent = rol.rodescripcion;
                    selectRol.appendChild(opt);
                });
                selectRol.disabled = false;
            } else {
                selectRol.innerHTML = '<option value="">Sin roles asignados</option>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            selectRol.innerHTML = '<option value="">Error al cargar roles</option>';
        });
});

document.getElementById('selectRolQuitar').addEventListener('change', function () {
    document.getElementById('btnQuitarRol').disabled = !this.value;
});
</script>

<?php include_once dirname(__DIR__, 1) . "/estructura/pie.php"; ?>