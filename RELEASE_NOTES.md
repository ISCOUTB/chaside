# CHASIDE Block - Release 1.6.6

## 🐛 Bugfix Release - 2025-10-13

### Correcciones Críticas

#### Mapeo Correcto de Áreas CHASIDE
- **Corregido**: El área **C** ahora se identifica correctamente como **Administrativa** (antes aparecía incorrectamente como "Científica")
- **Corregido**: El área **E** es **Ciencias Experimentales** (la verdadera área científica)
- **Impacto**: Las exportaciones CSV ahora muestran los nombres correctos de las áreas vocacionales

#### Exportación CSV Robusta
- **Agregado**: Manejo de errores con try-catch para proteger contra fallos en el cálculo de puntuaciones
- **Agregado**: Función helper `$get_score()` para acceso seguro a claves de array de scores
- **Corregido**: Protección contra valores vacíos o faltantes en exportaciones

### Mapeo Oficial CHASIDE

| Letra | Área Vocacional |
|-------|-----------------|
| **C** | Administrativa |
| **H** | Humanidades/Sociales/Jurídicas |
| **A** | Artística |
| **S** | Ciencias de la Salud |
| **I** | Enseñanzas Técnicas |
| **D** | Defensa y Seguridad |
| **E** | Ciencias Experimentales |

### Archivos Modificados

- `export.php` - Corrección de nombres de áreas y manejo robusto de errores
- `lang/es/block_chaside.php` - Actualización de cadenas de exportación con nombres correctos
- `version.php` - Incremento a v1.6.6

### Instalación / Actualización

Para usuarios existentes, simplemente actualice el plugin a través del panel de administración de Moodle. No se requieren cambios en la base de datos.

### Compatibilidad

- Moodle 4.1+
- PHP 7.4+

---

**Release Date**: October 13, 2025  
**Version**: 1.6.6  
**Build**: 2025101310
