<?php
// Archivo: Vista/admin/roles/accion/rolesDeUsuario.php

ob_clean();
header('Content-Type: application/json; charset=utf-8');

// subir 5 niveles desde esta carpeta hasta la raíz del proyecto
$rootPath = dirname(__DIR__, 3);
require_once $rootPath . '/configuracion.php';

$idusuario = $_GET['idusuario'] ?? null;
$abmUR = new AbmUsuarioRol();
$abmUR->accionRolesDelUsuario($idusuario);