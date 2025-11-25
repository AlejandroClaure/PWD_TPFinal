<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


class AbmCompra
{

    public function alta($datos)
    {
        $obj = new Compra();
        $obj->setear(0, $datos["cofecha"], $datos["idusuario"]);
        if ($obj->insertar()) {
            return $obj; // DEVOLVEMOS EL OBJETO CON ID
        }
        return false;
    }


    public function baja($datos)
    {
        if (!isset($datos["idcompra"])) return false;

        $obj = new Compra();
        $obj->setIdCompra($datos["idcompra"]);
        return $obj->eliminar();
    }

    public function modificacion($datos)
    {
        if (!isset($datos["idcompra"])) return false;

        $obj = new Compra();
        $obj->setear(
            $datos["idcompra"],
            $datos["cofecha"],
            $datos["idusuario"]
        );

        return $obj->modificar();
    }

    public function buscar($param = null)
    {
        $where = " true ";

        if ($param !== null) {
            if (isset($param["idcompra"])) {
                $where .= " AND idcompra = " . $param["idcompra"];
            }
            if (isset($param["idusuario"])) {
                $where .= " AND idusuario = " . $param["idusuario"];
            }
        }

        $obj = new Compra();
        return $obj->listar($where);
    }




    // ULTIMOS CAMBIOS AL COMPRA. SI FUNCIONAN, DEJARLOS



    public function generarComprobantePDF($compra, $items)
    {
        $idcompra = $compra->getIdCompra();

        // Ruta donde guardar PDF
        $rutaCarpeta = dirname(__DIR__, 1) . "/Archivos/ventas/";
        if (!is_dir($rutaCarpeta)) {
            mkdir($rutaCarpeta, 0777, true);
        }

        // Construcción de tabla
        $tabla = "";
        $total = 0;

        foreach ($items as $item) {
            $prod = $item->getObjProducto();

            $nombre   = $prod->getProNombre();
            $precio   = $prod->getProPrecio();
            $cantidad = $item->getCiCantidad();
            $sub      = $precio * $cantidad;

            $total += $sub;

            $tabla .= "
        <tr>
            <td>$nombre</td>
            <td>$cantidad</td>
            <td>\$$precio</td>
            <td>\$$sub</td>
        </tr>";
        }

        // HTML final
        $html = "
    <h2>Comprobante de Compra #$idcompra</h2>
    <p>Fecha: " . date("d/m/Y H:i") . "</p>
    <table width='100%' border='1' cellpadding='6'>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
        </tr>
        $tabla
    </table>
    <h3>Total final: \$$total</h3>
    ";

        // DOMPDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $rutaPDF = $rutaCarpeta . "comprobante_pedido_$idcompra.pdf";
        file_put_contents($rutaPDF, $dompdf->output());

        return $rutaPDF;
    }

    /**
     * Finaliza la compra en curso del usuario
     */
   public function finalizarCompraDirecto($idCompra)
{
    $abmEstado = new AbmCompraEstado();

    // estado 1 = iniciada
    $abmEstado->alta([
        "idcompra" => $idCompra,
        "idcompraestadotipo" => 1,
        "cefechaini" => date("Y-m-d H:i:s")
    ]);

    // estado 5 = finalizada
    $abmEstado->cambiarEstadoCompra($idCompra, 5);

    return true;
}






    public function modificar($datos)
    {
        $compra = new Compra();
        $compra->setIdCompra($datos['idcompra']);

        if ($compra->cargar()) {
            if (isset($datos['cofecha'])) {
                $compra->setCoFecha($datos['cofecha']);
            }
            if (isset($datos['idusuario'])) {
                $compra->setIdUsuario($datos['idusuario']);
            }
            return $compra->modificar();
        }

        return false;
    }
}
