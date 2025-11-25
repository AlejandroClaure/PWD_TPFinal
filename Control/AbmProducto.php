<?php
include_once dirname(__DIR__) . '/Modelo/Producto.php';

class AbmProducto
{
    // ======================================================
    // CREAR
    // ======================================================
   public function crear($datos)
{
    try {
        // Recibir y sanitizar datos
        $pronombre    = ($datos['pronombre'] ?? '');
        $proprecio    = floatval($datos['proprecio'] ?? 0);
        $procantstock = intval($datos['procantstock'] ?? 0);
        $prodetalle   = trim($datos['prodetalle'] ?? '');
        $idmenu       = intval($datos['categoria'] ?? 0);
        $idusuario    = intval($datos['idusuario'] ?? 0);
        $archivoImg   = $datos['proimagen'] ?? null;

        // Validaciones básicas
        if ($pronombre === '' || $idmenu <= 0 || $idusuario <= 0 || 
            $proprecio < 0 || $procantstock < 0) {
            throw new Exception("Datos obligatorios faltantes o inválidos.");
        }

        // Obtener cadena de categorías
        $abmMenu = new AbmMenu();
        $menus = $abmMenu->buscar(['idmenu' => $idmenu]);

        if (empty($menus)) {
            throw new Exception("Categoría no encontrada (idmenu: $idmenu)");
        }

        $menu = $menus[0];
        $categorias = [];
        $actual = $menu;

        while ($actual !== null) {
            $categorias[] = strtolower(trim($actual->getMeNombre()));
            $actual = $actual->getObjMenuPadre();
        }

        $categorias = array_reverse($categorias);
        $prefijo = implode('_', $categorias) . '_';

        $nombreFinal = $prefijo . $pronombre;

        // Subir imagen
        $imagenNombre = null;
        if ($archivoImg && $archivoImg['error'] === UPLOAD_ERR_OK && $archivoImg['size'] > 0) {

            $ext = strtolower(pathinfo($archivoImg['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $extensionesPermitidas)) {
                throw new Exception("Solo se permiten imágenes JPG, PNG, GIF o WEBP.");
            }

            $imagenNombre = $this->limpiarNombre($pronombre) . "_" . time() . ".$ext";
            $carpeta = $_SERVER['DOCUMENT_ROOT'] . "/PWD_TPFinal/Vista/imagenes/productos/";

            if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
                throw new Exception("No se pudo crear la carpeta de imágenes.");
            }

            $rutaFinal = $carpeta . $imagenNombre;

            if (!move_uploaded_file($archivoImg['tmp_name'], $rutaFinal)) {
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Crear producto
        $obj = new Producto();
        $obj->setear(
            0,
            $nombreFinal,
            $prodetalle,
            $proprecio,
            $procantstock,
            0,
            null,
            null,
            $idusuario,
            $imagenNombre
        );

        if (!$obj->insertar()) {

            if ($imagenNombre && isset($rutaFinal) && file_exists($rutaFinal)) {
                unlink($rutaFinal);
            }

            throw new Exception("Error al insertar producto en la base de datos.");
        }

        return true;

    } catch (Exception $e) {
        // Esto reemplaza setMensajeOperacion
        return [
            "ok" => false,
            "error" => $e->getMessage()
        ];
    }
}


    // Función auxiliar para limpiar nombres (para archivo y nombre de producto)
    private function limpiarNombre($texto)
    {
        $texto = trim($texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto); // quitar tildes
        $texto = preg_replace('/[^a-zA-Z0-9_-]/', '_', $texto);
        $texto = preg_replace('/_+/', '_', $texto); // evitar ___
        return $texto;
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
