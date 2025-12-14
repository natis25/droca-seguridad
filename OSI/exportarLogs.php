<?php
session_start();
require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    header('Location: ../login.php');
    exit();
}

$conn = Conectarse();
if (!$conn) {
    die("Error de conexión");
}

// Verificar rol OSI
if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
    $conn->close();
    header('Location: panelControl.php');
    exit();
}

$tipo_log = isset($_GET['tipo']) ? $_GET['tipo'] : 'seguridad';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-d', strtotime('-7 days'));
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');

$tabla = ($tipo_log === 'aplicacion') ? 'log_aplicacion' : 'log_seguridad';

$sql = "SELECT * FROM {$tabla} 
        WHERE fecha_hora BETWEEN ? AND ? 
        ORDER BY fecha_hora DESC";

$stmt = $conn->prepare($sql);
$fecha_desde_full = $fecha_desde . ' 00:00:00';
$fecha_hasta_full = $fecha_hasta . ' 23:59:59';
$stmt->bind_param("ss", $fecha_desde_full, $fecha_hasta_full);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Generar archivo Excel (CSV)
$filename = "logs_{$tipo_log}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, ['ID', 'Fecha/Hora', 'Evento', 'Nivel', 'Usuario', 'IP', 'Descripción', 'User Agent']);

// Datos
foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['fecha_hora'],
        $log['evento'],
        $log['nivel_log'],
        $log['usuario'] ?? '',
        $log['ip_address'] ?? '',
        $log['descripcion'] ?? '',
        $log['user_agent'] ?? ''
    ]);
}

fclose($output);
exit();