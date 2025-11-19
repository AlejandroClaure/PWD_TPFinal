<?php

class AbmCompraItem {

    public function alta($datos) {
        $resp = false;

        $obj = new CompraItem();

        $objProducto = new Producto();
        $objProducto->setIdProducto($datos["idproducto"]);

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos["idcompra"]);

        $obj->setear(
            null,
            $objProducto,
            $objCompra,
            $datos["cicantidad"]
        );

        if ($obj->insertar()) {
            $resp = true;
        }

        return $resp;
    }

    public function baja($datos) {
        $resp = false;

        if (isset($datos["idcompraitem"])) {
            $obj = new CompraItem();
            $obj->setIdCompraItem($datos["idcompraitem"]);

            if ($obj->eliminar()) $resp = true;
        }

        return $resp;
    }

    public function modificacion($datos) {
        $resp = false;

        if (isset($datos["idcompraitem"])) {
            $obj = new CompraItem();

            $objProducto = new Producto();
            $objProducto->setIdProducto($datos["idproducto"]);

            $objCompra = new Compra();
            $objCompra->setIdCompra($datos["idcompra"]);

            $obj->setear(
                $datos["idcompraitem"],
                $objProducto,
                $objCompra,
                $datos["cicantidad"]
            );

            if ($obj->modificar()) $resp = true;
        }

        return $resp;
    }

    public function buscar($param = null) {
        $where = " true ";

        if ($param != null) {
            if (isset($param["idcompraitem"]))
                $where .= " AND idcompraitem = " . $param["idcompraitem"];

            if (isset($param["idcompra"]))
                $where .= " AND idcompra = " . $param["idcompra"];

            if (isset($param["idproducto"]))
                $where .= " AND idproducto = " . $param["idproducto"];
        }

        $obj = new CompraItem();
        return $obj->listar($where);
    }
}
?>
