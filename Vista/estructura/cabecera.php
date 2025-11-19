<?php
// Vista/estructura/cabecera.php

include_once dirname(__DIR__, 2) . '/configuracion.php';
include_once dirname(__DIR__, 2) . '/Control/AbmMenu.php';
include_once dirname(__DIR__, 2) . '/Control/AbmUsuarioRol.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

$rolesUsuario = [];
if ($usuario) {
   $abmUR = new AbmUsuarioRol();
   $rolesUsuario = $abmUR->rolesDeUsuario($usuario->getIdUsuario());
}

/* -----------------------------------------------------
   🔹 NUEVO: obtener TODOS los menús visibles (medeshabilitado = 0)
   ----------------------------------------------------- */
$abmMenu = new AbmMenu();
$menus = $abmMenu->buscar("medeshabilitado = 0");

/* -----------------------------------------------------
   🔹 Agrupar padres e hijos
   ----------------------------------------------------- */
$menusPadre = [];
$menusHijos = [];

foreach ($menus as $m) {
   $padreObj = $m->getObjMenuPadre();

   if ($padreObj === null) {
      $menusPadre[] = $m;
   } else {
      $menusHijos[$padreObj->getIdMenu()][] = $m;
   }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Celulandia</title>
   <!-- Icono de la página -->
   <link rel="icon" type="image/x-icon" href="./Vista/imagenes/icon.ico">

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <link rel="stylesheet" href="<?= $GLOBALS['CSS_URL']; ?>cabecera.css">
   <link rel="stylesheet" href="<?= $GLOBALS['CSS_URL']; ?>pie.css">
   <link rel="stylesheet" href="<?= $GLOBALS['CSS_URL']; ?>carrito.css">
   <link rel="stylesheet" href="<?= $GLOBALS['CSS_URL']; ?>albumProductos.css">
</head>

<body>

   <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
         <div class="container">

            <!-- Botón menú lateral -->
            <button class="btn btn-outline-dark me-3"
               data-bs-toggle="offcanvas"
               data-bs-target="#sidebarMenu">
               <i class="fa fa-bars"></i>
            </button>

            <!-- Logo -->
            <a class="navbar-brand logo" href="<?= $GLOBALS['BASE_URL']; ?>">
               Celulandia
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav1">
               <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav1">
               <ul class="navbar-nav ms-auto">

                  <li class="nav-item">
                     <a class="nav-link" href="<?= $GLOBALS['VISTA_URL']; ?>producto/producto.php">Productos</a>
                  </li>

                  <li class="nav-item">
                     <a class="nav-link" href="<?= $GLOBALS['VISTA_URL']; ?>compra/carrito.php">Carrito</a>
                  </li>

                  <li class="nav-item">
                     <a class="nav-link" href="<?= $GLOBALS['VISTA_URL']; ?>contacto/contacto.php">Contacto</a>
                  </li>

                  <!-- ADMIN -->
                  <?php if ($usuario && in_array("admin", $rolesUsuario)): ?>
                     <li class="nav-item">
                        <a class="nav-link text-warning fw-bold"
                           href="<?= $GLOBALS['VISTA_URL']; ?>admin/panelAdmin.php">
                           <i class="fa fa-bars"></i> Administrar Paneles
                        </a>
                     </li>
                  <?php endif; ?>

                  <!-- Usuario -->
                  <?php if ($usuario): ?>
                     <li class="nav-item">
                        <a class="nav-link text-primary fw-bold"
                           href="<?= $GLOBALS['VISTA_URL']; ?>login/paginaSegura.php">
                           <i class="fa fa-user"></i>
                           <?= htmlspecialchars($usuario->getUsNombre()); ?>
                        </a>
                     </li>

                     <li class="nav-item">
                        <a class="nav-link text-danger fw-bold"
                           href="<?= $GLOBALS['VISTA_URL']; ?>login/accion/cerrarSesion.php">
                           <i class="fa fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                     </li>

                  <?php else: ?>
                     <li class="nav-item">
                        <a class="nav-link" href="<?= $GLOBALS['VISTA_URL']; ?>login/login.php">
                           <i class="fa fa-sign-in-alt"></i> Login
                        </a>
                     </li>
                  <?php endif; ?>

               </ul>
            </div>
         </div>
      </nav>
   </header>


<!-- MENÚ LATERAL -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5><i class="fa fa-bars me-1"></i> Menú</h5>
        <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <?php if (!empty($menusPadre)): ?>
            <ul class="list-group">

                <?php foreach ($menusPadre as $padre): ?>
                    <li class="list-group-item">

                        <!-- Nombre categoría padre -->
                        <strong><?= htmlspecialchars($padre->getMeNombre()); ?></strong>

                        <!-- "Ver todo" -->
                        <div class="mb-1">
                            <a href="<?= $GLOBALS['VISTA_URL']; ?>secciones/<?= $padre->getMeDescripcion(); ?>" class="text-decoration-none small">
                                Ver todo <?= htmlspecialchars($padre->getMeNombre()); ?>
                            </a>
                        </div>

                        <?php if (!empty($menusHijos[$padre->getIdMenu()])): ?>
                            <ul class="list-group ms-3 mt-1">
                                <?php foreach ($menusHijos[$padre->getIdMenu()] as $hijo): ?>
                                    <li class="list-group-item py-1">
                                        <a href="<?= $GLOBALS['VISTA_URL']; ?>secciones/<?= $hijo->getMeDescripcion(); ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($hijo->getMeNombre()); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>

            </ul>
        <?php else: ?>
            <p class="text-muted">No hay secciones aún.</p>
        <?php endif; ?>
    </div>
</div>


   <div style="padding-top: 90px;"></div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>