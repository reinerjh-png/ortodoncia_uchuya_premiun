-- Script para agregar la columna hora_cita a la tabla pacientes
-- Ejecutar en phpMyAdmin o cualquier cliente MySQL

ALTER TABLE pacientes
ADD COLUMN hora_cita TIME NULL DEFAULT NULL AFTER fecha_ultima_cita;
