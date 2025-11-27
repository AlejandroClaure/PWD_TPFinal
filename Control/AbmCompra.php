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


    /*
 * Agrega un producto al carrito del usuario
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function agregarCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
            exit;
        }

        $usuario   = $session->getUsuario();
        $usuarioId = $usuario->getIdUsuario();

        // Parámetros GET
        $idProducto = intval($_GET['id'] ?? 0);
        $cantidad   = intval($_GET['cantidad'] ?? 1);
        $redirect   = $_GET['redirect'] ?? 'compra/carrito.php';

        // Validación básica
        if ($idProducto <= 0 || $cantidad <= 0) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "producto/producto.php?error=1");
            exit;
        }

        // Aquí creamos la instancia SOLO cuando la necesitamos
        $abmCompraItem = new AbmCompraItem();

        // Agregamos el producto al carrito
        $ok = $abmCompraItem->agregarProducto($usuarioId, $idProducto, $cantidad);

        // Redirección
        $param = $ok ? 'ok=1' : 'error=3';
        header("Location: " . $GLOBALS['VISTA_URL'] . $redirect . "?" . $param);
        exit;
    }

    /*
 * cancela la compra de un producto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function cancelarCompraCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa() || !$session->tieneRol('cliente')) {
            header("Location: ../../login/login.php");
            exit;
        }

        $idCompra = $_GET['id'] ?? null;

        if (!$idCompra) {
            header("Location: ../verCompraCliente.php?msg=error_id");
            exit;
        }

        $abmEstado = new AbmCompraEstado();

        // 4 = cancelada
        $ok = $abmEstado->cambiarEstadoCompra($idCompra, 4);

        if ($ok) {
            header("Location: ../detalleCompra.php?id=$idCompra&msg=cancel_ok");
        } else {
            header("Location: ../detalleCompra.php?id=$idCompra&msg=cancel_fail");
        }

        exit;
    }



    /*
 * elimina un item del carrito
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function eliminarItemCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
            exit;
        }

        $usuario = $session->getUsuario();
        $usuarioId = $usuario->getIdUsuario();

        $idProducto = intval($_GET['id'] ?? 0);
        if ($idProducto <= 0) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?error=1");
            exit;
        }

        $abm = new AbmCompraItem();
        $eliminado = $abm->eliminarProducto($usuarioId, $idProducto);

        $redirect = $_GET['redirect'] ?? 'compra/carrito.php';
        header("Location: " . $GLOBALS['VISTA_URL'] . $redirect . ($eliminado ? "?ok=2" : "?error=2"));
        exit;
    }


    /*
 * finaliza la compra de un producto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function finalizarCompraCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) die("No login");

        $usuario  = $session->getUsuario();
        $idUsuario = $usuario->getIdUsuario();
        // 2) Controladores
        $abmCompra = new AbmCompra();
        $abmItem   = new AbmCompraItem();
        $abmEstado = new AbmCompraEstado();
        // 3) Crear compra 
        $compra = $abmCompra->alta([
            "cofecha" => date('Y-m-d H:i:s'),
            "idusuario" => $idUsuario
        ]);
        if (!$compra) die("Error al crear compra.");
        $idCompraNueva = $compra->getIdCompra();
        // 4) Transferir items del carrito
        $ok = $abmItem->transferirCarritoACompra($idUsuario, $idCompraNueva);
        if (!$ok) die("Error al transferir los items.");
        // 5) Obtener items reales para PDF
        $itemsCompra = $abmItem->buscar(['idcompra' => $idCompraNueva]);
        // 6) PDF comprobante
        $rutaPDF = $abmCompra->generarComprobantePDF($compra, $itemsCompra);
        // 7) Estado = iniciada
        $abmEstado->alta([
            "idcompra" => $idCompraNueva,
            "idcompraestadotipo" => COMPRA_ESTADO_INICIADA,
            "cefechaini" => date("Y-m-d H:i:s")
        ]);
        // 8) Vaciar carrito origen
        $abmItem->vaciarCarrito($idUsuario);
        $_SESSION['carrito'] = [];
        // 9) Redirigir
        header("Location: ../compra_exitosa.php?id=$idCompraNueva");
        exit;
    }


    /*
 * resta en 1 stock de un producto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function restarStockCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
            exit;
        }

        $usuario = $session->getUsuario();
        $usuarioId = $usuario->getIdUsuario();

        $idProducto = intval($_GET['id'] ?? 0);
        if ($idProducto <= 0) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?error=1");
            exit;
        }

        $abm = new AbmCompraItem();
        $abm->modificarCantidad($usuarioId, $idProducto, 'restar');

        $redirect = $_GET['redirect'] ?? 'compra/carrito.php';
        header("Location: " . $GLOBALS['VISTA_URL'] . $redirect);
        exit;
    }

    /*
 * suma en 1 stock de un producto
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function sumarStockCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
            exit;
        }

        $usuario = $session->getUsuario();
        $usuarioId = $usuario->getIdUsuario();

        $idProducto = intval($_GET['id'] ?? 0);
        if ($idProducto <= 0) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?error=1");
            exit;
        }

        $abm = new AbmCompraItem();
        $abm->modificarCantidad($usuarioId, $idProducto, 'sumar');

        $redirect = $_GET['redirect'] ?? 'compra/carrito.php';
        header("Location: " . $GLOBALS['VISTA_URL'] . $redirect);
        exit;
    }

    /*
 * vacia carrito de compra
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function vaciarCarrito($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa()) {
            header("Location: " . $GLOBALS['VISTA_URL'] . "login/login.php?error=2");
            exit;
        }

        $usuarioId = $session->getUsuario()->getIdUsuario();

        // Vaciar carrito
        $abm = new AbmCompraItem();
        $abm->vaciarCarrito($usuarioId);

        // Redirigir
        header("Location: " . $GLOBALS['VISTA_URL'] . "compra/carrito.php?vaciado=1");
        exit;
    }

    /*
 * cambiar estado de compra
 *
 * @param Session $session  Objeto sesión ya iniciado desde el archivo de acción
 */
    public function cambioEstadoCompra($session)
    {
        // Verificamos sesión (doble chequeo, nunca está de más)
        if (!$session->activa() || !$session->tieneRol('admin')) {
            exit;
        }

        $idCompra = intval($_POST['idcompra'] ?? 0);
        $nuevoEstado = intval($_POST['nuevoestado'] ?? 0);

        if ($idCompra > 0 && in_array($nuevoEstado, [2, 3, 4, 5])) {

            $abmEstado = new AbmCompraEstado();
            $abmEstado->cambiarEstadoCompra($idCompra, $nuevoEstado);
        }

        header("Location: ../verCompraAdmin.php?id=" . $idCompra);
        exit;
    }
}
