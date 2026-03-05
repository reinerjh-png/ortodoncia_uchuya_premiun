-- ═══════════════════════════════════════════════════════════════
-- Migración: Sistema de Usuarios y Log de Actividad
-- Clínica Dental Premium Uchuya
-- Fecha: 2026-03-05
-- ═══════════════════════════════════════════════════════════════
-- Ejecutar este script en la base de datos existente
-- para agregar las tablas de usuarios y actividad.
-- ═══════════════════════════════════════════════════════════════

-- ────────────────────────────────────────
-- Tabla: usuarios
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(64) NOT NULL COMMENT 'SHA-256 hash',
  `nombre_completo` varchar(150) NOT NULL,
  `rol` enum('admin','recepcionista') NOT NULL DEFAULT 'recepcionista',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────
-- Usuarios por defecto
-- ────────────────────────────────────────
-- user / MEILYNG123 (recepcionista)
-- admin / admin123 (administrador)
INSERT INTO `usuarios` (`usuario`, `password_hash`, `nombre_completo`, `rol`, `estado`) VALUES
('user',  SHA2('MEILYNG123', 256), 'Recepcionista', 'recepcionista', 1),
('admin', SHA2('admin123', 256),   'Administrador', 'admin', 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ────────────────────────────────────────
-- Tabla: actividad_log
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `actividad_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `detalle` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_actividad_usuario` (`usuario_id`),
  KEY `idx_actividad_fecha` (`created_at`),
  CONSTRAINT `fk_actividad_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
