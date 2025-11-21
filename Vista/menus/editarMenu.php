<?php
include_once dirname(__DIR__, 2) . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$session = new Session();
$usuario = $session->getUsuario();
$rolesUsuario = $usuario ? (new AbmUsuarioRol())->rolesDeUsuario($usuario->getIdUsuario()) : [];
if (!$usuario || !in_array("admin", $rolesUsuario)) {
    header("Location: " . $GLOBALS['VISTA_URL'] . "login/paginaSegura.php");
    exit;
}

$abmMenu = new AbmMenu();
$idmenu = $_GET['idmenu'] ?? null;
$menu = $abmMenu->buscar(['idmenu' => $idmenu])[0] ?? null;

if (!$menu) {
    header("Location: ../gestionMenus.php?ok=0");
    exit;
}

$padres = $abmMenu->buscar(['idpadre' => null]);
include_once dirname(__DIR__, 1) . '/estructura/cabecera.php';
?>

<div class="container mt-5">
    <h2>Editar Menú: <?= $menu->getMeNombre(); ?></h2>
    <form action="accion/accionEditarMenu.php" method="POST">
        <input type="hidden" name="idmenu" value="<?= $menu->getIdMenu(); ?>">
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="menombre" class="form-control" value="<?= $menu->getMeNombre(); ?>" required>
        </div>
        <div class="mb-3">
            <label>Tipo</label>
            <select name="tipo" id="tipo" class="form-select">
                <option value="raiz" <?= $menu->getObjMenuPadre() ? '' : 'selected'; ?>>Categoría principal</option>
                <option value="sub" <?= $menu->getObjMenuPadre() ? 'selected' : ''; ?>>Subcategoría</option>
            </select>
        </div>
        <div class="mb-3" id="bloquePadre" style="display: <?= $menu->getObjMenuPadre() ? 'block' : 'none'; ?>;">
            <label>Categoría padre</label>
            <select name="idpadre" class="form-select">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($padres as $p): ?>
                    <option value="<?= $p->getIdMenu(); ?>" <?= ($menu->getObjMenuPadre() && $menu->getObjMenuPadre()->getIdMenu() == $p->getIdMenu()) ? 'selected' : ''; ?>>
                        <?= $p->getMeNombre(); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary">Guardar cambios</button>
    </form>
</div>

<script>
document.getElementById("tipo").addEventListener("change", function() {
    document.getElementById("bloquePadre").style.display = this.value === "sub" ? "block" : "none";
});
</script>

<?php include_once dirname(__DIR__, 1) . '/estructura/pie.php'; ?>
