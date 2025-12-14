<?php
session_start();

// 🆕 Configurar zona horaria de Bolivia
date_default_timezone_set('America/La_Paz');

require_once '../Logica/sql.php';
require_once '../Logica/helpers.php';

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
    header('Location: panelControl.php');
    exit();
}

// Obtener filtros
$tipo_log = isset($_GET['tipo']) ? $_GET['tipo'] : 'seguridad';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-d', strtotime('-7 days'));
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');
$evento_filtro = isset($_GET['evento']) ? $_GET['evento'] : '';
$nivel_filtro = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$usuario_filtro = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// Construir consulta según el tipo de log
$tabla = ($tipo_log === 'aplicacion') ? 'log_aplicacion' : 'log_seguridad';

$sql = "SELECT * FROM {$tabla} WHERE fecha_hora BETWEEN ? AND ?";
$params = [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'];
$types = 'ss';

if ($evento_filtro) {
    $sql .= " AND evento = ?";
    $params[] = $evento_filtro;
    $types .= 's';
}

if ($nivel_filtro) {
    $sql .= " AND nivel_log = ?";
    $params[] = $nivel_filtro;
    $types .= 's';
}

if ($usuario_filtro) {
    $sql .= " AND usuario LIKE ?";
    $params[] = '%' . $usuario_filtro . '%';
    $types .= 's';
}

$sql .= " ORDER BY fecha_hora DESC LIMIT 500";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener eventos únicos para el filtro
$sql_eventos = "SELECT DISTINCT evento FROM {$tabla} ORDER BY evento";
$result_eventos = $conn->query($sql_eventos);
$eventos = $result_eventos->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Logs - OSI</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .header h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .filters-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .log-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }
        .badge-INFO { 
            background-color: #17a2b8; 
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .badge-WARNING { 
            background-color: #ffc107; 
            color: #000;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .badge-ERROR { 
            background-color: #dc3545; 
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .badge-CRITICAL { 
            background-color: #6f42c1; 
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .json-details {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .stats-card h3 {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stats-card p {
            color: #666;
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stats-card i {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.7;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-view-detail {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-view-detail:hover {
            background: #138496;
            transform: scale(1.05);
        }
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .filter-section h5 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .export-section {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .timeline-item {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '●';
            position: absolute;
            left: -8px;
            top: 0;
            color: #667eea;
            font-size: 20px;
        }
        table.dataTable tbody tr:hover {
            background-color: #f0f0f0;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.active {
            display: flex;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container-fluid mt-4">
        <div class="header">
            <h2><i class="fas fa-shield-alt"></i> Visualización de Logs del Sistema</h2>
            <p class="mb-0">Panel de Monitoreo OSI - DRoca Inmobiliaria</p>
            <small>Usuario: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'OSI'); ?></strong> | 
                   Fecha: <strong><?php echo date('d/m/Y H:i'); ?></strong></small>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-list-alt" style="color: #667eea;"></i>
                    <p>Total de Logs</p>
                    <h3 style="color: #667eea;"><?php echo count($logs); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                    <p>Logs Críticos</p>
                    <h3 style="color: #dc3545;">
                        <?php echo count(array_filter($logs, function($log) { return $log['nivel_log'] === 'CRITICAL'; })); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                    <p>Advertencias</p>
                    <h3 style="color: #ffc107;">
                        <?php echo count(array_filter($logs, function($log) { return $log['nivel_log'] === 'WARNING'; })); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-info-circle" style="color: #17a2b8;"></i>
                    <p>Informativos</p>
                    <h3 style="color: #17a2b8;">
                        <?php echo count(array_filter($logs, function($log) { return $log['nivel_log'] === 'INFO'; })); ?>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-card">
            <div class="filter-section">
                <h5><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
                <form method="GET" action="" id="filterForm">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="tipo">Tipo de Log:</label>
                            <select name="tipo" id="tipo" class="form-control" onchange="this.form.submit()">
                                <option value="seguridad" <?php echo $tipo_log === 'seguridad' ? 'selected' : ''; ?>>🔒 Seguridad</option>
                                <option value="aplicacion" <?php echo $tipo_log === 'aplicacion' ? 'selected' : ''; ?>>⚙️ Aplicación</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="fecha_desde">Desde:</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?php echo $fecha_desde; ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="fecha_hasta">Hasta:</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?php echo $fecha_hasta; ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="nivel">Nivel:</label>
                            <select name="nivel" id="nivel" class="form-control">
                                <option value="">Todos</option>
                                <option value="INFO" <?php echo $nivel_filtro === 'INFO' ? 'selected' : ''; ?>>ℹ️ INFO</option>
                                <option value="WARNING" <?php echo $nivel_filtro === 'WARNING' ? 'selected' : ''; ?>>⚠️ WARNING</option>
                                <option value="ERROR" <?php echo $nivel_filtro === 'ERROR' ? 'selected' : ''; ?>>❌ ERROR</option>
                                <option value="CRITICAL" <?php echo $nivel_filtro === 'CRITICAL' ? 'selected' : ''; ?>>🔥 CRITICAL</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="evento">Evento:</label>
                            <select name="evento" id="evento" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach ($eventos as $evt): ?>
                                    <option value="<?php echo htmlspecialchars($evt['evento']); ?>" 
                                            <?php echo $evento_filtro === $evt['evento'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($evt['evento']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="usuario">Usuario:</label>
                            <input type="text" name="usuario" id="usuario" class="form-control" 
                                   placeholder="Buscar..." value="<?php echo htmlspecialchars($usuario_filtro); ?>">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="verLogs.php?tipo=<?php echo $tipo_log; ?>" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </a>
                            <button type="button" class="btn btn-info" onclick="refreshLogs()">
                                <i class="fas fa-sync-alt"></i> Refrescar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Logs -->
        <div class="log-table">
            <div class="d-flex justify-content-between mb-3">
                <h4><i class="fas fa-table"></i> Logs de <?php echo ucfirst($tipo_log); ?></h4>
                <div class="export-section">
                    <a href="exportarLogs.php?tipo=<?php echo $tipo_log; ?>&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>&nivel=<?php echo $nivel_filtro; ?>&evento=<?php echo $evento_filtro; ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="exportarLogsPDF.php?tipo=<?php echo $tipo_log; ?>&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>" 
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a href="panelControlOSI.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </div>

            <?php if (count($logs) === 0): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>No se encontraron logs con los filtros aplicados</h5>
                    <p>Intenta cambiar los criterios de búsqueda</p>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table id="logsTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 12%;">Fecha/Hora</th>
                            <th style="width: 15%;">Evento</th>
                            <th style="width: 10%;">Nivel</th>
                            <th style="width: 12%;">Usuario</th>
                            <th style="width: 12%;">IP Address</th>
                            <th style="width: 24%;">Descripción</th>
                            <th style="width: 10%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-center"><strong>#<?php echo $log['id']; ?></strong></td>
                                <td>
                                    <small>
                                        <i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($log['fecha_hora'])); ?><br>
                                        <i class="far fa-clock"></i> <?php echo date('H:i:s', strtotime($log['fecha_hora'])); ?>
                                    </small>
                                </td>
                                <td><strong><?php echo htmlspecialchars($log['evento']); ?></strong></td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $log['nivel_log']; ?>">
                                        <?php 
                                        $iconos = [
                                            'INFO' => 'ℹ️',
                                            'WARNING' => '⚠️',
                                            'ERROR' => '❌',
                                            'CRITICAL' => '🔥'
                                        ];
                                        echo ($iconos[$log['nivel_log']] ?? '') . ' ' . $log['nivel_log']; 
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['usuario'] ?? 'N/A'); ?></td>
                                <td><code><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></code></td>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($log['descripcion'] ?? '', 0, 60)); ?>
                                    <?php if (strlen($log['descripcion'] ?? '') > 60) echo '...'; ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-view-detail" onclick="verDetalle(<?php echo $log['id']; ?>)" 
                                                title="Ver detalles completos">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt"></i> Detalles del Log</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#logsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
                    "decimal": ",",
                    "thousands": "."
                },
                "order": [[1, "desc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                "responsive": true,
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                "columnDefs": [
                    { "orderable": false, "targets": 7 }
                ]
            });
        });

        function verDetalle(id) {
            $('#loadingOverlay').addClass('active');
            $('#modalBody').html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>');
            
            $.ajax({
                url: 'obtenerDetalleLog.php',
                method: 'GET',
                data: { 
                    id: id, 
                    tipo: '<?php echo $tipo_log; ?>' 
                },
                success: function(response) {
                    $('#modalBody').html(response);
                    $('#detalleModal').modal('show');
                    $('#loadingOverlay').removeClass('active');
                },
                error: function(xhr, status, error) {
                    $('#loadingOverlay').removeClass('active');
                    alert('Error al cargar los detalles del log: ' + error);
                }
            });
        }

        function refreshLogs() {
            $('#loadingOverlay').addClass('active');
            location.reload();
        }

        // Auto-refresh cada 30 segundos (opcional)
        // setInterval(refreshLogs, 30000);
    </script>
</body>
</html>