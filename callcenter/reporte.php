<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/callcenter_functions.php';

// Obtener fecha del reporte (hoy por defecto)
$fechaReporte = isset($_GET['fecha']) ? sanitizar($_GET['fecha']) : date('Y-m-d');

//Obtener estadísticas del día seleccionado
$statsHoy = obtenerEstadisticasCallCenter($pdo, $fechaReporte);

// Obtener pacientes individuales del día seleccionado
$pacientesDelDia = obtenerPacientesDelDiaReporte($pdo, $fechaReporte);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Call Center - Clínica Dental Premium Uchuya</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .report-selector {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
            padding: 20px;
            text-align: center;
        }

        .stat-box-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-box-value {
            font-family: var(--font-titulo);
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-box-label {
            color: var(--color-gris);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .history-table {
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
            padding: 25px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .report-title {
            font-family: var(--font-titulo);
            font-size: 1.5rem;
            color: var(--color-dorado);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="header-logo">
                <i class="fas fa-chart-bar" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Reportes Call Center - Sede Tingo Maria</span>
            </a>
            <nav class="header-nav">
                <a href="index.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Call Center
                </a>
                <a href="../dashboard.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i>
            Reportes del Call Center
        </h1>

        <!-- Selector de Fecha -->
        <form method="GET" class="report-selector">
            <label for="fecha" class="form-label" style="margin: 0; color: var(--color-blanco);">
                <i class="fas fa-calendar-day"></i> Fecha del reporte:
            </label>
            <input type="date" 
                   id="fecha" 
                   name="fecha" 
                   class="form-control" 
                   value="<?php echo $fechaReporte; ?>"
                   max="<?php echo date('Y-m-d'); ?>"
                   style="max-width: 200px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i> Ver Reporte
            </button>
        </form>

        <!-- Estadísticas del Día Seleccionado -->
        <h2 class="report-title">
            <i class="fas fa-calendar-check"></i>
            Estadísticas del <?php echo date('d/m/Y', strtotime($fechaReporte)); ?>
        </h2>

        <div class="stats-summary">
            <div class="stat-box">
                <div class="stat-box-icon" style="color: #17a2b8;">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="stat-box-value" style="color: #17a2b8;">
                    <?php echo $statsHoy['total']; ?>
                </div>
                <div class="stat-box-label">Total Asignadas</div>
            </div>

            <div class="stat-box">
                <div class="stat-box-icon" style="color: #28a745;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-box-value" style="color: #28a745;">
                    <?php echo $statsHoy['completadas']; ?>
                </div>
                <div class="stat-box-label">Completadas</div>
            </div>



            <div class="stat-box">
                <div class="stat-box-icon" style="color: #dc3545;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-box-value" style="color: #dc3545;">
                    <?php echo $statsHoy['rechazadas']; ?>
                </div>
                <div class="stat-box-label">Rechazadas</div>
            </div>

            <div class="stat-box">
                <div class="stat-box-icon" style="color: var(--color-blanco);">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-box-value" style="color: var(--color-blanco);">
                    <?php echo $statsHoy['pendientes'] + $statsHoy['pospuestas']; ?>
                </div>
                <div class="stat-box-label">Pendientes</div>
            </div>

            <div class="stat-box">
                <div class="stat-box-icon" style="color: var(--color-dorado);">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-box-value" style="color: var(--color-dorado);">
                    <?php echo ($statsHoy['completadas'] + $statsHoy['rechazadas']); ?> de <?php echo $statsHoy['total']; ?>
                </div>
                <div class="stat-box-label">Progreso</div>
            </div>
        </div>

        <!-- Historial (Últimos 30 días) -->
        <h2 class="report-title">
            <i class="fas fa-users"></i>
            Pacientes del Día - <?php echo date('d/m/Y', strtotime($fechaReporte)); ?> (<?php echo count($pacientesDelDia); ?>)
        </h2>

        <div class="history-table">
            <?php if (!empty($pacientesDelDia)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N° Historia</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Género</th>
                                <th>Edad</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacientesDelDia as $paciente): ?>
                                <?php
                                    $estado = $paciente['llamada_estado'];
                                    switch ($estado) {
                                        case 'completada':
                                            $badgeColor = '#28a745';
                                            $badgeBg = 'rgba(40, 167, 69, 0.2)';
                                            $estadoTexto = 'Completada';
                                            $estadoIcon = 'fas fa-check-circle';
                                            break;
                                        case 'pospuesta':
                                            $badgeColor = '#ffc107';
                                            $badgeBg = 'rgba(255, 193, 7, 0.2)';
                                            $estadoTexto = 'Pospuesta';
                                            $estadoIcon = 'fas fa-clock';
                                            break;
                                        case 'rechazada':
                                            $badgeColor = '#dc3545';
                                            $badgeBg = 'rgba(220, 53, 69, 0.2)';
                                            $estadoTexto = 'Rechazada';
                                            $estadoIcon = 'fas fa-times-circle';
                                            break;
                                        default:
                                            $badgeColor = 'var(--color-blanco)';
                                            $badgeBg = 'rgba(255, 255, 255, 0.1)';
                                            $estadoTexto = 'Pendiente';
                                            $estadoIcon = 'fas fa-hourglass-half';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-gold">
                                            <?php echo htmlspecialchars($paciente['numero_historia']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($paciente['nombres']); ?></td>
                                    <td style="color: var(--color-dorado); font-weight: bold;">
                                        <?php echo htmlspecialchars($paciente['celular']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($paciente['genero'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['edad'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['direccion'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge" style="background: <?php echo $badgeBg; ?>; color: <?php echo $badgeColor; ?>; border: 1px solid <?php echo $badgeColor; ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            <i class="<?php echo $estadoIcon; ?>"></i>
                                            <?php echo $estadoTexto; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No hay pacientes asignados para esta fecha</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Reportes Call Center
        </p>
    </footer>
</body>
</html>
