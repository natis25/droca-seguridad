<?php
require 'vendor/autoload.php';

// Rutas de archivos necesarios
require_once __DIR__ . '/Logica/sql.php';
require_once __DIR__ . '/Logica/csrf_helpers.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generar token CSRF
csrf_generate_token();

// Generar nonce para CSP
try {
    $CSP_NONCE = base64_encode(random_bytes(16));
} catch (Exception $e) {
    $CSP_NONCE = base64_encode(openssl_random_pseudo_bytes(16));
}

/* ================== HEADERS DE SEGURIDAD ================== */
header("X-Frame-Options: DENY"); 
header("X-Content-Type-Options: nosniff");

header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "base-uri 'self'; " .
    "object-src 'none'; " .
    "frame-ancestors 'none'; " .
    "script-src 'self' 'nonce-{$CSP_NONCE}'; " . 
    "style-src 'self' 'nonce-{$CSP_NONCE}'; " . 
    "img-src 'self' data:; " .
    "connect-src 'self'; " .
    "upgrade-insecure-requests"
);

// Carga segura de variables de entorno (Solo una vez)
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Función auxiliar
function getEnvVar($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

$conn = Conectarse();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar token CSRF
    if (!csrf_validate()) {
        echo "<script nonce='{$CSP_NONCE}'>alert('❌ Token de seguridad inválido. Por favor, intenta nuevamente.');</script>";
    } else {
        $email = trim($_POST['correo']);

        // Buscar si el correo existe
        $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE Correo=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generar token
            $token = bin2hex(random_bytes(50));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $conn->prepare(
                "UPDATE cliente SET rcvPass_token=?, rcvPass_token_expires=? WHERE Correo=?"
            );
            $update->bind_param("sss", $token, $expira, $email);
            $update->execute();

            // Detectar dominio real
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $link = $protocol . $domainName . "/Logica/resetPassword.php?token=$token";

            // Enviar correo
            $mail = new PHPMailer(true);
            try {
                // 1. AUMENTAR TIEMPO LIMITE PHP (Evita error 500 por timeout)
                set_time_limit(120); 

                $mail->isSMTP();

                // Configuración de Debug para Railway
                $mail->SMTPDebug = 2; 
                $mail->Debugoutput = function($str, $level) {
                    error_log("SMTP: $str"); 
                };

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
                
                // NOTA: No ponemos SMTPDebug = 0 aquí para que si falla, veamos el log.
                
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                // 2. TRUCO DE IPV4 (Evita bloqueo de IPv6 de Gmail)
                $host = getEnvVar('MAIL_HOST');
                $mail->Host = gethostbyname($host); 
                
                $mail->SMTPAuth = true;
                $mail->Username = getEnvVar('MAIL_USERNAME');
                $mail->Password = getEnvVar('MAIL_PASSWORD');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = getEnvVar('MAIL_PORT');

                $fromName = getEnvVar('MAIL_FROM_NAME', 'Soporte DRoca');
                $mail->setFrom(getEnvVar('MAIL_FROM'), $fromName);

                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "🔐 Recuperación de contraseña - DRoca Inmobiliaria";
                
                // Usamos un HTML un poco más bonito
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #007bff;'>Solicitud de recuperación de contraseña</h2>
                    <p>Haga clic en el siguiente enlace para restablecer su contraseña:</p>
                    <p>
                        <a href='$link' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Restablecer Contraseña
                        </a>
                    </p>
                    <p style='font-size: 12px; color: #666;'>Si el botón no funciona, copia y pega este enlace: <br> $link</p>
                    <p>Este enlace expirará en 1 hora.</p>
                </div>
                ";

                $mail->send();
                echo "<script nonce='{$CSP_NONCE}'>alert('✅ Se envió un enlace de recuperación a tu correo.'); window.location='login.php';</script>";
            } catch (Exception $e) {
                // Esto registrará el error exacto en los LOGS de Railway
                error_log("Mailer Error Fatal: " . $mail->ErrorInfo);
                echo "<script nonce='{$CSP_NONCE}'>alert('❌ Error al enviar correo. Revisa los logs del servidor.');</script>";
            }
        } else {
            echo "<script nonce='{$CSP_NONCE}'>alert('⚠️ El correo no está registrado.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style nonce="<?= $CSP_NONCE ?>">
        body {
            background: linear-gradient(135deg, #40baf3ff, #560bad);
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
            text-align: center;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 1em auto 2em;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            color: #ffd500ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form action="recover.php" method="POST">
        <?php echo csrf_field(); ?>
        <h2>🔑 Recuperar Contraseña</h2>
        <input type="email" name="correo" placeholder="Tu correo registrado" required>
        <button type="submit">Enviar enlace</button><br><br>
        <a href="login.php">Volver a Inicio de Sesión</a>
    </form>
</body>
</html>