<?php

class Usuariorol extends BaseDatos
{

    private $objusuario;
    private $objrol;
    private $mensajeoperacion;

    public function __construct()
    {
        parent::__construct();
        $this->objusuario = new Usuario();
        $this->objrol = new Rol();
    }

    public function setear($objusuario, $objrol)
    {
        $this->setObjUsuario($objusuario);
        $this->setObjRol($objrol);
    }

    public function setearConClave($idusuario, $idjrol)
    {
        $this->getObjRol()->setIdRol($idjrol);
        $this->getObjUsuario()->setIdUsuario($idusuario);
    }

    public function getObjUsuario()
    {
        return $this->objusuario;
    }
    public function setObjUsuario($objusuario)
    {
        $this->objusuario = $objusuario;
    }
    public function getObjRol()
    {
        return $this->objrol;
    }
    public function setObjRol($objrol)
    {
        $this->objrol = $objrol;
    }
    public function getMensajeOperacion()
    {
        return $this->mensajeoperacion;
    }
    public function setMensajeOperacion($valor)
    {
        $this->mensajeoperacion = $valor;
    }

    public function cargar() {
    $resp = false;
    $sql = "SELECT * FROM usuariorol WHERE idrol = " . $this->getObjRol()->getIdRol() . 
           " AND idusuario = " . $this->getObjUsuario()->getIdUsuario() . ";";
    
    if ($this->Iniciar()) {
        $res = $this->Ejecutar($sql);
        if ($res > -1) {
            if ($res > 0) {
                $row = $this->Registro();
                $obj1 = new Usuario();
                $obj1->setIdUsuario($row['idusuario']);
                $obj1->cargar();  // Esto carga los datos del usuario
                
                $obj2 = new Rol();
                $obj2->setIdRol($row['idrol']);
                $obj2->cargar();  // Esto carga los datos del rol
                
                $this->setear($obj1, $obj2);
                $resp = true; 
            }
        } else {
            $this->setMensajeOperacion("UsuarioRol->cargar: " . $this->getError());
        }
    }
    return $resp;  
}

    public function insertar()
    {
        $resp = false;
        $sql = "INSERT INTO usuariorol(idrol,idusuario)  VALUES(" . $this->getObjRol()->getIdRol() . "," . $this->getObjUsuario()->getIdUsuario() . ");";
        if ($this->Iniciar()) {
            if ($elid = $this->Ejecutar($sql)) {
                // $this->setidrol($elid);
                $resp = true;
            } else {
                $this->setMensajeOperacion("Usuario->insertar: " . $this->getError());
            }
        } else {
            $this->setMensajeOperacion("Usuario->insertar: " . $this->getError());
        }
        return $resp;
    }

    public function modificar()
    {
        $resp = false;
        return $resp;
    }


   public function eliminar() {
    $resp = false;
    $sql = "DELETE FROM usuariorol WHERE idrol=" . $this->getObjRol()->getIdRol() . 
           " AND idusuario =" . $this->getObjUsuario()->getIdUsuario() . ";";
    
    if ($this->Iniciar()) {
        if ($this->Ejecutar($sql) > -1) {  // Ejecutar devuelve filas afectadas
            $resp = true;
        } else {
            $this->setMensajeOperacion("UsuarioRol->eliminar: " . $this->getError());
        }
    }
    return $resp;
}

    public function listar($parametro = "")
    {
        $arreglo = array();
        $sql = "SELECT * FROM usuariorol ";
        if ($parametro != "") {
            $sql .= 'WHERE ' . $parametro;
        }
        if ($this->Iniciar()) {
            // echo $sql;
            $res = $this->Ejecutar($sql);
            if ($res > -1) {
                if ($res > 0) {
                    while ($row = $this->Registro()) {
                        $obj = new UsuarioRol();

                        $obj->getObjUsuario()->setIdUsuario($row['idusuario']);
                        $obj->getObjRol()->setIdRol($row['idrol']);
                        $obj->cargar();
                        array_push($arreglo, $obj);
                    }
                }
            } else {
                $this->setMensajeOperacion("Usuario->listar: " . $this->getError());
            }
        }
        return $arreglo;
    }

    public function obtenerRoles()
    {
        $roles = [];
        $idUsuario = $this->getObjUsuario()->getIdUsuario();

        $sql = "SELECT r.rodescripcion 
            FROM usuariorol ur 
            INNER JOIN rol r ON ur.idrol = r.idrol 
            WHERE ur.idusuario = " . $idUsuario;

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                while ($row = $this->Registro()) {
                    $roles[] = $row['rodescripcion'];
                }
            }
        }
        return $roles;
    }

    public function obtenerRolesConID()
    {
        $roles = [];
        $idUsuario = $this->getObjUsuario()->getIdUsuario();

        $sql = "SELECT r.idrol, r.rodescripcion 
            FROM usuariorol ur 
            INNER JOIN rol r ON ur.idrol = r.idrol 
            WHERE ur.idusuario = " . $idUsuario;

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                while ($row = $this->Registro()) {
                    $roles[] = [
                        "idrol" => $row["idrol"],
                        "rodescripcion" => $row["rodescripcion"]
                    ];
                }
            }
        }
        return $roles;
    }
}
