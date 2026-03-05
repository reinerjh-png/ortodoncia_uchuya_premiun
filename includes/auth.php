<?php
/**
 * Sistema de Autenticación con Roles
 * Clínica Dental Premium Uchuya
 * 
 * Roles:
 *  - admin: Acceso completo (eliminar pacientes, gestión doctores/usuarios, ver actividad)
 *  - recepcionista: Acceso estándar (crear/editar/archivar pacientes, call center)
 */

// Configuración de intentos
define('MAX_INTENTOS_LOGIN', 5);
define('BLOQUEO_MINUTOS', 15);

/**
 * Verifica si el usuario tiene una sesión activa.
 * Si no la tiene, redirige al login.
 */
function verificarSesion() {
    if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
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
 * Verifica si el usuario actual es administrador.
 */
function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Requiere que el usuario sea admin. Si no lo es, redirige al dashboard.
 */
function requiereAdmin() {
    if (!esAdmin()) {
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
        $authDir = str_replace('\\', '/', dirname(__DIR__));
        
        if (strpos($scriptPath, $authDir) === 0) {
            $relPath = substr(dirname($scriptPath), strlen($authDir) + 1);
            $depth = $relPath ? substr_count($relPath, '/') + 1 : 0;
            $prefix = str_repeat('../', $depth);
        } else {
            $prefix = '';
        }
        
        header('Location: ' . $prefix . 'dashboard.php');
        exit;
    }
}

/**
 * Intenta autenticar al usuario con usuario y contraseña.
 * Valida contra la tabla `usuarios` en la base de datos.
 * 
 * @param PDO $pdo Conexión a la BD
 * @param string $usuario Nombre de usuario
 * @param string $password Contraseña en texto plano
 * @return array ['success' => bool, 'message' => string]
 */
function intentarLogin($pdo, $usuario, $password) {
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
            unset($_SESSION['login_bloqueado_hasta']);
            unset($_SESSION['login_intentos']);
        }
    }

    // Inicializar contador de intentos
    if (!isset($_SESSION['login_intentos'])) {
        $_SESSION['login_intentos'] = 0;
    }

    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id, usuario, password_hash, nombre_completo, rol, estado FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    // Verificar credenciales
    if ($user && $user['password_hash'] === hash('sha256', $password)) {
        // Verificar si el usuario está activo
        if ($user['estado'] == 0) {
            return [
                'success' => false,
                'message' => 'Su cuenta ha sido desactivada. Contacte al administrador.'
            ];
        }

        // Login exitoso — guardar datos en sesión
        $_SESSION['autenticado'] = true;
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre_completo'];
        $_SESSION['usuario_usuario'] = $user['usuario'];
        $_SESSION['usuario_rol'] = $user['rol'];
        $_SESSION['login_tiempo'] = time();
        unset($_SESSION['login_intentos']);
        unset($_SESSION['login_bloqueado_hasta']);

        // Registrar actividad de login
        registrarActividad($pdo, 'Login', 'Inicio de sesión exitoso');
        
        return [
            'success' => true,
            'message' => 'Acceso concedido'
        ];
    } else {
        // Credenciales incorrectas — incrementar intentos
        $_SESSION['login_intentos']++;
        
        $intentosRestantes = MAX_INTENTOS_LOGIN - $_SESSION['login_intentos'];
        
        if ($_SESSION['login_intentos'] >= MAX_INTENTOS_LOGIN) {
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
            'message' => "Usuario o contraseña incorrectos. Le quedan {$intentosRestantes} intento(s)."
        ];
    }
}

/**
 * Registra una actividad en el log.
 * 
 * @param PDO $pdo Conexión a la BD
 * @param string $accion Tipo de acción (Login, Crear Paciente, etc.)
 * @param string $detalle Detalle de la acción
 */
function registrarActividad($pdo, $accion, $detalle = '') {
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    if (!$usuario_id) return;
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO actividad_log (usuario_id, accion, detalle, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $accion, $detalle, $ip]);
    } catch (Exception $e) {
        // Silenciar errores del log para no afectar la operación principal
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
