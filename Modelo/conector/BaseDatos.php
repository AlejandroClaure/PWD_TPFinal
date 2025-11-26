<?php
class BaseDatos extends PDO
{

    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;
    private $debug;
    private $conec;
    private $indice;
    private $resultado;
    private $error;
    private $sql;

    public function __construct()
    {

        // CONFIGURACIÓN CORRECTA PARA TU PROYECTO
        $this->engine   = 'mysql';
        $this->host     = 'localhost';
        $this->database = 'bdcarritocompras';
        $this->user     = 'root';
        $this->pass     = '';
        $this->debug    = true;

        $this->error    = "";
        $this->sql      = "";
        $this->indice   = 0;

        $dsn = "{$this->engine}:dbname={$this->database};host={$this->host};charset=utf8";

        try {
            parent::__construct($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]);

            $this->conec = true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->conec = false;

            if ($this->debug) {
                echo "<pre> ❌ ERROR DE CONEXIÓN A LA BD: {$this->error}</pre>";
            }
        }
    }

    public function Iniciar()
    {
        return $this->conec;
    }

    public function getConec()
    {
        return $this->conec;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function setError($e)
    {
        $this->error = $e;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setSQL($sql)
    {
        $this->sql = $sql;
    }

    public function getSQL()
    {
        return $this->sql;
    }

    public function Ejecutar($sql){
        $this->setError("");
        $this->setSQL($sql);

        $sqlTrim = ltrim($sql);

        if (preg_match('/^insert/i', $sqlTrim)) {
            return $this->EjecutarInsert($sqlTrim);
        }
        if (preg_match('/^(update|delete)/i', $sqlTrim)) {
            return $this->EjecutarDeleteUpdate($sqlTrim);
        }
        if (preg_match('/^select/i', $sqlTrim)) {
            return $this->EjecutarSelect($sqlTrim);
        }

        return -1;
    }


    private function EjecutarInsert($sql)
    {
        try {
            parent::exec($sql);  
            return parent::lastInsertId(); 
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return -1;
        }
    }


    private function EjecutarDeleteUpdate($sql)
    {
        try {
            return parent::exec($sql);
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return -1;
        }
    }

    private function EjecutarSelect($sql)
    {
        try {
            $stmt = parent::query($sql);
            $rows = $stmt->fetchAll();

            $this->resultado = $rows;
            $this->indice = 0;

            return count($rows);
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return -1;
        }
    }

    public function Registro()
    {
        if ($this->indice < count($this->resultado)) {
            return $this->resultado[$this->indice++];
        }
        return false;
    }
    public function ultimoId()
    {
        $id = null;

        $sql = "SELECT LAST_INSERT_ID() as id";
        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $id = $row["id"];
            }
        }

        return $id;
    }
}
