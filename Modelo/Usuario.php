<?php
include_once __DIR__ . '/Rol.php'; // Clase Rol
include_once __DIR__ . '/conector/BaseDatos.php'; // Clase BaseDatos

class Usuario extends BaseDatos {

    private $idusuario;
    private $usnombre;
    private $uspass;
    private $usmail;
    private $usdeshabilitado;
    private $mensajeoperacion;

    // Array de objetos Rol
    private $roles;

    public function __construct() {
        parent::__construct();
        $this->idusuario = "";
        $this->usnombre = "";
        $this->uspass = "";
        $this->usmail = "";
        $this->usdeshabilitado = 0;
        $this->mensajeoperacion = "";
        $this->roles = [];
    }

    
    public function setear($idusuario, $usnombre, $uspass, $usmail, $usdeshabilitado) {
        $this->setIdUsuario($idusuario);
        $this->setUsNombre($usnombre);
        $this->setUsPass($uspass);
        $this->setUsMail($usmail);
        $this->setUsDeshabilitado($usdeshabilitado);
    }

   
    public function getIdUsuario() { 
        return $this->idusuario; 
    }
    public function setIdUsuario($idusuario) {
         $this->idusuario = $idusuario; 
        }

    public function getUsNombre() { 
        return $this->usnombre; 
    }
    public function setUsNombre($usnombre) { 
        $this->usnombre = $usnombre; 
    }

    public function getUsPass() { 
        return $this->uspass; 
    }
    public function setUsPass($uspass) {
         $this->uspass = $uspass; 
    }

    public function getUsMail() { 
        return $this->usmail; 
    }
    public function setUsMail($usmail) {
         $this->usmail = $usmail; 
    }

    public function getUsDeshabilitado() { 
        return $this->usdeshabilitado; 
    }
    public function setUsDeshabilitado($usdeshabilitado) {
         $this->usdeshabilitado = $usdeshabilitado; 
    }

    public function getMensajeOperacion() { 
        return $this->mensajeoperacion; 
    }
    public function setMensajeOperacion($valor) {
         $this->mensajeoperacion = $valor; 
    }

    public function getRoles() { 
        return $this->roles; 
    }
    public function setRoles($roles) { 
        $this->roles = $roles; 
    }

    public function cargarRoles() {
        $this->roles = [];
        $sql = "SELECT r.idrol, r.rodescripcion 
                FROM rol r 
                INNER JOIN usuariorol ur ON r.idrol = ur.idrol
                WHERE ur.idusuario = " . $this->getIdUsuario();

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                while ($row = $this->Registro()) {
                    $rol = new Rol();
                    $rol->setear($row['idrol'], $row['rodescripcion']);
                    $this->roles[] = $rol;
                }
            }
        } else {
            $this->setMensajeOperacion("Usuario->cargarRoles: " . $this->getError());
        }
    }

    
    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM usuario WHERE idusuario = " . $this->getIdUsuario();

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                $row = $this->Registro();
                $this->setear(
                    $row['idusuario'],
                    $row['usnombre'],
                    $row['uspass'],
                    $row['usmail'],
                    $row['usdeshabilitado']
                );
                $resp = true;

                // ⚡ Cargar roles asociados
                $this->cargarRoles();
            }
        } else {
            $this->setMensajeOperacion("Usuario->cargar: " . $this->getError());
        }
        return $resp;
    }

    public function insertar() {
        $resp = false;

        $sql = "INSERT INTO usuario (usnombre, uspass, usmail, usdeshabilitado)
                VALUES (
                    '" . $this->getUsNombre() . "',
                    '" . $this->getUsPass() . "',
                    '" . $this->getUsMail() . "',
                    NULL
                )";

        if ($this->Iniciar()) {
             error_log("SQL ejecutar: $sql");
            // Ejecuta el INSERT — devuelve lastInsertId()
            $lastId = $this->Ejecutar($sql);

            if ($lastId != -1 && $lastId != null) {

                
                // Asignar el ID DEVUELTO por Ejecutar(), no llamar a ultimoId().
                $this->setIdUsuario($lastId);

                $resp = true;

            } else {
                error_log(" SQL ERROR insertar usuario: " . $this->getError());
                $this->setMensajeOperacion("Usuario->insertar: " . $this->getError());
            }
        }

        return $resp;
    }





    // MODIFICAR
    public function modificar() {
        $resp = false;
        $sql = "UPDATE usuario SET 
                    usnombre='" . $this->getUsNombre() . "', 
                    uspass='" . $this->getUsPass() . "', 
                    usmail='" . $this->getUsMail() . "', 
                    usdeshabilitado=" . ($this->getUsDeshabilitado() ? "'" . $this->getUsDeshabilitado() . "'" : "NULL") . "
                WHERE idusuario=" . $this->getIdUsuario();

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("Usuario->modificar: " . $this->getError());
            }
        }
        return $resp;
    }

   
    public function eliminar() {
        $resp = false;
        $sql = "DELETE FROM usuario WHERE idusuario=" . $this->getIdUsuario();

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->setMensajeOperacion("Usuario->eliminar: " . $this->getError());
            }
        }
        return $resp;
    }

    public function listar($parametro = "") {
        $arreglo = [];
        $sql = "SELECT * FROM usuario ";
        if ($parametro != "") {
            $sql .= " WHERE $parametro";
        }

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);
            if ($res > 0) {
                while ($row = $this->Registro()) {
                    $obj = new Usuario();
                    $obj->setear(
                        $row['idusuario'],
                        $row['usnombre'],
                        $row['uspass'],
                        $row['usmail'],
                        $row['usdeshabilitado']
                    );
                    $obj->cargarRoles();
                    $arreglo[] = $obj;
                }
            }
        }
        return $arreglo;
    }

    
    public function buscar($param) {
        $where = " true ";
        if (isset($param['idusuario'])) {
            $where .= " AND idusuario = " . $param['idusuario'];
        }
        if (isset($param['usnombre'])) {
            $where .= " AND usnombre = '" . $param['usnombre'] . "'";
        }
        if (isset($param['usmail'])) {
            $where .= " AND usmail = '" . $param['usmail'] . "'";
        }
        if (isset($param['uspass'])) {
            $where .= " AND uspass = '" . $param['uspass'] . "'";
        }

        $obj = new Usuario();
        return $obj->listar($where);
    }

}
?>
