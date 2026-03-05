<?php
/**
 * Sistema de Autenticación
 * Clínica Dental Premium Uchuya
 * 
 * ═══════════════════════════════════════════════════════════════
 *  CÓMO CAMBIAR LA CONTRASEÑA EN EL FUTURO:
 * ═══════════════════════════════════════════════════════════════
 *  1. Busca la línea que dice: define('AUTH_PASSWORD', 'INGRESA TU CONTRASEÑA AQUI');
 *  2. Cambia 'INGRESA TU CONTRASEÑA AQUI' por tu nueva contraseña.
 *  3. Guarda el archivo. ¡Eso es todo!
 * ═══════════════════════════════════════════════════════════════
 */

// ┌──────────────────────────────────────────┐
// │  CONTRASEÑA DEL SISTEMA (cambiar aquí)   │
// └──────────────────────────────────────────┘
define('AUTH_PASSWORD', 'INGRESA TU CONTRASEÑA AQUI');

// Configuración de intentos
define('MAX_INTENTOS_LOGIN', 5);
define('BLOQUEO_MINUTOS', 15);

/**
 * Verifica si el usuario tiene una sesión activa.
 * Si no la tiene, redirige al login.
 * Debe llamarse al inicio de cada página protegida.
 */
function verificarSesion() {
    if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
        // Determinar la ruta al index.php según la ubicación del archivo actual
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
        $authDir = str_replace('\\', '/', dirname(__DIR__));
        
        if (strpos($scriptPath, $authDir) === 0) {
            $relPath = substr(dirname($scriptPath), strlen($authDir) + 1);
            $depth = $relPath ? substr_count($relPath, '/') + 1 : 0;
            $prefix = str_repeat('../', $depth);
        } else {
            $prefix = '';
        }
        
        header('Location: ' . $prefix . 'index.php');
        exit;
    }
}

/**
 * Intenta autenticar al usuario con la contraseña proporcionada.
 * Implementa protección contra fuerza bruta.
 * 
 * @param string $password La contraseña ingresada
 * @return array ['success' => bool, 'message' => string]
 */
function intentarLogin($password) {
    // Verificar si está bloqueado por intentos fallidos
    if (isset($_SESSION['login_bloqueado_hasta'])) {
        $tiempoRestante = $_SESSION['login_bloqueado_hasta'] - time();
        if ($tiempoRestante > 0) {
            $minutosRestantes = ceil($tiempoRestante / 60);
            return [
                'success' => false,
                'message' => "Cuenta bloqueada por seguridad. Intente en {$minutosRestantes} minuto(s).",
                'bloqueado' => true
            ];
        } else {
            // El bloqueo expiró, limpiar
            unset($_SESSION['login_bloqueado_hasta']);
            unset($_SESSION['login_intentos']);
        }
    }

    // Inicializar contador de intentos
    if (!isset($_SESSION['login_intentos'])) {
        $_SESSION['login_intentos'] = 0;
    }

    // Verificar contraseña
    if ($password === AUTH_PASSWORD) {
        // Contraseña correcta — limpiar intentos y autenticar
        $_SESSION['autenticado'] = true;
        $_SESSION['login_tiempo'] = time();
        unset($_SESSION['login_intentos']);
        unset($_SESSION['login_bloqueado_hasta']);
        
        return [
            'success' => true,
            'message' => 'Acceso concedido'
        ];
    } else {
        // Contraseña incorrecta — incrementar intentos
        $_SESSION['login_intentos']++;
        
        $intentosRestantes = MAX_INTENTOS_LOGIN - $_SESSION['login_intentos'];
        
        if ($_SESSION['login_intentos'] >= MAX_INTENTOS_LOGIN) {
            // Bloquear cuenta
            $_SESSION['login_bloqueado_hasta'] = time() + (BLOQUEO_MINUTOS * 60);
            $_SESSION['login_intentos'] = 0;
            
            return [
                'success' => false,
                'message' => "Demasiados intentos fallidos. Cuenta bloqueada por " . BLOQUEO_MINUTOS . " minutos.",
                'bloqueado' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => "Contraseña incorrecta. Le quedan {$intentosRestantes} intento(s)."
        ];
    }
}

/**
 * Cierra la sesión del usuario y redirige al login.
 */
function cerrarSesion() {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}
