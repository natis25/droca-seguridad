<?php
/**
 * SecurityLogger - Maneja logs de seguridad y eventos de usuarios
 * Sistema: DRoca Inmobiliaria
 * Fecha: 2025
 */

class SecurityLogger {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Método privado para escribir en la base de datos
     */
    private function writeLog($nivel, $evento, $usuario, $detalles = []) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $detallesJson = json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $stmt = $this->conn->prepare(
            "INSERT INTO log_seguridad (nivel_log, evento, usuario, ip_address, user_agent, detalles) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt) {
            $stmt->bind_param("ssssss", $nivel, $evento, $usuario, $ip, $userAgent, $detallesJson);
            
            if (!$stmt->execute()) {
                error_log("Error al escribir log de seguridad: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            error_log("Error al preparar statement de log_seguridad: " . $this->conn->error);
        }
    }
    
    // ==================== LOGS DE AUTENTICACIÓN ====================
    
    public function logLoginExitoso($usuario, $tipoUsuario = 'trabajador') {
        $this->writeLog('INFO', 'LOGIN_EXITOSO', $usuario, [
            'tipo_usuario' => $tipoUsuario,
            'metodo' => 'password'
        ]);
    }
    
    public function logLoginFallido($usuario, $intentos = 1) {
        $this->writeLog('WARNING', 'LOGIN_FALLIDO', $usuario, [
            'intentos_acumulados' => $intentos,
            'motivo' => 'credenciales_invalidas'
        ]);
    }
    
    public function logLogout($usuario) {
        $this->writeLog('INFO', 'LOGOUT', $usuario, [
            'tipo' => 'manual'
        ]);
    }
    
    // ==================== LOGS DE BLOQUEO DE CUENTAS ====================
    
    public function logCuentaBloqueada($usuario, $motivo = '3_intentos_fallidos') {
        $this->writeLog('CRITICAL', 'CUENTA_BLOQUEADA', $usuario, [
            'motivo' => $motivo,
            'accion_requerida' => 'contactar_administrador'
        ]);
    }
    
    public function logCuentaDesbloqueada($usuario) {
        $this->writeLog('INFO', 'CUENTA_DESBLOQUEADA', $usuario, [
            'metodo' => 'administrador'
        ]);
    }
    
    public function logIntentoAccesoBloqueado($usuario) {
        $this->writeLog('WARNING', 'ACCESO_BLOQUEADO_INTENTADO', $usuario, [
            'estado_cuenta' => 'bloqueada'
        ]);
    }
    
    // ==================== LOGS DE CONTRASEÑAS ====================
    
    public function logCambioContrasena($usuario) {
        $this->writeLog('INFO', 'CAMBIO_CONTRASENA', $usuario, [
            'tipo' => 'voluntario'
        ]);
    }
    
    public function logRecuperacionSolicitada($usuario) {
        $this->writeLog('INFO', 'RECUPERACION_SOLICITADA', $usuario, [
            'metodo' => 'correo_electronico'
        ]);
    }
    
    // ==================== LOGS DE ROLES Y PERMISOS ====================
    
    public function logAsignacionRol($usuarioModificado, $rolAsignado, $usuarioOSI) {
        $this->writeLog('INFO', 'ROL_ASIGNADO', $usuarioModificado, [
            'rol_nuevo' => $rolAsignado,
            'asignado_por' => $usuarioOSI
        ]);
    }
    
    public function logCambioPermisos($usuarioModificado, $usuarioOSI) {
        $this->writeLog('INFO', 'PERMISOS_MODIFICADOS', $usuarioModificado, [
            'modificado_por' => $usuarioOSI
        ]);
    }
    
    public function logAccesoNoAutorizado($usuario, $moduloIntentado) {
        $this->writeLog('WARNING', 'ACCESO_NO_AUTORIZADO', $usuario, [
            'modulo' => $moduloIntentado,
            'resultado' => 'denegado'
        ]);
    }
    
    // ==================== LOGS DE GESTIÓN DE USUARIOS ====================
    
    public function logCreacionUsuario($nuevoUsuario, $tipoUsuario, $creadoPor = '') {
        $this->writeLog('INFO', 'USUARIO_CREADO', $nuevoUsuario, [
            'tipo_usuario' => $tipoUsuario,
            'creado_por' => $creadoPor ?: ($_SESSION['username'] ?? 'SISTEMA')
        ]);
    }
    
    public function logModificacionUsuario($usuarioModificado, $modificadoPor = '') {
        $this->writeLog('INFO', 'USUARIO_MODIFICADO', $usuarioModificado, [
            'modificado_por' => $modificadoPor ?: ($_SESSION['username'] ?? 'SISTEMA')
        ]);
    }
    
    public function logEliminacionUsuario($usuarioEliminado, $eliminadoPor = '') {
        $this->writeLog('WARNING', 'USUARIO_ELIMINADO', $usuarioEliminado, [
            'eliminado_por' => $eliminadoPor ?: ($_SESSION['username'] ?? 'SISTEMA')
        ]);
    }
}
?>