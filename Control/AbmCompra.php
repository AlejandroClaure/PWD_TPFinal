<?php

class AbmCompra {

    /**
     * Crea una compra nueva
     */
    public function alta($datos) {
        $resp = false;

        $obj = new Compra();
        $objUsuario = new Usuario();
        $objUsuario->setIdUsuario($datos["idusuario"]);

        $obj->setear(
            null,
            date("Y-m-d H:i:s"),
            $objUsuario
        );

        if ($obj->insertar()) {
            $resp = true;
        }

        return $resp;
    }

    /**
     * Elimina una compra
     */
    public function baja($datos) {
        $resp = false;

        if (isset($datos["idcompra"])) {
            $obj = new Compra();
            $obj->setIdCompra($datos["idcompra"]);

            if ($obj->eliminar()) {
                $resp = true;
            }
        }

        return $resp;
    }

    /**
     * Modifica una compra
     */
    public function modificacion($datos) {
        $resp = false;

        if (isset($datos["idcompra"])) {
            $obj = new Compra();

            $objUsuario = new Usuario();
            $objUsuario->setIdUsuario($datos["idusuario"]);

            $obj->setear(
                $datos["idcompra"],
                $datos["cofecha"],
                $objUsuario
            );

            if ($obj->modificar()) {
                $resp = true;
            }
        }

        return $resp;
    }

    /**
     * Busca compras según criterio
     */
    public function buscar($param = null) {
        $where = " true ";

        if ($param != null) {
            if (isset($param["idcompra"]))
                $where .= " AND idcompra = " . $param["idcompra"];

            if (isset($param["idusuario"]))
                $where .= " AND idusuario = " . $param["idusuario"];
        }

        $obj = new Compra();
        return $obj->listar($where);
    }
}
?>
