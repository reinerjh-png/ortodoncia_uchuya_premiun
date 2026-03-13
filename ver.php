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

// Obtener imágenes del paciente
$imagenesPaciente = obtenerImagenesPaciente($pdo, $id);

// Obtener nombre del doctor
$doctorNombre = '';
if ($paciente['doctor_id']) {
    $stmtDoctor = $pdo->prepare("SELECT nombre FROM doctores WHERE id = ?");
    $stmtDoctor->execute([$paciente['doctor_id']]);
    $doctor = $stmtDoctor->fetch();
    $doctorNombre = $doctor ? $doctor['nombre'] : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Historia Clínica - Clínica Dental Premium Uchuya</title>
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
                <a href="editar.php?id=<?php echo $id; ?>" class="btn-nav btn-nav-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="dashboard.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Inicio
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <h1 class="page-title">Detalle de Historia Clínica</h1>
        
        <div class="card">
            <!-- Encabezado del paciente -->
            <div class="detalle-header">
                <div class="detalle-numero">
                    <span class="detalle-label">N° Historia</span>
                    <span class="detalle-valor-grande"><?php echo htmlspecialchars($paciente['numero_historia']); ?></span>
                </div>
                <div class="detalle-estado">
                    <span class="badge badge-activo">
                        <i class="fas fa-check-circle"></i> Activo
                    </span>
                </div>
            </div>
            
            <!-- Información del paciente -->
            <div class="detalle-grid">
                <!-- Datos personales -->
                <div class="detalle-seccion">
                    <h3 class="detalle-seccion-titulo">
                        <i class="fas fa-user"></i> Datos Personales
                    </h3>
                    <div class="detalle-contenido">
                        <div class="detalle-item">
                            <span class="detalle-label">Nombres Completos</span>
                            <span class="detalle-valor"><?php echo htmlspecialchars($paciente['nombres']); ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">DNI</span>
                            <span class="detalle-valor"><?php echo htmlspecialchars($paciente['dni']); ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Género</span>
                            <span class="detalle-valor"><?php echo $paciente['genero'] ?: '-'; ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Edad</span>
                            <span class="detalle-valor"><?php echo $paciente['edad'] ? $paciente['edad'] . ' años' : '-'; ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Celular</span>
                            <span class="detalle-valor"><?php echo $paciente['celular'] ? htmlspecialchars($paciente['celular']) : '-'; ?></span>
                        </div>
                        <div class="detalle-item full-width">
                            <span class="detalle-label">Dirección</span>
                            <span class="detalle-valor"><?php echo $paciente['direccion'] ? htmlspecialchars($paciente['direccion']) : '-'; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Información clínica -->
                <div class="detalle-seccion">
                    <h3 class="detalle-seccion-titulo">
                        <i class="fas fa-stethoscope"></i> Información Clínica
                    </h3>
                    <div class="detalle-contenido">
                        <div class="detalle-item">
                            <span class="detalle-label">Fecha de Registro</span>
                            <span class="detalle-valor">
                                <?php echo $paciente['fecha_registro'] ? date('d/m/Y', strtotime($paciente['fecha_registro'])) : '-'; ?>
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Última Cita</span>
                            <span class="detalle-valor">
                                <?php echo $paciente['fecha_ultima_cita'] ? date('d/m/Y', strtotime($paciente['fecha_ultima_cita'])) : '-'; ?>
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-label">Hora de Cita</span>
                            <span class="detalle-valor">
                                <?php echo !empty($paciente['hora_cita']) ? date('g:i A', strtotime($paciente['hora_cita'])) : '-'; ?>
                            </span>
                        </div>
                        <div class="detalle-item full-width">
                            <span class="detalle-label">Doctor Asignado</span>
                            <span class="detalle-valor">
                                <?php if ($doctorNombre): ?>
                                    <span class="badge badge-doctor">
                                        <i class="fas fa-user-md"></i> Dr. <?php echo htmlspecialchars($doctorNombre); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray">Sin asignar</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tratamientos -->
            <div class="detalle-seccion-full">
                <h3 class="detalle-seccion-titulo">
                    <i class="fas fa-tooth"></i> Tratamientos
                </h3>
                <div class="detalle-tratamientos">
                    <?php if (!empty($tratamientosPaciente)): ?>
                        <?php foreach ($tratamientosPaciente as $tratamiento): ?>
                            <span class="badge badge-tratamiento-grande">
                                <i class="fas fa-check"></i>
                                <?php echo htmlspecialchars($tratamiento['nombre']); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-gray">No hay tratamientos registrados</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Observaciones -->
            <div class="detalle-seccion-full">
                <h3 class="detalle-seccion-titulo">
                    <i class="fas fa-clipboard"></i> Observaciones
                </h3>
                <div class="detalle-observaciones">
                    <?php if ($paciente['observaciones']): ?>
                        <?php echo nl2br(htmlspecialchars($paciente['observaciones'])); ?>
                    <?php else: ?>
                        <span class="text-gray">No hay observaciones registradas</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Galería de Imágenes -->
            <div class="detalle-seccion-full">
                <h3 class="detalle-seccion-titulo">
                    <i class="fas fa-images"></i> Galería de Imágenes
                </h3>
                <?php if (!empty($imagenesPaciente)): ?>
                    <div class="galeria-grid">
                        <?php foreach ($imagenesPaciente as $index => $img): ?>
                            <div class="galeria-item" onclick="abrirLightbox(<?php echo $index; ?>)">
                                <img src="uploads/pacientes/<?php echo htmlspecialchars($paciente['numero_historia']); ?>/<?php echo htmlspecialchars($img['nombre_archivo']); ?>" 
                                     alt="<?php echo htmlspecialchars($img['nombre_original']); ?>">
                                <div class="galeria-item-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="detalle-observaciones">
                        <span class="text-gray">No hay imágenes registradas</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Botones de acción -->
            <div class="detalle-acciones">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
                <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Historia
                </a>
            </div>
        </div>
    </main>
    
    <!-- Lightbox -->
    <?php if (!empty($imagenesPaciente)): ?>
    <div class="lightbox-overlay" id="lightboxOverlay">
        <button class="lightbox-close" id="lightboxClose">
            <i class="fas fa-times"></i>
        </button>
        <?php if (count($imagenesPaciente) > 1): ?>
            <button class="lightbox-nav lightbox-prev" id="lightboxPrev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-nav lightbox-next" id="lightboxNext">
                <i class="fas fa-chevron-right"></i>
            </button>
        <?php endif; ?>
        <div class="lightbox-content">
            <img src="" alt="" id="lightboxImg">
            <div class="lightbox-caption" id="lightboxCaption"></div>
            <div class="lightbox-counter" id="lightboxCounter"></div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="footer">
        <p class="footer-text">
            © <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Sistema de Historias Clínicas
        </p>
    </footer>

<?php if (!empty($imagenesPaciente)): ?>
<script>
// Datos de imágenes para el lightbox
const imagenes = [
    <?php foreach ($imagenesPaciente as $img): ?>
    {
        url: "uploads/pacientes/<?php echo htmlspecialchars($paciente['numero_historia']); ?>/<?php echo htmlspecialchars($img['nombre_archivo']); ?>",
        nombre: "<?php echo htmlspecialchars($img['nombre_original']); ?>"
    },
    <?php endforeach; ?>
];

let currentIndex = 0;
const overlay = document.getElementById('lightboxOverlay');
const lightboxImg = document.getElementById('lightboxImg');
const lightboxCaption = document.getElementById('lightboxCaption');
const lightboxCounter = document.getElementById('lightboxCounter');

function abrirLightbox(index) {
    currentIndex = index;
    mostrarImagen();
    overlay.classList.add('lightbox-active');
    document.body.style.overflow = 'hidden';
}

function cerrarLightbox() {
    overlay.classList.remove('lightbox-active');
    document.body.style.overflow = '';
}

function mostrarImagen() {
    const img = imagenes[currentIndex];
    lightboxImg.src = img.url;
    lightboxImg.alt = img.nombre;
    lightboxCaption.textContent = img.nombre;
    lightboxCounter.textContent = (currentIndex + 1) + ' / ' + imagenes.length;
}

function siguienteImagen() {
    currentIndex = (currentIndex + 1) % imagenes.length;
    mostrarImagen();
}

function anteriorImagen() {
    currentIndex = (currentIndex - 1 + imagenes.length) % imagenes.length;
    mostrarImagen();
}

// Event listeners
document.getElementById('lightboxClose').addEventListener('click', cerrarLightbox);

<?php if (count($imagenesPaciente) > 1): ?>
document.getElementById('lightboxPrev').addEventListener('click', anteriorImagen);
document.getElementById('lightboxNext').addEventListener('click', siguienteImagen);
<?php endif; ?>

// Cerrar con Escape o clic fuera
overlay.addEventListener('click', (e) => {
    if (e.target === overlay) cerrarLightbox();
});

document.addEventListener('keydown', (e) => {
    if (!overlay.classList.contains('lightbox-active')) return;
    if (e.key === 'Escape') cerrarLightbox();
    if (e.key === 'ArrowLeft') anteriorImagen();
    if (e.key === 'ArrowRight') siguienteImagen();
});
</script>
<?php endif; ?>
</body>
</html>
