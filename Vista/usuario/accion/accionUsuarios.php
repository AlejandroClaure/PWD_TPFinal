<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../configuracion.php';

$accion = $_REQUEST['accion'] ?? null;
$controller = new GestionUsuariosControl();

try {
    switch ($accion) {
        case 'obtener':
            echo json_encode($controller->obtener(intval($_GET['id'] ?? 0)));
            break;
        case 'editar':
            echo json_encode($controller->editar($_POST));
            break;
        case 'deshabilitar':
            echo json_encode($controller->deshabilitar(intval($_POST['id'] ?? 0)));
            break;
        case 'habilitar':
            echo json_encode($controller->habilitar(intval($_POST['id'] ?? 0)));
            break;
        default:
            echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
