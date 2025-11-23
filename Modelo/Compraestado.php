<?php
class CompraEstado extends BaseDatos
{

    private $idCompraEstado;
    private $objCompra;
    private $objCompraEstadoTipo;
    private $ceFechaIni;
    private $ceFechaFin;
    private $mensajeOperacion;

    public function __construct()
    {
        parent::__construct();
        $this->idCompraEstado = 0;
        $this->objCompra = new Compra();
        $this->objCompraEstadoTipo = new CompraEstadoTipo();
        $this->ceFechaIni = "";
        $this->ceFechaFin = null;
        $this->mensajeOperacion = "";
    }

    public function setear($id, $objCompra, $objTipo, $fechaIni, $fechaFin)
    {
        $this->setIdCompraEstado($id);
        $this->setObjCompra($objCompra);
        $this->setObjCompraEstadoTipo($objTipo);
        $this->setCeFechaIni($fechaIni);
        $this->setCeFechaFin($fechaFin);
    }

    // Getters y Setters
    public function getIdCompraEstado()
    {
        return $this->idCompraEstado;
    }
    public function setIdCompraEstado($v)
    {
        $this->idCompraEstado = $v;
    }

    public function getObjCompra()
    {
        return $this->objCompra;
    }
    public function setObjCompra($v)
    {
        $this->objCompra = $v;
    }

    public function getObjCompraEstadoTipo()
    {
        return $this->objCompraEstadoTipo;
    }
    public function setObjCompraEstadoTipo($v)
    {
        $this->objCompraEstadoTipo = $v;
    }

    // Getter directo del tipo para ABM/acciones
    public function getIdCompraEstadoTipo()
    {
        return $this->objCompraEstadoTipo->getIdCompraEstadoTipo();
    }

    public function getCeFechaIni()
    {
        return $this->ceFechaIni;
    }
    public function setCeFechaIni($v)
    {
        $this->ceFechaIni = $v;
    }

    public function getCeFechaFin()
    {
        return $this->ceFechaFin;
    }
    public function setCeFechaFin($v)
    {
        $this->ceFechaFin = $v;
    }

    public function getMensajeOperacion()
    {
        return $this->mensajeOperacion;
    }
    public function setMensajeOperacion($v)
    {
        $this->mensajeOperacion = $v;
    }

    // Cargar por id
    public function cargar()
    {
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

    // Insertar
    public function insertar()
    {
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

    // Modificar
    public function modificar()
    {
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

    // Eliminar
    public function eliminar()
    {
        $resp = false;
        $sql = "DELETE FROM compraestado WHERE idcompraestado = " . $this->getIdCompraEstado();
        if ($this->Ejecutar($sql) >= 0) $resp = true;
        return $resp;
    }

    // Listar
    public function listar($parametro = "")
    {
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
                $arreglo[] = $obj;
            }
        }
        return $arreglo;
    }

    // Cerrar estado actual
    public function cerrarEstadoActual($idcompra)
    {
        $sql = "UPDATE compraestado SET cefechafin = NOW() 
                WHERE idcompra = $idcompra AND cefechafin IS NULL";
        return $this->Ejecutar($sql) >= 0;
    }
}
