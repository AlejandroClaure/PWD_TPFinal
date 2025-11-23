<?php
class AbmCompraEstado {

    // Alta: crea un nuevo estado y cierra el anterior
    public function alta($datos) {
    // Cerrar estado anterior usando el ABM (correcto)
    $estadoActual = $this->obtenerEstadoActual($datos['idcompra']);
    if ($estadoActual) {
        $estadoActual->setCeFechaFin(date('Y-m-d H:i:s'));
        $estadoActual->modificar();
    }

    $objCompra = new Compra();
    $objCompra->setIdCompra($datos['idcompra']);
    if (!$objCompra->cargar()) return false;

    $objTipo = new CompraEstadoTipo();
    $objTipo->setIdCompraEstadoTipo($datos['idcompraestadotipo']);
    if (!$objTipo->cargar()) return false;

    $nuevo = new CompraEstado();
    $nuevo->setear(
        0,
        $objCompra,
        $objTipo,
        $datos['cefechaini'] ?? date('Y-m-d H:i:s'),
        null
    );

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

    // Control/AbmCompraEstado.php

public function cambiarEstadoCompra($idCompra, $nuevoEstadoTipo)
{
    $nuevoEstadoTipo = (int)$nuevoEstadoTipo;
    if (!in_array($nuevoEstadoTipo, [2, 3, 4])) return false; // solo aceptada, enviada, cancelada

    // 1. Cerrar estado actual (si existe y no está cerrado)
    $estadoActual = $this->buscar([
        'idcompra' => $idCompra,
        'cefechafin' => null
    ]);

    if (!empty($estadoActual)) {
        $estado = $estadoActual[0];
        $estado->setCeFechaFin(date('Y-m-d H:i:s'));
        $estado->modificar();
    }

    // 2. Crear nuevo estado
    $datos = [
        'idcompra' => $idCompra,
        'idcompraestadotipo' => $nuevoEstadoTipo,
        'cefechaini' => date('Y-m-d H:i:s'),
        'cefechafin' => null
    ];

    return $this->alta($datos);
}

public function obtenerEstadoActual($idCompra)
{
    $estados = $this->buscar([
        'idcompra' => $idCompra,
        'cefechafin' => null
    ]);
    return !empty($estados) ? $estados[0] : null;
}
}
?>
