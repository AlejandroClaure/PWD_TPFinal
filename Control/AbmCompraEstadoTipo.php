<?php

class AbmCompraEstadoTipo {

    public function alta($datos) {
        $resp = false;

        $obj = new CompraEstadoTipo();
        $obj->setear(
            $datos["idcompraestadotipo"],
            $datos["cetdescripcion"],
            $datos["cetdetalle"]
        );

        if ($obj->insertar()) $resp = true;

        return $resp;
    }

    public function baja($datos) {
        $resp = false;

        if (isset($datos["idcompraestadotipo"])) {
            $obj = new CompraEstadoTipo();
            $obj->setIdCompraEstadoTipo($datos["idcompraestadotipo"]);

            if ($obj->eliminar()) $resp = true;
        }

        return $resp;
    }

    public function modificacion($datos) {
        $resp = false;

        if (isset($datos["idcompraestadotipo"])) {
            $obj = new CompraEstadoTipo();
            $obj->setear(
                $datos["idcompraestadotipo"],
                $datos["cetdescripcion"],
                $datos["cetdetalle"]
            );

            if ($obj->modificar()) $resp = true;
        }

        return $resp;
    }

    public function buscar($param = null) {
        $where = " true ";

        if ($param != null) {

            if (isset($param["idcompraestadotipo"]))
                $where .= " AND idcompraestadotipo = " . $param["idcompraestadotipo"];
        }

        $obj = new CompraEstadoTipo();
        return $obj->listar($where);
    }
}
?>
