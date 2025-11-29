<?php
// Archivo: Vista/admin/roles/accion/rolesDeUsuario.php

ob_clean();
header('Content-Type: application/json; charset=utf-8');

// subir 5 niveles desde esta carpeta hasta la raíz del proyecto
$rootPath = dirname(__DIR__, 3);
require_once $rootPath . '/configuracion.php';

$idusuario = $_GET['idusuario'] ?? null;
$abmUR = new AbmUsuarioRol();
$result = $abmUR->accionRolesDelUsuario($idusuario);

if (!$result['estado']) {

    if ($result['error'] === "id_invalido") {
        http_response_code(400); // Bad Request
        echo json_encode([]);
        exit;
    }

    if ($result['error'] === "server_error") {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Error en el servidor"]);
        exit;
    }
}

// Éxito
http_response_code(200);
echo json_encode($result['data'], JSON_UNESCAPED_UNICODE);
exit;