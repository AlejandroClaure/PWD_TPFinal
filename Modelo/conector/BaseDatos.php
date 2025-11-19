<?php
class BaseDatos extends PDO {

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

    public function __construct() {

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

    public function Iniciar() {
        return $this->conec;
    }

    public function getConec() {
        return $this->conec;
    }

    public function setDebug($debug) {
        $this->debug = $debug;
    }

    public function getDebug() {
        return $this->debug;
    }

    public function setError($e) {
        $this->error = $e;
    }

    public function getError() {
        return $this->error;
    }

    public function setSQL($sql) {
        $this->sql = $sql;
    }

    public function getSQL() {
        return $this->sql;
    }

    public function Ejecutar($sql) {
        $this->setError("");
        $this->setSQL($sql);

        if (stripos($sql, "insert") === 0) return $this->EjecutarInsert($sql);
        if (stripos($sql, "update") === 0 || stripos($sql, "delete") === 0) return $this->EjecutarDeleteUpdate($sql);
        if (stripos($sql, "select") === 0) return $this->EjecutarSelect($sql);

        return -1;
    }

    private function EjecutarInsert($sql) {
        try {
            $res = parent::exec($sql);
            return $this->lastInsertId();
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return -1;
        }
    }

    private function EjecutarDeleteUpdate($sql) {
        try {
            return parent::exec($sql);
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return -1;
        }
    }

    private function EjecutarSelect($sql) {
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

    public function Registro() {
        if ($this->indice < count($this->resultado)) {
            return $this->resultado[$this->indice++];
        }
        return false;
    }
}
