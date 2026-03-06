<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
verificarSesion();
require_once 'includes/functions.php';
require_once 'includes/callcenter_functions.php';
require_once 'includes/comunicado.php';

// Lógica para mostrar comunicado una sola vez por sesión
$mostrarComunicado = $comunicado_activo && !isset($_SESSION['comunicado_visto']);
if ($mostrarComunicado) {
    $_SESSION['comunicado_visto'] = true;
}


// Parámetros de búsqueda y filtro
$busqueda     = isset($_GET['buscar'])       ? sanitizar($_GET['buscar'])       : '';
$tipoBusqueda = isset($_GET['tipo_busqueda'])? sanitizar($_GET['tipo_busqueda']): '';
$verCitas      = isset($_GET['ver']) && $_GET['ver'] === 'citas';
$verArchivados = isset($_GET['ver']) && $_GET['ver'] === 'archivados';
$estadoActual  = $verArchivados ? 0 : 1;

// Paginación
define('REGISTROS_POR_PAGINA', 50);
$paginaActual  = max(1, (int) ($_GET['pagina'] ?? 1));
$offset        = ($paginaActual - 1) * REGISTROS_POR_PAGINA;
$totalRegistros = contarPacientes($pdo, $busqueda, $tipoBusqueda, $estadoActual, $verCitas);
$totalPaginas   = max(1, (int) ceil($totalRegistros / REGISTROS_POR_PAGINA));
if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;

// Una sola consulta: trae pacientes + tratamientos agrupados
$pacientes = obtenerPacientes($pdo, $busqueda, $tipoBusqueda, $estadoActual, $verCitas, REGISTROS_POR_PAGINA, $offset);

// Totales para tarjetas de estadísticas (una sola consulta)
$statsRow = $pdo->query(
    "SELECT
        SUM(estado = 1)                                                       AS activos,
        SUM(estado = 0)                                                       AS archivados,
        SUM(estado = 1 AND fecha_ultima_cita >= CURRENT_DATE
            AND fecha_ultima_cita IS NOT NULL)                                AS citas
    FROM pacientes"
)->fetch();
$totalPacientesActivos = (int) $statsRow['activos'];
$totalArchivados       = (int) $statsRow['archivados'];
$totalCitasProx        = (int) $statsRow['citas'];

// Call center
$totalCallCenter = obtenerContadorCallCenter($pdo);

// Tratamientos para el selector de búsqueda
$todosTratamientos = obtenerTratamientos($pdo);

