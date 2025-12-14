<?php
include('sql.php');
require_once 'ApplicationLogger.php';

$conn = Conectarse();

if (!$conn) {
    die("Error de conexión a la base de datos");
}

$appLogger = new ApplicationLogger($conn);

$direccion = $_POST['direccion'] ?? '';
$montoPedido = $_POST['monto'] ?? 0;
$zona = $_POST['zona'] ?? 0;
$tipoVivienda = $_POST['tipo'] ?? 0;
$tipoOferta = $_POST['operacion'] ?? 0;

try {
    insertarVivienda($direccion, $montoPedido, $zona, $tipoVivienda, $tipoOferta);
    
    // Obtener el ID de la vivienda recién creada
    $idVivienda = $conn->insert_id;
    
    // 🆕 LOG: Propiedad creada
    $appLogger->logCrearPropiedad($idVivienda, $direccion, $montoPedido, $zona, $tipoVivienda, $tipoOferta);
    
} catch (Exception $e) {
    // LOG: Error al crear propiedad
    $appLogger->logError('propiedades', $e->getMessage());
}

$conn->close();
header("Location: ../gestionarInmuebles.php");
exit();
?>