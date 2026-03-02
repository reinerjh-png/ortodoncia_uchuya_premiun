-- phpMyAdmin SQL Dump
-- Script limpio - Solo estructura de tablas
-- Base de datos: `clinica_uchuya`
-- Generado: 2026-03-02
-- Incluye: 24 tratamientos por defecto + Doctor Fernando Uchuya
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `clinica_uchuya_aucayacu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `call_center_historial`
--

CREATE TABLE `call_center_historial` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `accion` enum('completada','pospuesta','rechazada') NOT NULL,
  `agendar_cita` tinyint(1) DEFAULT 0,
  `fecha_cita` date DEFAULT NULL,
  `fecha_accion` datetime NOT NULL,
  `ciclo_actual` tinyint(1) DEFAULT 1 COMMENT '1 = ciclo actual, 0 = ciclos anteriores',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `call_center_llamadas`
--

CREATE TABLE `call_center_llamadas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `estado` enum('pendiente','completada','pospuesta','rechazada') DEFAULT 'pendiente',
  `fecha_procesado` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctores`
--

CREATE TABLE `doctores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especialidad` varchar(100) DEFAULT 'Odontología General',
  `estado` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `doctores`
-- Solo se incluye al Dr. Fernando Uchuya por defecto
--

INSERT INTO `doctores` (`id`, `nombre`, `especialidad`, `estado`, `created_at`) VALUES
(1, 'Fernando Uchuya', 'Odontología General', 1, '2026-01-29 05:07:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `numero_historia` int(10) NOT NULL,
  `dni` int(8) DEFAULT NULL,
  `nombres` varchar(200) NOT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `celular` int(9) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `fecha_ultima_cita` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_imagenes`
--

CREATE TABLE `paciente_imagenes` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_tratamientos`
--

CREATE TABLE `paciente_tratamientos` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `tratamiento_id` int(11) NOT NULL,
  `fecha_asignacion` date DEFAULT curdate(),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

CREATE TABLE `tratamientos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tratamientos`
-- 24 tratamientos por defecto
--

INSERT INTO `tratamientos` (`id`, `nombre`, `descripcion`, `estado`, `created_at`) VALUES
(1, 'Blanqueamiento', NULL, 1, '2026-01-29 05:07:26'),
(2, 'Carillas', NULL, 1, '2026-01-29 05:07:26'),
(3, 'Cirugía', NULL, 1, '2026-01-29 05:07:26'),
(4, 'Coronas', NULL, 1, '2026-01-29 05:07:26'),
(5, 'Curaciones', NULL, 1, '2026-01-29 05:07:26'),
(6, 'Endodoncia', NULL, 1, '2026-01-29 05:07:26'),
(7, 'Extracción', NULL, 1, '2026-01-29 05:07:26'),
(8, 'Férula', NULL, 1, '2026-01-29 05:07:26'),
(9, 'Fluorización', NULL, 1, '2026-01-29 05:07:26'),
(10, 'Implantología', NULL, 1, '2026-01-29 05:07:26'),
(11, 'Impresión', NULL, 1, '2026-01-29 05:07:26'),
(12, 'Limpieza', NULL, 1, '2026-01-29 05:07:26'),
(13, 'Mantenedores', NULL, 1, '2026-01-29 05:07:26'),
(14, 'Ortodoncia', NULL, 1, '2026-01-29 05:07:26'),
(15, 'Pasta', NULL, 1, '2026-01-29 05:07:26'),
(16, 'Profilaxis', NULL, 1, '2026-01-29 05:07:26'),
(17, 'Prótesis', NULL, 1, '2026-01-29 05:07:26'),
(18, 'Pulpotomía', NULL, 1, '2026-01-29 05:07:26'),
(19, 'Radiografía', NULL, 1, '2026-01-29 05:07:26'),
(20, 'Rehabilitación', NULL, 1, '2026-01-29 05:07:26'),
(21, 'Reparación', NULL, 1, '2026-01-29 05:07:26'),
(22, 'Exodoncia', NULL, 1, '2026-01-29 22:11:55'),
(23, 'Tallado', NULL, 1, '2026-01-29 22:46:01'),
(24, 'Brackets', NULL, 1, '2026-02-07 21:10:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `call_center_historial`
--
ALTER TABLE `call_center_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paciente` (`paciente_id`),
  ADD KEY `idx_fecha_accion` (`fecha_accion`),
  ADD KEY `idx_ciclo` (`ciclo_actual`),
  ADD KEY `idx_accion` (`accion`);

--
-- Indices de la tabla `call_center_llamadas`
--
ALTER TABLE `call_center_llamadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha_asignacion` (`fecha_asignacion`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_paciente_fecha` (`paciente_id`,`fecha_asignacion`);

--
-- Indices de la tabla `doctores`
--
ALTER TABLE `doctores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_historia` (`numero_historia`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `idx_pacientes_dni` (`dni`),
  ADD KEY `idx_pacientes_nombres` (`nombres`),
  ADD KEY `idx_pacientes_numero_historia` (`numero_historia`);

--
-- Indices de la tabla `paciente_imagenes`
--
ALTER TABLE `paciente_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paciente_imagenes_paciente` (`paciente_id`);

--
-- Indices de la tabla `paciente_tratamientos`
--
ALTER TABLE `paciente_tratamientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_paciente_tratamiento` (`paciente_id`,`tratamiento_id`),
  ADD KEY `tratamiento_id` (`tratamiento_id`);

--
-- Indices de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `call_center_historial`
--
ALTER TABLE `call_center_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `call_center_llamadas`
--
ALTER TABLE `call_center_llamadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doctores`
--
ALTER TABLE `doctores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `paciente_imagenes`
--
ALTER TABLE `paciente_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paciente_tratamientos`
--
ALTER TABLE `paciente_tratamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `call_center_historial`
--
ALTER TABLE `call_center_historial`
  ADD CONSTRAINT `call_center_historial_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `call_center_llamadas`
--
ALTER TABLE `call_center_llamadas`
  ADD CONSTRAINT `call_center_llamadas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `paciente_imagenes`
--
ALTER TABLE `paciente_imagenes`
  ADD CONSTRAINT `paciente_imagenes_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `paciente_tratamientos`
--
ALTER TABLE `paciente_tratamientos`
  ADD CONSTRAINT `paciente_tratamientos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paciente_tratamientos_ibfk_2` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
