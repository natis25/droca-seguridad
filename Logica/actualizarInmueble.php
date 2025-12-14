<?php
require_once 'sql.php';
require_once 'ApplicationLogger.php';
require_once __DIR__ . '/csrf_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    csrf_validate_or_die('../gestionarInmuebles.php', 'Token de seguridad inválido.');

    $conn = Conectarse();

    if (!$conn) {
        die("Error de conexión a la base de datos");
    }

    $appLogger = new ApplicationLogger($conn);

    $idInmueble = $_POST['idInmueble'] ?? 0;
    $direccion = $_POST['direccion'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $zona = $_POST['zona'] ?? 0;
    $operacion = $_POST['operacion'] ?? 0;
    $tipo = $_POST['tipo'] ?? 0;

    try {
        // Obtener valores anteriores para el log
        $inmuebleAnterior = obtenerInmueblePorId($idInmueble);

        actualizarVivienda($idInmueble, $direccion, $monto, $zona, $tipo, $operacion);

        // 🆕 LOG: Propiedad modificada
        $cambios = [];

        if ($inmuebleAnterior['Direccion'] != $direccion) {
            $cambios['direccion'] = ['anterior' => $inmuebleAnterior['Direccion'], 'nuevo' => $direccion];
        }
        if ($inmuebleAnterior['MontoPedido'] != $monto) {
            $cambios['monto'] = ['anterior' => $inmuebleAnterior['MontoPedido'], 'nuevo' => $monto];
        }
        if ($inmuebleAnterior['Zonas_idZona'] != $zona) {
            $cambios['zona'] = ['anterior' => $inmuebleAnterior['Zonas_idZona'], 'nuevo' => $zona];
        }
        if ($inmuebleAnterior['TipoVivienda_idTipoV'] != $tipo) {
            $cambios['tipo_vivienda'] = ['anterior' => $inmuebleAnterior['TipoVivienda_idTipoV'], 'nuevo' => $tipo];
        }
        if ($inmuebleAnterior['TipoOferta_idTipoO'] != $operacion) {
            $cambios['tipo_oferta'] = ['anterior' => $inmuebleAnterior['TipoOferta_idTipoO'], 'nuevo' => $operacion];
        }

        if (!empty($cambios)) {
            $appLogger->logModificarPropiedad($idInmueble, $cambios);
        }

    } catch (Exception $e) {
        // LOG: Error al modificar propiedad
        $appLogger->logError('propiedades', $e->getMessage());
    }

    $conn->close();
    header("Location: ../gestionarInmuebles.php");
    exit();
}

function actualizarVivienda($idInmueble, $direccion, $monto, $zona, $tipoVivienda, $tipoOferta)
{
    $conexion = Conectarse();
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }

    $sql = "UPDATE Vivienda 
            SET Direccion = ?, MontoPedido = ?, Zonas_idZona = ?, TipoVivienda_idTipoV = ?, TipoOferta_idTipoO = ?
            WHERE idVivienda = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdiiii", $direccion, $monto, $zona, $tipoVivienda, $tipoOferta, $idInmueble);

    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el inmueble: " . $stmt->error);
    }

    $stmt->close();
    mysqli_close($conexion);
}
?>