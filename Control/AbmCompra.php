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


    // --------------------------------------------------
    // CORRECCION DE LOS ACCION DE COMPRA
    // --------------------------------------------------

/**
 * Agrega un producto al carrito del usuario
 *
 * @param int $usuarioId
 * @param int $idProducto
 * @param int $cantidad
 * @return array Resultado de la operación
 */
public function agregarAlCarrito($usuarioId, $idProducto, $cantidad)
{
    // Si alguno de los parámetros está mal, devolvemos false directamente (cortito y al pie)
    if ($idProducto <= 0 || $cantidad <= 0 || $usuarioId <= 0) {
        return false;
    }

    // Instanciamos el ABM de ítems del carrito
    $abmItem = new AbmCompraItem();
    
    // Intentamos agregar el producto y devolvemos true/false según si salió bien o mal
    return $abmItem->agregarProducto($usuarioId, $idProducto, $cantidad);
}
    /*
 * cancela la compra de un producto
 *
 * @param $idCompra
 */
    // Dentro de la clase AbmCompra.php
public function cancelarCompra($idCompra)
{
    // Validación mínima (el id tiene que ser número positivo)
    if ($idCompra <= 0) {
        return false;
    }

    $abmEstado = new AbmCompraEstado();
    // 4 = estado "cancelada" en tu sistema
    return $abmEstado->cambiarEstadoCompra($idCompra, 4);
}



    /*
 * elimina un item del carrito
 *
 * @param $usuarioId, 
 * @param $idProducto
 */
    // Elimina un producto del carrito del usuario
public function eliminarDelCarrito($usuarioId, $idProducto)
{
    // Si los datos están mal → chau
    if ($usuarioId <= 0 || $idProducto <= 0) return false;

    $abmItem = new AbmCompraItem();
    // Devuelve true si borró, false si no existía o falló
    return $abmItem->eliminarProducto($usuarioId, $idProducto);
}


    /*
 * Finaliza la compra: crea la compra, transfiere items, genera PDF, etc.
 *
 * @param $usuarioId
 */
public function finalizarCompra($usuarioId)
{
    if ($usuarioId <= 0) return false;

    $abmCompra = new AbmCompra();
    $abmItem   = new AbmCompraItem();
    $abmEstado = new AbmCompraEstado();

    // 1) Crear compra nueva
    $compra = $abmCompra->alta([
        "cofecha"   => date('Y-m-d H:i:s'),
        "idusuario" => $usuarioId
    ]);
    if (!$compra) return false;
    $idCompra = $compra->getIdCompra();

    // 2) Transferir items del carrito a la compra
    if (!$abmItem->transferirCarritoACompra($usuarioId, $idCompra)) return false;

    // 3) Generar PDF comprobante
    $items = $abmItem->buscar(['idcompra' => $idCompra]);
    $abmCompra->generarComprobantePDF($compra, $items);

    // 4) Poner estado "iniciada"
    $abmEstado->alta([
        "idcompra"           => $idCompra,
        "idcompraestadotipo" => COMPRA_ESTADO_INICIADA,
        "cefechaini"         => date("Y-m-d H:i:s")
    ]);

    // 5) Vaciar carrito y sesión
    $abmItem->vaciarCarrito($usuarioId);
    $_SESSION['carrito'] = [];

    return $idCompra; // devuelve el ID de la compra creada
}


    /*
 * resta en 1 stock de un producto
 *
 * @param $usuarioId, 
 * @param $idProducto
 */
    public function restarCantidad($usuarioId, $idProducto)
{
    if ($usuarioId <= 0 || $idProducto <= 0) return false;
    $abmItem = new AbmCompraItem();
    return $abmItem->modificarCantidad($usuarioId, $idProducto, 'restar');
}

    /*
 * suma en 1 stock de un producto
 *
 * @param $usuarioId, 
 * @param $idProducto
 */
    public function sumarCantidad($usuarioId, $idProducto)
{
    if ($usuarioId <= 0 || $idProducto <= 0) return false;
    $abmItem = new AbmCompraItem();
    return $abmItem->modificarCantidad($usuarioId, $idProducto, 'sumar');
}

    /*
 * vacia carrito de compra
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function vaciarCarritoUsuario($usuarioId)
{
    if ($usuarioId <= 0) return false;
    $abmItem = new AbmCompraItem();
    $abmItem->vaciarCarrito($usuarioId);
    $_SESSION['carrito'] = [];
    return true;
}

    /*
 * cambiar estado de compra
 *
 * @param $idCompra, 
 * @param $nuevoEstado
 */
    public function cambiarEstadoCompra($idCompra, $nuevoEstado)
{
    if ($idCompra <= 0 || !in_array($nuevoEstado, [2,3,4,5])) return false;
    $abmEstado = new AbmCompraEstado();
    return $abmEstado->cambiarEstadoCompra($idCompra, $nuevoEstado);
}
}
