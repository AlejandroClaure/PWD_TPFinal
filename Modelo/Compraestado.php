<?php
class CompraEstado extends BaseDatos {

    private $idcompraestado;
    private $objCompra;
    private $objCompraEstadoTipo;
    private $cefechaini;
    private $cefechafin;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idcompraestado = 0;
        $this->objCompra = new Compra();
        $this->objCompraEstadoTipo = new CompraEstadoTipo();
        $this->cefechaini = "";
        $this->cefechafin = null;
        $this->mensajeoperacion = "";
    }

    public function setear($id, $objCompra, $objTipo, $fechaini, $fechafin) {
        $this->setIdCompraEstado($id);
        $this->setObjCompra($objCompra);
        $this->setObjCompraEstadoTipo($objTipo);
        $this->setCeFechaIni($fechaini);
        $this->setCeFechaFin($fechafin);
    }

    
    public function getIdCompraEstado() { return $this->idcompraestado; }
    public function setIdCompraEstado($v) { $this->idcompraestado = $v; }
    public function getObjCompra() { return $this->objCompra; }
    public function setObjCompra($v) { $this->objCompra = $v; }
    public function getObjCompraEstadoTipo() { return $this->objCompraEstadoTipo; }
    public function setObjCompraEstadoTipo($v) { $this->objCompraEstadoTipo = $v; }
    public function getCeFechaIni() { return $this->cefechaini; }
    public function setCeFechaIni($v) { $this->cefechaini = $v; }
    public function getCeFechaFin() { return $this->cefechafin; }
    public function setCeFechaFin($v) { $this->cefechafin = $v; }
    public function getMensajeOperacion() { return $this->mensajeoperacion; }
    public function setMensajeOperacion($v) { $this->mensajeoperacion = $v; }

    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM compraestado WHERE idcompraestado = " . $this->getIdCompraEstado();
        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $objCompra = new Compra();
                $objCompra->setIdCompra($row['idcompra']);
                $objCompra->cargar();

                $objTipo = new CompraEstadoTipo();
                $objTipo->setIdCompraEstadoTipo($row['idcompraestadotipo']);
                $objTipo->cargar();

                $this->setear($row['idcompraestado'], $objCompra, $objTipo, $row['cefechaini'], $row['cefechafin']);
                $resp = true;
            }
        }
        return $resp;
    }

    public function insertar() {
        $resp = false;
        $fechafin = $this->getCeFechaFin() ? "'" . $this->getCeFechaFin() . "'" : "NULL";
        $sql = "INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini, cefechafin) VALUES (
            " . $this->getObjCompra()->getIdCompra() . ",
            " . $this->getObjCompraEstadoTipo()->getIdCompraEstadoTipo() . ",
            '" . $this->getCeFechaIni() . "',
            $fechafin
        )";
        $id = $this->Ejecutar($sql);
        if ($id > 0) {
            $this->setIdCompraEstado($id);
            $resp = true;
        } else {
            $this->setMensajeOperacion("CompraEstado->insertar: " . $this->getError());
        }
        return $resp;
    }

    public function modificar() {
        $resp = false;
        $fechafin = $this->getCeFechaFin() ? "'" . $this->getCeFechaFin() . "'" : "NULL";
        $sql = "UPDATE compraestado SET 
                cefechafin = $fechafin
                WHERE idcompraestado = " . $this->getIdCompraEstado();
        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        }
        return $resp;
    }

    public function eliminar() {
        $resp = false;
        $sql = "DELETE FROM compraestado WHERE idcompraestado = " . $this->getIdCompraEstado();
        if ($this->Ejecutar($sql) >= 0) $resp = true;
        return $resp;
    }

    public function listar($parametro = "") {
        $arreglo = array();
        $sql = "SELECT ce.*, cet.cetdescripcion FROM compraestado ce 
                JOIN compraestadotipo cet ON ce.idcompraestadotipo = cet.idcompraestadotipo";
        if ($parametro != "") $sql .= " WHERE " . $parametro;
        $sql .= " ORDER BY cefechaini DESC";
        $res = $this->Ejecutar($sql);
        if ($res > 0) {
            while ($row = $this->Registro()) {
                $obj = new CompraEstado();

                $objCompra = new Compra();
                $objCompra->setIdCompra($row['idcompra']);
                $objCompra->cargar();

                $objTipo = new CompraEstadoTipo();
                $objTipo->setIdCompraEstadoTipo($row['idcompraestadotipo']);
                $objTipo->cargar();

                $obj->setear($row['idcompraestado'], $objCompra, $objTipo, $row['cefechaini'], $row['cefechafin']);
                array_push($arreglo, $obj);
            }
        }
        return $arreglo;
    }

    // Método útil: cerrar estado actual de una compra
    public function cerrarEstadoActual($idcompra) {
        $sql = "UPDATE compraestado SET cefechafin = NOW() 
                WHERE idcompra = $idcompra AND cefechafin IS NULL";
        return $this->Ejecutar($sql) >= 0;
    }
}
?>