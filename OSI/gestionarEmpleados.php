<?php
session_start();
require_once __DIR__ . '/../Logica/sql.php';
require_once __DIR__ . '/../Logica/helpers.php';

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
    header('Location: ../panelControl.php');
    exit();
}

// Obtener lista de trabajadores con JOIN para Posición y Rol
$sql = "
    SELECT t.idTrabajador, t.Nombre, t.Apellido, t.Usuario, t.Telefono, t.Correo, t.EstadoCuenta,
           c.NombreCargo AS Posicion, r.NombreRol AS Rol
    FROM trabajador t 
    LEFT JOIN cargo c ON t.idCargo = c.idCargo 
    LEFT JOIN rol r ON t.idRol = r.idRol 
    WHERE t.is_deleted = 0
";
$result = mysqli_query($conn, $sql);
$trabajadores = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Trabajadores</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style nonce="<?= $CSP_NONCE ?>">
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding-top: 120px;
            min-height: 100vh;
        }

        .table-container {
            margin: 30px auto;
            max-width: 1400px;
            padding: 0 20px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header-info::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .header-info h3 {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header-info p {
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .new-property-btn {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #0d7a6f 0%, #2dd368 100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #63408f 100%);
        }

        .btn-osi {
            background: linear-gradient(135deg, #fd7e14 0%, #ff6b35 100%);
            color: white;
        }

        .btn-osi:hover {
            background: linear-gradient(135deg, #e96b00 0%, #e65a25 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d77fe6 0%, #e04456 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #e85d87 0%, #ecd32a 100%);
            color: white;
        }

        h3.text-center {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-responsive {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 18px 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border: none;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            box-shadow: 0 4px 10px rgba(17, 153, 142, 0.3);
        }

        .badge-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            box-shadow: 0 4px 10px rgba(250, 112, 154, 0.3);
        }

        .btn-group-sm .btn {
            padding: 8px 16px;
            font-size: 13px;
            margin: 0 3px;
        }

        code {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            padding: 4px 10px;
            border-radius: 6px;
            color: #667eea;
            font-weight: 500;
        }

        /* Modal enhancements */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
        }

        .modal-footer {
            border: none;
            padding: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-info h3 {
                font-size: 22px;
            }

            .new-property-btn {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .table-responsive {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    <?php include('../header.php'); ?>

    <div class="table-container">
        <div class="header-info">
            <h3>🛡️ Gestión de Empleados - Panel OSI</h3>
            <p class="mb-0">Acceso exclusivo para administración de trabajadores</p>
        </div>

        <!-- Mostrar mensajes flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_success']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_error']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center new-property-btn">
            <div>
                <a href="registrarEmpleado.php" class="btn btn-success">➕ Agregar Trabajador</a>
                <a href="gestionarRolesyPermisos.php" class="btn btn-osi">🔐 Gestionar Roles</a>
            </div>
            <a href="panelControlOSI.php" class="btn btn-primary">📊 Volver al Panel</a>
        </div>

        <h3 class="text-center mb-4">Lista de Trabajadores</h3>

        <?php if (empty($trabajadores)): ?>
            <div class="alert alert-info text-center">
                No hay trabajadores registrados en el sistema.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Usuario</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Posición</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajadores as $trabajador):
                            $estado_badge = ($trabajador['EstadoCuenta'] === 'Activo') ?
                                '<span class="badge badge-success">Activo</span>' :
                                '<span class="badge badge-danger">Bloqueado</span>';
                            $id_formatted = 'EMP' . str_pad($trabajador['idTrabajador'], 3, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td><strong><?php echo $id_formatted; ?></strong></td>
                                <td><?php echo htmlspecialchars($trabajador['Nombre'] . ' ' . $trabajador['Apellido']); ?></td>
                                <td><code><?php echo htmlspecialchars($trabajador['Usuario']); ?></code></td>
                                <td><?php echo htmlspecialchars($trabajador['Telefono']); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Correo']); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Posicion'] ?? 'No asignado'); ?></td>
                                <td><?php echo htmlspecialchars($trabajador['Rol'] ?? 'No asignado'); ?></td>
                                <td><?php echo $estado_badge; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href='modificarEmpleado.php?id=<?php echo $trabajador['idTrabajador']; ?>'
                                            class='btn btn-warning' title='Modificar'>
                                            ✏️ Editar
                                        </a>
                                        <?php if ($trabajador['EstadoCuenta'] === 'Activo'): ?>
                                            <button class='btn btn-danger'
                                                onclick='bloquearTrabajador(<?php echo $trabajador['idTrabajador']; ?>)'
                                                title='Bloquear'>
                                                🔒 Bloquear
                                            </button>
                                        <?php else: ?>
                                            <button class='btn btn-success'
                                                onclick='activarTrabajador(<?php echo $trabajador['idTrabajador']; ?>)'
                                                title='Activar'>
                                                🔓 Activar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para confirmar acciones -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- El contenido se llena con JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let trabajadorId = null;
        let accionActual = '';

        function bloquearTrabajador(id) {
            trabajadorId = id;
            accionActual = 'bloquear';
            document.getElementById('modalBody').innerHTML =
                '¿Estás seguro de que deseas <strong>bloquear</strong> este trabajador?<br><br>' +
                'El trabajador no podrá iniciar sesión hasta que sea activado nuevamente.';
            document.getElementById('confirmAction').className = 'btn btn-danger';
            document.getElementById('confirmAction').textContent = 'Sí, Bloquear';
            $('#confirmModal').modal('show');
        }

        function activarTrabajador(id) {
            trabajadorId = id;
            accionActual = 'activar';
            document.getElementById('modalBody').innerHTML =
                '¿Estás seguro de que deseas <strong>activar</strong> este trabajador?<br><br>' +
                'El trabajador podrá iniciar sesión nuevamente.';
            document.getElementById('confirmAction').className = 'btn btn-success';
            document.getElementById('confirmAction').textContent = 'Sí, Activar';
            $('#confirmModal').modal('show');
        }

        document.getElementById('confirmAction').addEventListener('click', function () {
            if (trabajadorId && accionActual) {
                window.location.href = `../Logica/${accionActual}Trabajador.php?id=${trabajadorId}`;
            }
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>