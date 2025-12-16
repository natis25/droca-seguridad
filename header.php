<?php
// Generar nonce para CSP si no existe
if (!isset($CSP_NONCE)) {
    try {
        $CSP_NONCE = base64_encode(random_bytes(16));
    } catch (Exception $e) {
        $CSP_NONCE = base64_encode(openssl_random_pseudo_bytes(16));
    }
}

// ================== HEADERS DE SEGURIDAD (OWASP ZAP) ==================
// Deben enviarse ANTES de cualquier salida HTML
header("X-Frame-Options: DENY"); // Anti-Clickjacking
header("X-Content-Type-Options: nosniff");

header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .                 // Fallback
    "base-uri 'self'; " .
    "object-src 'none'; " .
    "frame-ancestors 'none'; " .              // Anti-Clickjacking
    "script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net https://maxcdn.bootstrapcdn.com; " .
    "style-src 'self' https://fonts.googleapis.com https://maxcdn.bootstrapcdn.com 'nonce-{$CSP_NONCE}'; " .
    "font-src 'self' https://fonts.gstatic.com; " .
    "img-src 'self' data:; " .
    "connect-src 'self'; " .
    "upgrade-insecure-requests"
);
// =====================================================================

// header.php - Header universal para todo el sistema
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Detectar si estamos en el área OSI (carpeta OSI)
$is_osi_area = (strpos($_SERVER['PHP_SELF'], '/OSI/') !== false);

// Determinar la ruta correcta para las imágenes
$logo_path = $is_osi_area ? '../images/Logo.png' : 'images/Logo.png';
$home_path = $is_osi_area ? '../index.php' : 'index.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style nonce="<?= $CSP_NONCE ?>">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding-top: 100px;
        }

        .navbar {
            height: 100px;
            background:
                <?php echo $is_osi_area
                    ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                    : 'linear-gradient(135deg, #f5deb3 0%, #daa520 100%)'; ?>
            ;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            height: 80px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.15);
        }

        .navbar .navbar-brand {
            display: flex;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            height: 100%;
            transition: all 0.3s ease;
        }

        .navbar .navbar-brand img {
            max-height: 80%;
            height: auto;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
            transition: transform 0.3s ease;
        }

        .navbar .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .navbar-nav {
            align-items: center;
        }

        .navbar-nav .nav-link {
            font-size: 16px;
            font-weight: 500;
            padding: 12px 20px !important;
            margin: 0 8px;
            color:
                <?php echo $is_osi_area ? '#ffffff' : '#333333'; ?>
                !important;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .navbar-nav .nav-link:hover::before {
            left: 0;
        }

        .navbar-nav .nav-link:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-nav .nav-link:active {
            transform: translateY(0);
        }

        .navbar-toggler {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                margin-top: 15px;
                padding: 15px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            }

            .navbar-nav .nav-link {
                color: #333333 !important;
                margin: 5px 0;
            }

            .navbar-nav .nav-link:hover {
                background: rgba(102, 126, 234, 0.1);
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .navbar {
                height: 80px;
            }

            .navbar .navbar-brand img {
                max-height: 70%;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="<?php echo $home_path; ?>">
            <img src="<?php echo $logo_path; ?>" class="d-inline-block align-top" alt="Logo">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $home_path; ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../inmuebles.php' : 'inmuebles.php'; ?>">
                        Inmuebles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../citas.php' : 'citas.php'; ?>">
                        Cancelar Cita
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_osi_area ? '../contacto.php' : 'contacto.php'; ?>">
                        Contacto
                    </a>
                </li>
            </ul>

            <?php if (!$is_osi_area): ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">¿Eres empleado?</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>