<?php
include_once dirname(__DIR__, 4) . '/configuracion.php';

$abm = new AbmMenu();
if ($abm->modificacion($_POST)) {
    header("Location: ../gestionMenus.php");
} else {
    echo "Error al editar menú";
}
