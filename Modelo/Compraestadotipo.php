<?php
class CompraEstadoTipo extends BaseDatos {

    private $idcompraestadotipo;
    private $cetdescripcion;
    private $cetdetalle;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idcompraestadotipo = 0;
        $this->cetdescripcion = "";
        $this->cetdetalle = "";
        $this->mensajeoperacion = "";
    }

    public function setear($id, $descripcion, $detalle) {
        $this->setIdCompraEstadoTipo($id);
        $this->setCetDescripcion($descripcion);
        $this->setCetDetalle($detalle);
    }

    
    public function getIdCompraEstadoTipo() { return $this->idcompraestadotipo; }
    public function setIdCompraEstadoTipo($valor) { $this->idcompraestadotipo = $valor; }
    public function getCetDescripcion() { return $this->cetdescripcion; }
    public function setCetDescripcion($valor) { $this->cetdescripcion = $valor; }
    public function getCetDetalle() { return $this->cetdetalle; }
    public function setCetDetalle($valor) { $this->cetdetalle = $valor; }
    public function getMensajeOperacion() { return $this->mensajeoperacion; }
    public function setMensajeOperacion($valor) { $this->mensajeoperacion = $valor; }

    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM compraestadotipo WHERE idcompraestadotipo = " . $this->getIdCompraEstadoTipo();
        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $this->setear($row['idcompraestadotipo'], $row['cetdescripcion'], $row['cetdetalle']);
                $resp = true;
            }
        }
        return $resp;
    }

    public function insertar() {
        $resp = false;
        $sql = "INSERT INTO compraestadotipo (idcompraestadotipo, cetdescripcion, cetdetalle) VALUES (
            " . $this->getIdCompraEstadoTipo() . ",
            '" . $this->getCetDescripcion() . "',
            '" . $this->getCetDetalle() . "'
        )";
        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("CompraEstadoTipo->insertar: " . $this->getError());
        }
        return $resp;
    }

    // Normalmente no se modifica ni elimina (son tipos fijos)
    public function modificar() { return false; }
    public function eliminar() { return false; }

    public function listar($parametro = "") {
        $arreglo = array();
        $sql = "SELECT * FROM compraestadotipo";
        if ($parametro != "") $sql .= " WHERE " . $parametro;
        $res = $this->Ejecutar($sql);
        if ($res > 0) {
            while ($row = $this->Registro()) {
                $obj = new CompraEstadoTipo();
                $obj->setear($row['idcompraestadotipo'], $row['cetdescripcion'], $row['cetdetalle']);
                array_push($arreglo, $obj);
            }
        }
        return $arreglo;
    }
}
?>