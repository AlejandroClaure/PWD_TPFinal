<?php
include_once dirname(__DIR__, 4) . '/configuracion.php';
include_once dirname(__DIR__, 4) . '/Control/AbmMenu.php';

$abmMenu = new AbmMenu();

$idmenu = $_POST['idmenu'] ?? null;
$menombre = trim($_POST['menombre'] ?? "");
$tipo = $_POST['tipo'] ?? "raiz";
$idPadre = $_POST['idpadre'] ?? null;

if (!$idmenu || $menombre === "") {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

// Normalizar nombre y generar slug
$menombre = ucfirst($menombre);
$slug = strtolower(trim($menombre));
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, "-");

// Ruta física de secciones
$carpetaSecciones = $GLOBALS['VISTA_PATH'] . "secciones/";

// Determinar nueva ruta del archivo
if ($tipo === "raiz") {
    $nuevaRuta = $slug . ".php";
} else {
    $padre = $abmMenu->buscar(['idmenu' => $idPadre])[0] ?? null;
    $padreSlug = $padre ? strtolower(str_replace(".php", "", $padre->getMeDescripcion())) : '';
    $nuevaRuta = $padreSlug . "/" . $slug . ".php";
}

// Obtener menú actual
$menu = $abmMenu->buscar(['idmenu' => $idmenu])[0] ?? null;
if (!$menu) {
    header("Location: ../../menus/gestionMenus.php?ok=0");
    exit;
}

// Renombrar archivo físico si existe
$rutaActual = $carpetaSecciones . $menu->getMeDescripcion();
$rutaNuevaFull = $carpetaSecciones . $nuevaRuta;

if (file_exists($rutaActual)) {
    $dirNueva = dirname($rutaNuevaFull);
    if (!is_dir($dirNueva)) mkdir($dirNueva, 0777, true);
    rename($rutaActual, $rutaNuevaFull);
}

// Evitar que un menú sea su propio padre
if ($idPadre == $idmenu) $idPadre = null;

// Actualizar menú en BD
$datos = [
    "idmenu" => $idmenu,
    "menombre" => $menombre,
    "medescripcion" => $nuevaRuta,
    "idpadre" => ($tipo === "sub") ? $idPadre : null
];

$abmMenu->modificacion($datos);

// Actualizar rutas de submenús si es padre
$hijos = $abmMenu->buscar(['idpadre' => $idmenu]);
foreach ($hijos as $hijo) {
    $hSlug = strtolower(trim($hijo->getMeNombre()));
    $hSlug = preg_replace('/[^a-z0-9]+/', '-', $hSlug);
    $rutaHijoNueva = $slug . "/" . $hSlug . ".php";

    $rutaHijoActual = $carpetaSecciones . $hijo->getMeDescripcion();
    $rutaHijoNuevaFull = $carpetaSecciones . $rutaHijoNueva;

    if (file_exists($rutaHijoActual)) {
        $dirNuevaHijo = dirname($rutaHijoNuevaFull);
        if (!is_dir($dirNuevaHijo)) mkdir($dirNuevaHijo, 0777, true);
        rename($rutaHijoActual, $rutaHijoNuevaFull);
    }

    $abmMenu->modificacion([
        "idmenu" => $hijo->getIdMenu(),
        "medescripcion" => $rutaHijoNueva
    ]);
}

// Redirigir correctamente
header("Location: ../../menus/gestionMenus.php?ok=1");
exit;
?>
