<?php
// accionUsuarios.php
header('Content-Type: application/json; charset=utf-8');

// ajustar ruta a tu configuracion.php
require_once __DIR__ . '/../../../../configuracion.php';

$abm = new AbmUsuario();

// leer acción (GET o POST)
$accion = $_REQUEST['accion'] ?? null;

try {
    if ($accion === 'obtener') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }
        $arr = $abm->buscar(['idusuario' => $id]);
        if (!$arr || count($arr) === 0) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
        $u = $arr[0];
        echo json_encode(['ok' => true, 'usuario' => [
            'idusuario' => $u->getIdUsuario(),
            'usnombre'  => $u->getUsNombre(),
            'usmail'    => $u->getUsMail(),
            'usdeshabilitado' => $u->getUsDeshabilitado()
        ]]);
        exit;
    }

    if ($accion === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['idusuario'] ?? 0);
        $nombre = trim($_POST['usnombre'] ?? '');
        $mail = trim($_POST['usmail'] ?? '');
        $pass = $_POST['uspass'] ?? '';

        if ($id <= 0 || $nombre === '' || $mail === '') {
            echo json_encode(['ok' => false, 'error' => 'ID, nombre y email son requeridos.']);
            exit;
        }

        $params = ['idusuario' => $id, 'usnombre' => $nombre, 'usmail' => $mail];
        // pasar contraseña sin hashear; AbmUsuario->modificacion la procesará (hash)
        if ($pass !== '') $params['uspass'] = $pass;

        $ok = $abm->modificacion($params);
        if ($ok) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'No se pudo modificar.']);
        }
        exit;
    }

    // DESHABILITAR -> grabar fecha/hora en usdeshabilitado
    if ($accion === 'deshabilitar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }

        // cargamos usuario directo y seteamos fecha
        $u = new Usuario();
        $u->setIdUsuario($id);
        if (!$u->cargar()) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
        $u->setUsDeshabilitado(date('Y-m-d H:i:s'));
        $ok = $u->modificar();
        if ($ok) echo json_encode(['ok' => true]);
        else echo json_encode(['ok' => false, 'error' => $u->getMensajeOperacion() ?? 'No se pudo deshabilitar']);
        exit;
    }

    // HABILITAR -> setear usdeshabilitado a NULL
    if ($accion === 'habilitar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }

        $u = new Usuario();
        $u->setIdUsuario($id);
        if (!$u->cargar()) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
        // poner NULL -> dependiendo del setter, pasar null
        $u->setUsDeshabilitado(null);
        $ok = $u->modificar();
        if ($ok) echo json_encode(['ok' => true]);
        else echo json_encode(['ok' => false, 'error' => $u->getMensajeOperacion() ?? 'No se pudo habilitar']);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
