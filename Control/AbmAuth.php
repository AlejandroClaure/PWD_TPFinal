<?php
require_once '../../../configuracion.php';

class AbmAuth {

    public function registrarYLogin($data)
{
    $abmUsuario = new AbmUsuario();

    // 1) Validar si el mail ya existe
    $existe = $abmUsuario->buscar(["usmail" => $data['usmail']]);

    if (count($existe) > 0) {
        error_log("El email ya está registrado");
        return false;
    }

    // 2) Crear usuario usando TU MÉTODO registrar()
    $idUsuario = $abmUsuario->registrar($data);

    if (!$idUsuario) {
        error_log("No se pudo crear el usuario");
        return false;
    }

    error_log("ID creado: $idUsuario");

    // 3) Asignar rol cliente = idrol 2
    $abmUR = new AbmUsuarioRol();
    $rolAsignado = $abmUR->asignarRol($idUsuario,2);
    
    error_log("Resultado asignar rol: " . ($rolAsignado ? "OK" : "FALLO"));

    // 4) Iniciar sesión automática
    $session = new Session();
    $session->iniciar($data['usnombre'], $data['uspass']);

    return true;
}

}