// Helper: armar URL manteniendo parámetros actuales, cambiando solo la página
function urlPagina($pagina, $busqueda, $tipoBusqueda, $verCitas, $verArchivados) {
    $params = ['pagina' => $pagina];
    if ($busqueda)     $params['buscar']       = $busqueda;
    if ($tipoBusqueda) $params['tipo_busqueda']= $tipoBusqueda;
    if ($verCitas)     $params['ver']          = 'citas';
    if ($verArchivados)$params['ver']          = 'archivados';
    return 'dashboard.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clínica Dental Premium Uchuya</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Ortodoncia Uchuya Premium - Meilyng "Tingo María"</span>
            </a>
            <nav class="header-nav">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-right: 8px;">
                    <span style="color: var(--color-gris); font-size: 0.85rem;">
                        <i class="fas fa-user-circle" style="color: var(--color-dorado);"></i>
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>
                    </span>
                    <?php if (isset($_SESSION['usuario_rol'])): ?>
                        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                            <span style="background: rgba(212,175,55,0.2); color: var(--color-dorado); padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-top: 4px;">ADMIN</span>
                        <?php else: ?>
                            <span style="background: rgba(40,167,69,0.15); color: #28a745; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-top: 4px;">RECEPCIÓN</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <a href="crear.php" class="btn-nav btn-nav-primary">
                    <i class="fas fa-plus"></i> Nueva Historia
                </a>
                <?php if (esAdmin()): ?>
                    <a href="admin/index.php" class="btn-nav btn-nav-secondary" style="background: rgba(212,175,55,0.15); color: var(--color-dorado); border-color: rgba(212,175,55,0.4);">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </nav>
        </div>
    </header>

    <!-- Alerta de Comunicado Simple -->
    <?php if ($mostrarComunicado): ?>
    <div id="alert-comunicado" class="gold-alert-container">
        <div class="gold-alert-content">
            <div class="gold-alert-header">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($comunicado_titulo); ?>
            </div>
            <div class="gold-alert-body">
                <?php echo htmlspecialchars($comunicado_mensaje); ?>
            </div>
            <div class="gold-alert-footer">
                <button onclick="cerrarAlertaGold()" class="btn-gold-alert">OK</button>
            </div>
        </div>
    </div>

    <style>
        .gold-alert-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: 90%;
            max-width: 450px;
            background: #1a1a1a;
            border: 2px solid var(--color-dorado);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            animation: slideDownAlert 0.4s cubic-bezier(0.17, 0.84, 0.44, 1);
        }
        .gold-alert-content {
            padding: 20px;
        }
        .gold-alert-header {
            color: var(--color-dorado);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gold-alert-body {
            color: #fff;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .gold-alert-footer {
            display: flex;
            justify-content: flex-end;
        }
        .btn-gold-alert {
            background: var(--color-dorado);
            color: #000;
            border: none;
            padding: 8px 30px;
            border-radius: 4px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-gold-alert:hover {
            background: var(--color-dorado-claro);
            transform: scale(1.05);
        }
        @keyframes slideDownAlert {
            from { top: -100px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }
        .fade-out-alert {
            opacity: 0;
            top: -100px;
            transition: all 0.4s ease;
        }
    </style>

    <script>
        function cerrarAlertaGold() {
            const alert = document.getElementById('alert-comunicado');
            alert.classList.add('fade-out-alert');
            setTimeout(() => {
                alert.remove();
            }, 400);
        }
    </script>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="main-container">
        <!-- Título de la página -->
        <h1 class="page-title">Historias Clínicas</h1>
        
        <!-- Alertas -->
        <?php echo mostrarAlerta(); ?>
        
        <!-- Estadísticas -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <a href="dashboard.php" class="stat-card <?php echo !$verCitas && !$verArchivados ? 'stat-card-active' : ''; ?>" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: var(--color-dorado);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" style="color: var(--color-dorado);"><?php echo $totalPacientesActivos; ?></div>
                <div class="stat-label">PACIENTES</div>
            </a>
            <a href="dashboard.php?ver=citas" class="stat-card <?php echo $verCitas ? 'stat-card-active' : ''; ?>" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: var(--color-dorado);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number" style="color: var(--color-dorado);"><?php echo $totalCitasProx; ?></div>
                <div class="stat-label">CITAS</div>
            </a>
            <a href="callcenter/index.php" class="stat-card" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon phone-wave-icon" style="color: #28a745; position: relative;">
                    <i class="fas fa-phone"></i>
                    <?php if ($totalCallCenter > 0): ?>
                        <span class="wave-ring wave-ring-1"></span>
                        <span class="wave-ring wave-ring-2"></span>
                        <span class="wave-ring wave-ring-3"></span>
                    <?php endif; ?>
                </div>
                <div class="stat-number" style="color: #28a745;"><?php echo $totalCallCenter; ?></div>
                <div class="stat-label">CALL CENTER</div>
            </a>
            <a href="dashboard.php?ver=archivados" class="stat-card <?php echo $verArchivados ? 'stat-card-active' : ''; ?>" style="text-decoration: none; cursor: pointer;">
                <div class="stat-icon" style="color: #dc3545;">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="stat-number" style="color: #dc3545;"><?php echo $totalArchivados; ?></div>
                <div class="stat-label">ARCHIVADOS</div>
            </a>
        </div>
        
        <!-- Barra de búsqueda -->
        <form action="" method="GET" class="search-container">
            <div class="search-select-wrapper">
                <select name="tipo_busqueda" class="search-select">
                    <option value="" <?php echo $tipoBusqueda === '' ? 'selected' : ''; ?>>Todos los campos</option>
                    <option value="numero_historia" <?php echo $tipoBusqueda === 'numero_historia' ? 'selected' : ''; ?>>N° Historia</option>
                    <option value="dni" <?php echo $tipoBusqueda === 'dni' ? 'selected' : ''; ?>>DNI</option>
                    <option value="nombre" <?php echo $tipoBusqueda === 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                    <option value="tratamiento" <?php echo $tipoBusqueda === 'tratamiento' ? 'selected' : ''; ?>>Tratamiento</option>
                </select>
            </div>
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                
                <!-- Input de texto normal -->
                <input type="text" name="buscar" id="search_input" class="search-input" 
                       placeholder="Ingrese su búsqueda..." 
                       autocomplete="off"
                       value="<?php echo $tipoBusqueda !== 'tratamiento' ? htmlspecialchars($busqueda) : ''; ?>"
                       <?php echo $tipoBusqueda === 'tratamiento' ? 'style="display:none;" disabled' : ''; ?>>
                
                <!-- Selector de tratamientos (solo visible cuando se elige 'tratamiento') -->
                <select name="buscar" id="search_select_tratamiento" class="search-input" 
                        autocomplete="off"
                        <?php echo $tipoBusqueda !== 'tratamiento' ? 'style="display:none;" disabled' : ''; ?>>
                    <option value="">Seleccione un tratamiento...</option>
                    <?php foreach ($todosTratamientos as $trat): ?>
                        <option value="<?php echo htmlspecialchars($trat['nombre']); ?>" 
                                <?php echo ($tipoBusqueda === 'tratamiento' && $busqueda === $trat['nombre']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($trat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-buscar">
                <i class="fas fa-search"></i> Buscar
            </button>
            <?php if ($busqueda): ?>
                <a href="dashboard.php" class="btn-buscar" style="background: var(--color-gris-oscuro);">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            <?php endif; ?>
        </form>

        <script>
            // Lógica para cambiar entre input de texto y selector de tratamientos
            // Colocado aquí para que funcione instantáneamente sin esperar a que cargue la tabla gigante
            (function() {
                const tipoBusquedaSelect = document.querySelector('select[name="tipo_busqueda"]');
                const searchInput = document.getElementById('search_input');
                const searchSelectTratamiento = document.getElementById('search_select_tratamiento');

                function toggleSearchInputs() {
                    if (!tipoBusquedaSelect || !searchInput || !searchSelectTratamiento) return;
                    
                    if (tipoBusquedaSelect.value === 'tratamiento') {
                        searchInput.style.display = 'none';
                        searchInput.disabled = true;
                        searchSelectTratamiento.style.display = 'block';
                        searchSelectTratamiento.disabled = false;
                    } else {
                        searchInput.style.display = 'block';
                        searchInput.disabled = false;
                        searchSelectTratamiento.style.display = 'none';
                        searchSelectTratamiento.disabled = true;
                    }
                }

                if (tipoBusquedaSelect) {
                    tipoBusquedaSelect.addEventListener('change', toggleSearchInputs);
                    // Ejecutar inmediatamente por si ya viene seleccionado por PHP o caché
                    toggleSearchInputs();
                }
            })();
        </script>
        
        <!-- Tabla de pacientes -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas <?php echo $verCitas ? 'fa-calendar-check' : ($verArchivados ? 'fa-archive' : 'fa-clipboard-list'); ?>"></i> 
                    <?php 
                        if ($verCitas) echo "Citas Próximas";
                        elseif ($verArchivados) echo "Historias Archivadas";
                        else echo "Listado de Historias Clínicas";
                    ?>
                </h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span class="text-gray">
                        <?php echo number_format($totalRegistros); ?> registro(s) &mdash;
                        página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                    </span>
                    <?php if ($verArchivados || $verCitas): ?>
                        <a href="dashboard.php" class="btn-nav btn-nav-secondary" style="padding: 8px 12px; font-size: 0.85rem;">
                            <i class="fas fa-arrow-left"></i> Volver a Activos
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Historia</th>
                            <th>DNI</th>
                            <th>Paciente</th>
                            <th>Celular</th>
                            <th>Doctor</th>
                            <th>Próxima Cita</th>
                            <th>Tratamientos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pacientes)): ?>
                            <tr>
                                <td colspan="9" class="table-empty">
                                    <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                    No se encontraron historias clínicas
                                    <?php if ($busqueda): ?>
                                        para "<?php echo htmlspecialchars($busqueda); ?>"
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pacientes as $paciente): ?>
                                <?php
                                    // Los tratamientos vienen ya incluidos en la consulta principal (GROUP_CONCAT)
                                    $tratamientosPaciente = $paciente['tratamientos'];
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-gold"><?php echo htmlspecialchars($paciente['numero_historia']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($paciente['dni']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['nombres']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['celular']); ?></td>
                                    <td>
                                        <?php if ($paciente['doctor_nombre']): ?>
                                            <span class="badge badge-doctor">
                                                <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($paciente['doctor_nombre']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($paciente['fecha_ultima_cita']): ?>
                                            <?php echo date('d/m/Y', strtotime($paciente['fecha_ultima_cita'])); ?>
                                        <?php else: ?>
                                            <span class="text-gray">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($tratamientosPaciente)): ?>
                                            <?php foreach (array_slice($tratamientosPaciente, 0, 2) as $trat): ?>
                                                <span class="badge badge-tratamiento"><?php echo htmlspecialchars($trat); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($tratamientosPaciente) > 2): ?>
                                                <span class="badge badge-tratamiento">+<?php echo count($tratamientosPaciente) - 2; ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acciones">
                                            <a href="ver.php?id=<?php echo $paciente['id']; ?>" 
                                               class="btn-accion btn-ver" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?php echo $paciente['id']; ?>" 
                                               class="btn-accion btn-editar" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($verArchivados): ?>
                                                <button type="button" class="btn-accion btn-ver" 
                                                        style="background: #27ae60;"
                                                        title="Restaurar"
                                                        onclick="confirmarAccion(<?php echo $paciente['id']; ?>, '<?php echo htmlspecialchars(addslashes($paciente['nombres'])); ?>', 'restaurar')">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                                <?php if (esAdmin()): ?>
                                                    <button type="button" class="btn-accion btn-eliminar" 
                                                            style="background: rgba(220, 53, 69, 0.2); color: #dc3545;"
                                                            title="Eliminar permanentemente"
                                                            onclick="confirmarAccion(<?php echo $paciente['id']; ?>, '<?php echo htmlspecialchars(addslashes($paciente['nombres'])); ?>', 'eliminar')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button type="button" class="btn-accion btn-eliminar" 
                                                        style="background: rgba(220, 53, 69, 0.2); color: #dc3545;"
                                                        title="Archivar"
                                                        onclick="confirmarAccion(<?php echo $paciente['id']; ?>, '<?php echo htmlspecialchars(addslashes($paciente['nombres'])); ?>', 'archivar')">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div style="display:flex; justify-content:center; align-items:center; gap:8px; padding: 16px 0;">
                <?php if ($paginaActual > 1): ?>
                    <a href="<?php echo htmlspecialchars(urlPagina(1, $busqueda, $tipoBusqueda, $verCitas, $verArchivados)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;" title="Primera página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars(urlPagina($paginaActual - 1, $busqueda, $tipoBusqueda, $verCitas, $verArchivados)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;">
                        <i class="fas fa-angle-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <?php
                $inicio = max(1, $paginaActual - 2);
                $fin    = min($totalPaginas, $paginaActual + 2);
                for ($p = $inicio; $p <= $fin; $p++):
                ?>
                    <a href="<?php echo htmlspecialchars(urlPagina($p, $busqueda, $tipoBusqueda, $verCitas, $verArchivados)); ?>"
                       class="btn-nav <?php echo $p === $paginaActual ? 'btn-nav-primary' : 'btn-nav-secondary'; ?>"
                       style="padding:6px 12px; min-width:36px; text-align:center;">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="<?php echo htmlspecialchars(urlPagina($paginaActual + 1, $busqueda, $tipoBusqueda, $verCitas, $verArchivados)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;">
                        Siguiente <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars(urlPagina($totalPaginas, $busqueda, $tipoBusqueda, $verCitas, $verArchivados)); ?>" class="btn-nav btn-nav-secondary" style="padding:6px 12px;" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Sistema de Historias Clínicas
        </p>
    </footer>
    
    <!-- Modal de confirmación (Archivar/Restaurar) -->
    <div class="modal-overlay" id="modalAccion">
        <div class="modal">
            <div class="modal-icon" id="modalIcon">
                <i class="fas fa-archive"></i>
            </div>
            <h3 class="modal-title" id="modalTitle">¿Archivar Historia Clínica?</h3>
            <p class="modal-text" id="modalTexto">
                Esta acción moverá la historia al archivo.
            </p>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <a href="#" id="btnConfirmarAccion" class="btn">
                    <i class="fas fa-check"></i> Confirmar
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function confirmarAccion(id, nombre, tipo) {
            const modal = document.getElementById('modalAccion');
            const titulo = document.getElementById('modalTitle');
            const texto = document.getElementById('modalTexto');
            const btn = document.getElementById('btnConfirmarAccion');
            const icon = document.getElementById('modalIcon');
            
            if (tipo === 'archivar') {
                titulo.innerText = '¿Archivar Historia Clínica?';
                texto.innerHTML = '¿Está seguro de archivar la historia clínica de <strong>' + nombre + '</strong>?<br>Se podrá restaurar más tarde.';
                btn.href = 'archivar.php?id=' + id;
                btn.className = 'btn';
                btn.style.backgroundColor = '#dc3545';
                icon.innerHTML = '<i class="fas fa-archive"></i>';
                icon.style.color = '#dc3545';
            } else if (tipo === 'restaurar') {
                titulo.innerText = '¿Restaurar Historia Clínica?';
                texto.innerHTML = '¿Desea restaurar la historia clínica de <strong>' + nombre + '</strong> al listado activo?';
                btn.href = 'restaurar.php?id=' + id;
                btn.className = 'btn';
                btn.style.backgroundColor = '#27ae60';
                icon.innerHTML = '<i class="fas fa-trash-restore"></i>';
                icon.style.color = '#27ae60';
            } else if (tipo === 'eliminar') {
                titulo.innerText = '¿ELIMINAR Historia Clínica?';
                texto.innerHTML = '<span style="color:#dc3545;font-weight:bold;">¡ADVERTENCIA!</span> Esta acción borrará todas las imágenes, tratamientos y datos de <strong>' + nombre + '</strong> permanentemente.';
                btn.href = 'eliminar.php?id=' + id;
                btn.className = 'btn';
                btn.style.backgroundColor = '#dc3545';
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                icon.style.color = '#dc3545';
            }
            
            modal.classList.add('active');
        }
        
        function cerrarModal() {
            document.getElementById('modalAccion').classList.remove('active');
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalAccion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
        
        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });
        
        // Lógica del buscador movida arriba del listado para mayor rapidez
    </script>
</body>
</html>
