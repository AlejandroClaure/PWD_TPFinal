<?php
class Producto extends BaseDatos
{

    private $idproducto;
    private $pronombre;
    private $prodetalle;
    private $procantstock;
    private $prodeshabilitado;
    private $idusuario;

    public function __construct()
    {
        parent::__construct();
        $this->idproducto = 0;
        $this->pronombre = "";
        $this->prodetalle = "";
        $this->procantstock = 0;
        $this->prodeshabilitado = null;
        $this->idusuario = 0;
    }

    public function setear($idproducto, $pronombre, $prodetalle, $procantstock, $prodeshabilitado, $idusuario)
    {
        $this->idproducto = $idproducto;
        $this->pronombre = $pronombre;
        $this->prodetalle = $prodetalle;
        $this->procantstock = $procantstock;
        $this->prodeshabilitado = $prodeshabilitado;
        $this->idusuario = $idusuario;
    }
    
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
    public function getProCantStock()
    {
        return $this->procantstock;
    }
    public function getProDeshabilitado()
    {
        return $this->prodeshabilitado;
    }
    public function getIdUsuario()
    {
        return $this->idusuario;
    }

    
    public function setIdProducto($idproducto)
    {
        $this->idproducto = $idproducto;
    }

    public function setProNombre($pronombre)
    {
        $this->pronombre = $pronombre;
    }

    public function setProDetalle($prodetalle)
    {
        $this->prodetalle = $prodetalle;
    }

    public function setProCantStock($procantstock)
    {
        $this->procantstock = $procantstock;
    }

    public function setProDeshabilitado($prodeshabilitado)
    {
        $this->prodeshabilitado = $prodeshabilitado;
    }

    public function setIdUsuario($idusuario)
    {
        $this->idusuario = $idusuario;
    }


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
                $row['procantstock'],
                $row['prodeshabilitado'],
                $row['idusuario']
            );
            return true;
        }
        return false;
    }

    public function insertar()
    {
        $sql = "INSERT INTO producto (pronombre, prodetalle, procantstock, idusuario)
                VALUES (
                    '{$this->pronombre}',
                    '{$this->prodetalle}',
                    {$this->procantstock},
                    {$this->idusuario}
                )";

        $id = $this->Ejecutar($sql);
        if ($id > 0) {
            $this->idproducto = $id;
            return true;
        }
        return false;
    }

    public function modificar()
    {
        $sql = "UPDATE producto SET 
                pronombre = '{$this->pronombre}', 
                prodetalle = '{$this->prodetalle}',
                procantstock = {$this->procantstock}
                WHERE idproducto = {$this->idproducto}";
        return $this->Ejecutar($sql) >= 0;
    }

    public function eliminar()
    {
        $sql = "DELETE FROM producto WHERE idproducto = {$this->idproducto}";
        return $this->Ejecutar($sql) >= 0;
    }

    public function listar($condicion = "")
    {
        $arreglo = [];
        $sql = "SELECT * FROM producto";
        if ($condicion != "") {
            $sql .= " WHERE " . $condicion;
        }

        $res = $this->Ejecutar($sql);

        if ($res > 0) {
            while ($row = $this->Registro()) {
                $obj = new Producto();
                $obj->setear(
                    $row['idproducto'],
                    $row['pronombre'],
                    $row['prodetalle'],
                    $row['procantstock'],
                    $row['prodeshabilitado'],
                    $row['idusuario']
                );
                $arreglo[] = $obj;
            }
        }
        return $arreglo;
    }
}
