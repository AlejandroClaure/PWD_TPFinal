<?php
// control/AbmCompraItem.php
class AbmCompraItem
{
    // buscar items por parametros -> devuelve array de CompraItem
    public function buscar($param = [])
    {
        $where = "true";
        if (isset($param['idcompraitem'])) $where .= " AND idcompraitem = " . intval($param['idcompraitem']);
        if (isset($param['idcompra'])) $where .= " AND idcompra = " . intval($param['idcompra']);
        if (isset($param['idproducto'])) $where .= " AND idproducto = " . intval($param['idproducto']);
        return CompraItem::listar($where);
    }

    // alta: recibe array con idcompra, idproducto, cicantidad
    public function alta($datos)
    {
        $obj = new CompraItem();
        $objProd = new Producto();
        $objProd->setIdProducto($datos['idproducto']);
        $objProd->cargar();

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos['idcompra']);
        $objCompra->cargar();

        $obj->setear(0, $objCompra, $objProd, intval($datos['cicantidad']));
        return $obj->insertar();
    }

    // baja por idcompraitem
    public function baja($datos)
    {
        $obj = new CompraItem();
        $obj->setIdCompraItem($datos['idcompraitem']);
        return $obj->eliminar();
    }

    // modificacion por idcompraitem
    public function modificacion($datos)
    {
        $obj = new CompraItem();
        $obj->setIdCompraItem($datos['idcompraitem']);
        $obj->cargar();

        $objProd = new Producto();
        $objProd->setIdProducto($datos['idproducto']);
        $objProd->cargar();

        $objCompra = new Compra();
        $objCompra->setIdCompra($datos['idcompra']);
        $objCompra->cargar();

        $obj->setear($datos['idcompraitem'], $objCompra, $objProd, intval($datos['cicantidad']));
        return $obj->modificar();
    }

    // --------------------------------------------------
    // Obtener compra iniciada del usuario (si no existe crea)
    // --------------------------------------------------
    private function getCompraIniciada($usuarioId)
    {
        // Buscar compras que estén en estado INICIADA
        $sql = "
        idcompra IN (
            SELECT idcompra 
            FROM compraestado 
            WHERE idcompraestadotipo = " . COMPRA_ESTADO_INICIADA . "
        )
        AND idusuario = " . intval($usuarioId);

        // Llamada correcta (no estática)
        $objCompra = new Compra();
        $compras = $objCompra->listar($sql);

        // Si existe, retornarla
        if (!empty($compras)) {
            return $compras[0];
        }

        // Si no existe, crear nueva compra
        $abmCompra = new AbmCompra();

        $nuevaCompraObj = $abmCompra->alta([
            "cofecha" => date("Y-m-d H:i:s"),
            "idusuario" => $usuarioId
        ]);

        if (!$nuevaCompraObj) {
            return null; // por seguridad
        }

        // Cargar objeto compra recién creada
        $compra = new Compra();
        $compra->setIdCompra($nuevaCompraObj->getIdCompra());
        $compra->cargar();

        // Crear estado INICIADA
        $abmEstado = new AbmCompraEstado();
        $abmEstado->alta([
            "idcompra" => $compra->getIdCompra(),
            "idcompraestadotipo" => COMPRA_ESTADO_INICIADA
        ]);

        return $compra;
    }



    // --------------------------------------------------
    // Añadir producto al carrito del usuario (NO TOCA STOCK)
    // Si ya existe, incrementa cantidad
    // --------------------------------------------------
    public function agregarProducto($usuarioId, $idProducto, $cantidad = 1)
    {
        $compra = $this->getCompraIniciada($usuarioId);
        $compraId = $compra->getIdCompra();

        // buscar item existente
        $items = $this->buscar(['idcompra' => $compraId, 'idproducto' => $idProducto]);
        if (!empty($items)) {
            $item = $items[0];
            $item->setCiCantidad($item->getCiCantidad() + intval($cantidad));
            return $item->modificar();
        }

        // crear nuevo item
        return $this->alta(['idcompra' => $compraId, 'idproducto' => $idProducto, 'cicantidad' => intval($cantidad)]);
    }

    // --------------------------------------------------
    // Modificar cantidad (sumar/restar)
    // $accion = 'sumar'|'restar'
    // --------------------------------------------------
    public function modificarCantidad($usuarioId, $idProducto, $accion)
    {
        $compra = $this->getCompraIniciada($usuarioId);
        $compraId = $compra->getIdCompra();

        $items = $this->buscar(['idcompra' => $compraId, 'idproducto' => $idProducto]);
        if (empty($items)) return false;

        $item = $items[0];

        // refrescar stock del producto
        $producto = $item->getObjProducto();
        $producto->cargar();

        $cantidadActual = $item->getCiCantidad();

        if ($accion === 'sumar') {
            // sumar solo si no supera stock
            $nuevaCantidad = min($cantidadActual + 1, $producto->getProCantStock());
            if ($nuevaCantidad != $cantidadActual) {
                $item->setCiCantidad($nuevaCantidad);
                $item->modificar();
            }
        } elseif ($accion === 'restar') {
            $nuevaCantidad = $cantidadActual - 1;
            if ($nuevaCantidad <= 0) {
                // eliminar item si llega a cero
                $this->baja(['idcompraitem' => $item->getIdCompraItem()]);
            } else {
                $item->setCiCantidad($nuevaCantidad);
                $item->modificar();
            }
        } else {
            return false; // acción inválida
        }

        // actualizar sesión
        $this->cargarCarritoSesion($usuarioId);
        return true;
    }

    // --------------------------------------------------
    // Eliminar producto completo del carrito
    // --------------------------------------------------
    public function eliminarProducto($usuarioId, $idProducto)
    {
        $compra = $this->getCompraIniciada($usuarioId);
        $compraId = $compra->getIdCompra();

        $items = $this->buscar(['idcompra' => $compraId, 'idproducto' => $idProducto]);
        if (empty($items)) return false;

        $ok = $this->baja(['idcompraitem' => $items[0]->getIdCompraItem()]);

        // actualizar sesión
        $this->cargarCarritoSesion($usuarioId);
        return $ok;
    }
    // --------------------------------------------------
    // Devuelve [$compra, $carritoArray] para la vista
    // --------------------------------------------------
    public function obtenerCompraYCarrito($usuarioId)
    {
        $compra = $this->getCompraIniciada($usuarioId);
        $items = $this->buscar(['idcompra' => $compra->getIdCompra()]);

        $carrito = [];
        foreach ($items as $item) {
            $prod = $item->getObjProducto();
            // si tu nombre en BD usa prefijo, extraigo parte real
            $nombreBD = $prod->getProNombre();
            $partes = explode("_", $nombreBD);
            $nombreReal = array_pop($partes);

            $carrito[$prod->getIdProducto()] = [
                'idproducto' => $prod->getIdProducto(),
                'nombre'     => $nombreReal,
                'precio'     => floatval($prod->getProPrecio()),
                'detalle'    => $prod->getProDetalle(),
                'imagen'     => $prod->getProImagen(),
                'stock'      => intval($prod->getProCantStock()),
                'cantidad'   => intval($item->getCiCantidad())
            ];
        }

        // actualizar sesión
        $_SESSION['carrito'] = $carrito;

        return [$compra, $carrito];
    }

    // --------------------------------------------------
    // Cargar carrito a session (útil luego de cambios)
    // --------------------------------------------------
    public function cargarCarritoSesion($usuarioId)
    {
        $this->obtenerCompraYCarrito($usuarioId); // ya actualiza $_SESSION['carrito']
    }

    /**
     * Vacía completamente el carrito del usuario
     */
    public function vaciarCarrito($usuarioId)
    {
        $compra = $this->getCompraIniciada($usuarioId);
        $items = $this->buscar(['idcompra' => $compra->getIdCompra()]);

        $eliminados = 0;
        foreach ($items as $item) {
            if ($this->baja(['idcompraitem' => $item->getIdCompraItem()])) {
                $eliminados++;
            }
        }

        // Actualizar sesión
        $this->cargarCarritoSesion($usuarioId);

        return $eliminados;
    }

    public function transferirCarritoACompra($idUsuario, $idCompraDestino)
    {
        // 1) Obtener la compra "carrito" actual del usuario (la que contiene los items)
        $compraOrigen = $this->getCompraIniciada($idUsuario);
        if (!$compraOrigen) {
            return false; // no hay carrito
        }

        // 2) Traer items de la compra origen
        $itemsOrigen = $this->buscar(['idcompra' => $compraOrigen->getIdCompra()]);
        if (empty($itemsOrigen)) {
            return false; // no hay items que transferir
        }

        // 3) Preparar objeto Compra destino correctamente cargado
        $compraDestino = new Compra();
        $compraDestino->setIdCompra(intval($idCompraDestino));
        $compraDestino->cargar();

        // 4) Insertar cada item en la compra destino
        foreach ($itemsOrigen as $itemOrigen) {
            $prod = $itemOrigen->getObjProducto(); // ya es objeto Producto
            $cantidad = $itemOrigen->getCiCantidad();

            $nuevoItem = new CompraItem();
            $nuevoItem->setear(
                0,
                $compraDestino,
                $prod,
                $cantidad
            );
            $nuevoItem->insertar();
        }

        return true;
    }
}
