<?php
include_once dirname(__DIR__) . '/Modelo/Producto.php';

class AbmProducto
{

    // ======================================================
    // CREAR
    // ======================================================
    public function crear($datos)
    {
        $pronombre     = trim($datos['pronombre'] ?? '');
        $proprecio     = floatval($datos['proprecio'] ?? 0);
        $procantstock  = intval($datos['procantstock'] ?? 0);
        $prodetalle    = trim($datos['prodetalle'] ?? '');
        $categoria     = trim($datos['categoria'] ?? '');
        $idusuario     = intval($datos['idusuario'] ?? 0);
        $archivoImg    = $datos['proimagen'] ?? null;

        if ($pronombre == '' || $categoria == '' || $idusuario <= 0) {
            return false;
        }

        // ======================================================
        // SUBIR IMAGEN
        // ======================================================
        $imagenNombre = null;

        if ($archivoImg && !empty($archivoImg['name'])) {

            $ext = strtolower(pathinfo($archivoImg['name'], PATHINFO_EXTENSION));

            $imagenNombre = preg_replace(
                '/[^a-zA-Z0-9_-]/',
                '',
                str_replace(' ', '_', $pronombre)
            ) . "_" . time() . "." . $ext;

            $rutaCarpeta = $_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/Vista/imagenes/productos/";

            if (!is_dir($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0777, true);
            }

            if (!move_uploaded_file(
                $archivoImg['tmp_name'],
                $rutaCarpeta . $imagenNombre
            )) {
                return false;
            }
        }

        // ======================================================
        // CADENA DE CATEGORÍAS
        // ======================================================
        $abmMenu = new AbmMenu();
        $menus = $abmMenu->buscar(['menombre' => $categoria]);

        if (empty($menus)) {
            return false;
        }

        $menu = $menus[0];
        $cadenaCategorias = [];

        while ($menu) {
            $cadenaCategorias[] = strtolower($menu->getMeNombre());
            $menu = $menu->getObjMenuPadre();
        }

        $cadenaCategorias = array_reverse($cadenaCategorias);
        $prefijo = implode('_', $cadenaCategorias) . '_';

        $nombreFinal = $prefijo . $pronombre;

        // ======================================================
        // INSERTAR PRODUCTO (10 ARGUMENTOS)
        // ======================================================
        $obj = new Producto();
        $obj->setear(
            0,                  // idproducto
            $nombreFinal,       // pronombre
            $prodetalle,        // prodetalle
            $proprecio,         // proprecio
            $procantstock,      // procantstock
            0,                  // prooferta
            null,               // profinoffer
            null,               // prodeshabilitado
            $idusuario,         // idusuario
            $imagenNombre       // proimagen
        );

        return $obj->insertar();
    }

    // ======================================================
    // MODIFICAR
    // ======================================================
    public function modificar($param)
    {
        $obj = new Producto();

        $obj->setear(
            $param['idproducto'],
            $param['pronombre'],
            $param['prodetalle'] ?? "",
            $param['proprecio'],
            $param['procantstock'],
            $param['prooferta'] ?? 0,
            $param['profinoffer'] ?? null,
            null, // prodeshabilitado NO se modifica aquí
            $param['idusuario'],
            $param['proimagen'] ?? null
        );

        return $obj->modificar();
    }

    // ======================================================
    // ELIMINAR (BAJA LÓGICA)
    // ======================================================
    public function eliminar($id)
    {
        $obj = new Producto();
        $obj->setIdProducto($id);
        return $obj->deshabilitar();
    }

    // ======================================================
    // HABILITAR
    // ======================================================
    public function habilitar($id)
    {
        $obj = new Producto();
        $obj->setIdProducto($id);
        return $obj->habilitar();
    }

    // ======================================================
    // LISTAR HABILITADOS
    // ======================================================
    public function listar()
    {
        $obj = new Producto();
        return $obj->listar("prodeshabilitado IS NULL");
    }
 // ======================================================
    // LISTAR TODOS (PASAR NULL)
    // ======================================================
    public function listarTodo($estado = 'habilitados')
    {
        $producto = new Producto();

        switch ($estado) {
            case 'habilitados':
                return $producto->listar("prodeshabilitado IS NULL");
            case 'deshabilitados':
                return $producto->listar("prodeshabilitado IS NOT NULL");
            default: // null o cualquier otro -> todos
                return $producto->listar(); // sin condición
        }
    }

    // ======================================================
    // LISTAR POR USUARIO
    // ======================================================
    public function listarPorUsuario($idusuario)
    {
        $obj = new Producto();
        return $obj->listar("idusuario = $idusuario AND prodeshabilitado IS NULL");
    }

    // ======================================================
    // BUSCAR POR ID
    // ======================================================
    public function buscarPorId($id)
    {
        $obj = new Producto();
        $obj->setIdProducto($id);
        $obj->cargar();
        return $obj;
    }

    // ======================================================
    // BUSCAR POR PRIMERA PALABRA
    // ======================================================
    public function buscarPorNombrePrimeraPalabra($palabra)
    {
        $obj = new Producto();
        $todos = $obj->listar("prodeshabilitado IS NULL");
        $filtrados = [];

        foreach ($todos as $prod) {
            $firstWord = explode(' ', trim($prod->getProNombre()))[0];
            if ($firstWord === $palabra) {
                $filtrados[] = $prod;
            }
        }
        return $filtrados;
    }

}
