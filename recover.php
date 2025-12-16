<?php
require 'vendor/autoload.php';

// Rutas correctas
require_once __DIR__ . '/Logica/sql.php';
require_once __DIR__ . '/Logica/csrf_helpers.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Inicialización segura de entorno
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

function getEnvVar($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// Generar token CSRF y Nonce
csrf_generate_token();

try {
    $CSP_NONCE = base64_encode(random_bytes(16));
} catch (Exception $e) {
    $CSP_NONCE = base64_encode(openssl_random_pseudo_bytes(16));
}

// Headers de seguridad
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

$conn = Conectarse();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!csrf_validate()) {
        echo "<script nonce='{$CSP_NONCE}'>alert('❌ Token de seguridad inválido. Recarga la página.');</script>";
    } else {
        $email = trim($_POST['correo']);

        // Verificar DB
        $stmt = $conn->prepare("SELECT idCliente FROM cliente WHERE Correo=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Tokens DB
            $token = bin2hex(random_bytes(50));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $conn->prepare("UPDATE cliente SET rcvPass_token=?, rcvPass_token_expires=? WHERE Correo=?");
            $update->bind_param("sss", $token, $expira, $email);
            $update->execute();

            // Link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $link = $protocol . $domainName . "/Logica/resetPassword.php?token=$token";

            // --- INICIO ENVÍO CORREO ---
            $mail = new PHPMailer(true);
            try {
                // Aumentar tiempo de espera
                set_time_limit(120);

                // Configuración Debug para Logs de Railway
                $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Nivel 2
                $mail->Debugoutput = function($str, $level) {
                    error_log("SMTP LOG: $str");
                };

                $mail->isSMTP();
                $mail->Host = gethostbyname(getEnvVar('MAIL_HOST')); // Truco IPv4
                
                $mail->SMTPAuth = true;
                $mail->Username = getEnvVar('MAIL_USERNAME');
                $mail->Password = getEnvVar('MAIL_PASSWORD');
                
                // CAMBIO IMPORTANTE: Usar SSL (Puerto 465) en lugar de TLS (587)
                // Esto suele fallar menos en la nube.
                // Asegúrate de cambiar la variable MAIL_PORT a 465 en Railway
                $port = getEnvVar('MAIL_PORT');
                
                if ($port == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL Implícito
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS Explícito (587)
                }
                
                $mail->Port = $port;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                $fromName = getEnvVar('MAIL_FROM_NAME', 'Soporte DRoca');
                $mail->setFrom(getEnvVar('MAIL_FROM'), $fromName);
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = "Recuperar Contraseña";
                $mail->Body = "Haga clic aquí para recuperar su contraseña: <br> <a href='$link'>$link</a>";

                $mail->send();
                echo "<script nonce='{$CSP_NONCE}'>alert('✅ Correo enviado. Revisa tu bandeja de entrada o spam.'); window.location='login.php';</script>";
            
            } catch (Exception $e) {
                error_log("MAILER ERROR FATAL: " . $mail->ErrorInfo);
                echo "<script nonce='{$CSP_NONCE}'>alert('❌ Error de envío. Revisa los logs del servidor.');</script>";
            }
        } else {
            echo "<script nonce='{$CSP_NONCE}'>alert('⚠️ Correo no registrado.');</script>";
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
        /* Tu CSS aquí */
        body { font-family: sans-serif; display: flex; justify-content: center; height: 100vh; align-items: center; background: #eee; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { margin: 10px 0; padding: 10px; width: 100%; box-sizing: border-box; }
        button { padding: 10px; background: blue; color: white; border: none; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <form action="recover.php" method="POST">
        <?php echo csrf_field(); ?>
        <h2>Recuperar Contraseña</h2>
        <input type="email" name="correo" placeholder="Tu correo" required>
        <button type="submit">Enviar</button>
        <br><br>
        <a href="login.php">Volver</a>
    </form>
</body>
</html>