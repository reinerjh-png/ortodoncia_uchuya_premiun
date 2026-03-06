# 👑 Clínica Dental Premium Uchuya - Sistema de Historias Clínicas

Sistema profesional de gestión de historias clínicas desarrollado para la **Clínica Odontológica Uchuya Premium**. El sistema presenta un diseño elegante y moderno basado en la identidad corporativa de la clínica (Dorado y Negro).

## ✨ Funcionalidades y Características Principales

- 🔐 **Autenticación y Roles**: Sistema de login seguro. Acceso basado en roles (Administradores y Usuarios estándar), protegiendo características sensibles en el panel de control.
- 📋 **Gestión de Historias Clínicas**: Registro completo, edición, y visualización detallada de datos y consultas de pacientes.
- �️ **Galería Odontológica**: Subida, gestión y visualización de imágenes (radiografías o fotos clínicas) asociadas a la historia de cada paciente.
- �📁 **Sistema de Archivado (Soft-Delete)**: Permite archivar historias de pacientes y restaurarlas cuando sea necesario, manteniendo la integridad de los datos.
- 🦷 **Catálogo de Tratamientos y Doctores**: Administración de catálogo de tratamientos dentales y asignación de múltiples tratamientos e información de doctores a los pacientes de forma dinámica.
- 📞 **Módulo de Call Center**: Panel dedicado para seguimiento de llamadas, registro de contactos, gestión de prospectos y reporte de métricas del call center.
- ⚙️ **Panel de Administración**: Gestión de usuarios internos del sistema, catálogo de doctores y registro detallado de la actividad (logs) dentro de la aplicación.
- 📢 **Comunicados del Sistema**: Alerta dorada no intrusiva integrada en el dashboard para emitir avisos importantes a todo el personal.
- 📱 **Interfaz de Usuario (UX/UI)**: Diseño responsivo para usarse fluidamente tanto en computadoras de escritorio como en tablets y móviles, con la capacidad de deslizar al final de la vista de listas largas.

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.x
- **Base de Datos**: MySQL (Manejo de consultas preparadas mediante PDO y base de datos relacional robusta).
- **Frontend**: HTML5, CSS3 (Custom CSS sin frameworks externos pesados), JavaScript (Peticiones asíncronas / manipulación del DOM).
- **Iconografía**: FontAwesome 6
- **Diseño**: Temática _Dark Premium_ con acentos dorados (`#d4af37`), enfocado en la alta legibilidad y aspecto profesional corporativo.

## � Estructura del Proyecto

- `/admin/`: Módulos exclusivos de administración (Usuarios, Doctores, Registros de Actividad).
- `/assets/`: Recursos visuales estáticos y logotipos.
- `/callcenter/`: Módulo dedicado a la captación, registro y gestión de llamadas y prospectos.
- `/css/`: Archivos de hojas de estilos agrupados.
- `/database/`: Archivos SQL de estructura y configuraciones de seguridad (`.htaccess`).
- `/includes/`: Lógica de negocios central, conexión a base de datos (`config.php`) y utilidades comunes (`functions.php`).
- `/uploads/`: Directorio protegido donde se almacenan las imágenes subidas a las historias clínicas.

## �🚀 Instalación y Configuración

1. **Clonar el repositorio**:

   ```bash
   git clone https://github.com/tu-usuario/clinica_dental_tingo.git
   ```

2. **Configurar la base de datos**:
   - Crea una base de datos en tu servidor MySQL (ej. `clinica_uchuya`).
   - Importa el script principal de la estructura de tablas (por ejemplo `database/script_tablas.sql` si existiera, o el provisto nativamente).
   - Edita el archivo `includes/config.php` configurando tus credenciales de conexión seguras.

3. **Configurar Servidor Web**:
   - Se requiere un servidor Apache con soporte para archivos `.htaccess` (módulo `mod_rewrite` activado) para la protección de accesos no autorizados a ciertas carpetas.
   - Asegúrate de que las carpetas dentro de `/uploads/` posean los permisos de escritura necesarios (ej. `chmod 775`).

---

© 2026 **Ortodoncia Uchuya Premium - Meilyng** - Excelencia en su sonrisa.
