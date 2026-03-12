<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
verificarSesion();
require_once 'includes/functions.php';

// Obtener doctores y tratamientos para los selectores
$doctores = obtenerDoctores($pdo);
$tratamientos = obtenerTratamientos($pdo);

// Obtener siguiente número de historia
$stmtMax = $pdo->query("SELECT MAX(CAST(numero_historia AS UNSIGNED)) FROM pacientes");
$siguienteNumero = intval($stmtMax->fetchColumn()) + 1;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = [
            'numero_historia' => sanitizar($_POST['numero_historia']),
            'dni' => sanitizar($_POST['dni']),
            'nombres' => sanitizar($_POST['nombres']),
            'genero' => sanitizar($_POST['genero']),
            'celular' => sanitizar($_POST['celular']),
            'edad' => intval($_POST['edad']),
            'direccion' => sanitizar($_POST['direccion']),
            'fecha_registro' => $_POST['fecha_registro'] ?: null,
            'doctor_id' => $_POST['doctor_id'] ?: null,
            'fecha_ultima_cita' => $_POST['fecha_ultima_cita'] ?: null,
            'hora_cita' => null,
            'observaciones' => sanitizar($_POST['observaciones'])
        ];
        
        // Convertir hora 12h a formato 24h TIME
        if (!empty($_POST['hora_cita_hora']) && !empty($_POST['hora_cita_ampm'])) {
            $h = intval($_POST['hora_cita_hora']);
            $m = isset($_POST['hora_cita_min']) ? str_pad(intval($_POST['hora_cita_min']), 2, '0', STR_PAD_LEFT) : '00';
            $ampm = $_POST['hora_cita_ampm'];
            if ($ampm === 'PM' && $h < 12) $h += 12;
            if ($ampm === 'AM' && $h == 12) $h = 0;
            $datos['hora_cita'] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $m . ':00';
        }
        
        $tratamientosSeleccionados = $_POST['tratamientos'] ?? [];
        
        // Validaciones
        $errores = [];
        if (empty($datos['numero_historia'])) $errores[] = "El número de historia es obligatorio";
        // DNI ya no es obligatorio - puede ser NULL en la base de datos
        if (empty($datos['nombres'])) $errores[] = "Los nombres son obligatorios";
        
        // Verificar si el número de historia ya existe
        $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE numero_historia = ? AND estado = 1");
        $stmt->execute([$datos['numero_historia']]);
        if ($stmt->fetch()) {
            $errores[] = "El número de historia ya existe";
        }
        
        if (empty($errores)) {
            crearPaciente($pdo, $datos, $tratamientosSeleccionados);
            registrarActividad($pdo, 'Crear Paciente', 'Creó historia clínica: ' . $datos['numero_historia'] . ' - ' . $datos['nombres']);
            setMensaje('Historia clínica ' . htmlspecialchars($datos['numero_historia']) . ' creada exitosamente', 'success');
            
            // Redirigir según la acción seleccionada
            if (isset($_POST['accion']) && $_POST['accion'] === 'guardar_y_crear') {
                header('Location: crear.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $mensajeError = implode('<br>', $errores);
        }
        
    } catch (Exception $e) {
        $mensajeError = 'Error al crear la historia clínica: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Historia Clínica - Clínica Dental Premium Uchuya</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/fontawesome/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Clínica Odontológica Uchuya Premium de Meilyng - Tingo María</span>
            </a>
            <nav class="header-nav">
                <a href="dashboard.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <h1 class="page-title">Nueva Historia Clínica</h1>
        
        <!-- Mensaje de error si existe -->
        <?php if (isset($mensajeError)): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $mensajeError; ?>
            </div>
        <?php endif; ?>
        
        <div class="card form-container">
            <form action="" method="POST" id="formHistoria">
                <input type="hidden" name="accion" id="accionForm" value="guardar">
                <div class="form-grid">
                    <!-- Número de Historia -->
                    <div class="form-group">
                        <label class="form-label">
                            Número de Historia <span class="required">*</span>
                        </label>
                        <input type="text" name="numero_historia" class="form-control" 
                               placeholder="Solo números" required
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               value="<?php echo isset($_POST['numero_historia']) ? htmlspecialchars($_POST['numero_historia']) : $siguienteNumero; ?>">
                    </div>
                    
                    <!-- DNI -->
                    <div class="form-group">
                        <label class="form-label">
                            DNI
                        </label>
                        <input type="text" name="dni" class="form-control" 
                               placeholder="8 dígitos" maxlength="8"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                               value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">
                    </div>
                    
                    <!-- Nombres -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            Nombres (Apellidos y Nombres) <span class="required">*</span>
                        </label>
                        <input type="text" name="nombres" class="form-control" 
                               placeholder="Apellido Paterno, Apellido Materno, Nombres" required
                               value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
                    </div>

                    <!-- Género -->
                    <div class="form-group">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-control">
                            <option value="">-- Seleccionar --</option>
                            <option value="Masculino" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Femenino" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                        </select>
                    </div>
                    
                    <!-- Celular -->
                    <div class="form-group">
                        <label class="form-label">Celular</label>
                        <input type="tel" name="celular" class="form-control" 
                               placeholder="9 dígitos" maxlength="9"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                               value="<?php echo isset($_POST['celular']) ? htmlspecialchars($_POST['celular']) : ''; ?>">
                    </div>
                    
                    <!-- Edad -->
                    <div class="form-group">
                        <label class="form-label">Edad</label>
                        <input type="number" name="edad" class="form-control" 
                               placeholder="Edad del paciente" min="0" max="150"
                               value="<?php echo isset($_POST['edad']) ? htmlspecialchars($_POST['edad']) : ''; ?>">
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group full-width">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" 
                               placeholder="Dirección completa"
                               value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Fecha de Registro
                        </label>
                        <input type="date" name="fecha_registro" class="form-control" 
                               value="<?php echo isset($_POST['fecha_registro']) ? $_POST['fecha_registro'] : date('Y-m-d'); ?>">
                    </div>
                    
                    <!-- Doctor -->
                    <div class="form-group">
                        <label class="form-label">Doctor</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">-- Seleccionar Doctor --</option>
                            <?php foreach ($doctores as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>"
                                    <?php echo (isset($_POST['doctor_id']) && $_POST['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                    Dr. <?php echo htmlspecialchars($doctor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Fecha de proxima cita -->
                    <div class="form-group">
                        <label class="form-label">Agendar Cita</label>
                        <input type="date" name="fecha_ultima_cita" class="form-control"
                               value="<?php echo isset($_POST['fecha_ultima_cita']) ? $_POST['fecha_ultima_cita'] : ''; ?>">
                    </div>
                    
                    <!-- Hora de cita -->
                    <div class="form-group">
                        <label class="form-label">Hora de Cita</label>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <select name="hora_cita_hora" class="form-control" style="flex: 1; min-width: 0;">
                                <option value="">Hora</option>
                                <?php for ($h = 1; $h <= 12; $h++): ?>
                                    <option value="<?php echo $h; ?>" <?php echo (isset($_POST['hora_cita_hora']) && $_POST['hora_cita_hora'] == $h) ? 'selected' : ''; ?>><?php echo $h; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span style="color: var(--color-gris); font-weight: 700;">:</span>
                            <select name="hora_cita_min" class="form-control" style="flex: 1; min-width: 0;">
                                <?php foreach (['00','15','30','45'] as $min): ?>
                                    <option value="<?php echo $min; ?>" <?php echo (isset($_POST['hora_cita_min']) && $_POST['hora_cita_min'] == $min) ? 'selected' : ''; ?>><?php echo $min; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="hora_cita_ampm" class="form-control" style="flex: 1; min-width: 0;">
                                <option value="">--</option>
                                <option value="AM" <?php echo (isset($_POST['hora_cita_ampm']) && $_POST['hora_cita_ampm'] === 'AM') ? 'selected' : ''; ?>>AM</option>
                                <option value="PM" <?php echo (isset($_POST['hora_cita_ampm']) && $_POST['hora_cita_ampm'] === 'PM') ? 'selected' : ''; ?>>PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Tratamientos -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            Tratamientos <span class="text-gray" style="font-weight: normal; text-transform: none;">(Seleccione uno o más)</span>
                        </label>
                        <div class="tratamientos-container">
                            <?php foreach ($tratamientos as $tratamiento): ?>
                                <div class="tratamiento-item">
                                    <input type="checkbox" 
                                           name="tratamientos[]" 
                                           value="<?php echo $tratamiento['id']; ?>"
                                           id="trat_<?php echo $tratamiento['id']; ?>"
                                           <?php echo (isset($_POST['tratamientos']) && in_array($tratamiento['id'], $_POST['tratamientos'])) ? 'checked' : ''; ?>>
                                    <label for="trat_<?php echo $tratamiento['id']; ?>">
                                        <?php echo htmlspecialchars($tratamiento['nombre']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="form-group full-width">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" 
                                  placeholder="Notas adicionales sobre el paciente..."><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="form-buttons">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success" onclick="document.getElementById('accionForm').value='guardar_y_crear'">
                        <i class="fas fa-plus-circle"></i> Guardar y Crear Otra
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Historia
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Sistema de Historias Clínicas
        </p>
    </footer>
</body>
</html>
