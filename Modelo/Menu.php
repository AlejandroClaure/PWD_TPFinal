<?php

class Menu extends BaseDatos
{

    private $idmenu;
    private $menombre;
    private $melink;
    private $objmenupadre;
    private $medescripcion;
    private $medeshabilitado;
    private $mensajeoperacion;

    public function __construct()
    {
        parent::__construct();
        $this->idmenu = null;
        $this->menombre = "";
        $this->melink = "";
        $this->objmenupadre = null;
        $this->medescripcion = "";
        $this->medeshabilitado = null;
        $this->mensajeoperacion = "";
    }

    public function setear($idmenu, $menombre, $melink, $objpadre, $medescripcion, $medeshabilitado)
    {
        $this->idmenu = $idmenu;
        $this->menombre = $menombre;
        $this->melink = $melink;
        $this->objmenupadre = $objpadre;
        $this->medescripcion = $medescripcion;
        $this->medeshabilitado = $medeshabilitado;
    }

    // ======== GETTERS & SETTERS ========

    public function getIdMenu()
    {
        return $this->idmenu;
    }
    public function setIdMenu($id)
    {
        $this->idmenu = $id;
    }

    public function getMeNombre()
    {
        return $this->menombre;
    }
    public function setMeNombre($n)
    {
        $this->menombre = $n;
    }

    public function getMeLink()
    {
        return $this->melink;
    }
    public function setMeLink($l)
    {
        $this->melink = $l;
    }

    public function getObjMenuPadre()
    {
        return $this->objmenupadre;
    }
    public function setObjMenuPadre($o)
    {
        $this->objmenupadre = $o;
    }

    public function getMeDescripcion()
    {
        return $this->medescripcion;
    }
    public function setMeDescripcion($d)
    {
        $this->medescripcion = $d;
    }

    public function getMeDeshabilitado()
    {
        return $this->medeshabilitado;
    }
    public function setMeDeshabilitado($d)
    {
        $this->medeshabilitado = $d;
    }

    public function getMensajeOperacion()
    {
        return $this->mensajeoperacion;
    }
    public function setMensajeOperacion($m)
    {
        $this->mensajeoperacion = $m;
    }


    public function cargar()
    {

        $resp = false;
        $sql = "SELECT * FROM menu WHERE idmenu = " . $this->idmenu;

        if ($this->Iniciar()) {

            $res = $this->Ejecutar($sql);
            if ($res > 0) {

                $row = $this->Registro();

                $padreObj = null;
                if (!empty($row['idpadre'])) {
                    $padreObj = new Menu();
                    $padreObj->setIdMenu($row['idpadre']);
                    $padreObj->cargar();
                }

                $this->setear(
                    $row['idmenu'],
                    $row['menombre'],
                    $row['melink'],
                    $padreObj,
                    $row['medescripcion'],
                    $row['medeshabilitado']
                );

                $resp = true;
            }
        } else {
            $this->mensajeoperacion = "menu->cargar: " . $this->getError();
        }

        return $resp;
    }


    public function insertar()
    {
        $idpadre = (!empty($this->objmenupadre))
            ? (is_object($this->objmenupadre) ? $this->objmenupadre->getIdMenu() : intval($this->objmenupadre))
            : "NULL";

        $melink = !empty($this->melink) ? "'" . addslashes($this->melink) . "'" : "NULL";

        // Manejo unificado de medeshabilitado
        if ($this->medeshabilitado === null || $this->medeshabilitado === "" || $this->medeshabilitado === "0000-00-00 00:00:00") {
            $deshab = "'0000-00-00 00:00:00'"; // habilitado
        } else {
            $deshab = "'" . addslashes($this->medeshabilitado) . "'"; // fecha válida
        }

        $descripcion = addslashes($this->medescripcion ?? "");

        $sql = "INSERT INTO menu (menombre, melink, idpadre, medescripcion, medeshabilitado)
            VALUES ('" . addslashes($this->menombre) . "', $melink, $idpadre, '$descripcion', $deshab)";

        if ($this->Iniciar()) {
            $id = $this->Ejecutar($sql);
            if ($id) {
                $this->idmenu = $id;
                return true;
            } else {
                $this->mensajeoperacion = "menu->insertar: " . $this->getError();
            }
        } else {
            $this->mensajeoperacion = "menu->insertar: no se pudo iniciar BD";
        }

        return false;
    }



    public function modificar()
    {
        $idpadre = ($this->objmenupadre instanceof Menu)
            ? $this->objmenupadre->getIdMenu()
            : "NULL";

        $menombre = addslashes($this->menombre);
        $melink = !empty($this->melink) ? "'" . addslashes($this->melink) . "'" : "NULL";
        $medescripcion = addslashes($this->medescripcion);

        // Unificar lógica: 1=deshabilitar → fecha / 0=habilitar → 0000-00-00
        if ($this->medeshabilitado == 1) {
            $medeshabilitado = "'" . date("Y-m-d H:i:s") . "'";
        } else {
            $medeshabilitado = "'0000-00-00 00:00:00'";
        }

        $sql = "UPDATE menu SET
            menombre = '$menombre',
            melink = $melink,
            idpadre = $idpadre,
            medescripcion = '$medescripcion',
            medeshabilitado = $medeshabilitado
            WHERE idmenu = {$this->idmenu}";

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res === false) {
                $this->mensajeoperacion = "menu->modificar: " . $this->getError();
                return false;
            }
            return true;
        } else {
            $this->mensajeoperacion = "menu->modificar: no se pudo iniciar BD";
            return false;
        }
    }



    public function eliminar()
    {
        $sql = "DELETE FROM menu WHERE idmenu = {$this->idmenu}";

        if ($this->Iniciar()) {
            return $this->Ejecutar($sql) !== false;
        }

        return false;
    }

    public static function listar($param = "")
    {

        $arreglo = [];
        $base = new BaseDatos();

        $sql = "SELECT * FROM menu";
        if (!empty($param)) {
            $sql .= " WHERE $param";
        }

        if ($base->Iniciar() && ($res = $base->Ejecutar($sql)) > 0) {

            while ($row = $base->Registro()) {

                $obj = new Menu();

                $padreObj = null;
                if (!empty($row['idpadre'])) {
                    $padreObj = new Menu();
                    $padreObj->setIdMenu($row['idpadre']);
                    $padreObj->cargar();
                }

                $obj->setear(
                    $row['idmenu'],
                    $row['menombre'],
                    $row['melink'],
                    $padreObj,
                    $row['medescripcion'],
                    $row['medeshabilitado']
                );

                $arreglo[] = $obj;
            }
        }

        return $arreglo;
    }
}
