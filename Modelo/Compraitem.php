<?php
// modelo/CompraItem.php
class CompraItem
{
    private $idcompraitem;
    private $objcompra;
    private $objproducto;
    private $cicantidad;
    private $mensajeoperacion;

    public function __construct()
    {
        $this->idcompraitem = 0;
        $this->objcompra = new Compra();
        $this->objproducto = new Producto();
        $this->cicantidad = 0;
        $this->mensajeoperacion = "";
    }

    public function setear($idcompraitem, $objcompra, $objproducto, $cicantidad)
    {
        $this->idcompraitem = $idcompraitem;
        $this->objcompra = $objcompra;
        $this->objproducto = $objproducto;
        $this->cicantidad = $cicantidad;
    }

    public function getIdCompraItem() { return $this->idcompraitem; }
    public function getObjCompra() { return $this->objcompra; }
    public function getObjProducto() { return $this->objproducto; }
    public function getCiCantidad() { return $this->cicantidad; }

    public function setIdCompraItem($v) { $this->idcompraitem = $v; }
    public function setObjCompra($v) { $this->objcompra = $v; }
    public function setObjProducto($v) { $this->objproducto = $v; }
    public function setCiCantidad($v) { $this->cicantidad = $v; }

    public function getMensajeOperacion() { return $this->mensajeoperacion; }
    public function setMensajeOperacion($v) { $this->mensajeoperacion = $v; }

    // CARGAR
    public function cargar()
    {
        $resp = false;
        $sql = "SELECT * FROM compraitem WHERE idcompraitem = " . intval($this->getIdCompraItem());
        $base = new BaseDatos();
        if ($base->Iniciar()) {
            if ($base->Ejecutar($sql) > 0) {
                if ($row = $base->Registro()) {
                    $objProd = new Producto();
                    $objProd->setIdProducto($row['idproducto']);
                    $objProd->cargar();

                    $objCompra = new Compra();
                    $objCompra->setIdCompra($row['idcompra']);
                    $objCompra->cargar();

                    $this->setear($row['idcompraitem'], $objCompra, $objProd, $row['cicantidad']);
                    $resp = true;
                }
            } else {
                $this->setMensajeOperacion("CompraItem->cargar: " . $base->getError());
            }
        }
        return $resp;
    }

    // INSERTAR
    public function insertar()
    {
        $resp = false;
        $sql = "INSERT INTO compraitem (idproducto, idcompra, cicantidad) VALUES (
            " . intval($this->getObjProducto()->getIdProducto()) . ",
            " . intval($this->getObjCompra()->getIdCompra()) . ",
            " . intval($this->getCiCantidad()) . "
        )";
        $base = new BaseDatos();
        if ($base->Iniciar()) {
            if ($base->Ejecutar($sql) > 0) {
                // tu clase BaseDatos tiene ultimoId()
                $this->setIdCompraItem($base->ultimoId());
                $resp = true;
            } else {
                $this->setMensajeOperacion("CompraItem->insertar: " . $base->getError());
            }
        }
        return $resp;
    }

    // MODIFICAR
    public function modificar()
    {
        $resp = false;
        $sql = "UPDATE compraitem SET cicantidad = " . intval($this->getCiCantidad()) .
            " WHERE idcompraitem = " . intval($this->getIdCompraItem());
        $base = new BaseDatos();
        if ($base->Iniciar()) {
            if ($base->Ejecutar($sql) >= 0) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("CompraItem->modificar: " . $base->getError());
            }
        }
        return $resp;
    }

    // ELIMINAR
    public function eliminar()
    {
        $resp = false;
        $sql = "DELETE FROM compraitem WHERE idcompraitem = " . intval($this->getIdCompraItem());
        $base = new BaseDatos();
        if ($base->Iniciar()) {
            if ($base->Ejecutar($sql) >= 0) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("CompraItem->eliminar: " . $base->getError());
            }
        }
        return $resp;
    }

    // LISTAR (static)
    public static function listar($parametro = "")
    {
        $arreglo = [];
        $sql = "SELECT * FROM compraitem";
        if ($parametro != "") $sql .= " WHERE " . $parametro;
        $base = new BaseDatos();
        if ($base->Iniciar()) {
            if ($base->Ejecutar($sql) > 0) {
                while ($row = $base->Registro()) {
                    $obj = new CompraItem();
                    $objProd = new Producto();
                    $objProd->setIdProducto($row['idproducto']);
                    $objProd->cargar();

                    $objCompra = new Compra();
                    $objCompra->setIdCompra($row['idcompra']);
                    $objCompra->cargar();

                    $obj->setear($row['idcompraitem'], $objCompra, $objProd, $row['cicantidad']);
                    $arreglo[] = $obj;
                }
            }
        }
        return $arreglo;
    }
}
