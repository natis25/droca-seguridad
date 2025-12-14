<?php
include('sql.php');
require_once 'ApplicationLogger.php';
require_once __DIR__ . '/csrf_helpers.php';

$conn = Conectarse();

if (!$conn) {
    die("Error de conexión a la base de datos");
}

$appLogger = new ApplicationLogger($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar token CSRF
    csrf_validate_or_die('../inmuebles.php', 'Token de seguridad inválido.');

    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';

    $fechaVisita = $_POST['fecha'] ?? '';
    $horaInicio = $_POST['hora_inicio'] ?? '';
    $idVivienda = $_POST['idVivienda'] ?? 0;

    $horaFin = date("H:i", strtotime($horaInicio . " +1 hour"));

    try {
        $idCliente = insertarCliente($nombre, $telefono, $correo);

        if ($idCliente) {
            insertarCita($fechaVisita, $horaInicio, $horaFin, $idVivienda, $idCliente, 1);

            // Obtener el ID de la cita recién creada
            $idCita = $conn->insert_id;

            // 🆕 LOG: Cita agendada
            $appLogger->logCrearCita($idCita, $idVivienda, $nombre, $fechaVisita, $horaInicio);

        } else {
            throw new Exception("Error al registrar el cliente");
        }

    } catch (Exception $e) {
        // LOG: Error al crear cita
        $appLogger->logError('citas', $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
header("Location: ../inmuebles.php");
exit();
?>