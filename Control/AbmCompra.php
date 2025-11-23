<?php

class AbmCompra
{

    public function alta($datos)
    {
        $obj = new Compra();
        $obj->setear(0, $datos["cofecha"], $datos["idusuario"]);
        if ($obj->insertar()) {
            return $obj; // DEVOLVEMOS EL OBJETO CON ID
        }
        return false;
    }


    public function baja($datos)
    {
        if (!isset($datos["idcompra"])) return false;

        $obj = new Compra();
        $obj->setIdCompra($datos["idcompra"]);
        return $obj->eliminar();
    }

    public function modificacion($datos)
    {
        if (!isset($datos["idcompra"])) return false;

        $obj = new Compra();
        $obj->setear(
            $datos["idcompra"],
            $datos["cofecha"],
            $datos["idusuario"]
        );

        return $obj->modificar();
    }

    public function buscar($param = null)
    {
        $where = " true ";

        if ($param !== null) {
            if (isset($param["idcompra"])) {
                $where .= " AND idcompra = " . $param["idcompra"];
            }
            if (isset($param["idusuario"])) {
                $where .= " AND idusuario = " . $param["idusuario"];
            }
        }

        $obj = new Compra();
        return $obj->listar($where);
    }
}
