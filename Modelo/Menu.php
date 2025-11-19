<?php

class Menu extends BaseDatos {

    private $idmenu;
    private $menombre;
    private $medescripcion;
    private $objmenupadre;      // Objeto Menu o null
    private $medeshabilitado;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idmenu = null;
        $this->menombre = "";
        $this->medescripcion = "";
        $this->objmenupadre = null;
        $this->medeshabilitado = 0;
        $this->mensajeoperacion = "";
    }

    
    public function setear($idmenu, $menombre, $medescripcion, $objpadre, $medeshabilitado) {
        $this->idmenu = $idmenu;
        $this->menombre = $menombre;
        $this->medescripcion = $medescripcion;
        $this->objmenupadre = $objpadre;
        $this->medeshabilitado = $medeshabilitado;
    }

    public function getIdMenu() { 
        return $this->idmenu; 
    }
    public function setIdMenu($id) { 
        $this->idmenu = $id; 
    }

    public function getMeNombre() { 
        return $this->menombre; 
    }
    public function setMeNombre($n) { 
        $this->menombre = $n; 
    }

    public function getMeDescripcion() {
         return $this->medescripcion; 
    }
    public function setMeDescripcion($d) {
         $this->medescripcion = $d; 
    }

    public function getObjMenuPadre() { 
        return $this->objmenupadre; 
    }
    public function setObjMenuPadre($o) { 
        $this->objmenupadre = $o; 
    }

    public function getMeDeshabilitado() { 
        return $this->medeshabilitado; 
    }
    public function setMeDeshabilitado($d) {
         $this->medeshabilitado = $d; 
    }

    public function getMensajeOperacion() {
         return $this->mensajeoperacion; 
    }
    public function setMensajeOperacion($m) { 
        $this->mensajeoperacion = $m; 
    }


    
    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM menu WHERE idmenu = " . $this->getIdMenu();

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);

            if ($res > 0) {
                $row = $this->Registro();

                // Si no tiene padre → null
                $padre = null;
                if (!empty($row['idpadre'])) {
                    $padre = new Menu();
                    $padre->setIdMenu($row['idpadre']);
                    $padre->cargar();
                }

                $this->setear(
                    $row['idmenu'],
                    $row['menombre'],
                    $row['medescripcion'],
                    $padre,
                    $row['medeshabilitado']
                );

                $resp = true;
            }
        } else {
            $this->mensajeoperacion = "menu->cargar: " . $this->getError();
        }
        return $resp;
    }

   public function insertar() {
    $resp = false;

    // Normalizar padre: puede venir objeto, número o null
    $idpadre = "NULL";

    if (!empty($this->objmenupadre)) {

        // Si NO es un objeto → convertirlo en objeto Menu
        if (!is_object($this->objmenupadre)) {
            $padreObj = new Menu();
            $padreObj->setIdMenu(intval($this->objmenupadre));
            $padreObj->cargar();
            $this->objmenupadre = $padreObj;
        }

        // Ahora sí, es un objeto
        $idpadre = $this->objmenupadre->getIdMenu();
    }

    $deshab = $this->medeshabilitado ?? 0;

    $sql = "INSERT INTO menu (menombre, medescripcion, idpadre, medeshabilitado)
            VALUES ('{$this->menombre}', '{$this->medescripcion}', $idpadre, $deshab)";

    if ($this->Iniciar()) {
        $id = $this->Ejecutar($sql);
        if ($id) {
            $this->idmenu = $id;
            $resp = true;
        } else {
            $this->mensajeoperacion = "menu->insertar: " . $this->getError();
        }
    }

    return $resp;
}


   public function modificar() {
    $resp = false;

    $idpadre = "NULL";

    if (!empty($this->objmenupadre)) {

        if (!is_object($this->objmenupadre)) {
            $padreObj = new Menu();
            $padreObj->setIdMenu(intval($this->objmenupadre));
            $padreObj->cargar();
            $this->objmenupadre = $padreObj;
        }

        $idpadre = $this->objmenupadre->getIdMenu();
    }

    $deshab = $this->medeshabilitado ?? 0;

    $sql = "UPDATE menu SET
                menombre = '{$this->menombre}',
                medescripcion = '{$this->medescripcion}',
                idpadre = $idpadre,
                medeshabilitado = $deshab
            WHERE idmenu = {$this->idmenu}";

    if ($this->Iniciar()) {
        if ($this->Ejecutar($sql)) {
            $resp = true;
        } else {
            $this->mensajeoperacion = "menu->modificar: " . $this->getError();
        }
    }

    return $resp;
}


  
    public function eliminar() {
        $resp = false;

        $sql = "DELETE FROM menu WHERE idmenu = {$this->idmenu}";

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->mensajeoperacion = "menu->eliminar: " . $this->getError();
            }
        }
        return $resp;
    }

    
    public static function listar($param = "") {
    $arreglo = [];
    $base = new BaseDatos();

    $sql = "SELECT * FROM menu";
    if ($param != "") {
        $sql .= " WHERE $param";
    }

    if ($base->Iniciar() && ($res = $base->Ejecutar($sql)) > 0) {
        while ($row = $base->Registro()) {

            $obj = new Menu();
            $padre = null;

            if (!empty($row['idpadre'])) {
                $padre = new Menu();
                $padre->setIdMenu($row['idpadre']);
                $padre->cargar();
            }

            $obj->setear(
                $row['idmenu'],
                $row['menombre'],
                $row['medescripcion'],
                $padre,
                $row['medeshabilitado']
            );

            $arreglo[] = $obj;
        }
    }

    return $arreglo;
    }  
}
?>
