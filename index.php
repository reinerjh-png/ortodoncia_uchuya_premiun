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
            margin-bottom: 32px;
            line-height: 1.6;
        }

        /* ── Botón de ingreso rápido ── */
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
                Acceso directo al panel de control.
            </p>

            <!-- Botón de ingreso rápido (sin contraseña) -->
            <a href="dashboard.php" class="btn-quick-enter" id="btn-entrar">
                <i class="fas fa-hospital-user"></i>
                <span>Ingresar al Sistema</span>
                <i class="fas fa-arrow-right"></i>
            </a>

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
</body>
</html>
