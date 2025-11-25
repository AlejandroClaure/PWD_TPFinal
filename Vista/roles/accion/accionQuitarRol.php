<?php
require_once dirname(__DIR__, 3) . '/configuracion.php';

$session = new Session();
$usuario = $session->getUsuario();

$abmUR = new AbmUsuarioRol();

$result = $abmUR->quitarRolSeguro(
    $usuario->getIdUsuario(),      // el que ejecuta
    $_POST['idusuario'] ?? null,   // usuario objetivo
    $_POST['idrol'] ?? null        // rol a quitar
);

$_SESSION['mensaje'] = $result['msg'];

header("Location: ../panelRoles.php");
exit;
