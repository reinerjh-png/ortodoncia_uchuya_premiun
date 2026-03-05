<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
verificarSesion();
requiereAdmin();
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $usuario = sanitizar($_POST['usuario'] ?? '');
        $nombre = sanitizar($_POST['nombre'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'recepcionista';
        
        $errores = [];
        if (empty($usuario)) $errores[] = 'El usuario es obligatorio';
        if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
        if (empty($password) || strlen($password) < 4) $errores[] = 'La contraseña debe tener al menos 4 caracteres';
        if (!in_array($rol, ['admin', 'recepcionista'])) $errores[] = 'Rol inválido';
        
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check->execute([$usuario]);
        if ($check->fetch()) $errores[] = 'El nombre de usuario ya existe';
        
        if (empty($errores)) {
            $hash = hash('sha256', $password);
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password_hash, nombre_completo, rol, estado) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$usuario, $hash, $nombre, $rol]);
            registrarActividad($pdo, 'Agregar Usuario', 'Agregó al usuario: ' . $usuario . ' (' . $nombre . ') con rol ' . $rol);
            setMensaje('Usuario creado exitosamente', 'success');
        } else {
            setMensaje(implode('. ', $errores), 'error');
        }
    }
    
    if ($accion === 'activar') {
        $id = intval($_POST['user_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $u = $pdo->prepare("SELECT usuario FROM usuarios WHERE id = ?");
        $u->execute([$id]);
        $uData = $u->fetch();
        registrarActividad($pdo, 'Activar Usuario', 'Activó al usuario: ' . ($uData['usuario'] ?? 'ID ' . $id));
        setMensaje('Usuario activado exitosamente', 'success');
    }
    
    if ($accion === 'desactivar') {
        $id = intval($_POST['user_id'] ?? 0);
        if ($id == $_SESSION['usuario_id']) {
            setMensaje('No puede desactivar su propio usuario', 'error');
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = 0 WHERE id = ?");
            $stmt->execute([$id]);
            $u = $pdo->prepare("SELECT usuario FROM usuarios WHERE id = ?");
            $u->execute([$id]);
            $uData = $u->fetch();
            registrarActividad($pdo, 'Desactivar Usuario', 'Desactivó al usuario: ' . ($uData['usuario'] ?? 'ID ' . $id));
            setMensaje('Usuario desactivado exitosamente', 'success');
        }
    }
    
    if ($accion === 'eliminar') {
        $id = intval($_POST['user_id'] ?? 0);
        if ($id == $_SESSION['usuario_id']) {
            setMensaje('No puede eliminar su propio usuario', 'error');
        } else {
            $u = $pdo->prepare("SELECT usuario, nombre_completo FROM usuarios WHERE id = ?");
            $u->execute([$id]);
            $uData = $u->fetch();
            $pdo->prepare("DELETE FROM actividad_log WHERE usuario_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            registrarActividad($pdo, 'Eliminar Usuario', 'Eliminó al usuario: ' . ($uData['usuario'] ?? 'ID ' . $id) . ' (' . ($uData['nombre_completo'] ?? '') . ')');
            setMensaje('Usuario eliminado exitosamente', 'success');
        }
    }
    
    if ($accion === 'editar_usuario') {
        $id = intval($_POST['user_id'] ?? 0);
        $nombre = sanitizar($_POST['nombre'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        
        $errores = [];
        if (empty($nombre)) $errores[] = 'El nombre es obligatorio';
        
        if (empty($errores)) {
            $u = $pdo->prepare("SELECT usuario FROM usuarios WHERE id = ?");
            $u->execute([$id]);
            $uData = $u->fetch();
            $usuarioTexto = $uData['usuario'] ?? 'ID ' . $id;

            if (!empty($newPassword)) {
                if (strlen($newPassword) < 4) {
                    setMensaje('La contraseña debe tener al menos 4 caracteres', 'error');
                } else {
                    $hash = hash('sha256', $newPassword);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$nombre, $hash, $id]);
                    registrarActividad($pdo, 'Editar Usuario', 'Actualizó nombre y contraseña del usuario: ' . $usuarioTexto);
                    setMensaje('Usuario y contraseña actualizados exitosamente', 'success');
                }
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ? WHERE id = ?");
                $stmt->execute([$nombre, $id]);
                registrarActividad($pdo, 'Editar Usuario', 'Actualizó nombre del usuario: ' . $usuarioTexto);
                setMensaje('Nombre de usuario actualizado exitosamente', 'success');
            }
        } else {
            setMensaje(implode('. ', $errores), 'error');
        }
    }
    
    header('Location: usuarios.php');
    exit;
}

$usuarios = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM actividad_log WHERE usuario_id = u.id) as total_acciones,
    (SELECT MAX(created_at) FROM actividad_log WHERE usuario_id = u.id) as ultima_actividad
    FROM usuarios u ORDER BY u.estado DESC, u.rol ASC, u.nombre_completo ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Panel Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .modal-admin-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; }
        .modal-admin-overlay.active { display: flex; }
        .modal-admin { background: #1a1a1a; border: 2px solid var(--color-dorado); border-radius: 12px; padding: 30px; width: 90%; max-width: 400px; text-align: center; }
        .modal-admin h3 { color: var(--color-dorado); margin-bottom: 20px; font-family: var(--font-titulo); }
        .modal-admin input { width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; margin-bottom: 15px; box-sizing: border-box; }
        .modal-admin input:focus { outline: none; border-color: var(--color-dorado); }
        .modal-admin-buttons { display: flex; gap: 10px; justify-content: center; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="header-logo">
                <i class="fas fa-crown" style="color: var(--color-dorado); font-size: 1.8rem;"></i>
                <span class="header-logo-text">Gestión de Usuarios</span>
            </a>
            <nav class="header-nav">
                <a href="index.php" class="btn-nav btn-nav-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Gestión de Usuarios</h1>
        <?php echo mostrarAlerta(); ?>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-plus"></i> Agregar Nuevo Usuario</h2>
            </div>
            <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="accion" value="agregar">
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Usuario *</label>
                    <input type="text" name="usuario" placeholder="Ej: recepcion1" required
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Nombre completo *</label>
                    <input type="text" name="nombre" placeholder="Ej: María López" required
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Contraseña *</label>
                    <input type="password" name="password" placeholder="Mín. 4 caracteres" required minlength="4"
                           style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                </div>
                <div style="min-width: 160px;">
                    <label style="display: block; color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Rol</label>
                    <select name="rol" style="width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #fff; font-size: 0.95rem; box-sizing: border-box;">
                        <option value="recepcionista" style="background: #1a1a1a; color: #fff;">Recepcionista</option>
                        <option value="admin" style="background: #1a1a1a; color: #fff;">Administrador</option>
                    </select>
                </div>
                <button type="submit" style="padding: 12px 30px; background: linear-gradient(135deg, var(--color-dorado), var(--color-dorado-oscuro)); color: #000; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">
                    <i class="fas fa-plus"></i> Crear
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users-cog"></i> Usuarios del Sistema</h2>
                <span class="text-gray"><?php echo count($usuarios); ?> usuario(s)</span>
            </div>
            <div class="table-container">
                <table class="table" style="min-width: 700px;">
                    <thead>
                        <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Estado</th><th>Acciones Log</th><th>Última Actividad</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usr): ?>
                            <tr style="<?php echo $usr['estado'] == 0 ? 'opacity: 0.5;' : ''; ?>">
                                <td><strong class="text-gold"><?php echo $usr['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($usr['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usr['nombre_completo']); ?></td>
                                <td>
                                    <?php if ($usr['rol'] === 'admin'): ?>
                                        <span style="background: rgba(212,175,55,0.15); color: var(--color-dorado); padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Admin</span>
                                    <?php else: ?>
                                        <span style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Recepcionista</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usr['estado'] == 1): ?>
                                        <span style="color: #28a745; font-weight: 600;"><i class="fas fa-check-circle"></i> Activo</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545; font-weight: 600;"><i class="fas fa-times-circle"></i> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-tratamiento"><?php echo $usr['total_acciones']; ?></span></td>
                                <td style="font-size: 0.85rem; color: var(--color-gris);">
                                    <?php echo $usr['ultima_actividad'] ? date('d/m/Y H:i', strtotime($usr['ultima_actividad'])) : 'Sin actividad'; ?>
                                </td>
                                <td>
                                    <div class="acciones">
                                        <button type="button" class="btn-accion" style="background: rgba(23,162,184,0.2); color: #17a2b8;" title="Editar usuario" 
                                                onclick="abrirModalEditar(<?php echo $usr['id']; ?>, '<?php echo htmlspecialchars($usr['usuario']); ?>', '<?php echo htmlspecialchars(addslashes($usr['nombre_completo'])); ?>')">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <?php if ($usr['id'] != $_SESSION['usuario_id']): ?>
                                            <?php if ($usr['estado'] == 1): ?>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar al usuario <?php echo htmlspecialchars($usr['usuario']); ?>?');">
                                                    <input type="hidden" name="accion" value="desactivar">
                                                    <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                                    <button type="submit" class="btn-accion" style="background: rgba(255,193,7,0.2); color: #ffc107;" title="Desactivar"><i class="fas fa-ban"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="accion" value="activar">
                                                    <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                                    <button type="submit" class="btn-accion" style="background: rgba(40,167,69,0.2); color: #28a745;" title="Activar"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿ELIMINAR permanentemente al usuario <?php echo htmlspecialchars($usr['usuario']); ?>?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                                <button type="submit" class="btn-accion btn-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: var(--color-gris); font-size: 0.75rem; font-style: italic;">Tú</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal-admin-overlay" id="modalEditar">
        <div class="modal-admin">
            <h3><i class="fas fa-user-edit"></i> Editar Usuario</h3>
            <p style="color: var(--color-gris); margin-bottom: 15px; font-size: 0.9rem;">Usuario: <strong id="modalEditarUser" style="color: var(--color-dorado);"></strong></p>
            <form method="POST">
                <input type="hidden" name="accion" value="editar_usuario">
                <input type="hidden" name="user_id" id="modalEditarId">
                
                <div style="text-align: left; margin-bottom: 5px;">
                    <label style="color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase;">Nombre completo *</label>
                </div>
                <input type="text" name="nombre" id="modalEditarNombre" required>
                
                <div style="text-align: left; margin-bottom: 5px; margin-top: 10px;">
                    <label style="color: var(--color-dorado); font-size: 0.8rem; text-transform: uppercase;">Nueva Contraseña</label>
                </div>
                <input type="password" name="new_password" placeholder="Dejar en blanco para no cambiar">
                
                <div class="modal-admin-buttons" style="margin-top: 20px;">
                    <button type="button" onclick="cerrarModalEditar()" style="padding: 10px 25px; background: var(--color-gris-oscuro); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancelar</button>
                    <button type="submit" style="padding: 10px 25px; background: linear-gradient(135deg, var(--color-dorado), var(--color-dorado-oscuro)); color: #000; border: none; border-radius: 8px; cursor: pointer; font-weight: 700;"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p class="footer-text">© <?php echo date('Y'); ?> <span>Clínica Dental Premium Uchuya</span> - Panel de Administración</p>
    </footer>

    <script>
        function abrirModalEditar(id, usuario, nombre) {
            document.getElementById('modalEditarId').value = id;
            document.getElementById('modalEditarUser').textContent = usuario;
            document.getElementById('modalEditarNombre').value = nombre;
            document.getElementById('modalEditar').classList.add('active');
        }
        function cerrarModalEditar() {
            document.getElementById('modalEditar').classList.remove('active');
        }
        document.getElementById('modalEditar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalEditar();
        });
    </script>
</body>
</html>
