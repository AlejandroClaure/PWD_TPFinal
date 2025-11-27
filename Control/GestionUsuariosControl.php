<?php
class GestionUsuariosControl {
    private $abm;

    public function __construct() {
        $this->abm = new AbmUsuario();
    }

    public function obtener($id) {
        if ($id <= 0) return ['ok' => false, 'error' => 'ID inválido'];
        $arr = $this->abm->buscar(['idusuario' => $id]);
        if (!$arr) return ['ok' => false, 'error' => 'Usuario no encontrado'];
        $u = $arr[0];
        return ['ok' => true, 'usuario' => [
            'idusuario' => $u->getIdUsuario(),
            'usnombre' => $u->getUsNombre(),
            'usmail' => $u->getUsMail(),
            'usdeshabilitado' => $u->getUsDeshabilitado()
        ]];
    }

    public function editar($params) {
        $id = intval($params['idusuario'] ?? 0);
        $nombre = trim($params['usnombre'] ?? '');
        $mail = trim($params['usmail'] ?? '');
        $pass = $params['uspass'] ?? '';

        if ($id <= 0 || $nombre === '' || $mail === '') {
            return ['ok' => false, 'error' => 'ID, nombre y email son requeridos.'];
        }

        $data = ['idusuario' => $id, 'usnombre' => $nombre, 'usmail' => $mail];
        if ($pass !== '') $data['uspass'] = $pass;

        $ok = $this->abm->modificacion($data);
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'No se pudo modificar.'];
    }

    public function deshabilitar($id) {
        return $this->cambiarEstado($id, date('Y-m-d H:i:s'));
    }

    public function habilitar($id) {
        return $this->cambiarEstado($id, null);
    }

    private function cambiarEstado($id, $fecha) {
        if ($id <= 0) return ['ok' => false, 'error' => 'ID inválido'];
        $u = new Usuario();
        $u->setIdUsuario($id);
        if (!$u->cargar()) return ['ok' => false, 'error' => 'Usuario no encontrado'];
        $u->setUsDeshabilitado($fecha);
        $ok = $u->modificar();
        return $ok ? ['ok' => true] : ['ok' => false, 'error' => $u->getMensajeOperacion() ?? 'No se pudo cambiar estado'];
    }

    //Modificacion de accion Usuario//

    public function accionUsuarios($accion,$controller){

        

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
            }
}
