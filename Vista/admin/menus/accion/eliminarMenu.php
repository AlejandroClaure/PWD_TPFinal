<?php
include_once dirname(__DIR__, 4) . '/configuracion.php';

$abm = new AbmMenu();
if ($abm->baja($_GET)) {
    header("Location: ../gestionMenus.php");
} else {
    echo "Error al eliminar menú";
}
