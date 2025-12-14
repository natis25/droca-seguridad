<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Inmuebles</title>

    <!-- Enlaces a Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .form-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }

        .card {
            width: 100%;
            max-width: 600px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-panel {
            padding: 2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php
    require_once __DIR__ . '/Logica/csrf_helpers.php';
    csrf_generate_token();
    include('header.php');
    ?>

    <div class="form-container">
        <div class="card">
            <div class="card-header text-center">
                <h3>Registro Inmueble</h3>
            </div>
            <div class="form-panel">
                <form method="POST" action="Logica/setVivienda.php" onsubmit="return validarMonto();">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion"
                            placeholder="Ingresa la dirección" required>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto Solicitado</label>
                        <input type="number" class="form-control" id="monto" name="monto"
                            placeholder="Ingresa el monto solicitado" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="zona">Zona</label>
                        <select class="form-control" id="zona" name="zona" required>
                            <option value="">Selecciona una zona</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="operacion">Operación</label>
                        <select class="form-control" id="operacion" name="operacion" required>
                            <option value="">Selecciona una operación</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="">Selecciona un tipo</option>
                        </select>
                    </div>

                    <div class="button-group">
                        <a href="gestionarInmuebles.php" class="btn btn-secondary">Volver a Gestión</a>
                        <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            function cargarOpciones(selectId, endpoint) {
                $.ajax({
                    url: endpoint,
                    type: 'GET',
                    success: function (response) {
                        const opciones = JSON.parse(response);
                        $(selectId).empty().append('<option value=""></option>');
                        opciones.forEach(opcion => {
                            $(selectId).append(new Option(opcion.text, opcion.id));
                        });
                    },
                    error: function () {
                        alert("Error al cargar opciones");
                    }
                });
            }

            cargarOpciones('#zona', 'Logica/getZonas.php');
            cargarOpciones('#operacion', 'Logica/getOperaciones.php');
            cargarOpciones('#tipo', 'Logica/getTipos.php');
        });

        function validarMonto() {
            const monto = parseFloat(document.getElementById('monto').value);
            if (isNaN(monto) || monto < 0) {
                alert("El monto no puede ser negativo.");
                return false;
            }
            return true;
        }
    </script>

</body>

</html>