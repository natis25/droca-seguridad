<?php
/**
 * ApplicationLogger - Maneja logs de funcionalidades críticas de la aplicación
 * Sistema: DRoca Inmobiliaria
 * Fecha: 2025
 */

class ApplicationLogger {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Método privado para escribir en la base de datos
     */
    private function writeLog($nivel, $evento, $modulo, $detalles = []) {
        $usuario = isset($_SESSION['username']) ? $_SESSION['username'] : 'SISTEMA';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $detallesJson = json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $stmt = $this->conn->prepare(
            "INSERT INTO log_aplicacion (nivel_log, evento, usuario, ip_address, user_agent, modulo, detalles) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt) {
            $stmt->bind_param("sssssss", $nivel, $evento, $usuario, $ip, $userAgent, $modulo, $detallesJson);
            
            if (!$stmt->execute()) {
                error_log("Error al escribir log de aplicación: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            error_log("Error al preparar statement de log_aplicacion: " . $this->conn->error);
        }
    }
    
    // ==================== LOGS PARA VIVIENDAS/PROPIEDADES ====================
    
    public function logCrearPropiedad($idVivienda, $direccion, $monto, $zona, $tipoVivienda, $tipoOferta) {
        $this->writeLog('INFO', 'PROPIEDAD_CREADA', 'propiedades', [
            'id_vivienda' => $idVivienda,
            'direccion' => $direccion,
            'monto' => $monto,
            'zona' => $zona,
            'tipo_vivienda' => $tipoVivienda,
            'tipo_oferta' => $tipoOferta
        ]);
    }
    
    public function logModificarPropiedad($idVivienda, $cambios = []) {
        $this->writeLog('INFO', 'PROPIEDAD_MODIFICADA', 'propiedades', [
            'id_vivienda' => $idVivienda,
            'cambios' => $cambios
        ]);
    }
    
    public function logEliminarPropiedad($idVivienda) {
        $this->writeLog('WARNING', 'PROPIEDAD_ELIMINADA', 'propiedades', [
            'id_vivienda' => $idVivienda
        ]);
    }
    
    // ==================== LOGS PARA CITAS ====================
    
    public function logCrearCita($idCita, $idVivienda, $nombreCliente, $fecha, $horaInicio) {
        $this->writeLog('INFO', 'CITA_AGENDADA', 'citas', [
            'id_cita' => $idCita,
            'id_vivienda' => $idVivienda,
            'cliente' => $nombreCliente,
            'fecha_cita' => $fecha,
            'hora_inicio' => $horaInicio
        ]);
    }
    
    public function logCancelarCita($idCita) {
        $this->writeLog('INFO', 'CITA_CANCELADA', 'citas', [
            'id_cita' => $idCita
        ]);
    }
    
    // ==================== LOGS DE ERRORES ====================
    
    public function logError($modulo, $error) {
        $this->writeLog('ERROR', 'ERROR_APLICACION', $modulo, [
            'error' => $error
        ]);
    }
    
    public function logErrorCritico($modulo, $error) {
        $this->writeLog('CRITICAL', 'ERROR_CRITICO', $modulo, [
            'error' => $error
        ]);
    }
}
?>