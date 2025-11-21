<?php
include_once dirname(__DIR__, 2) . '/configuracion.php';

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

include_once dirname(__DIR__, 1) . '/estructura/cabecera.php';
?>

<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Menús y Productos</h2>
        <a href="<?= $GLOBALS['VISTA_URL']; ?>/panelAdmin.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if ($ok == 1): ?>
        <div class="alert alert-success">Operación realizada correctamente.</div>
    <?php elseif ($ok === "0"): ?>
        <div class="alert alert-danger">Ocurrió un error al realizar la operación.</div>
    <?php endif; ?>

    <?php if ($toggle == 1): ?>
        <div class="alert alert-info">Se actualizó la visibilidad.</div>
    <?php endif; ?>

    <!-- ================= CREAR NUEVA SECCIÓN ================= -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Crear nueva sección</div>
        <div class="card-body">
            <form action="accion/accionCrearMenu.php" method="POST">
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
            <form action="accion/accionCrearProducto.php" method="POST" enctype="multipart/form-data">
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
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= $p->getMeNombre(); ?></strong>
                                   <!-- <div class="small text-muted"><?= $p->getMeDescripcion(); ?></div>-->
                                </div>
                                <div class="btn-group">
                                    <a href="accion/toggleVisibilidad.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-info">
                                        <?= $p->getMeDeshabilitado() ? "🚫" : "👁️" ?>
                                    </a>
                                    <a href="<?= $GLOBALS['VISTA_URL']; ?>menus/editarMenu.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                    <a href="accion/accionEliminarMenu.php?idmenu=<?= $p->getIdMenu(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar sección?');">Eliminar</a>
                                </div>
                            </div>

                            <?php if (isset($hijosMap[$p->getIdMenu()])): ?>
                                <ul class="mt-2 ms-3">
                                    <?php foreach ($hijosMap[$p->getIdMenu()] as $h): ?>
                                        <li class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?= $h->getMeNombre(); ?>
                                                <!-- <div class="small text-muted"><?= $h->getMeDescripcion(); ?></div>-->
                                            </div>
                                            <div class="btn-group">
                                                <a href="accion/toggleVisibilidad.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-info">
                                                    <?= $h->getMeDeshabilitado() ? "🚫" : "👁️" ?>
                                                </a>
                                                <a href="<?= $GLOBALS['VISTA_URL']; ?>menus/editarMenu.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                                <a href="accion/accionEliminarMenu.php?idmenu=<?= $h->getIdMenu(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar sub-sección?');">Eliminar</a>
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

    <!-- ================= GESTIÓN DE STOCK CON AJAX  ================= -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <strong>Control de Stock en Tiempo Real</strong>
            <small class="text-success" id="mensajeAjax"></small>
        </div>
        <div class="card-body">
            <?php
            // Aseguramos que la variable exista y contenga un array (evita warnings)
            if (!isset($abmProducto)) {
                $abmProducto = new AbmProducto();
            }
            $todosLosProductos = $abmProducto->listar() ?? []; // si listar() devuelve null, lo convertimos a array vacío
            ?>

            <?php if (empty($todosLosProductos)): ?>
                <p class="text-muted">No hay productos registrados todavía.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaStock">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Detalle (Precio de los productos)</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Acciones Rápidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todosLosProductos as $prod): ?>
                                <tr data-id="<?= $prod->getIdProducto(); ?>">
                                    <td><?= $prod->getIdProducto(); ?></td>
                                    <td><strong><?= htmlspecialchars($prod->getProNombre()); ?></strong></td>

                                    <!-- DETALLE EDITABLE (precio de los productos) -->
                                    <td>
                                        <input type="text"
                                            class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($prod->getProDetalle()); ?>"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            onblur="actualizarDetalle(<?= $prod->getIdProducto(); ?>, this.value)">
                                    </td>

                                    <!-- STOCK -->
                                    <td class="text-center">
                                        <span class="badge fs-6 <?= $prod->getProCantStock() <= 0 ? 'bg-danger' : ($prod->getProCantStock() <= 5 ? 'bg-warning text-dark' : 'bg-success') ?>">
                                            <?= $prod->getProCantStock(); ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm me-1" onclick="cambiarStock(<?= $prod->getIdProducto(); ?>, 1)" title="+1">
                                            <i class="fa fa-plus"></i>
                                        </button>

                                        <button class="btn btn-danger btn-sm me-2" onclick="cambiarStock(<?= $prod->getIdProducto(); ?>, -1)" title="-1"
                                            <?= $prod->getProCantStock() <= 0 ? 'disabled' : '' ?>>
                                            <i class="fa fa-minus"></i>
                                        </button>

                                        <input type="number" min="0" value="<?= $prod->getProCantStock(); ?>"
                                            class="form-control form-control-sm d-inline-block text-center"
                                            style="width: 80px;"
                                            onblur="actualizarStockDirecto(<?= $prod->getIdProducto(); ?>, this.value)">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>


</div>
</div>
<script>
    // Función para +1 o -1
    function cambiarStock(id, cambio) {
        fetch(`accion/accionStockAjax.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&cambio=${cambio}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const fila = document.querySelector(`tr[data-id="${id}"]`);
                    const badge = fila.querySelector('.badge');
                    const input = fila.querySelector('input[type="number"]');
                    const btnMenos = fila.querySelector('button[title="-1"]');

                    // Actualizar badge y input
                    badge.textContent = data.nuevoStock;
                    input.value = data.nuevoStock;

                    // Cambiar color del badge
                    badge.classList.remove('bg-danger', 'bg-warning', 'bg-success', 'text-dark');
                    if (data.nuevoStock <= 0) {
                        badge.classList.add('bg-danger');
                        btnMenos.disabled = true;
                    } else if (data.nuevoStock <= 5) {
                        badge.classList.add('bg-warning', 'text-dark');
                        btnMenos.disabled = false;
                    } else {
                        badge.classList.add('bg-success');
                        btnMenos.disabled = false;
                    }

                    // Mensaje flotante
                    mostrarMensaje('Stock actualizado', 'success');
                }
            });
    }

    // Actualizar con el input directo
    function actualizarStockDirecto(id, valor) {
        const nuevo = parseInt(valor);
        if (isNaN(nuevo) || nuevo < 0) return;

        fetch(`accion/accionStockAjax.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&stock=${nuevo}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const fila = document.querySelector(`tr[data-id="${id}"]`);
                    const badge = fila.querySelector('.badge');
                    badge.textContent = data.nuevoStock;
                    badge.className = 'badge fs-6 ' + (data.nuevoStock <= 0 ? 'bg-danger' : data.nuevoStock <= 5 ? 'bg-warning text-dark' : 'bg-success');
                    mostrarMensaje('Stock actualizado', 'success');
                }
            });
    }

    // Mensaje flotante bonito
    function mostrarMensaje(texto, tipo = 'success') {
        const msg = document.getElementById('mensajeAjax');
        msg.textContent = texto;
        msg.className = tipo === 'success' ? 'text-success' : 'text-danger';
        setTimeout(() => msg.textContent = '', 2000);
    }

    function actualizarDetalle(id, detalle) {
        fetch(`accion/accionStockAjax.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&detalle=${encodeURIComponent(detalle)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje('Detalle actualizado', 'success');
                } else {
                    mostrarMensaje('Error al actualizar detalle', 'danger');
                }
            });
    }
</script>

<script>
    document.getElementById("tipo").addEventListener("change", function() {
        document.getElementById("bloquePadre").style.display =
            (this.value === "sub") ? "block" : "none";
    });
</script>

<?php include_once dirname(__DIR__, 1) . '/estructura/pie.php'; ?>