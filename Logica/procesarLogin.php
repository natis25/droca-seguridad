<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Nota el "/../" para salir de la carpeta Logica
use Dotenv\Dotenv;

// Carga segura de .env (si existe en local)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

include('sql.php');
include('CAPTCHA.php');
require_once '../SecurityLogger.php';
require_once __DIR__ . '/csrf_helpers.php';

session_start();

$conn = Conectarse();

if (!$conn) {
    $_SESSION['error'] = "Error interno: Fallo al Conectar con la Base de Datos.";
    header("Location: ../login.php");
    exit();
}

// Crear instancia del logger de seguridad
$secLogger = new SecurityLogger($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conn->close();
    header("Location: ../login.php");
    exit();
}

// Validar token CSRF
csrf_validate_or_die('../login.php', 'Token de seguridad inválido. Por favor, intenta nuevamente.');

$usuario = trim($_POST["usuario"]);
$password = trim($_POST["password"]);

if (empty($usuario) || empty($password)) {
    $_SESSION['error'] = "Debes ingresar tu Usuario y Contraseña.";
    $conn->close();
    header("Location: ../login.php");
    exit();
}

$user_found = null;
$user_type = null;

if (empty($captcha_result["success"]) || !$captcha_result["success"]) {
    echo "<script>alert('⚠️ Verificación CAPTCHA fallida');</script>";
    var_dump($captcha_result);
} else if ($captcha_result && isset($captcha_result['success']) && $captcha_result['success'] === true) {

    // Buscar en trabajador
    $sql_trabajador = "
        SELECT t.idTrabajador AS id, t.Nombre, t.Apellido, t.Usuario, ph.PasswordHash, t.idRol, t.EstadoCuenta, t.IntentosFallidos
        FROM trabajador t
        LEFT JOIN password_history ph ON ph.user_type = 'trabajador' AND ph.user_id = t.idTrabajador
        WHERE t.Usuario = ? AND t.is_deleted = 0
        ORDER BY ph.FechaCambio DESC LIMIT 1
    ";
    $stmt_trabajador = $conn->prepare($sql_trabajador);

    if ($stmt_trabajador) {
        $stmt_trabajador->bind_param("s", $usuario);
        $stmt_trabajador->execute();
        $result_trabajador = $stmt_trabajador->get_result();

        if ($result_trabajador->num_rows > 0) {
            $user_found = $result_trabajador->fetch_assoc();
            $user_type = 'trabajador';
        }
        $stmt_trabajador->close();
    } else {
        error_log("Error en prepare trabajador: " . $conn->error);
        $_SESSION['error'] = "Error interno en la consulta.";
        $conn->close();
        header("Location: ../login.php");
        exit();
    }

    // Buscar en cliente si no se encontró en trabajador
    if (!$user_found) {
        $sql_cliente = "
            SELECT c.idCliente AS id, c.Nombre, c.Apellido, c.Usuario, ph.PasswordHash, c.EstadoCuenta, c.IntentosFallidos
            FROM cliente c
            LEFT JOIN password_history ph ON ph.user_type = 'cliente' AND ph.user_id = c.idCliente
            WHERE c.Usuario = ? AND c.is_deleted = 0
            ORDER BY ph.FechaCambio DESC LIMIT 1
        ";
        $stmt_cliente = $conn->prepare($sql_cliente);

        if ($stmt_cliente) {
            $stmt_cliente->bind_param("s", $usuario);
            $stmt_cliente->execute();
            $result_cliente = $stmt_cliente->get_result();

            if ($result_cliente->num_rows > 0) {
                $user_found = $result_cliente->fetch_assoc();
                $user_type = 'cliente';
            }
            $stmt_cliente->close();
        } else {
            error_log("Error en prepare cliente: " . $conn->error);
            $_SESSION['error'] = "Error interno en la consulta.";
            $conn->close();
            header("Location: ../login.php");
            exit();
        }
    }

    if ($user_found) {
        // Verificar si la cuenta está bloqueada
        if ($user_found['EstadoCuenta'] === 'Bloqueado') {
            // LOG: Intento de acceso con cuenta bloqueada
            $secLogger->logIntentoAccesoBloqueado($usuario);

            $_SESSION['error'] = "Tu cuenta está bloqueada. Contacta al administrador.";
            $conn->close();
            header("Location: ../login.php");
            exit();
        }

        // Verificar contraseña
        if (password_verify($password, $user_found['PasswordHash'])) {
            // ✅ LOGIN EXITOSO
            $_SESSION['user_id'] = $user_found['id'];
            $_SESSION['username'] = $user_found['Usuario'];
            $_SESSION['nombre_completo'] = $user_found['Nombre'] . ' ' . $user_found['Apellido'];
            $_SESSION['user_type'] = $user_type;

            // LOG: Login exitoso
            $secLogger->logLoginExitoso($usuario, $user_type);

            // Resetear intentos fallidos
            $update_login = "UPDATE {$user_type} SET last_login_at = NOW(), IntentosFallidos = 0 WHERE id" . ucfirst($user_type) . " = ?";
            if ($stmt_update = $conn->prepare($update_login)) {
                $stmt_update->bind_param("i", $user_found['id']);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $conn->close();
            header("Location: ../OSI/panelControlOSI.php");
            exit();

        } else {
            // ❌ LOGIN FALLIDO
            $intentos = $user_found['IntentosFallidos'] + 1;

            // LOG: Login fallido
            $secLogger->logLoginFallido($usuario, $intentos);

            // Actualizar intentos fallidos
            $update_intentos = "UPDATE {$user_type} SET IntentosFallidos = IntentosFallidos + 1 WHERE id" . ucfirst($user_type) . " = ?";
            if ($stmt_update = $conn->prepare($update_intentos)) {
                $stmt_update->bind_param("i", $user_found['id']);
                $stmt_update->execute();
                $stmt_update->close();
            }

            // Bloquear si alcanzó 3 intentos
            if ($intentos >= 3) {
                $update_bloqueo = "UPDATE {$user_type} SET EstadoCuenta = 'Bloqueado', locked_at = NOW() WHERE id" . ucfirst($user_type) . " = ?";
                if ($stmt_bloqueo = $conn->prepare($update_bloqueo)) {
                    $stmt_bloqueo->bind_param("i", $user_found['id']);
                    $stmt_bloqueo->execute();
                    $stmt_bloqueo->close();
                }

                // LOG: Cuenta bloqueada
                $secLogger->logCuentaBloqueada($usuario);

                $_SESSION['error'] = "Tu cuenta ha sido bloqueada por múltiples intentos fallidos. Contacta al administrador.";
            } else {
                $_SESSION['error'] = "❌ Datos ingresados incorrectos. Intentos restantes: " . (3 - $intentos);
            }

            $conn->close();
            header("Location: ../login.php");
            exit();
        }
    } else {
        // Usuario no encontrado
        // LOG: Login fallido (usuario no existe)
        $secLogger->logLoginFallido($usuario, 1);

        $_SESSION['error'] = "❌ El Usuario no está registrado.";
        $conn->close();
        header("Location: ../login.php");
        exit();
    }
}
?>