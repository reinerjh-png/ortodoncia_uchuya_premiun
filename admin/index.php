<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarSesion();
requiereAdmin();
require_once '../includes/functions.php';

// Contar datos para las tarjetas
$totalDoctores = $pdo->query("SELECT COUNT(*) FROM doctores WHERE estado = 1")->fetchColumn();
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 1")->fetchColumn();
$totalActividad = $pdo->query("SELECT COUNT(*) FROM actividad_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Clínica Dental Uchuya Premium de Mailyng</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="../dashboard.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Panel de Administración</span>
            </a>
            <nav class="header-nav">
                <span style="color: var(--color-gris); font-size: 0.85rem; margin-right: 8px;">
                    <i class="fas fa-user-shield" style="color: var(--color-dorado);"></i>
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    <span style="background: rgba(212,175,55,0.2); color: var(--color-dorado); padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 4px;">ADMIN</span>
                </span>
                <a href="../dashboard.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Sistema
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <h1 class="page-title">Panel de Administración</h1>
        
        <?php echo mostrarAlerta(); ?>

        <!-- Tarjetas de acceso rápido -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 40px;">
            <a href="doctores.php" class="stat-card" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: var(--color-dorado);">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-number" style="color: var(--color-dorado);"><?php echo $totalDoctores; ?></div>
                <div class="stat-label">DOCTORES</div>
                <p style="color: var(--color-gris); font-size: 0.8rem; margin-top: 8px;">Agregar, editar y eliminar doctores</p>
            </a>
            <a href="usuarios.php" class="stat-card" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: #28a745;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-number" style="color: #28a745;"><?php echo $totalUsuarios; ?></div>
                <div class="stat-label">USUARIOS</div>
                <p style="color: var(--color-gris); font-size: 0.8rem; margin-top: 8px;">Gestionar usuarios del sistema</p>
            </a>
            <a href="actividad.php" class="stat-card" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: #17a2b8;">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-number" style="color: #17a2b8;"><?php echo $totalActividad; ?></div>
                <div class="stat-label">ACTIVIDAD HOY</div>
                <p style="color: var(--color-gris); font-size: 0.8rem; margin-top: 8px;">Ver registro de acciones</p>
            </a>
        </div>

        <!-- Info del sistema -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-info-circle"></i> Información del Sistema</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div>
                    <p style="color: var(--color-gris); font-size: 0.85rem; margin-bottom: 4px;">Usuario actual</p>
                    <p style="color: var(--color-blanco); font-weight: 600;"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> (<?php echo htmlspecialchars($_SESSION['usuario_usuario']); ?>)</p>
                </div>
                <div>
                    <p style="color: var(--color-gris); font-size: 0.85rem; margin-bottom: 4px;">Rol</p>
                    <p style="color: var(--color-dorado); font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></p>
                </div>
                <div>
                    <p style="color: var(--color-gris); font-size: 0.85rem; margin-bottom: 4px;">Sesión iniciada</p>
                    <p style="color: var(--color-blanco); font-weight: 600;"><?php echo date('d/m/Y H:i', $_SESSION['login_tiempo']); ?></p>
                </div>
                <div>
                    <p style="color: var(--color-gris); font-size: 0.85rem; margin-bottom: 4px;">Base de datos</p>
                    <p style="color: var(--color-blanco); font-weight: 600;"><?php echo DB_NAME; ?></p>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Panel de Administración
        </p>
    </footer>
</body>
</html>
