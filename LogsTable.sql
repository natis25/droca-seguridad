-- =====================================================
-- SISTEMA DE LOGS PARA DROCA INMOBILIARIA
-- Ejecutar en la base de datos 'droca'
-- ===================================================

USE droca;

-- Tabla para Logs de Aplicación (funcionalidades críticas)
CREATE TABLE IF NOT EXISTS log_aplicacion (
    idLog INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    nivel_log ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') NOT NULL,
    evento VARCHAR(100) NOT NULL,
    usuario VARCHAR(100) DEFAULT 'SISTEMA',
    ip_address VARCHAR(45),
    user_agent TEXT,
    modulo VARCHAR(50),
    detalles JSON,
    INDEX idx_fecha (fecha_hora),
    INDEX idx_evento (evento),
    INDEX idx_usuario (usuario),
    INDEX idx_modulo (modulo),
    INDEX idx_nivel (nivel_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Logs de Seguridad (eventos de usuarios)
CREATE TABLE IF NOT EXISTS log_seguridad (
    idLog INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    nivel_log ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') NOT NULL,
    evento VARCHAR(100) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    detalles JSON,
    INDEX idx_fecha (fecha_hora),
    INDEX idx_evento (evento),
    INDEX idx_usuario (usuario),
    INDEX idx_nivel (nivel_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que las tablas se crearon correctamente
SHOW TABLES LIKE 'log_%';