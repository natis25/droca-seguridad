<?php
include('sql.php');
require_once 'ApplicationLogger.php';

$conn = Conectarse();

if (!$conn) {
    die("Error de conexión a la base de datos");
}

$appLogger = new ApplicationLogger($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCita = $_POST['idCita'] ?? 0;
    
    try {
        if ($idCita > 0) {
            cancelarCita($idCita);
            
            // 🆕 LOG: Cita cancelada
            $appLogger->logCancelarCita($idCita);
        } else {
            throw new Exception("ID de cita no válido");
        }
        
    } catch (Exception $e) {
        // LOG: Error al cancelar cita
        $appLogger->logError('citas', $e->getMessage());
    }
}

$conn->close();
header("Location: ../Citas.php");
exit();
?>