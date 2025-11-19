// control/AbmCompraItem.php
<?php
class AbmCompraItem {
    public function alta($datos) {
        $obj = new CompraItem();
        $objProd = new Producto();
        $objProd->setIdProducto($datos['idproducto']);
        $objProd->cargar();

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos['idcompra']);
        $objCompra->cargar();

        $obj->setear(0, $objProd, $objCompra, $datos['cicantidad']);
        return $obj->insertar();
    }

    public function baja($datos) {
        $obj = new CompraItem();
        $obj->setIdCompraItem($datos['idcompraitem']);
        return $obj->eliminar();
    }

    public function modificacion($datos) {
        $obj = new CompraItem();
        $obj->setIdCompraItem($datos['idcompraitem']);
        $obj->cargar();

        $objProd = new Producto();
        $objProd->setIdProducto($datos['idproducto']);
        $objProd->cargar();

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos['idcompra']);
        $objCompra->cargar();

        $obj->setear($datos['idcompraitem'], $objProd, $objCompra, $datos['cicantidad']);
        return $obj->modificar();
    }

    public function buscar($param = array()) {
        $where = "true";
        if (isset($param['idcompraitem'])) $where .= " AND idcompraitem = " . $param['idcompraitem'];
        if (isset($param['idcompra'])) $where .= " AND idcompra = " . $param['idcompra'];
        return (new CompraItem())->listar($where);
    }
}
?>