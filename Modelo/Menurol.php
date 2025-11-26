<?php
include_once __DIR__ . '/Menu.php';
include_once __DIR__ . '/Rol.php';

class MenuRol extends BaseDatos {

    private $objMenu;
    private $objRol;
    private $mensajeOperacion;

    public function __construct() {
        parent::__construct();
        $this->objMenu = new Menu();
        $this->objRol = new Rol();
        $this->mensajeOperacion = "";
    }

    public function setear($menu, $rol) {
        $this->objMenu = $menu;
        $this->objRol = $rol;
    }

    public function getObjMenu() {
        return $this->objMenu;
    }

    public function setObjMenu($obj) {
        $this->objMenu = $obj;
    }

    public function getObjRol() {
        return $this->objRol;
    }

    public function setObjRol($obj) {
        $this->objRol = $obj;
    }

    public function getMensajeOperacion() {
        return $this->mensajeOperacion;
    }

    public function setMensajeOperacion($valor) {
        $this->mensajeOperacion = $valor;
    }

    /**
     * Carga una relación menu–rol
     */
    public function cargar() {
        $resp = false;

        $sql = "SELECT * FROM menurol 
                WHERE idmenu = " . $this->objMenu->getIdMenu() . 
                " AND idrol = " . $this->objRol->getIdRol();

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);

            if ($res > 0) {
                $row = $this->Registro();

                $menu = new Menu();
                $menu->setIdMenu($row['idmenu']);
                $menu->cargar();

                $rol = new Rol();
                $rol->setIdRol($row['idrol']);
                $rol->cargar();

                $this->setear($menu, $rol);
                $resp = true;
            }

        } else {
            $this->setMensajeOperacion("menurol->cargar: " . $this->getError());
        }

        return $resp;
    }

    /**
     * Inserta una relación menu–rol
     */
    public function insertar() {
        $resp = false;

        $sql = "INSERT INTO menurol (idmenu, idrol)
                VALUES (" .
                $this->objMenu->getIdMenu() . ", " .
                $this->objRol->getIdRol() . ");";

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("menurol->insertar: " . $this->getError());
            }
        }

        return $resp;
    }

    /**
     * Modifica (solo si tu modelo lo pide)
     */
    public function modificar() {
        $resp = false;

        $sql = "UPDATE menurol 
                SET idrol = " . $this->objRol->getIdRol() . 
                " WHERE idmenu = " . $this->objMenu->getIdMenu();

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("menurol->modificar: " . $this->getError());
            }
        }

        return $resp;
    }

    /**
     * Borra una relación menu–rol
     */
    public function eliminar() {
        $resp = false;

        $sql = "DELETE FROM menurol 
                WHERE idmenu = " . $this->objMenu->getIdMenu() .
                " AND idrol = " . $this->objRol->getIdRol();

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("menurol->eliminar: " . $this->getError());
            }
        }

        return $resp;
    }

    /**
     * Lista relaciones menu–rol
     */
    public function listar($parametro = "") {
        $arreglo = [];
        $sql = "SELECT * FROM menurol ";

        if ($parametro != "") {
            $sql .= "WHERE " . $parametro;
        }

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                while ($row = $this->Registro()) {

                    $menu = new Menu();
                    $menu->setIdMenu($row['idmenu']);
                    $menu->cargar();

                    $rol = new Rol();
                    $rol->setIdRol($row['idrol']);
                    $rol->cargar();

                    $obj = new MenuRol();
                    $obj->setear($menu, $rol);

                    $arreglo[] = $obj;
                }
            }
        } else {
            $this->setMensajeOperacion("menurol->listar: " . $this->getError());
        }

        return $arreglo;
    }
}
?>
