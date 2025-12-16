<?php include('header.php'); ?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<style nonce="<?= $CSP_NONCE ?>">
  :root{
    --gold:#cda45e;
    --dark:#0c0b09;
    --bg:#f6f7f9;
    --card:#ffffff;
  }

  body{
    font-family: 'Roboto', Arial, sans-serif;
    background: var(--bg);
    color:#222;
    margin:0;
  }

  h1,h2,h3,h4,h5{
    font-family:'Overlock', serif;
    letter-spacing:.3px;
    color: var(--dark);
  }

  .properties-carousel h3{
    font-weight:900;
    margin-bottom:18px;
  }

  #propertyCarousel{
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 12px 35px rgba(0,0,0,.10);
    background:#000;
  }

  #propertyCarousel .carousel-item{
    position:relative;
    height:520px;
    min-height:420px;
    background:#000;
    overflow:hidden;
  }

  #propertyCarousel .carousel-item img{
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    width:100%;
    height:100%;
    object-fit:cover;
    object-position:center center;
    display:block;
    filter:saturate(1.05) contrast(1.02);
  }

  #propertyCarousel .carousel-item::after{
    content:"";
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    background:linear-gradient(to bottom, rgba(0,0,0,.15) 0%, rgba(0,0,0,.55) 65%, rgba(0,0,0,.75) 100%);
    z-index:1;
    pointer-events:none;
  }

  #propertyCarousel .carousel-caption{
    z-index:2;
    left:8%;
    right:8%;
    bottom:10%;
    text-align:left;

    padding:14px 16px;
    border-radius:14px;
    background:rgba(0,0,0,.28);
    border:1px solid rgba(255,255,255,.10);
    box-shadow:0 10px 30px rgba(0,0,0,.20);
  }

  #propertyCarousel .carousel-caption h4{
    color:#fff;
    font-weight:900;
    font-size:1.8rem;
    margin-bottom:.35rem;
    text-shadow:0 6px 18px rgba(0,0,0,.35);
  }

  #propertyCarousel .carousel-caption p{
    color:rgba(255,255,255,.92);
    font-size:1.05rem;
    margin-bottom:10px;
    text-shadow:0 6px 18px rgba(0,0,0,.35);
  }

  .cta-row{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
  }

  /* Solo queda el botón "Más información" */
  .btn-ghost{
    background:transparent;
    color:#fff;
    border:1px solid rgba(255,255,255,.35);
    border-radius:999px;
    padding:10px 16px;
    font-weight:700;
  }
  .btn-ghost:hover{ color:#fff; border-color:rgba(255,255,255,.6); }

  #propertyCarousel .carousel-indicators{
    margin-bottom:12px;
  }
  #propertyCarousel .carousel-indicators li{
    width:10px;
    height:10px;
    border-radius:50%;
    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.25);
  }
  #propertyCarousel .carousel-indicators .active{
    background:var(--gold);
    border-color:rgba(0,0,0,.15);
  }

  #propertyCarousel .carousel-control-prev,
  #propertyCarousel .carousel-control-next{
    width:10%;
    opacity:1;
  }

  #propertyCarousel .carousel-control-prev-icon,
  #propertyCarousel .carousel-control-next-icon{
    width:44px;
    height:44px;
    border-radius:50%;
    background-color:rgba(0,0,0,.55);
    background-size:60% 60%;
    border:1px solid rgba(255,255,255,.25);
    box-shadow:0 10px 25px rgba(0,0,0,.25);
  }

  #propertyCarousel .carousel-control-prev:hover .carousel-control-prev-icon,
  #propertyCarousel .carousel-control-next:hover .carousel-control-next-icon{
    background-color:rgba(205,164,94,.85);
    border-color:rgba(0,0,0,.15);
  }

  section.container.mt-5{
    max-width:980px;
    background:var(--card);
    border-radius:16px;
    padding:28px 26px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
  }

  section.container.mt-5 p{
    color:#444;
    line-height:1.8;
    margin:0;
  }

  @media (max-width: 992px){
    #propertyCarousel .carousel-item{ height:460px; }
    #propertyCarousel .carousel-caption h4{ font-size:1.5rem; }
  }
  @media (max-width: 576px){
    #propertyCarousel .carousel-item{ height:380px; }
    #propertyCarousel .carousel-caption{ bottom:8%; padding:12px; }
    #propertyCarousel .carousel-caption h4{ font-size:1.25rem; }
    #propertyCarousel .carousel-caption p{ font-size:.95rem; }
    #propertyCarousel .carousel-control-prev,
    #propertyCarousel .carousel-control-next{ width:14%; }
  }
</style>

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
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/dsggfsd.jpg" alt="Propiedad 2">
        <div class="carousel-caption">
          <h4>Departamento en Alquiler - 2 Habitaciones</h4>
          <p>Ciudad Y - $800/mes</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/gdfdf.jpg" alt="Propiedad 3">
        <div class="carousel-caption">
          <h4>Oficina en Alquiler - 100m²</h4>
          <p>Centro - $1,500/mes</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/gfhfdgdf.jpg" alt="Propiedad 4">
        <div class="carousel-caption">
          <h4>Casa en Venta - 4 Habitaciones</h4>
          <p>Ciudad Z - $250,000</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/hdfgfddgfd.jpg" alt="Propiedad 5">
        <div class="carousel-caption">
          <h4>Departamento en Alquiler - 1 Habitación</h4>
          <p>Ciudad W - $600/mes</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/images.jpg" alt="Propiedad 6">
        <div class="carousel-caption">
          <h4>Oficina en Venta - 150m²</h4>
          <p>Centro - $300,000</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/jhgfhy.jpg" alt="Propiedad 7">
        <div class="carousel-caption">
          <h4>Casa en Venta - 5 Habitaciones</h4>
          <p>Ciudad V - $350,000</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
        </div>
      </div>

      <div class="carousel-item">
        <img src="Images/Index/ygtujyt.jpg" alt="Propiedad 8">
        <div class="carousel-caption">
          <h4>Departamento en Alquiler - 3 Habitaciones</h4>
          <p>Ciudad U - $1,200/mes</p>
          <div class="cta-row">
            <a href="contacto.php" class="btn btn-ghost">Más información</a>
          </div>
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

</body>
</html>
