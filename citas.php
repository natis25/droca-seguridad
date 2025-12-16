<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Citas</title>

    <!-- Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
        }

        .citas-container {
            margin: 20px;
        }
    </style>
</head>

<body>

    <?php
    require_once __DIR__ . '/Logica/csrf_helpers.php';
    csrf_generate_token();
    include('header.php');
    ?>

    <div class="container">
        <h1>Buscar Citas por Teléfono</h1>
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" required>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <div class="citas-container">
            <?php
            include('Logica/sql.php');

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Validar token CSRF
                if (!csrf_validate()) {
                    echo "<div class='alert alert-danger'>Token de seguridad inválido. Por favor, intenta nuevamente.</div>";
                } else {
                    $telefono = $_POST['telefono'];
                    $citas = obtenerCitasPorTelefono($telefono);

                    if (!empty($citas)) {
                        echo "<h2>Citas Disponibles</h2>";
                        echo "<table class='table table-bordered'>";
                        echo "<thead><tr><th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th><th>Estado</th><th>Tipo Vivienda</th><th>Zona</th><th>Monto</th><th>Tipo Oferta</th><th>Acciones</th></tr></thead>";
                        echo "<tbody>";

                        foreach ($citas as $cita) {
                            echo "<tr>";
                            echo "<td>{$cita['FechaVisita']}</td>";
                            echo "<td>{$cita['HoraInicio']}</td>";
                            echo "<td>{$cita['HoraFin']}</td>";
                            echo "<td>{$cita['Estado']}</td>";
                            echo "<td>{$cita['TipoVivienda']}</td>";
                            echo "<td>{$cita['Zona']}</td>";
                            echo "<td>\${$cita['Monto']}</td>";
                            echo "<td>{$cita['TipoOferta']}</td>";
                            $csrf_token = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
                            echo "<td><form method='POST' action='Logica/updateCita.php'><input type='hidden' name='csrf' value='{$csrf_token}'><input type='hidden' name='idCita' value='{$cita['idCita']}'><button type='submit' class='btn btn-danger'>Cancelar</button></form></td>";
                            echo "</tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No se encontraron citas para el teléfono proporcionado.</p>";
                    }
                }
            }
            ?>
        </div>
    </div>

</body>

</html>