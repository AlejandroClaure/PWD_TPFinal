<?php

class AbmCompra {

    public function alta($datos) {
        $resp = false;

        $obj = new Compra();
        $obj->setear(
            0,                              // ID auto_increment
            $datos["cofecha"],
            $datos["idusuario"]
        );

        if ($obj->insertar()) {
            $resp = true;
        }

        return $resp;
    }


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


    public function modificacion($datos) {
        $resp = false;

        if (isset($datos["idcompra"])) {
            $obj = new Compra();
            $obj->setear(
                $datos["idcompra"],
                $datos["cofecha"],
                $datos["idusuario"]
            );

            if ($obj->modificar()) {
                $resp = true;
            }
        }

        return $resp;
    }


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
