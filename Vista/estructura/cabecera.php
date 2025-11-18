<?php
include_once dirname(__DIR__, 2) . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$session = new Session();
$usuario = $session->getUsuario();

$rolesUsuario = [];
if ($usuario) {
   // Esto devuelve strings (“admin”, “cliente”, …)
   $rolesUsuario = (new AbmUsuarioRol())->rolesDeUsuario($usuario->getIdUsuario());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tienda Online</title>

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

            <!-- Logo -->
            <a class="navbar-brand logo" href="<?= $GLOBALS['BASE_URL']; ?>">
               <img src="<?= $GLOBALS['IMG_URL']; ?>logo.png"
                  alt="Logo" width="50" height="50"
                  class="me-1 rounded-circle">
               Tienda Online
            </a>

            <!-- Botón menú responsive -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
               data-bs-target="#navbarNav1">
               <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav1">
               <ul class="navbar-nav ms-auto">
                  <?php
                  if ($usuario) {
                     $abmMenu = new AbmMenu();
                     $menus = $abmMenu->obtenerMenuPorRoles($rolesUsuario);

                     // Agrupar por padre
                     $menusPadre = array_filter($menus, fn($m) => $m->getIdPadre() === null);
                     $menusHijos = [];

                     foreach ($menus as $m) {
                        if ($m->getIdPadre() !== null) {
                           $menusHijos[$m->getIdPadre()][] = $m;
                        }
                     }
                  }
                  ?>

                  <?php if ($usuario && !empty($menus)): ?>

                     <?php foreach ($menusPadre as $padre): ?>
                        <?php $id = $padre->getIdMenu(); ?>

                        <?php if (isset($menusHijos[$id])): ?>
                           <!-- Dropdown -->
                           <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" role="button"
                                 data-bs-toggle="dropdown">
                                 <?= $padre->getMeNombre(); ?>
                              </a>
                              <ul class="dropdown-menu">
                                 <?php foreach ($menusHijos[$id] as $hijo): ?>
                                    <li>
                                       <a class="dropdown-item" href="<?= $GLOBALS['VISTA_URL'] . $hijo->getMeDescripcion(); ?>">
                                          <?= $hijo->getMeNombre(); ?>
                                       </a>
                                    </li>
                                 <?php endforeach; ?>
                              </ul>
                           </li>

                        <?php else: ?>
                           <!-- Ítem simple -->
                           <li class="nav-item">
                              <a class="nav-link" href="<?= $GLOBALS['VISTA_URL'] . $padre->getMeDescripcion(); ?>">
                                 <?= $padre->getMeNombre(); ?>
                              </a>
                           </li>
                        <?php endif; ?>

                     <?php endforeach; ?>

                  <?php endif; ?>

                  <!-- Ítems estándar -->
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
                           href="<?= $GLOBALS['VISTA_URL']; ?>admin/roles/panelRoles.php">
                           <i class="fa fa-users-cog"></i> Administrar Roles
                        </a>
                     </li>
                  <?php endif; ?>


                  <!-- Usuario logueado -->
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

                     <!-- Usuario NO logueado -->
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

   <!-- Espacio para que no quede el contenido debajo del header -->
   <div style="padding-top: 90px;"></div>