<?php

class Rol extends BaseDatos {

    private $idrol;
    private $rodescripcion;
    private $mensajeoperacion;

    public function __construct() {
        parent::__construct();
        $this->idrol = null;
        $this->rodescripcion = "";
        $this->mensajeoperacion = "";
    }

    
    public function setear($idrol, $desc) {
        $this->idrol = $idrol;
        $this->rodescripcion = $desc;
    }

    

    public function getIdRol() {
         return $this->idrol; 
    }
    public function setIdRol($id) {
         $this->idrol = $id; 
    }

    public function getRoDescripcion() {
         return $this->rodescripcion; 
    }
    public function setRoDescripcion($desc) {
         $this->rodescripcion = $desc; 
    }

    public function getMensajeOperacion() {
         return $this->mensajeoperacion; 
    }
    public function setMensajeOperacion($m) { 
        $this->mensajeoperacion = $m; 
    }

   
    public function cargar() {
        $resp = false;
        $sql = "SELECT * FROM rol WHERE idrol = " . $this->idrol;

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);

            if ($res > 0) {
                $row = $this->Registro();
                $this->setear($row['idrol'], $row['rodescripcion']);
                $resp = true;
            }
        } else {
            $this->mensajeoperacion = "Rol->cargar: " . $this->getError();
        }

        return $resp;
    }

    /**
 * Busca roles según parámetros
 * Totalmente compatible con tu clase BaseDatos que extiende PDO
 */
public function buscar($param = [])
{
    $where = " true ";
    if ($param != null) {
        if (isset($param['idrol'])) {
            $where .= " AND idrol = " . intval($param['idrol']);
        }
        if (isset($param['rodescripcion'])) {
            // Usamos $this->quote() → método nativo y seguro de PDO
            $desc = $this->quote($param['rodescripcion']);
            $where .= " AND rodescripcion = $desc";
        }
    }

    $arreglo = array();
    $sql = "SELECT * FROM rol WHERE " . $where;

    if ($this->Iniciar()) {
        $res = $this->Ejecutar($sql);
        if ($res > -1) {
            if ($res > 0) {
                while ($row = $this->Registro()) {
                    $obj = new Rol();
                    $obj->setear($row['idrol'], $row['rodescripcion']);
                    $arreglo[] = $obj;
                }
            }
        } else {
            $this->setMensajeOperacion("Rol->buscar: " . $this->getError());
        }
    }
    return $arreglo;
}

    
    public function insertar() {
        $resp = false;
        // Rol NO DEBE recibir idrol desde fuera → autoincremental
        $sql = "INSERT INTO rol (rodescripcion) VALUES ('{$this->rodescripcion}')";

        if ($this->Iniciar()) {
            $id = $this->Ejecutar($sql);
            if ($id) {
                $this->idrol = $id; // recupera id generado
                $resp = true;
            } else {
                $this->mensajeoperacion = "Rol->insertar: " . $this->getError();
            }
        } else {
            $this->mensajeoperacion = "Rol->insertar: " . $this->getError();
        }

        return $resp;
    }

    
    public function modificar() {
        $resp = false;

        $sql = "UPDATE rol SET 
                    rodescripcion = '{$this->rodescripcion}'
                WHERE idrol = {$this->idrol}";

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->mensajeoperacion = "Rol->modificar: " . $this->getError();
            }
        } else {
            $this->mensajeoperacion = "Rol->modificar: " . $this->getError();
        }

        return $resp;
    }

    
    public function eliminar() {
        $resp = false;
        $sql = "DELETE FROM rol WHERE idrol = {$this->idrol}";

        if ($this->Iniciar()) {
            if ($this->Ejecutar($sql)) {
                $resp = true;
            } else {
                $this->mensajeoperacion = "Rol->eliminar: " . $this->getError();
            }
        } else {
            $this->mensajeoperacion = "Rol->eliminar: " . $this->getError();
        }

        return $resp;
    }

    
    public function listar($parametro = "") {
        $arreglo = [];
        $sql = "SELECT * FROM rol";

        if ($parametro != "") {
            $sql .= " WHERE $parametro";
        }

        if ($this->Iniciar()) {
            $res = $this->Ejecutar($sql);

            if ($res > 0) {
                while ($row = $this->Registro()) {

                    $obj = new Rol();
                    $obj->setear(
                        $row['idrol'],
                        $row['rodescripcion']
                    );

                    $arreglo[] = $obj;
                }
            }
        } else {
            $this->mensajeoperacion = "Rol->listar: " . $this->getError();
        }

        return $arreglo;
    }
}
?>
