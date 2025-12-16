<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutos de límite

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

echo "<h1>🛠️ Diagnóstico de Correo</h1>";

// 1. Cargar Variables
echo "<h3>1. Cargando Configuración...</h3>";
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    echo "✅ .env cargado (Entorno Local)<br>";
} else {
    echo "☁️ Usando variables de entorno del sistema (Railway)<br>";
}

function getVar($key) {
    $val = getenv($key) ?: ($_ENV[$key] ?? $_SERVER[$key] ?? '');
    echo "Variable <b>$key</b>: " . ($val ? "✅ Cargada" : "❌ VACÍA") . "<br>";
    return $val;
}

$host = getVar('MAIL_HOST'); // smtp.gmail.com
$user = getVar('MAIL_USERNAME');
$pass = getVar('MAIL_PASSWORD');
$port = getVar('MAIL_PORT');

// Limpiar contraseña de espacios por si acaso
$pass = str_replace(' ', '', $pass);

echo "<hr>";
echo "<h3>2. Iniciando Intento de Envío...</h3>";

$mail = new PHPMailer(true);

try {
    // Debug VERBOSE en pantalla
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION; // Nivel 3: Cliente y Servidor
    $mail->Debugoutput = 'html'; // Formato HTML para leerlo bien

    $mail->isSMTP();
    
    // TRUCO IPV4: Resolvemos la IP antes de conectar
    $ip = gethostbyname($host);
    echo "IP Resuelta para $host: <b>$ip</b><br>";
    $mail->Host = $ip; 

    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $pass;

    // Configuración de Puerto Automática
    if ($port == 465) {
        echo "Modo: <b>SSL (SMTPS)</b> - Puerto 465<br>";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        echo "Modo: <b>TLS (STARTTLS)</b> - Puerto 587/Otro<br>";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Forzamos 587 si no es 465
    }
    $mail->Port = $port;

    // Opciones SSL permisivas para debug
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom($user, 'Test Diagnostico');
    $mail->addAddress($user); // Te lo envías a ti mismo
    
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de Diagnostico Railway';
    $mail->Body    = 'Si lees esto, el sistema funciona.';

    echo "<div style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc; font-family: monospace;'>";
    $mail->send();
    echo "</div>";
    
    echo "<h2 style='color: green'>✅ ÉXITO: El correo se envió correctamente.</h2>";

} catch (Exception $e) {
    echo "</div>"; // Cerrar div de debug si quedó abierto
    echo "<h2 style='color: red'>❌ ERROR FATAL</h2>";
    echo "<b>Mensaje:</b> " . $mail->ErrorInfo . "<br>";
    echo "<p>Revisa el log detallado arriba para ver dónde se cortó la conexión.</p>";
}
?>