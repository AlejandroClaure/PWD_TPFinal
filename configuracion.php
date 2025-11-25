<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

// ----------------------------------------------------
// Nombre del proyecto (carpeta bajo document_root)
// ----------------------------------------------------
$PROYECTO = 'PWD_TPFinal';

// ----------------------------------------------------
// RUTA FÍSICA (para PHP)
// ----------------------------------------------------
$GLOBALS['ROOT_PROYECTO'] =
    rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
    . DIRECTORY_SEPARATOR . $PROYECTO . DIRECTORY_SEPARATOR;

// ----------------------------------------------------
// URL PÚBLICA (para el navegador)
// ----------------------------------------------------
$GLOBALS['BASE_URL'] = "/" . trim($PROYECTO, "/") . "/";

// ----------------------------------------------------
// RUTAS DERIVADAS
// ----------------------------------------------------
$GLOBALS['VISTA_URL'] = $GLOBALS['BASE_URL'] . "Vista/";
$GLOBALS['CSS_URL']   = $GLOBALS['BASE_URL'] . "Vista/css/";
$GLOBALS['IMG_URL']   = $GLOBALS['BASE_URL'] . "Vista/imagenes/";

// Rutas físicas útiles
$GLOBALS['MODELO_PATH']  = $GLOBALS['ROOT_PROYECTO'] . "Modelo" . DIRECTORY_SEPARATOR;
$GLOBALS['CONTROL_PATH'] = $GLOBALS['ROOT_PROYECTO'] . "Control" . DIRECTORY_SEPARATOR;
$GLOBALS['VISTA_PATH']   = $GLOBALS['ROOT_PROYECTO'] . "Vista" . DIRECTORY_SEPARATOR;

// ----------------------------------------------------
// CARGAR SIEMPRE BaseDatos (UBICADO EN /Modelo/conector/)
// ----------------------------------------------------
$baseDatosPath = $GLOBALS['MODELO_PATH'] . "conector" . DIRECTORY_SEPARATOR . "BaseDatos.php";

if (file_exists($baseDatosPath)) {
    require_once $baseDatosPath;
} else {
    die("❌ ERROR: No se encontró BaseDatos.php en: $baseDatosPath");
}

// ----------------------------------------------------
// AUTOLOAD DE CLASES
// ----------------------------------------------------
spl_autoload_register(function ($className) {

    // Carpetas donde buscar clases
    $paths = [
        $GLOBALS['MODELO_PATH'],                     // /Modelo/
        $GLOBALS['MODELO_PATH'] . "conector/",       // /Modelo/conector/
        $GLOBALS['CONTROL_PATH'],                    // /Control/
    ];

    foreach ($paths as $path) {
        $file = $path . $className . ".php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    error_log("❌ Autoload: Clase no encontrada → $className");
});

// ----------------------------------------------------
// Función verificar rol
// ----------------------------------------------------
function tieneRol($rolRequerido) {
    return isset($_SESSION["roles"]) && in_array($rolRequerido, $_SESSION["roles"]);
}

// Tipos de estado de compra
define("COMPRA_ESTADO_INICIADA", 1);
define("COMPRA_ESTADO_ACEPTADA", 2);
define("COMPRA_ESTADO_ENVIADA", 3);
define("COMPRA_ESTADO_CANCELADA", 4);
define("COMPRA_ESTADO_FINALIZADA", 5);

?>
