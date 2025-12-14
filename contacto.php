<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .contact-section {
            margin: 50px auto;
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .contact-section h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .social-media img {
            margin: 0 10px;
            width: 40px;
        }
    </style>
</head>

<body>
    <div class="contact-section">
        <h1>Contáctanos</h1>

        <!-- Formulario de Contacto -->
        <form action="mailto:tu_correo@ejemplo.com" method="get" enctype="text/plain">
            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" class="form-control" id="name" name="subject" placeholder="Tu Nombre" required>
            </div>
            <div class="form-group">
                <label for="message">Mensaje:</label>
                <textarea class="form-control" id="message" name="body" rows="5" placeholder="Escribe tu mensaje aquí" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>

        <div class="social-media">
            <h2>Contáctanos</h2>
            <a href="https://www.facebook.com/profile.php?id=100064156680320&mibextid=ZbWKwL" target="_blank" rel="noopener">
                <img src="Images/Contactos/icons8-facebook.svg" alt="Facebook">
            </a>
            <a href="https://www.tiktok.com/@drocainm?_t=8qgXOUboT8X&_r=1" target="_blank" rel="noopener">
                <img src="Images/Contactos/icons8-tiktok.svg" alt="TikTok">
            </a>
            <a href="https://www.instagram.com/drocainmobiliaria?igsh=Zzg1MGFoMHFiZGY0" target="_blank" rel="noopener">
                <img src="Images/Contactos/icons8-instagram.svg" alt="Instagram">
            </a>
            <a href="https://api.whatsapp.com/send?phone=59160569601" target="_blank" rel="noopener">
                <img src="Images/Contactos/icons8-whatsapp.svg" alt="WhatsApp">
            </a>
        </div>
    </div>
</body>
</html>
