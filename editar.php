<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
verificarSesion();
require_once 'includes/functions.php';

// Verificar si se recibió un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setMensaje('ID de paciente inválido', 'error');
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener paciente
$paciente = obtenerPacientePorId($pdo, $id);

if (!$paciente) {
    setMensaje('Paciente no encontrado', 'error');
    header('Location: dashboard.php');
    exit;
}

// Obtener tratamientos del paciente
$tratamientosPaciente = obtenerTratamientosPaciente($pdo, $id);
$tratamientosIds = array_column($tratamientosPaciente, 'id');

// Obtener imágenes del paciente
$imagenesPaciente = obtenerImagenesPaciente($pdo, $id);

// Obtener doctores y tratamientos para los selectores
$doctores = obtenerDoctores($pdo);
$tratamientos = obtenerTratamientos($pdo);

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
        if (empty($datos['nombres'])) $errores[] = "Los nombres son obligatorios";
        
        // Verificar si el número de historia ya existe (excluyendo el actual)
        $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE numero_historia = ? AND id != ? AND estado = 1");
        $stmt->execute([$datos['numero_historia'], $id]);
        if ($stmt->fetch()) {
            $errores[] = "El número de historia ya existe en otro paciente";
        }
        
        if (empty($errores)) {
            actualizarPaciente($pdo, $id, $datos, $tratamientosSeleccionados);
            registrarActividad($pdo, 'Editar Paciente', 'Editó historia clínica: ' . $datos['numero_historia'] . ' - ' . $datos['nombres']);
            setMensaje('Historia clínica actualizada exitosamente', 'success');
            header('Location: dashboard.php');
            exit;
        } else {
            $mensajeError = implode('<br>', $errores);
        }
        
    } catch (Exception $e) {
        $mensajeError = 'Error al actualizar la historia clínica: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Historia Clínica - Clínica Dental Premium Uchuya</title>
    <link rel="stylesheet" href="css/styles.css">
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
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <h1 class="page-title">Editar Historia Clínica</h1>
        
        <!-- Mensaje de error si existe -->
        <?php if (isset($mensajeError)): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $mensajeError; ?>
            </div>
        <?php endif; ?>
        
        <div class="card form-container">
            <form action="" method="POST" id="formHistoria">
                <div class="form-grid">
                    <!-- Número de Historia -->
                    <div class="form-group">
                        <label class="form-label">
                            Número de Historia <span class="required">*</span>
                        </label>
                        <input type="text" name="numero_historia" class="form-control" 
                               placeholder="Solo números" required
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               value="<?php echo htmlspecialchars($paciente['numero_historia']); ?>">
                    </div>
                    
                    <!-- DNI -->
                    <div class="form-group">
                        <label class="form-label">
                            DNI
                        </label>
                        <input type="text" name="dni" class="form-control" 
                               placeholder="8 dígitos" maxlength="8"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                               value="<?php echo htmlspecialchars($paciente['dni']); ?>">
                    </div>
                    
                    <!-- Nombres -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            Nombres (Apellidos y Nombres) <span class="required">*</span>
                        </label>
                        <input type="text" name="nombres" class="form-control" 
                               placeholder="Apellido Paterno, Apellido Materno, Nombres" required
                               value="<?php echo htmlspecialchars($paciente['nombres']); ?>">
                    </div>

                    <!-- Género -->
                    <div class="form-group">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-control">
                            <option value="">-- Seleccionar --</option>
                            <option value="Masculino" <?php echo ($paciente['genero'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Femenino" <?php echo ($paciente['genero'] === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                        </select>
                    </div>
                    
                    <!-- Celular -->
                    <div class="form-group">
                        <label class="form-label">Celular</label>
                        <input type="tel" name="celular" class="form-control" 
                               placeholder="9 dígitos" maxlength="9"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                               value="<?php echo htmlspecialchars($paciente['celular']); ?>">
                    </div>
                    
                    <!-- Edad -->
                    <div class="form-group">
                        <label class="form-label">Edad</label>
                        <input type="number" name="edad" class="form-control" 
                               placeholder="Edad del paciente" min="0" max="150"
                               value="<?php echo $paciente['edad']; ?>">
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group full-width">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" 
                               placeholder="Dirección completa"
                               value="<?php echo htmlspecialchars($paciente['direccion']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Fecha de Registro
                        </label>
                        <input type="date" name="fecha_registro" class="form-control" 
                               value="<?php echo $paciente['fecha_registro']; ?>">
                    </div>
                    
                    <!-- Doctor -->
                    <div class="form-group">
                        <label class="form-label">Doctor</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">-- Seleccionar Doctor --</option>
                            <?php foreach ($doctores as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>"
                                    <?php echo ($paciente['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                    Dr. <?php echo htmlspecialchars($doctor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Fecha de proxima cita -->
                    <div class="form-group">
                        <label class="form-label">Agendar Cita</label>
                        <input type="date" name="fecha_ultima_cita" class="form-control"
                               value="<?php echo $paciente['fecha_ultima_cita']; ?>">
                    </div>
                    
                    <!-- Hora de cita -->
                    <?php
                        // Descomponer hora existente a formato 12h
                        $hora_cita_h = '';
                        $hora_cita_m = '00';
                        $hora_cita_ampm = '';
                        if (!empty($paciente['hora_cita'])) {
                            $hc = intval(date('G', strtotime($paciente['hora_cita'])));
                            $hora_cita_m = date('i', strtotime($paciente['hora_cita']));
                            $hora_cita_ampm = ($hc >= 12) ? 'PM' : 'AM';
                            if ($hc == 0) $hora_cita_h = 12;
                            elseif ($hc > 12) $hora_cita_h = $hc - 12;
                            else $hora_cita_h = $hc;
                        }
                    ?>
                    <div class="form-group">
                        <label class="form-label">Hora de Cita</label>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <select name="hora_cita_hora" class="form-control" style="flex: 1; min-width: 0;">
                                <option value="">Hora</option>
                                <?php for ($h = 1; $h <= 12; $h++): ?>
                                    <option value="<?php echo $h; ?>" <?php echo ($hora_cita_h == $h) ? 'selected' : ''; ?>><?php echo $h; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span style="color: var(--color-gris); font-weight: 700;">:</span>
                            <select name="hora_cita_min" class="form-control" style="flex: 1; min-width: 0;">
                                <?php foreach (['00','15','30','45'] as $min): ?>
                                    <option value="<?php echo $min; ?>" <?php echo ($hora_cita_m == $min) ? 'selected' : ''; ?>><?php echo $min; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="hora_cita_ampm" class="form-control" style="flex: 1; min-width: 0;">
                                <option value="">--</option>
                                <option value="AM" <?php echo ($hora_cita_ampm === 'AM') ? 'selected' : ''; ?>>AM</option>
                                <option value="PM" <?php echo ($hora_cita_ampm === 'PM') ? 'selected' : ''; ?>>PM</option>
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
                                           <?php echo in_array($tratamiento['id'], $tratamientosIds) ? 'checked' : ''; ?>>
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
                                  placeholder="Notas adicionales sobre el paciente..."><?php echo htmlspecialchars($paciente['observaciones']); ?></textarea>
                    </div>
                </div>
                
                <!-- Sección de Imágenes -->
                <div class="imagenes-seccion">
                    <h3 class="imagenes-titulo">
                        <i class="fas fa-images"></i> Imágenes del Paciente
                    </h3>
                    
                    <!-- Zona de carga -->
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-cloud-upload-alt upload-zone-icon"></i>
                        <p class="upload-zone-text">Arrastra imágenes aquí o haz clic para seleccionar</p>
                        <p class="upload-zone-hint">JPG, PNG, GIF, WEBP — Máx. 5MB por imagen</p>
                        <input type="file" id="inputImagen" accept="image/jpeg,image/png,image/gif,image/webp" multiple style="display:none">
                    </div>
                    
                    <!-- Galería de imágenes existentes -->
                    <div class="imagenes-grid" id="imagenesGrid">
                        <?php foreach ($imagenesPaciente as $img): ?>
                            <div class="imagen-item" data-id="<?php echo $img['id']; ?>">
                                <img src="uploads/pacientes/<?php echo htmlspecialchars($paciente['numero_historia']); ?>/<?php echo htmlspecialchars($img['nombre_archivo']); ?>" 
                                     alt="<?php echo htmlspecialchars($img['nombre_original']); ?>">
                                <div class="imagen-overlay">
                                    <span class="imagen-nombre"><?php echo htmlspecialchars($img['nombre_original']); ?></span>
                                    <button type="button" class="btn-eliminar-img" onclick="eliminarImagen(<?php echo $img['id']; ?>, this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="form-buttons">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
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

<script>
// Upload de imágenes
const uploadZone = document.getElementById('uploadZone');
const inputImagen = document.getElementById('inputImagen');
const imagenesGrid = document.getElementById('imagenesGrid');
const pacienteId = <?php echo $id; ?>;

// Click en zona de upload
uploadZone.addEventListener('click', () => inputImagen.click());

// Drag & Drop
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('upload-zone-active');
});
uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('upload-zone-active');
});
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('upload-zone-active');
    const files = e.dataTransfer.files;
    subirArchivos(files);
});

