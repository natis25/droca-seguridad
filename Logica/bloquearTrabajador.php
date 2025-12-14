<?php
session_start();
require_once 'sql.php';
require_once 'helpers.php';
require_once 'SecurityLogger.php';

// Verificar si el usuario está logueado y es OSI
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión");
}

// Verificar si el usuario tiene rol OSI
if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
    $conn->close();
    header('Location: ../panelControl.php');
    exit();
}

$secLogger = new SecurityLogger($conn);

// Obtener ID del trabajador
$idTrabajador = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idTrabajador > 0) {
    // Obtener el nombre del trabajador antes de bloquear
    $stmt_get = $conn->prepare("SELECT Usuario FROM trabajador WHERE idTrabajador = ?");
    $stmt_get->bind_param("i", $idTrabajador);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows > 0) {
        $trabajador = $result->fetch_assoc();
        $stmt_get->close();
        
        // Bloquear el trabajador
        $stmt = $conn->prepare("UPDATE trabajador SET EstadoCuenta = 'Bloqueado', locked_at = NOW() WHERE idTrabajador = ?");
        $stmt->bind_param("i", $idTrabajador);
        
        if ($stmt->execute()) {
            // 🆕 LOG: Cuenta bloqueada manualmente por administrador
            $secLogger->logCuentaBloqueada($trabajador['Usuario'], 'bloqueado_por_administrador');
            
            $_SESSION['mensaje'] = "✅ Trabajador bloqueado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al bloquear el trabajador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        $stmt->close();
    } else {
        $stmt_get->close();
        $_SESSION['mensaje'] = "❌ Trabajador no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "❌ ID de trabajador no válido";
    $_SESSION['tipo_mensaje'] = "danger";
}

$conn->close();
header("Location: ../OSI/gestionarEmpleados.php");
exit();
?>