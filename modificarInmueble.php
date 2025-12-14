<?php
require_once 'Logica/sql.php';
require_once __DIR__ . '/Logica/csrf_helpers.php';

csrf_generate_token();

if (isset($_GET['id'])) {
    $idInmueble = $_GET['id'];
    $inmueble = obtenerInmueblePorId($idInmueble);

    if ($inmueble) {
        $direccion = $inmueble['Direccion'];
        $monto = $inmueble['MontoPedido'];
        $zona = $inmueble['Zonas_idZona'];
        $operacion = $inmueble['TipoOferta_idTipoO'];
        $tipo = $inmueble['TipoVivienda_idTipoV'];
    } else {
        echo "Inmueble no encontrado";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Inmueble</title>
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

    <?php include('header.php'); ?>

    <div class="form-container">
        <div class="card">
            <div class="card-header text-center">
                <h3>Modificar Inmueble</h3>
            </div>
            <div class="form-panel">
                <form method="POST" action="Logica/actualizarInmueble.php" onsubmit="return validarMonto();">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="idInmueble" value="<?php echo $idInmueble; ?>">

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" 
                               value="<?php echo $direccion; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto Solicitado</label>
                        <input type="number" class="form-control" id="monto" name="monto" 
                               value="<?php echo $monto; ?>" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="zona">Zona</label>
                        <select class="form-control select2" id="zona" name="zona" required>
                            <option value="">Selecciona una zona</option>
                            <?php
                            $zonas = obtenerZonas();
                            foreach ($zonas as $z) {
                                $selected = ($zona == $z['idZona']) ? 'selected' : '';
                                echo "<option value='" . $z['idZona'] . "' $selected>" . $z['Zona'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="operacion">Operación</label>
                        <select class="form-control select2-no-search" id="operacion" name="operacion" required>
                            <option value="">Selecciona una operación</option>
                            <?php
                            $operaciones = obtenerTiposOferta();
                            foreach ($operaciones as $op) {
                                $selected = ($operacion == $op['idTipoO']) ? 'selected' : '';
                                echo "<option value='" . $op['idTipoO'] . "' $selected>" . $op['Oferta'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select class="form-control select2-no-search" id="tipo" name="tipo" required>
                            <option value="">Selecciona un tipo</option>
                            <?php
                            $tipos = obtenerTiposVivienda();
                            foreach ($tipos as $tp) {
                                $selected = ($tipo == $tp['idTipoV']) ? 'selected' : '';
                                echo "<option value='" . $tp['idTipoV'] . "' $selected>" . $tp['Vivienda'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="button-group">
                        <a href="gestionarInmuebles.php" class="btn btn-secondary">Volver</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
