<?php
include_once '../../../configuracion.php';

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Inicia la sesión usando nombre de usuario y contraseña
     */
    public function iniciar($usNombre, $usPass)
    {
        $abmUsuario = new AbmUsuario();

        // Busco el usuario por nombre
        $usuarios = $abmUsuario->buscar(['usnombre' => $usNombre]);

        if (empty($usuarios)) {
            return false;
        }

        $usuario = $usuarios[0];

        // Verifico la contraseña hasheada
        if (!password_verify($usPass, $usuario->getUsPass())) {
            return false;
        }

        // Login correcto → guardo datos en session
        $_SESSION['idusuario'] = $usuario->getIdUsuario();
        $_SESSION['usnombre']  = $usuario->getUsNombre();
        $_SESSION['usmail']    = $usuario->getUsMail();

        return true;
    }

    /**
     * Indica si el usuario está logueado
     */
    public function activa()
    {
        return isset($_SESSION['idusuario']);
    }

    /**
     * Devuelve el objeto usuario actualmente logueado
     */
    public function getUsuario()
    {
        if (!$this->activa()) {
            return null;
        }

        $abmUsuario = new AbmUsuario();
        $usuarios = $abmUsuario->buscar(['idusuario' => $_SESSION['idusuario']]);

        return $usuarios[0] ?? null;
    }

    /**
     * Cierra sesión
     */
    public function cerrar()
    {
        session_destroy();
        $_SESSION = [];
    }
}