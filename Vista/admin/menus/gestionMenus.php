<?php
include_once dirname(__DIR__, 3) . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

// Solo admin
$rolesUsuario = $usuario ? (new AbmUsuarioRol())->rolesDeUsuario($usuario->getIdUsuario()) : [];
if (!$usuario || !in_array("admin", $rolesUsuario)) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$abmMenu = new AbmMenu();
$menus = $abmMenu->buscar(null);

// Separar padres e hijos
$padres = [];
$hijosMap = [];
foreach ($menus as $m) {
    if ($m->getObjMenuPadre() === null) {
        $padres[] = $m;
    } else {
        $hijosMap[$m->getObjMenuPadre()->getIdMenu()][] = $m;
    }
}

$ok = $_GET['ok'] ?? null;
$toggle = $_GET['toggle'] ?? null;

include_once dirname(__DIR__, 2) . '/estructura/cabecera.php';
?>

<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Menús y Productos</h2>
        <a href="<?= $GLOBALS['VISTA_URL']; ?>admin/panelAdmin.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if ($ok == 1): ?>
        <div class="alert alert-success">Operación realizada correctamente.</div>
    <?php endif; ?>

    <?php if ($toggle == 1): ?>
        <div class="alert alert-info">Se actualizó la visibilidad.</div>
    <?php endif; ?>

    <!-- ================= CREAR NUEVA SECCIÓN ================= -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Crear nueva sección</div>
        <div class="card-body">
            <form action="accion/crearMenu.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Nombre</label>
                        <input type="text" name="menombre" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="raiz">Categoría principal</option>
                            <option value="sub">Subcategoría</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="bloquePadre" style="display:none;">
                        <label>Categoría padre</label>
                        <select name="idpadre" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($padres as $p): ?>
                                <option value="<?= $p->getIdMenu(); ?>"><?= $p->getMeNombre(); ?></option>
                                <?php if (isset($hijosMap[$p->getIdMenu()])): ?>
                                    <?php foreach ($hijosMap[$p->getIdMenu()] as $sub): ?>
                                        <option value="<?= $sub->getIdMenu(); ?>">&nbsp;&nbsp;↳ <?= $sub->getMeNombre(); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-success"><i class="fa fa-plus"></i> Crear sección</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= CREAR PRODUCTO ================= -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Agregar nuevo producto</div>
        <div class="card-body">
            <form action="accion/crearProducto.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Nombre</label>
                        <input type="text" name="pronombre" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Stock</label>
                        <input type="number" name="procantstock" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Sección</label>
                        <select name="categoria" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($padres as $p): ?>
                                <option value="<?= $p->getMeNombre(); ?>"><?= $p->getMeNombre(); ?></option>
                                <?php if (isset($hijosMap[$p->getIdMenu()])): ?>
                                    <?php foreach ($hijosMap[$p->getIdMenu()] as $h): ?>
                                        <option value="<?= $h->getMeNombre(); ?>">&nbsp;&nbsp;↳ <?= $h->getMeNombre(); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Descripción / Precio</label>
                        <input type="text" name="prodetalle" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>Imagen (JPG o PNG)</label>
                        <input type="file" name="proimagen" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary"><i class="fa fa-plus"></i> Agregar producto</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= LISTADO DEL MENÚ ================= -->
    <div class="card">
        <div class="card-header bg-dark text-white">Estructura actual</div>
        <div class="card-body">
            <?php if (empty($padres)): ?>
                <p class="text-muted">No hay menús creados.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($padres as $p): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= $p->getMeNombre(); ?></strong>
                                    <div class="small text-muted"><?= $p->getMeDescripcion(); ?></div>
                                </div>
                                <div class="btn-group">
                                    <a href="accion/toggleVisibilidad.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-info">
                                        <?= $p->getMeDeshabilitado() == 1 ? "🚫" : "👁️" ?>
                                    </a>
                                    <a href="editarMenu.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                    <a href="accion/eliminarMenu.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar sección?');">Eliminar</a>
                                </div>
                            </div>

                            <?php if (isset($hijosMap[$p->getIdMenu()])): ?>
                                <ul class="mt-2 ms-3">
                                    <?php foreach ($hijosMap[$p->getIdMenu()] as $h): ?>
                                        <li class="d-flex justify-content-between">
                                            <div><?= $h->getMeNombre(); ?><div class="small text-muted"><?= $h->getMeDescripcion(); ?></div></div>
                                            <div class="btn-group">
                                                <a href="accion/toggleVisibilidad.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-info">
                                                    <?= $h->getMeDeshabilitado() == 1 ? "🚫" : "👁️" ?>
                                                </a>
                                                <a href="editarMenu.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                                <a href="accion/eliminarMenu.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar sub-sección?');">Eliminar</a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.getElementById("tipo").addEventListener("change", function () {
    document.getElementById("bloquePadre").style.display =
        (this.value === "sub") ? "block" : "none";
});
</script>

<?php include_once dirname(__DIR__, 2) . '/estructura/pie.php'; ?>
