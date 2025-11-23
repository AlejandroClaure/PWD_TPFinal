<?php
class Compra extends BaseDatos
{

    private $idcompra;
    private $cofecha;
    private $idusuario;
    private $mensajeoperacion;
    private $objUsuario;

    public function __construct()
    {
        parent::__construct();
        $this->idcompra = 0;
        $this->cofecha = "";
        $this->idusuario = 0;
        $this->objUsuario = new Usuario(); // inicializamos
        $this->mensajeoperacion = "";
    }

    public function setear($id, $fecha, $idusuario)
    {
        $this->setIdCompra($id);
        $this->setCoFecha($fecha);
        $this->setIdUsuario($idusuario);

        // cargamos el objeto Usuario automáticamente
        $usuario = new Usuario();
        $usuario->setIdUsuario($idusuario);
        $usuario->cargar(); // suponiendo que tu clase Usuario tiene cargar()
        $this->setObjUsuario($usuario);
    }


    public function getIdCompra()
    {
        return $this->idcompra;
    }
    public function setIdCompra($valor)
    {
        $this->idcompra = $valor;
    }

    public function getCoFecha()
    {
        return $this->cofecha;
    }
    public function setCoFecha($valor)
    {
        $this->cofecha = $valor;
    }

    public function getIdUsuario()
    {
        return $this->idusuario;
    }
    public function setIdUsuario($valor)
    {
        $this->idusuario = $valor;
    }

    public function getMensajeOperacion()
    {
        return $this->mensajeoperacion;
    }
    public function setMensajeOperacion($valor)
    {
        $this->mensajeoperacion = $valor;
    }
    public function getObjUsuario()
    {
        return $this->objUsuario;
    }
    public function setObjUsuario($usuario)
    {
        $this->objUsuario = $usuario;
    }


    public function cargar()
    {
        $resp = false;
        $sql = "SELECT * FROM compra WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) > 0) {
            if ($row = $this->Registro()) {
                $this->setear($row["idcompra"], $row["cofecha"], $row["idusuario"]);
                $resp = true;
            }
        }

        return $resp;
    }


    public function insertar()
    {
        $resp = false;
        $sql = "INSERT INTO compra (cofecha, idusuario)
                VALUES (
                    '" . $this->getCoFecha() . "',
                    " . $this->getIdUsuario() . "
                )";

        if ($this->Ejecutar($sql) >= 0) {
            // Recupera el último id auto_increment
            $id = $this->ultimoId();
            if ($id !== null) {
                $this->setIdCompra($id);
                $resp = true;
            }
        } else {
            $this->setMensajeOperacion("Compra->insertar: " . $this->getError());
        }

        return $resp;
    }


    public function modificar()
    {
        $resp = false;

        $sql = "UPDATE compra SET
                cofecha = '" . $this->getCoFecha() . "',
                idusuario = " . $this->getIdUsuario() . "
                WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("Compra->modificar: " . $this->getError());
        }

        return $resp;
    }


    public function eliminar()
    {
        $resp = false;

        $sql = "DELETE FROM compra WHERE idcompra = " . $this->getIdCompra();

        if ($this->Ejecutar($sql) >= 0) {
            $resp = true;
        } else {
            $this->setMensajeOperacion("Compra->eliminar: " . $this->getError());
        }

        return $resp;
    }


    public function listar($parametro = "")
    {
        $arreglo = array();
        $sql = "SELECT * FROM compra";

        if ($parametro != "") {
            $sql .= " WHERE " . $parametro;
        }
        $sql .= " ORDER BY idcompra";

        if ($this->Ejecutar($sql) > 0) {
            while ($row = $this->Registro()) {
                $obj = new Compra();
                $obj->setear(
                    $row["idcompra"],
                    $row["cofecha"],
                    $row["idusuario"]
                );
                array_push($arreglo, $obj);
            }
        }

        return $arreglo;
    }
    public function listarPorUsuario($idusuario)
    {
        return $this->listar("idusuario = $idusuario ORDER BY cofecha DESC");
    }

    // Dentro de la clase Compra

    public function getItems()
    {
        $abmItem = new AbmCompraItem();
        return $abmItem->buscar(['idcompra' => $this->getIdcompra()]);
    }

    public function getHistorialEstados()
    {
        $abmCompraEstado = new AbmCompraEstado();
        return $abmCompraEstado->buscar(['idcompra' => $this->getIdcompra()]);
    }

    public function getEstadoActual()
    {
        $historial = $this->getHistorialEstados();

        foreach ($historial as $est) {
            if ($est->getCefechafin() == null) {
                return $est;
            }
        }
        return null;
    }

    public function getEstadoActualDescripcion()
    {
        $estado = $this->getEstadoActual();
        return $estado
            ? $estado->getObjCompraEstadoTipo()->getCetdescripcion()
            : "Sin estado";
    }
    public function getTotal()
    {
        $abmCompraItem = new AbmCompraItem();
        $items = $abmCompraItem->buscar(['idcompra' => $this->getIdcompra()]);

        $total = 0;
        foreach ($items as $item) {
            $total += $item->getCiCantidad() * $item->getObjProducto()->getProPrecio();
        }

        return $total;
    }
}
