-- ==============================================================================
-- SCRIPT DE OPTIMIZACIÓN DE RENDIMIENTO (VERSIÓN SEGURA V2)
-- Clínica Dental Premium Uchuya
-- Ejecutar en el servidor de PRODUCCIÓN para acelerar el sistema
-- ==============================================================================

-- Solución al error #1267 (Mezcla de collations):
-- Forzamos la comparación a un collation común (utf8_general_ci)

DELIMITER $$
DROP PROCEDURE IF EXISTS CrearIndiceSiNoExiste$$
CREATE PROCEDURE CrearIndiceSiNoExiste(
    IN idx_table VARCHAR(255),
    IN idx_name VARCHAR(255),
    IN idx_definition VARCHAR(1000)
)
BEGIN
    DECLARE index_count INT;
    
    -- Verifica si el índice ya existe usando COLLATE para evitar errores de sistema
    SELECT COUNT(1) INTO index_count 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA COLLATE utf8_general_ci = DATABASE() COLLATE utf8_general_ci
      AND TABLE_NAME COLLATE utf8_general_ci = idx_table COLLATE utf8_general_ci
      AND INDEX_NAME COLLATE utf8_general_ci = idx_name COLLATE utf8_general_ci;

    IF index_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', idx_table, ' ADD ', idx_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- 1. Asegurar índices relacionales (JOINS rápidos)
CALL CrearIndiceSiNoExiste('paciente_tratamientos', 'idx_pt_paciente', 'INDEX idx_pt_paciente (paciente_id)');
CALL CrearIndiceSiNoExiste('paciente_tratamientos', 'idx_pt_tratamiento', 'INDEX idx_pt_tratamiento (tratamiento_id)');

-- 2. Asegurar índices de log
CALL CrearIndiceSiNoExiste('actividad_log', 'idx_log_fecha', 'INDEX idx_log_fecha (created_at)');
CALL CrearIndiceSiNoExiste('actividad_log', 'idx_log_usuario', 'INDEX idx_log_usuario (usuario_id)');

-- Eliminar el procedimiento temporal
DROP PROCEDURE IF EXISTS CrearIndiceSiNoExiste;

-- ==============================================================================
-- NOTA: Los índices principales (nombres, dni, estado, historia) ya existen 
-- en tu base de datos actual, por eso este script ya no los intenta duplicar.
-- ==============================================================================
