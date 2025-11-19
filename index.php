<?php
include_once 'configuracion.php';
include_once $GLOBALS['VISTA_PATH'].'estructura/cabecera.php';
?>
<main class="mt-5 pt-5">

<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">

   <!-- Indicadores -->
   <div class="carousel-indicators">
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></button>
   </div>

   <!-- Imágenes -->
   <div class="carousel-inner">
      <div class="carousel-item active">
         <img src="Vista/imagenes/carousel01.jpg" class="d-block w-100" alt="promo iphone">
      </div>
      <div class="carousel-item">
         <img src="Vista/imagenes/carousel02.png" class="d-block w-100" alt="samsungF54">
      </div>
      <div class="carousel-item">
         <img src="Vista/imagenes/carousel03.jpg" class="d-block w-100" alt="samsungS25">
      </div>
   </div>

   <!-- Controles -->
   <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
   </button>

   <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
   </button>

</div>


</main>

<?php include_once $GLOBALS['VISTA_PATH'] . 'estructura/pie.php'; ?>

