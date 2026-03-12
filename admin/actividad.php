<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarSesion();
requiereAdmin();
require_once '../includes/functions.php';

$filtroUsuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : '';
$filtroFechaDesde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtroFechaHasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

$where = [];
$params = [];
if ($filtroUsuario) { $where[] = "a.usuario_id = ?"; $params[] = $filtroUsuario; }
if ($filtroFechaDesde) { $where[] = "DATE(a.created_at) >= ?"; $params[] = $filtroFechaDesde; }
if ($filtroFechaHasta) { $where[] = "DATE(a.created_at) <= ?"; $params[] = $filtroFechaHasta; }
$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$porPagina = 50;
$paginaActual = max(1, intval($_GET['pagina'] ?? 1));
$offset = ($paginaActual - 1) * $porPagina;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM actividad_log a $whereSQL");
$countStmt->execute($params);
$totalRegistros = $countStmt->fetchColumn();
$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

$stmt = $pdo->prepare("SELECT a.*, u.usuario, u.nombre_completo, u.rol FROM actividad_log a JOIN usuarios u ON u.id = a.usuario_id $whereSQL ORDER BY a.created_at DESC LIMIT $porPagina OFFSET $offset");
$stmt->execute($params);
$actividades = $stmt->fetchAll();

$todosUsuarios = $pdo->query("SELECT id, usuario, nombre_completo FROM usuarios ORDER BY nombre_completo")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Actividad - Panel Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Registro de Actividad</span>
            </a>
            <nav class="header-nav">
                <a href="index.php" class="btn-nav btn-nav-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Registro de Actividad</h1>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-filter"></i> Filtros</h2>
                <?php if ($filtroUsuario || $filtroFechaDesde || $filtroFechaHasta): ?>
                    <a href="actividad.php" style="color: var(--color-gris); text-decoration: none; font-size: 0.85rem;"><i class="fas fa-times"></i> Limpiar filtros</a>
                <?php endif; ?>
            </div>
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div style="min-width: 200px; flex: 1;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Usuario</label>
                    <select name="usuario" style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                        <option value="" style="background: #1a1a1a; color: #fff;">Todos los usuarios</option>
                        <?php foreach ($todosUsuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $filtroUsuario == $u['id'] ? 'selected' : ''; ?> style="background: #1a1a1a; color: #fff;">
                                <?php echo htmlspecialchars($u['nombre_completo']); ?> (<?php echo htmlspecialchars($u['usuario']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 180px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Desde</label>
                    <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($filtroFechaDesde); ?>"
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div style="min-width: 180px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?php echo htmlspecialchars($filtroFechaHasta); ?>"
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <button type="submit" style="padding: 12px 30px; background: linear-gradient(135deg, var(--color-dorado), var(--color-dorado-oscuro)); color: #000; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Log de Actividad</h2>
                <span class="text-gray"><?php echo number_format($totalRegistros); ?> registro(s) &mdash; página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?></span>
            </div>
            <div class="table-container">
                <table class="table" style="min-width: 800px;">
                    <thead>
                        <tr><th>Fecha y Hora</th><th>Usuario</th><th>Rol</th><th>Acción</th><th>Detalle</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($actividades)): ?>
                            <tr><td colspan="6" class="table-empty"><i class="fas fa-clipboard-list" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>No se encontraron registros de actividad</td></tr>
                        <?php else: ?>
                            <?php foreach ($actividades as $act): ?>
                                <tr>
                                    <td style="white-space: nowrap; font-size: 0.85rem;">
                                        <?php echo date('d/m/Y', strtotime($act['created_at'])); ?><br>
                                        <span style="color: var(--color-gris);"><?php echo date('H:i:s', strtotime($act['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($act['nombre_completo']); ?></strong><br>
                                        <span style="color: var(--color-gris); font-size: 0.8rem;"><?php echo htmlspecialchars($act['usuario']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($act['rol'] === 'admin'): ?>
                                            <span style="background: rgba(212,175,55,0.15); color: var(--color-dorado); padding: 3px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Admin</span>
                                        <?php else: ?>
                                            <span style="background: rgba(40,167,69,0.15); color: #28a745; padding: 3px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Recep.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $iconMap = [
                                            'Login' => 'fa-sign-in-alt', 'Logout' => 'fa-sign-out-alt',
                                            'Crear Paciente' => 'fa-user-plus', 'Editar Paciente' => 'fa-user-edit',
                                            'Archivar Paciente' => 'fa-archive', 'Restaurar Paciente' => 'fa-trash-restore',
                                            'Agregar Doctor' => 'fa-user-md', 'Eliminar Doctor' => 'fa-user-times',
                                            'Activar Doctor' => 'fa-check-circle', 'Desactivar Doctor' => 'fa-ban',
                                            'Agregar Usuario' => 'fa-user-plus', 'Eliminar Usuario' => 'fa-user-minus',
                                            'Activar Usuario' => 'fa-user-check', 'Desactivar Usuario' => 'fa-user-slash',
                                            'Cambiar Contraseña' => 'fa-key',
                                        ];
                                        $icon = $iconMap[$act['accion']] ?? 'fa-circle';
                                        ?>
                                        <span style="color: var(--color-dorado);"><i class="fas <?php echo $icon; ?>"></i></span>
                                        <?php echo htmlspecialchars($act['accion']); ?>
                                    </td>
                                    <td style="max-width: 300px; font-size: 0.85rem; color: var(--color-gris);"><?php echo htmlspecialchars($act['detalle']); ?></td>
                                    <td style="font-size: 0.8rem; color: var(--color-gris);"><?php echo htmlspecialchars($act['ip_address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 16px 0;">
                <?php
                $filterParams = [];
                if ($filtroUsuario) $filterParams['usuario'] = $filtroUsuario;
                if ($filtroFechaDesde) $filterParams['fecha_desde'] = $filtroFechaDesde;
                if ($filtroFechaHasta) $filterParams['fecha_hasta'] = $filtroFechaHasta;
                function urlPaginaActividad($pagina, $filterParams) {
                    $filterParams['pagina'] = $pagina;
                    return 'actividad.php?' . http_build_query($filterParams);
                }
                ?>
                <?php if ($paginaActual > 1): ?>
                    <a href="<?php echo htmlspecialchars(urlPaginaActividad(1, $filterParams)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;"><i class="fas fa-angle-double-left"></i></a>
                    <a href="<?php echo htmlspecialchars(urlPaginaActividad($paginaActual - 1, $filterParams)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;"><i class="fas fa-angle-left"></i> Anterior</a>
                <?php endif; ?>
                <?php for ($p = max(1, $paginaActual - 2); $p <= min($totalPaginas, $paginaActual + 2); $p++): ?>
                    <a href="<?php echo htmlspecialchars(urlPaginaActividad($p, $filterParams)); ?>" class="btn-nav <?php echo $p === $paginaActual ? 'btn-nav-primary' : 'btn-nav-secondary'; ?>" style="padding:6px 12px; min-width:36px; text-align:center;"><?php echo $p; ?></a>
                <?php endfor; ?>
                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="<?php echo htmlspecialchars(urlPaginaActividad($paginaActual + 1, $filterParams)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;">Siguiente <i class="fas fa-angle-right"></i></a>
                    <a href="<?php echo htmlspecialchars(urlPaginaActividad($totalPaginas, $filterParams)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;"><i class="fas fa-angle-double-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer">
        <p class="footer-text">© <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Panel de Administración</p>
    </footer>
</body>
</html>
