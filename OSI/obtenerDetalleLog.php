<?php
session_start();

// 🆕 Configurar zona horaria
date_default_timezone_set('America/La_Paz');

require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
    http_response_code(403);
    echo "Acceso denegado";
    exit();
}

$conn = Conectarse();
if (!$conn) {
    http_response_code(500);
    echo "Error de conexión";
    exit();
}

// Verificar rol OSI
if (!esUsuarioOSI($conn, $_SESSION['user_id'])) {
    http_response_code(403);
    echo "Sin permisos";
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'seguridad';

if ($id <= 0) {
    echo "ID inválido";
    exit();
}

$tabla = ($tipo === 'aplicacion') ? 'log_aplicacion' : 'log_seguridad';

$sql = "SELECT * FROM {$tabla} WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Log no encontrado";
    exit();
}

$log = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<div class="container-fluid">
    <table class="table table-bordered">
        <tr>
            <th style="width: 30%;">ID</th>
            <td><?php echo $log['id']; ?></td>
        </tr>
        <tr>
            <th>Fecha/Hora</th>
            <td><?php echo date('d/m/Y H:i:s', strtotime($log['fecha_hora'])); ?></td>
        </tr>
        <tr>
            <th>Evento</th>
            <td><strong><?php echo htmlspecialchars($log['evento']); ?></strong></td>
        </tr>
        <tr>
            <th>Nivel</th>
            <td>
                <span class="badge badge-<?php echo $log['nivel_log']; ?>">
                    <?php echo $log['nivel_log']; ?>
                </span>
            </td>
        </tr>
        <tr>
            <th>Usuario</th>
            <td><?php echo htmlspecialchars($log['usuario'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <th>User Agent</th>
            <td><small><?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?></small></td>
        </tr>
        <tr>
            <th>Descripción</th>
            <td><?php echo nl2br(htmlspecialchars($log['descripcion'] ?? '')); ?></td>
        </tr>
        <?php if (!empty($log['detalles_json'])): ?>
        <tr>
            <th>Detalles JSON</th>
            <td>
                <div class="json-details">
                    <pre><?php echo json_encode(json_decode($log['detalles_json']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                </div>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>