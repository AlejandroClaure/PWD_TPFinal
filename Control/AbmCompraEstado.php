// control/AbmCompraEstado.php
<?php
class AbmCompraEstado {
    public function alta($datos) {
        // Cerrar estado anterior
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

    public function buscar($param = array()) {
        $where = "true";
        if (isset($param['idcompra'])) $where .= " AND idcompra = " . $param['idcompra'];
        if (isset($param['activo'])) $where .= " AND cefechafin IS NULL";
        return (new CompraEstado())->listar($where);
    }
}
?>