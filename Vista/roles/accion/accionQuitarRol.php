<?php
require_once dirname(__DIR__, 3) . '/configuracion.php';

$session = new Session();
$usuario = $session->getUsuario();

$abmUR = new AbmUsuarioRol();

$abmUR->accionQuitarRol($abmUR,$usuario);

