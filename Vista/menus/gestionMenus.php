<?php
// Vista/menus/gestionMenus.php
include_once dirname(__DIR__, 2) . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

// Seguridad: solo admin
$rolesUsuario = $usuario ? (new AbmUsuarioRol())->rolesDeUsuario($usuario->getIdUsuario()) : [];
if (!$usuario || !in_array("admin", $rolesUsuario)) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$abmMenu = new AbmMenu();
$menus = $abmMenu->buscar(null) ?? [];

// Separar padres e hijos
$padres = [];
$hijosMap = [];
foreach ($menus as $m) {
    if ($m->getObjMenuPadre() === null) {
        $padres[] = $m;
    } else {
        $padreId = $m->getObjMenuPadre()->getIdMenu();
        $hijosMap[$padreId][] = $m;
    }
}

$tree = [];
$byId = [];

// Crear nodos
foreach ($menus as $m) {
    $id = $m->getIdMenu();
    $byId[$id] = $m;

    $tree[$id] = [
        'menu' => $m,
        'children' => []
    ];
}

$root = []; // categorías principales

foreach ($menus as $m) {
    $id = $m->getIdMenu();
    $padre = $m->getObjMenuPadre();

    if ($padre === null) {
        // SIN padre → categoría raíz
        $root[$id] = &$tree[$id];
    } else {
        // insertarlo como hijo del padre
        $tree[$padre->getIdMenu()]['children'][] = &$tree[$id];
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
                                <option value="<?= $p->getIdMenu(); ?>"><?= htmlspecialchars($p->getMeNombre()); ?></option>
                                <?php if (isset($hijosMap[$p->getIdMenu()])): ?>
                                    <?php foreach ($hijosMap[$p->getIdMenu()] as $sub): ?>
                                        <option value="<?= $sub->getIdMenu(); ?>">&nbsp;&nbsp;↳ <?= htmlspecialchars($sub->getMeNombre()); ?></option>
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

    <!-- ================= CREAR PRODUCTO  ================= -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Agregar nuevo producto</div>
        <div class="card-body">
            <form action="../producto/accion/accionCrearProducto.php" method="POST" enctype="multipart/form-data">
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

                            <?php
                            function printMenuOptions($node, $level = 0)
                            {
                                $menu = $node['menu'];
                                $indent = str_repeat("&nbsp;&nbsp;&nbsp;", $level);
                                $arrow = $level > 0 ? "↳ " : "";

                                echo "<option value='" . $menu->getIdMenu() . "'>";
                                echo $indent . $arrow . htmlspecialchars($menu->getMeNombre(), ENT_QUOTES, 'UTF-8');
                                echo "</option>";

                                foreach ($node['children'] as $child) {
                                    printMenuOptions($child, $level + 1);
                                }
                            }

                            // Imprimir opciones del select
                            foreach ($root as $node) {
                                printMenuOptions($node, 0);
                            }
                            ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Precio</label>
                        <input type="number" name="proprecio" class="form-control" required step="0.01">
                    </div>

                    <div class="col-md-6">
                        <label>Descripción (opcional)</label>
                        <textarea name="prodetalle" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Imagen (JPG o PNG)</label>
                        <input type="file" name="proimagen" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Agregar producto
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <!-- ================= LISTADO DEL MENÚ  ================= -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h4>Estructura actual del menú</h4>
        </div>
        <div class="card-body">
            <?php if (empty($root)): ?>
                <p class="text-muted">No hay secciones creadas aún.</p>
            <?php else: ?>
                <div class="list-group list-group-flush">

                    <?php
                    // Función recursiva para mostrar todo el árbol
                    function mostrarMenuJerarquico($nodos, $nivel = 0)
                    {
                        foreach ($nodos as $nodo) {
                            $menu = $nodo['menu'];
                            $deshabilitado = $menu->getMeDeshabilitado();
                            $esVisible = $deshabilitado === null || $deshabilitado === '0000-00-00 00:00:00';

                            $indentacion = str_repeat('    ', $nivel); // 4 espacios por nivel
                            $prefijo = $nivel > 0 ? '└─ ' : '';
                            $colorFondo = !$esVisible ? 'background-color: #f8d7da;' : '';
                    ?>
                            <div class="list-group-item border-0" style="<?= $colorFondo ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>
                                            <?= $indentacion ?><?= $prefijo ?><?= htmlspecialchars($menu->getMeNombre()) ?>
                                        </strong>
                                        <?php if (!$esVisible): ?>
                                            <span class="badge bg-danger ms-2">Oculto</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="accion/accionToggleVisibilidad.php?idmenu=<?= $menu->getIdMenu() ?>"
                                            class="btn <?= $esVisible ? 'btn-outline-danger' : 'btn-outline-success' ?> btn-sm"
                                            title="<?= $esVisible ? 'Ocultar' : 'Mostrar' ?>">
                                            <?= $esVisible ? 'Ocultar' : 'Mostrar' ?>
                                        </a>
                                        <a href="<?= $GLOBALS['VISTA_URL'] ?>menus/editarMenu.php?idmenu=<?= $menu->getIdMenu() ?>"
                                            class="btn btn-outline-warning btn-sm">Editar</a>
                                        <a href="accion/accionEliminarMenus.php?idmenu=<?= $menu->getIdMenu() ?>"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('¿Eliminar esta sección? Esta acción no se puede deshacer.');">
                                            Eliminar
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <?php
                            if (!empty($nodo['children'])) {
                                mostrarMenuJerarquico($nodo['children'], $nivel + 1);
                            }
                            ?>
                    <?php
                        }
                    }

                    // Mostrar todo el árbol desde la raíz
                    mostrarMenuJerarquico($root);
                    ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- ================= GESTIÓN DE PRODUCTOS EXISTENTES ================= -->
    <div class="card mt-5 shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fa fa-boxes me-2"></i> Gestión de Productos Existentes
            </h4>
        </div>
        <div class="card-body p-0">

            <?php
            $abmProducto = new AbmProducto();
            $productos = $abmProducto->listarTodo(null); // null = trae TODOS (habilitados + deshabilitados)

            $imgBaseUrl = $GLOBALS['VISTA_URL'] . "imagenes/productos/";
            $imgDir = $_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/Vista/imagenes/productos/";

            $productosPorCat = [];
            foreach ($productos as $prod) {
                $nombreCompleto = $prod->getProNombre();
                $partes = explode('_', $nombreCompleto);
                $categoria = ucfirst($partes[0] ?? 'sin-categoria');
                $nombreReal = end($partes);
                $nombreVisible = str_replace('_', ' ', $nombreReal);

                $imagenBD = $prod->getProimagen();
                $imagenURL = ($imagenBD && file_exists($imgDir . $imagenBD))
                    ? $imgBaseUrl . $imagenBD
                    : $imgBaseUrl . "no-image.jpeg";

                $productosPorCat[$categoria][] = [
                    'obj' => $prod,
                    'nombre' => $nombreVisible,
                    'imagen' => $imagenURL
                ];
            }
            ksort($productosPorCat);
            ?>

            <?php if (empty($productos)): ?>
                <div class="p-5 text-center">
                    <i class="fa fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted fs-5">Aún no hay productos creados.</p>
                </div>
            <?php else: ?>
                <div class="accordion accordion-flush" id="accordionProductos">
                    <?php foreach ($productosPorCat as $cat => $items): ?>
                        <div class="accordion-item border-start border-primary border-4">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-primary bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cat<?= md5($cat) ?>">
                                    <i class="fa fa-folder me-2"></i>
                                    <?= htmlspecialchars($cat) ?>
                                    <span class="badge bg-primary ms-2"><?= count($items) ?></span>
                                </button>
                            </h2>

                            <div id="cat<?= md5($cat) ?>" class="accordion-collapse collapse" data-bs-parent="#accordionProductos">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-primary text-dark">
                                                <tr>
                                                    <th class="text-center" width="90">Imagen</th>
                                                    <th>Producto</th>
                                                    <th width="120">Precio</th>
                                                    <th width="100">Stock</th>
                                                    <th width="460" class="text-center">Acciones Rápidas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item):
                                                    $p = $item['obj'];
                                                    $nombre = $item['nombre'];
                                                    $imagen = $item['imagen'];
                                                    $deshabilitado = $p->getProDeshabilitado() !== null;
                                                ?>
                                                    <tr class="<?= $deshabilitado ? 'table-secondary' : '' ?>">
                                                        <td class="text-center">
                                                            <img src="<?= htmlspecialchars($imagen) ?>"
                                                                width="70" height="70"
                                                                class="rounded shadow-sm object-fit-cover border"
                                                                alt="<?= htmlspecialchars($nombre) ?>"
                                                                onerror="this.src='<?= $imgBaseUrl ?>no-image.jpeg'">
                                                        </td>
                                                        <td>
                                                            <strong><?= htmlspecialchars($nombre) ?></strong>
                                                            <?php if ($deshabilitado): ?>
                                                                <span class="badge bg-danger ms-2">Oculto</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="fw-bold text-success">
                                                            $<?= number_format($p->getProPrecio(), 2) ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= $p->getProCantStock() <= 5 ? 'bg-danger' : 'bg-success' ?>">
                                                                <?= $p->getProCantStock() ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm d-flex flex-wrap gap-1" role="group">

                                                                <!-- Toggle Visibilidad -->
                                                                <a href="../producto/accion/accionToggleVisibilidadProducto.php?id=<?= $p->getIdProducto() ?>"
                                                                    class="btn <?= $deshabilitado ? 'btn-outline-success' : 'btn-outline-danger' ?> btn-sm"
                                                                    title="<?= $deshabilitado ? 'Habilitar' : 'Deshabilitar' ?>">
                                                                    <i class="fa <?= $deshabilitado ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                                                    <?= $deshabilitado ? 'Mostrar' : 'Ocultar' ?>
                                                                </a>

                                                                <!-- Cambiar Precio -->
                                                                <form action="../producto/accion/accionEditarPrecioProducto.php" method="POST" class="d-inline">
                                                                    <input type="hidden" name="idproducto" value="<?= $p->getIdProducto() ?>">
                                                                    <div class="input-group input-group-sm" style="width: 140px;">
                                                                        <span class="input-group-text">$</span>
                                                                        <input type="number" step="0.01" name="proprecio" value="<?= $p->getProPrecio() ?>"
                                                                            class="form-control form-control-sm" required>
                                                                        <button type="submit" class="btn btn-primary">OK</button>
                                                                    </div>
                                                                </form>

                                                                <!-- Cambiar Stock -->
                                                                <form action="../producto/accion/accionEditarStockProducto.php" method="POST" class="d-inline">
                                                                    <input type="hidden" name="idproducto" value="<?= $p->getIdProducto() ?>">
                                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                                        <input type="number" name="procantstock" value="<?= $p->getProCantStock() ?>"
                                                                            min="0" class="form-control form-control-sm" required>
                                                                        <button type="submit" class="btn btn-success">OK</button>
                                                                    </div>
                                                                </form>

                                                                <!-- Editar Detalle / Oferta / Imagen -->
                                                                <a href="<?= $GLOBALS['VISTA_URL'] ?>producto/editarProducto.php?id=<?= $p->getIdProducto() ?>"
                                                                    class="btn btn-warning btn-sm" title="Editar todo">
                                                                    <i class="fa fa-edit"></i> Editar
                                                                </a>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tipo = document.getElementById("tipo");
            const bloquePadre = document.getElementById("bloquePadre");

            function togglePadre() {
                if (tipo.value === "sub") {
                    bloquePadre.style.display = "block";
                } else {
                    bloquePadre.style.display = "none";
                }
            }

            // Ejecutar al cambiar
            tipo.addEventListener("change", togglePadre);

            // Ejecutar una vez por si hay valores cargados
            togglePadre();
        });
    </script>


    <?php include_once dirname(__DIR__, 1) . '/estructura/pie.php'; ?>