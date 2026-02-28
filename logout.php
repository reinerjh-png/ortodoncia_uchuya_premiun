<?php
/**
 * Cerrar Sesión
 * Clínica Dental Premium Uchuya
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

cerrarSesion();
header('Location: index.php');
exit;
