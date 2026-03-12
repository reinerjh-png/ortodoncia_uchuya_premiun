<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarSesion();
requiereAdmin();
require_once '../includes/functions.php';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $nombre = sanitizar($_POST['nombre'] ?? '');
        $especialidad = sanitizar($_POST['especialidad'] ?? 'Odontología General');
        
        if (!empty($nombre)) {
            $stmt = $pdo->prepare("INSERT INTO doctores (nombre, especialidad, estado) VALUES (?, ?, 1)");
            $stmt->execute([$nombre, $especialidad]);
            registrarActividad($pdo, 'Agregar Doctor', 'Agregó al doctor: ' . $nombre);
            setMensaje('Doctor agregado exitosamente', 'success');
        } else {
            setMensaje('El nombre del doctor es obligatorio', 'error');
        }
    }
    
    if ($accion === 'activar') {
        $id = intval($_POST['doctor_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE doctores SET estado = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $pdo->prepare("SELECT nombre FROM doctores WHERE id = ?");
        $doc->execute([$id]);
        $docData = $doc->fetch();
        registrarActividad($pdo, 'Activar Doctor', 'Activó al doctor: ' . ($docData['nombre'] ?? 'ID ' . $id));
        setMensaje('Doctor activado exitosamente', 'success');
    }
    
    if ($accion === 'desactivar') {
        $id = intval($_POST['doctor_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE doctores SET estado = 0 WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $pdo->prepare("SELECT nombre FROM doctores WHERE id = ?");
        $doc->execute([$id]);
        $docData = $doc->fetch();
        registrarActividad($pdo, 'Desactivar Doctor', 'Desactivó al doctor: ' . ($docData['nombre'] ?? 'ID ' . $id));
        setMensaje('Doctor desactivado exitosamente', 'success');
    }
    
    if ($accion === 'eliminar') {
        $id = intval($_POST['doctor_id'] ?? 0);
        $check = $pdo->prepare("SELECT COUNT(*) FROM pacientes WHERE doctor_id = ?");
        $check->execute([$id]);
        $pacientesAsignados = $check->fetchColumn();
        
        if ($pacientesAsignados > 0) {
            setMensaje('No se puede eliminar: el doctor tiene ' . $pacientesAsignados . ' paciente(s) asignado(s). Desasigne los pacientes primero o desactive al doctor.', 'error');
        } else {
            $doc = $pdo->prepare("SELECT nombre FROM doctores WHERE id = ?");
            $doc->execute([$id]);
            $docData = $doc->fetch();
            $stmt = $pdo->prepare("DELETE FROM doctores WHERE id = ?");
            $stmt->execute([$id]);
            registrarActividad($pdo, 'Eliminar Doctor', 'Eliminó al doctor: ' . ($docData['nombre'] ?? 'ID ' . $id));
            setMensaje('Doctor eliminado exitosamente', 'success');
        }
    }
    
    header('Location: doctores.php');
    exit;
}

$doctores = $pdo->query("SELECT d.*, 
    (SELECT COUNT(*) FROM pacientes WHERE doctor_id = d.id AND estado = 1) as total_pacientes
    FROM doctores d ORDER BY d.estado DESC, d.nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Doctores - Panel Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Gestión de Doctores</span>
            </a>
            <nav class="header-nav">
                <a href="index.php" class="btn-nav btn-nav-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Gestión de Doctores</h1>
        <?php echo mostrarAlerta(); ?>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Nuevo Doctor</h2>
            </div>
            <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="accion" value="agregar">
                <div style="flex: 1; min-width: 250px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Nombre completo *</label>
                    <input type="text" name="nombre" placeholder="Ej: Fernando Uchuya" required
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Especialidad</label>
                    <input type="text" name="especialidad" placeholder="Odontología General" value="Odontología General"
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <button type="submit" style="padding: 12px 30px; background: linear-gradient(135deg, var(--color-dorado), var(--color-dorado-oscuro)); color: #000; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-md"></i> Listado de Doctores</h2>
                <span class="text-gray"><?php echo count($doctores); ?> doctor(es)</span>
            </div>
            <div class="table-container">
                <table class="table" style="min-width: 600px;">
                    <thead>
                        <tr><th>ID</th><th>Nombre</th><th>Especialidad</th><th>Pacientes</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($doctores)): ?>
                            <tr><td colspan="6" class="table-empty">No hay doctores registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($doctores as $doc): ?>
                                <tr style="<?php echo $doc['estado'] == 0 ? 'opacity: 0.5;' : ''; ?>">
                                    <td><strong class="text-gold"><?php echo $doc['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($doc['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['especialidad']); ?></td>
                                    <td><span class="badge badge-tratamiento"><?php echo $doc['total_pacientes']; ?></span></td>
                                    <td>
                                        <?php if ($doc['estado'] == 1): ?>
                                            <span style="color: #28a745; font-weight: 600;"><i class="fas fa-check-circle"></i> Activo</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545; font-weight: 600;"><i class="fas fa-times-circle"></i> Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acciones">
                                            <?php if ($doc['estado'] == 1): ?>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar a Dr. <?php echo htmlspecialchars($doc['nombre']); ?>?');">
                                                    <input type="hidden" name="accion" value="desactivar">
                                                    <input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>">
                                                    <button type="submit" class="btn-accion" style="background: rgba(255,193,7,0.2); color: #ffc107;" title="Desactivar"><i class="fas fa-ban"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="accion" value="activar">
                                                    <input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>">
                                                    <button type="submit" class="btn-accion" style="background: rgba(40,167,69,0.2); color: #28a745;" title="Activar"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿ELIMINAR permanentemente a Dr. <?php echo htmlspecialchars($doc['nombre']); ?>? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>">
                                                <button type="submit" class="btn-accion btn-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p class="footer-text">© <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Panel de Administración</p>
    </footer>
</body>
</html>
