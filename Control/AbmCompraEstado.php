<?php
class AbmCompraEstado {

    // Alta: crea un nuevo estado y cierra el anterior
    public function alta($datos) {
        $ce = new CompraEstado();
        $ce->cerrarEstadoActual($datos['idcompra']);

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos['idcompra']);
        $objCompra->cargar();

        $objTipo = new CompraEstadoTipo();
        $objTipo->setIdCompraEstadoTipo($datos['idcompraestadotipo']);
        $objTipo->cargar();

        $nuevo = new CompraEstado();
        $nuevo->setear(0, $objCompra, $objTipo, date('Y-m-d H:i:s'), null);
        return $nuevo->insertar();
    }

    // Baja
    public function baja($datos) {
        $obj = new CompraEstado();
        $obj->setIdCompraEstado($datos['idcompraestado']);
        return $obj->eliminar();
    }

    // Modificación
    public function modificacion($datos) {
        $obj = new CompraEstado();
        $obj->setIdCompraEstado($datos['idcompraestado']);
        $obj->cargar();

        if (isset($datos['cefechafin'])) {
            $obj->setCeFechaFin($datos['cefechafin']);
        }

        return $obj->modificar();
    }

    // Buscar
    public function buscar($param = array()) {
        $where = "true";

        if (isset($param['idcompra'])) {
            $where .= " AND idcompra = " . $param['idcompra'];
        }

        if (isset($param['activo']) && $param['activo'] === true) {
            $where .= " AND cefechafin IS NULL";
        }

        return (new CompraEstado())->listar($where);
    }

    // Buscar el último estado activo de una compra
    public function buscarUltimoPorCompra($idcompra) {
        $estados = $this->buscar(['idcompra' => $idcompra, 'activo' => true]);
        if (!empty($estados)) {
            return $estados[0]; // El último activo
        }
        return null;
    }
}
?>
