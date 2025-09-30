# CHASIDE - Test Vocacional para Moodle

<div align="center">

![CHASIDE Logo](https://img.shields.io/badge/CHASIDE-Vocational%20Test-blue?style=for-the-badge)

**Plugin para Moodle que implementa el test vocacional CHASIDE para orientación profesional**

[![Moodle](https://img.shields.io/badge/Moodle-4.1%2B-orange?style=flat-square)](https://moodle.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v3-green?style=flat-square)](LICENSE)
[![Release](https://img.shields.io/github/v/release/ISCOUTB/chaside?style=flat-square)](https://github.com/ISCOUTB/chaside/releases)

![Screenshot](docs/chaside_dashboard.png)

</div>

## 📋 ¿Qué es CHASIDE?

CHASIDE es un test vocacional científicamente validado que evalúa las preferencias e intereses profesionales de los estudiantes en **7 áreas vocacionales**:

| Área | Nombre Oficial | Descripción Oficial | Ejemplos de Carreras |
|------|----------------|--------------------|----------------------|
| **C** | Administrativa | Fortalezas en organización, liderazgo y manejo numérico. | Administración, Contabilidad, Gestión Pública |
| **H** | Humanidades/Sociales/Jurídicas | Interés por lenguaje, justicia y análisis social. | Derecho, Psicología, Sociología, Historia |
| **A** | Artística | Creatividad y sensibilidad estética para resolver problemas. | Diseño, Arte, Música, Arquitectura |
| **S** | Ciencias de la Salud | Orientación a servicio, precisión y cuidado de personas. | Medicina, Enfermería, Odontología, Trabajo Social |
| **I** | Enseñanzas Técnicas | Pensamiento técnico-analítico y planificación. | Ingeniería, Informática, Tecnología |
| **D** | Defensa y Seguridad | Disciplina, trabajo en equipo y perseverancia. | Fuerzas Armadas, Policía, Bomberos |
| **E** | Ciencias Experimentales | Observación rigurosa, método científico e investigación. | Biología, Química, Física, Investigación |



## ✨ Características Principales

- 🎯 **98 preguntas** cuidadosamente diseñadas
- 📱 **Interfaz responsive** - Funciona en móviles y tablets
- 🔄 **Navegación por páginas** - 10 preguntas por página
- 💾 **Guardado automático** - El progreso se guarda en cada página
- 📊 **Resultados visuales** - Gráficos de barras con puntuaciones
- 🔐 **Control de acceso** - Solo estudiantes pueden tomar el test
- 🌐 **Multiidioma** - Disponible en Español e Inglés
- 📈 **Dashboard para profesores** - Estadísticas y exportación de datos
- 🖼️ **Ejemplo de resultado:**

![Resultados ejemplo](docs/chaside_result_example.png)

## 🧩 Ejemplo de Uso de la API

```php
// Obtener resultados de un usuario
$results = block_chaside_get_user_results($userid, $courseid);
foreach ($results as $area => $score) {
   echo "Área $area: $score puntos\n";
}
```

## 🛠️ Troubleshooting y Preguntas Frecuentes

- **No se guardan las respuestas:** Verifica permisos de escritura en la base de datos y que el usuario tenga rol de estudiante.
- **No aparecen los resultados:** Asegúrate de que el test esté completado y que el bloque esté correctamente configurado en el curso.
- **Problemas de exportación:** Revisa que el servidor tenga permisos para escribir archivos y que la carpeta `export/` exista.
- **Error de caché en Moodle:** Ejecuta `php admin/cli/purge_caches.php` en el servidor o desde Docker si usas contenedores.

## 📊 Diagrama de Flujo de Experiencia de Usuario

```mermaid
flowchart TD
   A[Inicio] --> B{¿Estudiante?}
   B -- Sí --> C[Realiza test]
   B -- No --> D[Acceso a dashboard]
   C --> E[Guarda respuestas]
   E --> F[Calcula resultados]
   F --> G[Visualiza resultados]
   D --> H[Exporta datos]
   G --> I[Fin]
   H --> I[Fin]
```

## 🚀 Instalación Rápida

### Requisitos
- **Moodle**: 4.1 o superior
- **PHP**: 8.1 o superior
- **Rol**: Administrador de Moodle

### Pasos

1. **Descargar** la última versión desde [Releases](https://github.com/ISCOUTB/chaside/releases)

2. **Instalar** en Moodle:
   ```bash
   cd /path/to/moodle/blocks/
   unzip chaside-v1.2.1.zip
   ```

3. **Completar instalación** visitando: `Administración del sitio → Notificaciones`

4. **Añadir al curso**: El bloque estará disponible en la lista de bloques

## 📖 Cómo Usar

### 👨‍🎓 Para Estudiantes
1. Accede al curso donde esté el bloque CHASIDE
2. Haz clic en **"Realizar Test CHASIDE"**
3. Responde las preguntas navegando página por página
4. Tu progreso se guarda automáticamente
5. Al finalizar, revisa tus resultados y recomendaciones

### 👨‍🏫 Para Profesores
1. Añade el bloque a tu curso desde **"Activar edición"**
2. Visualiza estadísticas de participación
3. Exporta resultados en formato CSV
4. Revisa el progreso de tus estudiantes

### 👨‍💼 Para Administradores
- Gestiona permisos y configuraciones globales
- Accede a reportes del sistema
- Configura aspectos técnicos del plugin

## 🔧 Estructura del Proyecto

```
chaside/
├── 📄 README.md              # Documentación principal
├── 📝 CHANGELOG.md           # Historial de cambios
├── ⚙️  version.php            # Metadatos del plugin
├── 🎯 block_chaside.php      # Clase principal
├── 📋 view.php               # Vista del test
├── 📊 view_results.php       # Vista de resultados
├── 🛠️  manage.php            # Panel de administración
├── 📤 export.php             # Exportación de datos
├── 🗄️  db/                   # Esquemas de base de datos
├── 🌐 lang/                  # Archivos de idioma
├── 🔧 classes/               # Clases PHP del plugin
└── 📚 docs/                  # Documentación adicional
```

## 🛠️ Desarrollo

### Configuración del Entorno de Desarrollo

```bash
# Clonar el repositorio
git clone https://github.com/ISCOUTB/chaside.git
cd chaside

# Instalar en entorno de desarrollo Moodle
ln -s $(pwd) /path/to/moodle/blocks/chaside
```

### Validación Local

```bash
# Validar sintaxis PHP
find . -name "*.php" -exec php -l {} \;

# Verificar estructura del plugin
./scripts/check-structure.sh
```

### Releases Automáticos

Este proyecto utiliza **GitHub Actions** para crear releases automáticamente:

1. **Actualiza la versión** en `version.php`:
   ```php
   $plugin->release = '1.3.0';
   ```

2. **Actualiza el CHANGELOG.md** con los cambios

3. **Haz commit y push** a `main`

4. **GitHub Actions** creará automáticamente:
   - Tag de la versión
   - Release en GitHub
   - Archivo ZIP listo para descargar

📖 **Documentación completa**: [docs/GITHUB_ACTIONS.md](docs/GITHUB_ACTIONS.md)

## 🤝 Contribuir

1. **Fork** el proyecto
2. **Crea una rama** para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. **Commit** tus cambios (`git commit -m 'feat: agregar nueva funcionalidad'`)
4. **Push** a la rama (`git push origin feature/nueva-funcionalidad`)
5. **Abre un Pull Request**

### Versionado

Seguimos [Semantic Versioning](https://semver.org/):
- **MAJOR**: Cambios incompatibles (ej: 2.0.0)
- **MINOR**: Nueva funcionalidad compatible (ej: 1.3.0)
- **PATCH**: Correcciones de bugs (ej: 1.2.1)

## 🔧 Especificaciones Técnicas

### Base de Datos

El plugin crea la tabla `block_chaside_responses` con los siguientes campos:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `userid` | int | ID del usuario (referencia a mdl_user) |
| `courseid` | int | ID del curso |
| `blockid` | int | ID de la instancia del bloque |
| `q1` - `q98` | tinyint | Respuestas individuales (0=No, 1=Sí) |
| `score_c` - `score_e` | int | Puntuaciones por área (0-14) |
| `is_completed` | tinyint | Estado de completado (0/1) |
| `timecreated` | int | Timestamp de creación |
| `timemodified` | int | Timestamp de modificación |
| `timecompleted` | int | Timestamp de completado |

### Algoritmo de Puntuación

- **98 preguntas** divididas en **7 áreas** (14 preguntas por área)
- **Mapeo de áreas**:
  - Área C: Preguntas 1-14
  - Área H: Preguntas 15-28  
  - Área A: Preguntas 29-42
  - Área S: Preguntas 43-56
  - Área I: Preguntas 57-70
  - Área D: Preguntas 71-84
  - Área E: Preguntas 85-98
- **Cálculo**: Cada "Sí" suma 1 punto al área correspondiente
- **Máximo por área**: 14 puntos

### Permisos y Roles

| Rol | Realizar Test | Ver Reportes | Exportar Datos | Administrar |
|-----|---------------|--------------|----------------|-------------|
| **Estudiante** | ✅ | ❌ | ❌ | ❌ |
| **Profesor** | ✅ | ✅ | ✅ | ❌ |
| **Profesor Editor** | ✅ | ✅ | ✅ | ✅ |
| **Manager** | ✅ | ✅ | ✅ | ✅ |

## 📄 Licencia

Este proyecto está licenciado bajo la **GNU General Public License v3.0**.

Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🆘 Soporte y Contacto

- 🐛 **Reportar problemas**: [GitHub Issues](https://github.com/ISCOUTB/chaside/issues)
- 📖 **Documentación**: [GitHub Wiki](https://github.com/ISCOUTB/chaside/wiki)
- 📧 **Contacto**: soporte@utb.edu.co

---

<div align="center">

**[⬆ Volver al inicio](#chaside---test-vocacional-para-moodle)**

Desarrollado con ❤️ por [**ISCOUTB**](https://github.com/ISCOUTB) - Universidad Tecnológica de Bolívar

![GitHub stars](https://img.shields.io/github/stars/ISCOUTB/chaside?style=social)
![GitHub forks](https://img.shields.io/github/forks/ISCOUTB/chaside?style=social)

</div>