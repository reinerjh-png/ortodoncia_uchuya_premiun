# 👑 Clínica Dental Premium Uchuya - Sistema de Historias Clínicas

Sistema profesional de gestión de historias clínicas desarrollado para la **Clínica Odontológica Uchuya Premium**. El sistema presenta un diseño elegante y moderno basado en la identidad corporativa de la clínica (Dorado y Negro).

## ✨ Características Principales

- 🔐 **Sistema de Autenticación Seguro**: Protección con contraseña y bloqueo por múltiples intentos fallidos.
- 📋 **Gestión de Historias Clínicas**: Registro, edición y visualización detallada de pacientes.
- 📁 **Sistema de Archivado**: Permite archivar historias antiguas y restaurarlas cuando sea necesario.
- 📢 **Comunicados del Sistema**: Alerta dorada no intrusiva para avisos importantes al personal.
- 📞 **Módulo de Call Center**: Seguimiento de llamadas y gestión de prospectos.
- 📱 **Diseño Responsive**: Optimizado para su uso en computadoras y tablets.

## 🛠️ Tecnologías Utilizadas

- **Lenguaje**: PHP 8.x
- **Base de Datos**: MySQL (PDO para seguridad)
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript
- **Iconografía**: FontAwesome 6
- **Diseño**: Temática Dark Premium con acentos Dorados (`#d4af37`)

## 🚀 Instalación y Configuración

1. **Clonar el repositorio**:

   ```bash
   git clone https://github.com/tu-usuario/clinica_dental_.git
   ```

2. **Configurar la base de datos**:
   - Copia el contenido del archivo `clinica_uchuya_DBdefaul.sql` de la carpeta `database`, crea una base de datos y ejecuta el script en SQL.
   - Edita `includes/config.php` con tus credenciales de MySQL.

3. **Configurar Servidor Web**:
   - El sistema requiere Apache con soporte para archivos `.htaccess` (mod_rewrite activado).
   - Asegúrate de que la carpeta `uploads/` tenga permisos de escritura.

## 📁 Estructura del Proyecto

- `/assets`: Recursos visuales y logotipos.
- `/callcenter`: Módulo específico de gestión de llamadas.
- `/css`: Archivos de estilos globales.
- `/database`: Archivos de respaldo y seguridad de la base de datos.
- `/includes`: Lógica central, configuración y funciones compartidas.
- `/uploads`: Carpeta para las imágenes de los pacientes (ignorada por Git).

---

© 2026 **Clínica Dental Uchuya Premium de Mailyng** - Excelencia en su sonrisa.
