-- ==============================================================================
-- SCRIPT DE OPTIMIZACIÓN DE RENDIMIENTO (VERSIÓN HOSTING COMPARTIDO)
-- Clínica Dental Premium Uchuya
-- Ejecutar en el servidor de PRODUCCIÓN
-- ==============================================================================
-- ⚠️ IMPORTANTE ⚠️
-- Como su servidor web restringe el uso de Procedimientos Almacenados,
-- esta versión contiene comandos directos.
-- 
-- Si PhpMyAdmin muestra un error de "Duplicate Key" al ejecutar alguna línea,
-- simplemente IGNÓRELO, significa que ese índice ya existe y siga con el éxito.
-- ==============================================================================

-- 1. Optimización de la tabla PACIENTES (Búsquedas y Filtros en Dashboard)
CREATE INDEX idx_pacientes_estado ON pacientes (estado);
CREATE INDEX idx_pacientes_fecha_cita ON pacientes (fecha_ultima_cita);

-- 2. Asegurar índices relacionales (JOINS rápidos entre pacientes y tratamientos)
CREATE INDEX idx_pt_paciente ON paciente_tratamientos (paciente_id);
CREATE INDEX idx_pt_tratamiento ON paciente_tratamientos (tratamiento_id);

-- 3. Asegurar índices de log (Auditoría rápida y listado de actividad)
CREATE INDEX idx_log_fecha ON actividad_log (created_at);
CREATE INDEX idx_log_usuario ON actividad_log (usuario_id);

-- Opcional sugerido (FullText para búsquedas muy rápidas por nombre):
-- ALTER TABLE pacientes ADD FULLTEXT INDEX ft_nombres (nombres);
