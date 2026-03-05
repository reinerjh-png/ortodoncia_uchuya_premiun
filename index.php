<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario de login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $resultado = intentarLogin($pdo, $usuario, $password);
    
    if ($resultado['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $resultado['message'];
    }
}

// Verificar si está bloqueado (para deshabilitar el formulario)
$estaBloqueado = isset($_SESSION['login_bloqueado_hasta']) && ($_SESSION['login_bloqueado_hasta'] - time()) > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental Premium Uchuya — Sede Tingo María</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ── Pantalla completa, centrada ── */
        .quick-login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            background: url('assets/background-dental.jpg') center/cover no-repeat;
            background-color: var(--color-negro);
            overflow: hidden;
        }

        .quick-login-page .login-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.96) 100%);
            z-index: 1;
        }

        /* ── Tarjeta central ── */
        .quick-card {
            position: relative;
            z-index: 2;
            background: rgba(10, 10, 10, 0.82);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(212, 175, 55, 0.35);
            border-radius: 24px;
            padding: 52px 60px 44px;
            width: 100%;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 40px rgba(212,175,55,0.07);
            animation: fadeInUp 0.7s ease both;
        }

        /* ── Logo ── */
        .quick-logo {
            width: 110px;
            height: auto;
            margin-bottom: 18px;
            filter: drop-shadow(0 0 18px rgba(212,175,55,0.45));
        }

        /* ── Textos de marca ── */
        .quick-tagline {
            font-size: 0.75rem;
            letter-spacing: 3px;
            color: var(--color-blanco);
            text-transform: uppercase;
            margin-bottom: 0;
        }

        .quick-brand {
            font-family: var(--font-titulo);
            font-size: 3.8rem;
            font-weight: 700;
            color: var(--color-dorado);
            line-height: 0.9;
            letter-spacing: 6px;
            margin: 6px 0 2px;
        }

        .quick-sub {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 3px;
            color: var(--color-blanco);
            text-transform: uppercase;
        }

        .quick-sub span {
            color: var(--color-dorado);
        }

        /* ── Divisor ── */
        .quick-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(212,175,55,0.5), transparent);
            margin: 24px 0;
        }

        /* ── Sede badge ── */
        .quick-sede {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(212,175,55,0.1);
            border: 1px solid rgba(212,175,55,0.3);
            border-radius: 50px;
            padding: 7px 20px;
            color: var(--color-dorado);
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }

        /* ── Descripción ── */
        .quick-desc {
            color: var(--color-gris);
            font-size: 0.88rem;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        /* ── Formulario de contraseña ── */
        .login-form {
            margin-bottom: 8px;
        }

        .password-wrapper {
            position: relative;
            margin-bottom: 16px;
        }

        .password-input {
            width: 100%;
            padding: 14px 50px 14px 18px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(212,175,55,0.3);
            border-radius: 12px;
            color: var(--color-blanco);
            font-size: 1rem;
            letter-spacing: 2px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .password-input::placeholder {
            color: rgba(255,255,255,0.3);
            letter-spacing: 1px;
        }

        .password-input:focus {
            border-color: var(--color-dorado);
            box-shadow: 0 0 20px rgba(212,175,55,0.15);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(212,175,55,0.5);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s;
            padding: 4px;
        }

        .password-toggle:hover {
            color: var(--color-dorado);
        }

        .password-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(212,175,55,0.4);
            font-size: 1rem;
        }

        .password-input {
            padding-left: 42px;
        }

        /* ── Mensaje de error ── */
        .login-error {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid rgba(220, 53, 69, 0.6);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 18px;
            color: #ff4d5e;
            font-size: 0.92rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shakeError 0.5s ease;
            text-align: left;
        }

        .login-error i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .login-error.bloqueado {
            background: rgba(255, 193, 7, 0.15);
            border-color: rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }

        /* ── Input con error ── */
        .password-input.input-error {
            border-color: rgba(220, 53, 69, 0.7) !important;
            box-shadow: 0 0 15px rgba(220, 53, 69, 0.2) !important;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            15% { transform: translateX(-10px); }
            30% { transform: translateX(10px); }
            45% { transform: translateX(-7px); }
            60% { transform: translateX(7px); }
            75% { transform: translateX(-3px); }
            90% { transform: translateX(3px); }
        }

        /* ── Botón de ingreso ── */
        .btn-quick-enter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--color-dorado) 0%, var(--color-dorado-oscuro) 100%);
            color: var(--color-negro);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: var(--transicion);
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(212,175,55,0.3);
        }

        .btn-quick-enter:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 30px rgba(212,175,55,0.5);
        }

        .btn-quick-enter:active {
            transform: scale(0.98);
        }

        .btn-quick-enter:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Ícono animado al hover ── */
        .btn-quick-enter i {
            transition: transform 0.3s ease;
        }

        .btn-quick-enter:hover i {
            transform: translateX(5px);
        }

        /* ── Slogan inferior ── */
        .quick-slogan {
            margin-top: 22px;
            color: rgba(212,175,55,0.7);
            font-size: 0.8rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* ── Footer ── */
        .quick-footer {
            position: relative;
            z-index: 2;
            margin-top: 30px;
            color: rgba(255,255,255,0.25);
            font-size: 0.75rem;
            text-align: center;
        }

        .quick-footer span {
            color: var(--color-dorado);
            opacity: 0.6;
        }

        /* ── Animación ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Partículas decorativas ── */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(212,175,55,0.15);
            animation: float linear infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes float {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* ── Responsive ── */
        @media (max-width: 520px) {
            .quick-card { padding: 40px 28px 36px; }
            .quick-brand { font-size: 3rem; }
        }
    </style>
</head>
<body>
    <div class="quick-login-page">
        <div class="login-overlay"></div>

        <!-- Partículas flotantes decorativas -->
        <div class="particle" style="width:8px;height:8px;left:15%;animation-duration:18s;animation-delay:0s;"></div>
        <div class="particle" style="width:5px;height:5px;left:35%;animation-duration:22s;animation-delay:4s;"></div>
        <div class="particle" style="width:10px;height:10px;left:55%;animation-duration:16s;animation-delay:2s;"></div>
        <div class="particle" style="width:6px;height:6px;left:75%;animation-duration:20s;animation-delay:7s;"></div>
        <div class="particle" style="width:7px;height:7px;left:88%;animation-duration:25s;animation-delay:1s;"></div>

        <!-- Tarjeta principal -->
        <div class="quick-card">

            <!-- Logo -->
            <img src="assets/logo.png" alt="Clínica Dental Uchuya" class="quick-logo"
                 onerror="this.style.display='none'">

            <!-- Marca -->
            <p class="quick-tagline">Ortodoncia Clínica · Dental</p>
            <h1 class="quick-brand">UCHUYA</h1>
            <p class="quick-sub">PREMIUM <span>DE MEILYNG</span></p>

            <div class="quick-divider"></div>

            <!-- Sede -->
            <div class="quick-sede">
                <i class="fas fa-map-marker-alt"></i>
                Sede Tingo María
            </div>

            <p class="quick-desc">
                Sistema de Gestión de Historias Clínicas.<br>
                Ingrese sus credenciales para acceder al sistema.
            </p>

            <!-- Mensaje de error -->
            <?php if (!empty($error)): ?>
                <div style="background: rgba(220, 53, 69, 0.25); border: 1.5px solid #dc3545; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; color: #ff4d5e; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 10px; opacity: 1 !important; visibility: visible !important;">
                    <i class="fas <?php echo $estaBloqueado ? 'fa-lock' : 'fa-exclamation-triangle'; ?>" style="font-size: 1.1rem; color: #ff4d5e; flex-shrink: 0;"></i>
                    <span style="color: #ff4d5e; opacity: 1;"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulario de login -->
            <form method="POST" action="index.php" class="login-form" id="loginForm">
                <div class="password-wrapper" style="margin-bottom: 12px;">
                    <i class="fas fa-user password-icon"></i>
                    <input type="text" 
                           name="usuario" 
                           id="usuarioInput"
                           class="password-input <?php echo !empty($error) ? 'input-error' : ''; ?>" 
                           placeholder="Usuario"
                           autocomplete="username"
                           value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : 'user'; ?>"
                           autofocus
                           <?php echo $estaBloqueado ? 'disabled' : ''; ?>
                           required>
                </div>
                <div class="password-wrapper">
                    <i class="fas fa-lock password-icon"></i>
                    <input type="password" 
                           name="password" 
                           id="passwordInput"
                           class="password-input <?php echo !empty($error) ? 'input-error' : ''; ?>" 
                           placeholder="Contraseña"
                           autocomplete="current-password"
                           <?php echo $estaBloqueado ? 'disabled' : ''; ?>
                           required>
                    <button type="button" class="password-toggle" id="togglePassword" title="Mostrar/ocultar contraseña">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn-quick-enter" id="btn-entrar" <?php echo $estaBloqueado ? 'disabled' : ''; ?>>
                    <i class="fas fa-hospital-user"></i>
                    <span>Ingresar al Sistema</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="quick-slogan">
                <i class="fas fa-tooth"></i>
                Cuidamos tu sonrisa
            </p>
        </div>

        <!-- Footer -->
        <p class="quick-footer">
            &copy; 2026 Sistema de Historias Clínicas &mdash;
            <span>Desarrollado por: Tec. Reiner Jimenez</span>
        </p>
    </div>

    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.classList.toggle('fa-eye', !isPassword);
                eyeIcon.classList.toggle('fa-eye-slash', isPassword);
            });
        }

        // Enter key to submit
        if (passwordInput) {
            passwordInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('loginForm').submit();
                }
            });
        }
    </script>
</body>
</html>
