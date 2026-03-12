<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarSesion();
require_once '../includes/functions.php';
require_once '../includes/callcenter_functions.php';

// Obtener llamadas del día (se asignan automáticamente si no existen)
$llamadas = obtenerLlamadasDelDia($pdo);

// Separar pendientes y pospuestas
$pendientes = array_filter($llamadas, function($l) { return $l['llamada_estado'] === 'pendiente'; });
$pospuestas = array_filter($llamadas, function($l) { return $l['llamada_estado'] === 'pospuesta'; });

// Obtener estadísticas
$stats = obtenerEstadisticasCallCenter($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Center - Clínica Dental Premium Uchuya de Meilyng</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
    <style>
        /* Estilos específicos del Call Center */
        .callcenter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
        }

        .callcenter-title {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: var(--font-titulo);
            font-size: 1.8rem;
            color: var(--color-dorado);
        }

        .callcenter-subtitle {
            color: var(--color-gris);
            font-size: 1rem;
            margin-top: 5px;
        }

        .progress-container {
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--color-blanco);
            font-weight: 500;
        }

        .progress-bar-container {
            width: 100%;
            height: 30px;
            background: var(--color-negro);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            border-radius: 15px;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .patient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .patient-card {
            background: linear-gradient(145deg, var(--color-negro-claro), var(--color-negro-suave));
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radio-borde);
            padding: 20px;
            transition: var(--transicion);
        }

        .patient-card:hover {
            border-color: var(--color-dorado);
            transform: translateY(-3px);
            box-shadow: var(--sombra-suave);
        }

        .patient-card.pospuesta {
            border-color: rgba(255, 193, 7, 0.4);
        }

        .patient-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .patient-header i {
            color: var(--color-dorado);
            font-size: 1.3rem;
        }

        .patient-name {
            font-family: var(--font-titulo);
            font-size: 1.1rem;
            color: var(--color-blanco);
            font-weight: 600;
        }

        .patient-historia {
            color: var(--color-dorado);
            font-size: 1.05rem;
            font-weight: 500;
        }

        .patient-phone {
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid var(--color-dorado);
            border-radius: 10px;
            padding: 10px;
            margin: 12px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .phone-label {
            color: var(--color-dorado);
            font-size: 1.4rem;
            margin-bottom: 0;
        }

        .phone-number {
            font-family: var(--font-titulo);
            font-size: 1.8rem;
            color: var(--color-dorado);
            font-weight: bold;
            letter-spacing: 2px;
        }

        .patient-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .info-item {
            color: var(--color-gris);
        }

        .info-label {
            font-weight: 500;
            color: var(--color-blanco);
        }

        .patient-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: var(--transicion);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-completada {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-completada:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.5);
        }

        .btn-posponer {
            background: linear-gradient(135deg, #ffc107, #ffca28);
            color: var(--color-negro);
        }

        .btn-posponer:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.5);
        }

        .btn-rechazar {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-rechazar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.5);
        }

        .section-title {
            font-family: var(--font-titulo);
            font-size: 1.5rem;
            color: var(--color-dorado);
            margin: 30px 0 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--color-gris);
            font-style: italic;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="../dashboard.php" class="header-logo">
                <i class="fas fa-phone" style="color: #28a745; font-size: 1.8rem;"></i>
                <span class="header-logo-text">Call Center - Sede Tingo Maria</span>
            </a>
            <nav class="header-nav">
                <a href="reporte.php" class="btn-nav btn-nav-primary">
                    <i class="fas fa-chart-bar"></i> Ver Reporte
                </a>
                <a href="../dashboard.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <!-- Encabezado del Call Center -->
        <div class="callcenter-header">
            <div>
                <div class="callcenter-title">
                    <i class="fas fa-phone-volume"></i>
                    Call Center - <?php echo date('d/m/Y'); ?>
                </div>
                <?php $restantes = $stats['pendientes'] + $stats['pospuestas']; ?>
                <div class="callcenter-subtitle">
                    Llamadas pendientes del día: <?php echo $restantes; ?> de <?php echo $stats['total']; ?>
                </div>
                <?php $procesadas = $stats['completadas'] + $stats['rechazadas']; ?>
            </div>
        </div>

        <!-- Barra de Progreso -->
        <div class="progress-container">
            <div class="progress-label">
                <span><i class="fas fa-tasks"></i> Progreso del Día</span>
                <span><strong style="color: var(--color-dorado);"><?php echo $procesadas; ?></strong> llamadas realizadas de <strong style="color: var(--color-dorado);"><?php echo $stats['total']; ?></strong></span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?php echo $stats['progreso']; ?>%;">
                    <?php if ($stats['progreso'] > 10): ?>
                        <?php echo $procesadas; ?> de <?php echo $stats['total']; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php echo mostrarAlerta(); ?>

        <!-- Pacientes Pendientes -->
        <?php if (!empty($pendientes)): ?>
            <h2 class="section-title">
                <i class="fas fa-users"></i>
                Pacientes por Llamar (<?php echo count($pendientes); ?>)
            </h2>
            <div class="patient-grid">
                <?php foreach ($pendientes as $paciente): ?>
                    <div class="patient-card" id="patient-card-<?php echo $paciente['llamada_id']; ?>">
                        <div class="patient-header">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <div class="patient-name"><?php echo htmlspecialchars($paciente['nombres']); ?></div>
                                <div class="patient-historia">N° Historia: <?php echo htmlspecialchars($paciente['numero_historia']); ?></div>
                            </div>
                        </div>

                        <div class="patient-phone">
                            <div class="phone-label">📞</div>
                            <div class="phone-number"><?php echo htmlspecialchars($paciente['celular']); ?></div>
                        </div>

                        <div class="patient-info">
                            <div class="info-item">
                                <div class="info-label">Género:</div>
                                <?php echo htmlspecialchars($paciente['genero'] ?? '-'); ?>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Edad:</div>
                                <?php echo htmlspecialchars($paciente['edad'] ?? '-'); ?> años
                            </div>
                        </div>

                        <div class="info-item" style="margin-bottom: 10px;">
                            <div class="info-label">Dirección:</div>
                            <?php echo htmlspecialchars($paciente['direccion'] ?? 'No especificada'); ?>
                        </div>

                        <div class="patient-actions" style="flex-direction: column;">
                            <button class="btn-action btn-completada" style="width: 100%;" onclick="marcarCompletada(<?php echo $paciente['llamada_id']; ?>, '<?php echo htmlspecialchars($paciente['nombres']); ?>')">
                                <i class="fas fa-check"></i> Completada
                            </button>
                            <div style="display: flex; gap: 8px; width: 100%;">
                                <button class="btn-action btn-posponer" onclick="marcarPospuesta(<?php echo $paciente['llamada_id']; ?>, '<?php echo htmlspecialchars($paciente['nombres']); ?>')">
                                    <i class="fas fa-clock"></i> Después
                                </button>
                                <button class="btn-action btn-rechazar" onclick="marcarRechazada(<?php echo $paciente['llamada_id']; ?>, '<?php echo htmlspecialchars($paciente['nombres']); ?>')">
                                    <i class="fas fa-times"></i> Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>¡Excelente trabajo! No hay llamadas pendientes por el momento.</p>
            </div>
        <?php endif; ?>

        <!-- Llamar Después -->
        <?php if (!empty($pospuestas)): ?>
            <h2 class="section-title" style="color: #ffc107;">
                <i class="fas fa-history"></i>
                Llamar Después (<?php echo count($pospuestas); ?>)
            </h2>
            <div class="patient-grid">
                <?php foreach ($pospuestas as $paciente): ?>
                    <div class="patient-card pospuesta" id="patient-card-<?php echo $paciente['llamada_id']; ?>">
                        <div class="patient-header">
                            <i class="fas fa-user-clock"></i>
                            <div>
                                <div class="patient-name"><?php echo htmlspecialchars($paciente['nombres']); ?></div>
                                <div class="patient-historia">N° Historia: <?php echo htmlspecialchars($paciente['numero_historia']); ?></div>
                            </div>
                        </div>

                        <div class="patient-phone">
                            <div class="phone-label">📞</div>
                            <div class="phone-number"><?php echo htmlspecialchars($paciente['celular']); ?></div>
                        </div>

                        <div class="patient-info">
                            <div class="info-item">
                                <div class="info-label">Género:</div>
                                <?php echo htmlspecialchars($paciente['genero'] ?? '-'); ?>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Edad:</div>
                                <?php echo htmlspecialchars($paciente['edad'] ?? '-'); ?> años
                            </div>
                        </div>

                        <div class="info-item" style="margin-bottom: 10px;">
                            <div class="info-label">Dirección:</div>
                            <?php echo htmlspecialchars($paciente['direccion'] ?? 'No especificada'); ?>
                        </div>

                        <div class="patient-actions">
                            <button class="btn-action btn-completada" onclick="marcarCompletada(<?php echo $paciente['llamada_id']; ?>, '<?php echo htmlspecialchars($paciente['nombres']); ?>')">
                                <i class="fas fa-check"></i> Completada
                            </button>
                            <button class="btn-action btn-rechazar" onclick="marcarRechazada(<?php echo $paciente['llamada_id']; ?>, '<?php echo htmlspecialchars($paciente['nombres']); ?>')">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Call Center
        </p>
    </footer>

    <!-- Modal Completada -->
    <div class="modal-overlay" id="modalCompletada">
        <div class="modal">
            <div class="modal-icon" style="color: #28a745;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="modal-title">¿Desea agendar una cita?</h3>
            <p class="modal-text" id="textoCompletada"></p>
            <div style="margin: 20px 0;">
                <label class="form-label" style="text-align: left; display: block; margin-bottom: 10px;">Fecha de la cita (opcional):</label>
                <input type="date" id="fechaCita" class="form-control" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCompletada()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmarCompletada()">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Rechazada -->
    <div class="modal-overlay" id="modalRechazada">
        <div class="modal">
            <div class="modal-icon" style="color: #dc3545;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">¿Rechazar esta llamada?</h3>
            <p class="modal-text" id="textoRechazada"></p>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalRechazada()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarRechazada()">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Pospuesta (Llamar Después) -->
    <div class="modal-overlay" id="modalPospuesta">
        <div class="modal">
            <div class="modal-icon" style="color: #ffc107;">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="modal-title">¿Llamar luego?</h3>
            <p class="modal-text" id="textoPospuesta"></p>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalPospuesta()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-posponer" style="background: var(--color-alerta); color: var(--color-negro);" onclick="confirmarPospuesta()">
                    <i class="fas fa-check"></i> Aceptar
                </button>
            </div>
        </div>
    </div>

    <script>
        let llamadaActual = null;
        let pacienteActual = '';

        function marcarCompletada(llamadaId, nombrePaciente) {
            llamadaActual = llamadaId;
            pacienteActual = nombrePaciente;
            document.getElementById('textoCompletada').innerHTML = 
                'Se marcará como completada la llamada de <strong>' + nombrePaciente + '</strong>.<br>Si desea, puede agendar una cita.';
            document.getElementById('modalCompletada').classList.add('active');
        }

        function cerrarModalCompletada() {
            document.getElementById('modalCompletada').classList.remove('active');
            document.getElementById('fechaCita').value = '';
            llamadaActual = null;
        }

        function confirmarCompletada() {
            const fechaCita = document.getElementById('fechaCita').value;
            const agendarCita = fechaCita ? 1 : 0;

            fetch('procesar_llamada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=completada&llamada_id=${llamadaActual}&agendar_cita=${agendarCita}&fecha_cita=${fechaCita}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('patient-card-' + llamadaActual);
                    if (card) card.remove();
                    cerrarModalCompletada();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la llamada');
            });
        }

        function marcarPospuesta(llamadaId, nombrePaciente) {
            llamadaActual = llamadaId;
            pacienteActual = nombrePaciente;
            document.getElementById('textoPospuesta').innerHTML = 
                '¿Deseas llamar luego al paciente <strong>' + nombrePaciente + '</strong>?';
            document.getElementById('modalPospuesta').classList.add('active');
        }

        function cerrarModalPospuesta() {
            document.getElementById('modalPospuesta').classList.remove('active');
            llamadaActual = null;
        }

        function confirmarPospuesta() {
            fetch('procesar_llamada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=posponer&llamada_id=${llamadaActual}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cerrarModalPospuesta();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la llamada');
            });
        }

        function marcarRechazada(llamadaId, nombrePaciente) {
            llamadaActual = llamadaId;
            pacienteActual = nombrePaciente;
            document.getElementById('textoRechazada').innerHTML = 
                '¿Está seguro que desea rechazar la llamada de <strong>' + nombrePaciente + '</strong>?<br>Esta acción marcará que el paciente no está interesado.';
            document.getElementById('modalRechazada').classList.add('active');
        }

        function cerrarModalRechazada() {
            document.getElementById('modalRechazada').classList.remove('active');
            llamadaActual = null;
        }

        function confirmarRechazada() {
            fetch('procesar_llamada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=rechazar&llamada_id=${llamadaActual}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('patient-card-' + llamadaActual);
                    if (card) card.remove();
                    cerrarModalRechazada();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la llamada');
            });
        }

        // Cerrar modales con clic fuera
        document.getElementById('modalCompletada').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalCompletada();
        });

        document.getElementById('modalRechazada').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalRechazada();
        });

        document.getElementById('modalPospuesta').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalPospuesta();
        });

        // Cerrar modales con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalCompletada();
                cerrarModalRechazada();
                cerrarModalPospuesta();
            }
        });
    </script>
</body>
</html>
