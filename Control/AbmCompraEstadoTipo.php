<?php

class AbmCompraEstadoTipo
{

    public function alta($datos)
    {
        $obj = new CompraEstadoTipo();
        $obj->setear(
            $datos["idcompraestadotipo"],
            $datos["cetdescripcion"],
            $datos["cetdetalle"]
        );
        return $obj->insertar();
    }

    public function baja($datos)
    {
        if (!isset($datos["idcompraestadotipo"])) return false;

        $obj = new CompraEstadoTipo();
        $obj->setIdCompraEstadoTipo($datos["idcompraestadotipo"]);

        return $obj->eliminar();
    }

    public function modificacion($datos)
    {
        if (!isset($datos["idcompraestadotipo"])) return false;

        $obj = new CompraEstadoTipo();
        $obj->setear(
            $datos["idcompraestadotipo"],
            $datos["cetdescripcion"],
            $datos["cetdetalle"]
        );

        return $obj->modificar();
    }

    public function buscar($param = null)
    {
        $where = " true ";

        if ($param != null && isset($param["idcompraestadotipo"])) {
            $where .= " AND idcompraestadotipo = " . $param["idcompraestadotipo"];
        }

        return (new CompraEstadoTipo())->listar($where);
    }

    public function buscarPorDescripcion($descripcion)
{
    $modelo = new CompraEstadoTipo();
    $tipos = $modelo->listar("cetdescripcion = '$descripcion'");
    return $tipos[0] ?? null;
}

}
