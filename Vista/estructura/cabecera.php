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
$menus = $abmMenu->buscar("medeshabilitado = '0000-00-00 00:00:00'");

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
   <link rel="icon" type="image/x-icon" href="<?= $GLOBALS['VISTA_URL']; ?>imagenes/icon.ico">

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

                  <!-- MIS COMPRAS -->
                  <?php if ($usuario && $session->tieneRol('cliente')): ?>
                     <li class="nav-item">
                        <a class="nav-link" href="<?= $GLOBALS['VISTA_URL']; ?>compra/verCompraCliente.php">
                           <i class="fa fa-shopping-bag"></i> Mis Compras
                        </a>
                     </li>
                  <?php endif; ?>

                  <?php if ($usuario && in_array("admin", $rolesUsuario)): ?>
                     <li class="nav-item">
                        <a class="nav-link text-warning fw-bold"
                           href="<?= $GLOBALS['VISTA_URL']; ?>compra/verCompraAdmin.php">
                           <i class="fa fa-shopping-bag"></i> Compras Admin
                        </a>
                     </li>
                  <?php endif; ?>



                  <!-- ADMIN -->
                  <?php if ($usuario && in_array("admin", $rolesUsuario)): ?>
                     <li class="nav-item">
                        <a class="nav-link text-warning fw-bold"
                           href="<?= $GLOBALS['VISTA_URL']; ?>panelAdmin.php">
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
         <h5 class="fw-bold">
            <i class="fa fa-bars me-2"></i> Categorías
         </h5>
         <button class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body">

         <?php if (!empty($menusPadre)): ?>

            <?php
            /* -----------------------------------------------------
               🔁 Función RECURSIVA — puro Bootstrap collapse
            ----------------------------------------------------- */
            function menuBootstrap($menu, $hijos, $nivel = 0)
            {

               $id = $menu->getIdMenu();
               $hasChildren = !empty($hijos[$id]);
               $padding = $nivel * 2; // p-2, p-4, p-6...

               echo "<div class='mb-1 ps-$padding'>";

               /* ---------- ENCABEZADO DE LA CATEGORÍA ---------- */
               if ($hasChildren) {
                  echo "
                        <a class='d-flex justify-content-between align-items-center text-dark text-decoration-none'
                           data-bs-toggle='collapse'
                           href='#collapse-$id'
                           role='button'
                           aria-expanded='false'>
                            <span>
                                <i class='fa fa-folder-open me-2 text-warning'></i>
                                " . htmlspecialchars($menu->getMeNombre()) . "
                            </span>
                            <i class='fa fa-chevron-down'></i>
                        </a>
                    ";
               } else {
                  echo "
                        <span class='d-flex align-items-center'>
                            <i class='fa fa-folder me-2 text-secondary'></i>
                            " . htmlspecialchars($menu->getMeNombre()) . "
                        </span>
                    ";
               }

               /* ---------- ENLACE VER TODO ---------- */
               echo "
                    <div class='ms-4'>
                        <a href='{$GLOBALS['VISTA_URL']}secciones/{$menu->getMeLink()}'
                           class='small text-primary text-decoration-none'>
                            Ver todo " . htmlspecialchars($menu->getMeNombre()) . "
                        </a>
                    </div>
                ";

               /* ---------- HIJOS ---------- */
               if ($hasChildren) {
                  echo "<div class='collapse mt-1' id='collapse-$id'>";

                  foreach ($hijos[$id] as $hijo) {
                     menuBootstrap($hijo, $hijos, $nivel + 1);
                  }

                  echo "</div>";
               }

               echo "</div>";
            }
            ?>

            <!-- Contenedor general -->
            <div>
               <?php foreach ($menusPadre as $padre): ?>
                  <?php menuBootstrap($padre, $menusHijos); ?>
               <?php endforeach; ?>
            </div>

         <?php else: ?>
            <p class="text-muted">No hay secciones aún.</p>
         <?php endif; ?>

      </div>
   </div>



   <div style="padding-top: 60px;"></div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>