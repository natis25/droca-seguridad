<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>D'Roca Inmobiliaria</title>

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Overlock:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <style>
      body { font-family: Arial, sans-serif; }
      nav a { color: white; margin-right: 20px; text-decoration: none; }
      .banner {
          background: url('images/banner.jpg') no-repeat center center;
          background-size: cover;
          height: 300px;
          text-align: center;
          color: white;
      }
      .banner h2 { padding-top: 100px; font-size: 48px; }
      .carousel-inner img { width: 100%; height: 500px; object-fit: cover; }
      .testimonials { background-color: #f9f9f9; padding: 30px; }
      .testimonials h3 { text-align: center; margin-bottom: 20px; }
      .testimonials p { font-style: italic; text-align: center; }
  </style>
</head>

<body>
<section class="properties-carousel container mt-5">
    <h3 class="text-center">Propiedades Destacadas</h3>
    <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
        <ul class="carousel-indicators">
            <li data-target="#propertyCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#propertyCarousel" data-slide-to="1"></li>
            <li data-target="#propertyCarousel" data-slide-to="2"></li>
            <li data-target="#propertyCarousel" data-slide-to="3"></li>
            <li data-target="#propertyCarousel" data-slide-to="4"></li>
            <li data-target="#propertyCarousel" data-slide-to="5"></li>
            <li data-target="#propertyCarousel" data-slide-to="6"></li>
            <li data-target="#propertyCarousel" data-slide-to="7"></li>
        </ul>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="Images/Index/dahgdfh.jpg" alt="Propiedad 1">
                <div class="carousel-caption">
                    <h4>Casa en Venta - 3 Habitaciones</h4>
                    <p>Ciudad X - $200,000</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/dsggfsd.jpg" alt="Propiedad 2">
                <div class="carousel-caption">
                    <h4>Departamento en Alquiler - 2 Habitaciones</h4>
                    <p>Ciudad Y - $800/mes</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/gdfdf.jpg" alt="Propiedad 3">
                <div class="carousel-caption">
                    <h4>Oficina en Alquiler - 100m²</h4>
                    <p>Centro - $1,500/mes</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/gfhfdgdf.jpg" alt="Propiedad 4">
                <div class="carousel-caption">
                    <h4>Casa en Venta - 4 Habitaciones</h4>
                    <p>Ciudad Z - $250,000</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/hdfgfddgfd.jpg" alt="Propiedad 5">
                <div class="carousel-caption">
                    <h4>Departamento en Alquiler - 1 Habitación</h4>
                    <p>Ciudad W - $600/mes</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/images.jpg" alt="Propiedad 6">
                <div class="carousel-caption">
                    <h4>Oficina en Venta - 150m²</h4>
                    <p>Centro - $300,000</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/jhgfhy.jpg" alt="Propiedad 7">
                <div class="carousel-caption">
                    <h4>Casa en Venta - 5 Habitaciones</h4>
                    <p>Ciudad V - $350,000</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="Images/Index/ygtujyt.jpg" alt="Propiedad 8">
                <div class="carousel-caption">
                    <h4>Departamento en Alquiler - 3 Habitaciones</h4>
                    <p>Ciudad U - $1,200/mes</p>
                </div>
            </div>
        </div>

        <a class="carousel-control-prev" href="#propertyCarousel" data-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#propertyCarousel" data-slide="next">
            <span class="carousel-control-next-icon"></span>
        </a>
    </div>
</section>

<section class="container mt-5">
    <h3 class="text-center">Sobre Nosotros</h3>
    <p class="text-center">
        Nosotros D'Roca como empresa inmobiliaria con 4 años de esperiencia en el mercado, nos dedicamos a la venta y alquiler de propiedades en todo el país. Contamos con un equipo de profesionales altamente capacitados para brindarle el mejor servicio y asesoramiento en la compra, venta o alquiler de su propiedad.
    </p>
</section>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
