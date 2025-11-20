<?php
class CompraItem extends BaseDatos {

    private $idcompraitem;
    private $objProducto;
    private $objCompra;
    private $cicantidad;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idcompraitem = 0;
        $this->objProducto = new Producto();
        $this->objCompra = new Compra();
        $this->cicantidad = 0;
        $this->mensajeoperacion = "";
    }

    public function setear($idcompraitem, $objProducto, $objCompra, $cicantidad) {
        $this->setIdCompraItem($idcompraitem);
        $this->setObjProducto($objProducto);
        $this->setObjCompra($objCompra);
        $this->setCiCantidad($cicantidad);
    }

    
    public function getIdCompraItem() { return $this->idcompraitem; }
    public function setIdCompraItem($valor) { $this->idcompraitem = $valor; }

    public function getObjProducto() { return $this->objProducto; }
    public function setObjProducto($valor) { $this->objProducto = $valor; }

    public function getObjCompra() { return $this->objCompra; }
    public function setObjCompra($valor) { $this->objCompra = $valor; }

    public function getCiCantidad() { return $this->cicantidad; }
    public function setCiCantidad($valor) { $this->cicantidad = $valor; }

    public function getMensajeOperacion() { return $this->mensajeoperacion; }
    public function setMensajeOperacion($valor) { $this->mensajeoperacion = $valor; }

    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM compraitem WHERE idcompraitem = " . $this->getIdCompraItem();
        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $objProd = new Producto();
                $objProd->setIdProducto($row['idproducto']);
                $objProd->cargar();

                $objCompra = new Compra();
                $objCompra->setIdCompra($row['idcompra']);
                $objCompra->cargar();

                $this->setear($row['idcompraitem'], $objProd, $objCompra, $row['cicantidad']);
                $resp = true;
            }
        }
        return $resp;
    }

    public function insertar() {
        $resp = false;
        $sql = "INSERT INTO compraitem (idproducto, idcompra, cicantidad) VALUES (
            " . $this->getObjProducto()->getIdProducto() . ",
            " . $this->getObjCompra()->getIdCompra() . ",
            " . $this->getCiCantidad() . "
        )";
        $id = $this->Ejecutar($sql);
        if ($id > 0) {
            $this->setIdCompraItem($id);
            $resp = true;
        } else {
            $this->setMensajeOperacion("CompraItem->insertar: " . $this->getError());
        }
        return $resp;
    }

    public function modificar() {
        $resp = false;
        $sql = "UPDATE compraitem SET 
                idproducto = " . $this->getObjProducto()->getIdProducto() . ",
                idcompra = " . $this->getObjCompra()->getIdCompra() . ",
                cicantidad = " . $this->getCiCantidad() . "
                WHERE idcompraitem = " . $this->getIdCompraItem();
        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("CompraItem->modificar: " . $this->getError());
        }
        return $resp;
    }

    public function eliminar() {
        $resp = false;
        $sql = "DELETE FROM compraitem WHERE idcompraitem = " . $this->getIdCompraItem();
        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("CompraItem->eliminar: " . $this->getError());
        }
        return $resp;
    }

    public function listar($parametro = "") {
        $arreglo = array();
        $sql = "SELECT ci.*, p.pronombre FROM compraitem ci 
                JOIN producto p ON ci.idproducto = p.idproducto";
        if ($parametro != "") {
            $sql .= " WHERE " . $parametro;
        }
        $res = $this->Ejecutar($sql);
        if ($res > 0) {
            while ($row = $this->Registro()) {
                $obj = new CompraItem();
                $objProd = new Producto();
                $objProd->setIdProducto($row['idproducto']);
                $objProd->cargar();

                $objCompra = new Compra();
                $objCompra->setIdCompra($row['idcompra']);
                $objCompra->cargar();

                $obj->setear($row['idcompraitem'], $objProd, $objCompra, $row['cicantidad']);
                array_push($arreglo, $obj);
            }
        }
        return $arreglo;
    }
}
?>