// Input file change
inputImagen.addEventListener('change', () => {
    subirArchivos(inputImagen.files);
    inputImagen.value = '';
});

// Pegar imagen desde el portapapeles
document.addEventListener('paste', (e) => {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
            const blob = items[i].getAsFile();
            // Crear un nombre de archivo para el blob
            const file = new File([blob], `cortesia_${new Date().getTime()}.png`, { type: blob.type });
            subirImagen(file);
        }
    }
});

function subirArchivos(files) {
    for (let i = 0; i < files.length; i++) {
        subirImagen(files[i]);
    }
}

function subirImagen(file) {
    const formData = new FormData();
    formData.append('paciente_id', pacienteId);
    formData.append('imagen', file);
    
    // Mostrar loading
    const loadingEl = document.createElement('div');
    loadingEl.className = 'imagen-item imagen-loading';
    loadingEl.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin"></i></div>';
    imagenesGrid.prepend(loadingEl);
    
    fetch('upload_imagen.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        loadingEl.remove();
        if (data.success) {
            const img = data.imagen;
            const div = document.createElement('div');
            div.className = 'imagen-item';
            div.setAttribute('data-id', img.id);
            div.innerHTML = `
                <img src="${img.url}" alt="${img.nombre_original}">
                <div class="imagen-overlay">
                    <span class="imagen-nombre">${img.nombre_original}</span>
                    <button type="button" class="btn-eliminar-img" onclick="eliminarImagen(${img.id}, this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            imagenesGrid.prepend(div);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(() => {
        loadingEl.remove();
        alert('Error de conexión al subir la imagen');
    });
}

function eliminarImagen(imagenId, btn) {
    if (!confirm('¿Eliminar esta imagen?')) return;
    
    const formData = new FormData();
    formData.append('imagen_id', imagenId);
    
    fetch('eliminar_imagen.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.imagen-item');
            item.style.transition = 'opacity 0.3s, transform 0.3s';
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            setTimeout(() => item.remove(), 300);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(() => alert('Error de conexión al eliminar'));
}
</script>
</body>
</html>
