<?php
class Producto extends BaseDatos
{
    private $idproducto;
    private $pronombre;
    private $prodetalle;
    private $proprecio;
    private $procantstock;
    private $prooferta;        // % de descuento
    private $profinoffer;      // fecha fin de oferta
    private $prodeshabilitado;
    private $idusuario;
    private $proimagen;

    public function __construct()
    {
        parent::__construct();
        $this->idproducto       = 0;
        $this->pronombre        = "";
        $this->prodetalle       = "";
        $this->proprecio        = 0;
        $this->procantstock     = 0;
        $this->prooferta        = 0;
        $this->profinoffer      = null;
        $this->prodeshabilitado = null;
        $this->idusuario        = 0;
        $this->proimagen        = null;
    }

    public function setear(
        $idproducto,
        $pronombre,
        $prodetalle,
        $proprecio,
        $procantstock,
        $prooferta,
        $profinoffer,
        $prodeshabilitado,
        $idusuario,
        $proimagen
    ) {
        $this->idproducto       = $idproducto;
        $this->pronombre        = $pronombre;
        $this->prodetalle       = $prodetalle;
        $this->proprecio        = $proprecio;
        $this->procantstock     = $procantstock;
        $this->prooferta        = $prooferta;
        $this->profinoffer      = $profinoffer;
        $this->prodeshabilitado = $prodeshabilitado;
        $this->idusuario        = $idusuario;
        $this->proimagen        = $proimagen;
    }

    // ========= Getters =========
    public function getIdProducto()
    {
        return $this->idproducto;
    }
    public function getProNombre()
    {
        return $this->pronombre;
    }
    public function getProDetalle()
    {
        return $this->prodetalle;
    }
    public function getProPrecio()
    {
        return $this->proprecio;
    }
    public function getProCantStock()
    {
        return $this->procantstock;
    }
    public function getProoferta()
    {
        return $this->prooferta;
    }
    public function getProFinOffer()
    {
        return $this->profinoffer;
    }
    public function getProDeshabilitado()
    {
        return $this->prodeshabilitado;
    }
    public function getIdUsuario()
    {
        return $this->idusuario;
    }
    public function getProimagen()
    {
        return $this->proimagen;
    }

    // ========= Setters =========
    public function setIdProducto($v)
    {
        $this->idproducto = $v;
    }
    public function setProNombre($v)
    {
        $this->pronombre = $v;
    }
    public function setProDetalle($v)
    {
        $this->prodetalle = $v;
    }
    public function setProPrecio($v)
    {
        $this->proprecio = $v;
    }
    public function setProCantStock($v)
    {
        $this->procantstock = $v;
    }
    public function setProoferta($v)
    {
        $this->prooferta = $v;
    }
    public function setProFinOffer($v)
    {
        $this->profinoffer = $v;
    }
    public function setProDeshabilitado($v)
    {
        $this->prodeshabilitado = $v;
    }
    public function setIdUsuario($v)
    {
        $this->idusuario = $v;
    }
    public function setProimagen($v)
    {
        $this->proimagen = $v;
    }

    // ========= Precio Final con Oferta =========
    public function getPrecioFinal()
    {
        if ($this->prooferta > 0) {

            // si hay fecha límite y la oferta expiró → ignorar oferta
            if ($this->profinoffer && strtotime($this->profinoffer) < time()) {
                return $this->proprecio;
            }

            return $this->proprecio * (1 - ($this->prooferta / 100));
        }

        return $this->proprecio;
    }

    // ========= Cargar =========
    public function cargar()
    {
        $sql = "SELECT * FROM producto WHERE idproducto = {$this->idproducto}";
        $res = $this->Ejecutar($sql);

        if ($res > 0) {
            $row = $this->Registro();
            $this->setear(
                $row['idproducto'],
                $row['pronombre'],
                $row['prodetalle'],
                $row['proprecio'],
                $row['procantstock'],
                $row['prooferta'],
                $row['profinoffer'],
                $row['prodeshabilitado'],
                $row['idusuario'],
                $row['proimagen']
            );
            return true;
        }
        return false;
    }

    // ========= Insertar =========
    public function insertar()
    {
        $sql = "INSERT INTO producto 
        (pronombre, prodetalle, proprecio, procantstock, prooferta, profinoffer, prodeshabilitado, idusuario, proimagen)
        VALUES (
            '{$this->pronombre}',
            '{$this->prodetalle}',
            {$this->proprecio},
            {$this->procantstock},
            {$this->prooferta},
            " . ($this->profinoffer ? "'{$this->profinoffer}'" : "NULL") . ",
            " . ($this->prodeshabilitado ? "'{$this->prodeshabilitado}'" : "NULL") . ",
            {$this->idusuario},
            " . ($this->proimagen ? "'{$this->proimagen}'" : "NULL") . "
        )";

        $id = $this->Ejecutar($sql);

        if ($id > 0) {
            $this->idproducto = $id;
            return true;
        }
        return false;
    }

    // ========= Modificar =========
    public function modificar()
    {
        $sql = "UPDATE producto SET
            pronombre = '{$this->pronombre}',
            prodetalle = '{$this->prodetalle}',
            proprecio = {$this->proprecio},
            procantstock = {$this->procantstock},
            prooferta = {$this->prooferta},
            profinoffer = " . ($this->profinoffer ? "'{$this->profinoffer}'" : "NULL") . ",
            proimagen = " . ($this->proimagen ? "'{$this->proimagen}'" : "NULL") . "
        WHERE idproducto = {$this->idproducto}";

        return $this->Ejecutar($sql) >= 0;
    }

    // === Baja lógica ===
    public function deshabilitar()
    {
        $sql = "UPDATE producto SET prodeshabilitado = NOW() WHERE idproducto = {$this->idproducto}";
        return $this->Ejecutar($sql) >= 0;
    }

    // === Rehabilitar ===
    public function habilitar()
    {
        $sql = "UPDATE producto SET prodeshabilitado = NULL WHERE idproducto = {$this->idproducto}";
        return $this->Ejecutar($sql) >= 0;
    }

    // ========= Listar =========
    public function listar($condicion = "")
    {
        $arreglo = [];
        $sql = "SELECT * FROM producto";
        if ($condicion != "") $sql .= " WHERE $condicion";

        $res = $this->Ejecutar($sql);

        if ($res > 0) {
            while ($row = $this->Registro()) {
                $obj = new Producto();
                $obj->setear(
                    $row['idproducto'],
                    $row['pronombre'],
                    $row['prodetalle'],
                    $row['proprecio'],
                    $row['procantstock'],
                    $row['prooferta'],
                    $row['profinoffer'],
                    $row['prodeshabilitado'],
                    $row['idusuario'],
                    $row['proimagen']
                );
                $arreglo[] = $obj;
            }
        }
        return $arreglo;
    }

    
}
