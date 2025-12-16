<?php
require 'vendor/autoload.php';
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

/* ================== HEADERS DE SEGURIDAD (OWASP ZAP) ================== */
header("X-Frame-Options: DENY"); // Anti-Clickjacking
header("X-Content-Type-Options: nosniff");

header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .                 // Fallback
    "base-uri 'self'; " .
    "object-src 'none'; " .
    "frame-ancestors 'none'; " .
    "script-src 'self'; " .                  // No usas JS externo aquí
    "style-src 'self' 'nonce-{$CSP_NONCE}'; " .   // Usar nonce para estilos inline
    "img-src 'self' data:; " .
    "connect-src 'self'; " .
    "upgrade-insecure-requests"
);
/* ===================================================================== */
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

function getEnvVar($key) {
    return getenv($key) ?: ($_ENV[$key] ?? '');
}

$conn = Conectarse();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar token CSRF
    if (!csrf_validate()) {
        echo "<script>alert('❌ Token de seguridad inválido. Por favor, intenta nuevamente.');</script>";
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

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST']; // Esto tomará 'droca-seguridad...railway.app'
            $link = $protocol . $domainName . "/Logica/resetPassword.php?token=$token";
            // Enlace para restablecer
            //$link = "http://localhost/logica/resetPassword.php?token=$token";

            // Enviar correo
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => true
                    ]
                ];
                $mail->SMTPDebug = 0;
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->Host = getEnvVar('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = getEnvVar('MAIL_USERNAME');
                $mail->Password = getEnvVar('MAIL_PASSWORD');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = getEnvVar('MAIL_PORT');

                $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = "🔐 Recuperación de contraseña - DRoca Inmobiliaria";
                $mail->Body = "
                <h2>Solicitud de recuperación de contraseña</h2>
                <p>Haga clic en el siguiente enlace para restablecer su contraseña:</p>
                <p><a href='$link'>$link</a></p>
                <p>Este enlace expirará en 1 hora.</p>
            ";

                $mail->send();
                echo "<script>alert('✅ Se envió un enlace de recuperación a tu correo.'); window.location='login.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('❌ Error al enviar correo.');</script>";
            }
        } else {
            echo "<script>alert('⚠️ El correo no está registrado.');</script>";
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