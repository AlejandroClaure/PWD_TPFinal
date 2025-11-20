<?php
include_once '../../configuracion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$session = new Session();

?>


<?php include_once '../estructura/cabecera.php'; ?>


<main class="mt-5 pt-5">
  <section class="container py-5">
    <h2 class="text-center mb-4">Nuestros Productos</h2>
    
    <?php include_once '../producto/albumProductos.php'; ?>
    
  </section>
</main>

<?php include_once '../estructura/pie.php'; ?>
