<?php

class AbmVenta {

   
    public function registrarVenta($idusuario, $carrito) {
        $db = new BaseDatos();
        if (!$db->Iniciar()) return false;

        try {
            // 1) Insertar compra
            $sqlCompra = "
                INSERT INTO compra (idusuario, cofecha)
                VALUES ($idusuario, NOW())
            ";

            $idcompra = $db->Ejecutar($sqlCompra);
            if ($idcompra < 1) throw new Exception("Error insertando compra");

            // 2) Estado inicial = 1 (iniciada)
            $sqlEstado = "
                INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini)
                VALUES ($idcompra, 1, NOW())
            ";
            if ($db->Ejecutar($sqlEstado) < 1)
                throw new Exception("Error insertando compraestado");

            // 3) Insertar cada item
            foreach ($carrito as $idprod => $item) {
                $cantidad = $item['cantidad'];

                $sqlItem = "
                    INSERT INTO compraitem (idproducto, idcompra, cicantidad)
                    VALUES ($idprod, $idcompra, $cantidad)
                ";
                if ($db->Ejecutar($sqlItem) < 1)
                    throw new Exception("Error insertando compraitem");

                // 4) Restar stock
                $sqlStock = "
                    UPDATE producto 
                    SET procantstock = procantstock - $cantidad
                    WHERE idproducto = $idprod
                ";
                $db->Ejecutar($sqlStock);
            }

            return $idcompra;

        } catch (Exception $e) {
            return false;
        }
    }

    
    // DEVUELVE UNA COMPRA COMPLETA
    public function obtenerCompra($idcompra) {
        $db = new BaseDatos();

        $sql = "
            SELECT c.*, u.usnombre, u.usmail
            FROM compra c
            JOIN usuario u ON u.idusuario = c.idusuario
            WHERE c.idcompra = $idcompra
        ";

        if ($db->Iniciar() && $db->Ejecutar($sql) > 0)
            return $db->Registro();

        return null;
    }

    
    // DEVUELVE LOS PRODUCTOS DE UNA COMPRA
    public function obtenerItems($idcompra) {
        $db = new BaseDatos();
        $sql = "
            SELECT ci.*, p.pronombre, p.prodetalle
            FROM compraitem ci
            JOIN producto p ON p.idproducto = ci.idproducto
            WHERE ci.idcompra = $idcompra
        ";

        $items = [];

        if ($db->Iniciar() && $db->Ejecutar($sql) > 0) {
            while ($row = $db->Registro()) {
                $items[] = $row;
            }
        }

        return $items;
    }


    
    // CAMBIA EL ESTADO DE UNA COMPRA
    public function cambiarEstado($idcompra, $nuevoEstado) {
        $db = new BaseDatos();
        if (!$db->Iniciar()) return false;

        // Cerrar el estado anterior
        $sqlCerrar = "
            UPDATE compraestado
            SET cefechafin = NOW()
            WHERE idcompra = $idcompra AND cefechafin IS NULL
        ";
        $db->Ejecutar($sqlCerrar);

        // Crear el nuevo estado
        $sqlNuevo = "
            INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini)
            VALUES ($idcompra, $nuevoEstado, NOW())
        ";

        return $db->Ejecutar($sqlNuevo) > 0;
    }


    
    // LISTAR TODAS LAS COMPRAS (ADMIN)
    public function listarCompras() {
        $db = new BaseDatos();

        $sql = "
            SELECT 
                c.idcompra,
                c.cofecha,
                u.usnombre,
                u.usmail,
                (
                    SELECT cetdescripcion
                    FROM compraestado ce
                    JOIN compraestadotipo cet ON cet.idcompraestadotipo = ce.idcompraestadotipo
                    WHERE ce.idcompra = c.idcompra
                    AND cefechafin IS NULL
                    LIMIT 1
                ) AS estado
            FROM compra c
            JOIN usuario u ON u.idusuario = c.idusuario
            ORDER BY c.idcompra DESC
        ";

        $compras = [];

        if ($db->Iniciar() && $db->Ejecutar($sql) > 0) {
            while ($row = $db->Registro()) {
                $compras[] = $row;
            }
        }

        return $compras;
    }
}
