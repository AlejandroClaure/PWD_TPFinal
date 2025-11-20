<?php
class Compra extends BaseDatos {

    private $idcompra;
    private $cofecha;
    private $idusuario;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idcompra = 0;
        $this->cofecha = "";
        $this->idusuario = 0;
        $this->mensajeoperacion = "";
    }

    public function setear($id, $fecha, $idusuario) {
        $this->setIdCompra($id);
        $this->setCoFecha($fecha);
        $this->setIdUsuario($idusuario);
    }

    
    public function getIdCompra() { return $this->idcompra; }
    public function setIdCompra($valor) { $this->idcompra = $valor; }

    public function getCoFecha() { return $this->cofecha; }
    public function setCoFecha($valor) { $this->cofecha = $valor; }

    public function getIdUsuario() { return $this->idusuario; }
    public function setIdUsuario($valor) { $this->idusuario = $valor; }

    public function getMensajeOperacion() { return $this->mensajeoperacion; }
    public function setMensajeOperacion($valor) { $this->mensajeoperacion = $valor; }


    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM compra WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $this->setear($row["idcompra"], $row["cofecha"], $row["idusuario"]);
                $resp = true;
            }
        }

        return $resp;
    }


    public function insertar() {
        $resp = false;
        $sql = "INSERT INTO compra (cofecha, idusuario)
                VALUES (
                    '" . $this->getCoFecha() . "',
                    " . $this->getIdUsuario() . "
                )";

        if ($this->Ejecutar($sql) >= 0) {
            // Recupera el último id auto_increment
            $id = $this->ultimoId();
            if ($id !== null) {
                $this->setIdCompra($id);
                $resp = true;
            }
        } else {
            $this->setMensajeOperacion("Compra->insertar: " . $this->getError());
        }

        return $resp;
    }


    public function modificar() {
        $resp = false;

        $sql = "UPDATE compra SET
                cofecha = '" . $this->getCoFecha() . "',
                idusuario = " . $this->getIdUsuario() . "
                WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("Compra->modificar: " . $this->getError());
        }

        return $resp;
    }


    public function eliminar() {
        $resp = false;

        $sql = "DELETE FROM compra WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("Compra->eliminar: " . $this->getError());
        }

        return $resp;
    }


    public function listar($parametro = "") {
        $arreglo = array();
        $sql = "SELECT * FROM compra";

        if ($parametro != "") {
            $sql .= " WHERE " . $parametro;
        }
        $sql .= " ORDER BY idcompra";

        if ($this->Ejecutar($sql) > 0) {
            while ($row = $this->Registro()) {
                $obj = new Compra();
                $obj->setear(
                    $row["idcompra"],
                    $row["cofecha"],
                    $row["idusuario"]
                );
                array_push($arreglo, $obj);
            }
        }

        return $arreglo;
    }
}
?>
