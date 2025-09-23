# GitHub Actions - Guía de Uso

Este documento explica cómo funcionan las GitHub Actions configuradas para el proyecto CHASIDE y cómo utilizarlas efectivamente.

## 🔄 Workflows Configurados

### 1. Automated Release (`release.yml`)

**Propósito**: Crear releases automáticos cuando se actualiza la versión del plugin.

**Triggers**:
- Push a `main` branch que modifique `version.php`
- Ejecución manual desde GitHub Actions

**Proceso Automático**:
1. ✅ Detecta cambios en `version.php`
2. ✅ Extrae la nueva versión del archivo
3. ✅ Verifica que no exista ya un tag para esa versión
4. ✅ Valida sintaxis PHP de todos los archivos
5. ✅ Crea un archivo ZIP del plugin
6. ✅ Extrae el changelog correspondiente
7. ✅ Crea el tag Git automáticamente
8. ✅ Genera el release en GitHub
9. ✅ Sube el archivo ZIP como asset

### 2. Continuous Integration (`ci.yml`)

**Propósito**: Validar código en cada push y pull request.

**Triggers**:
- Push a `main` o `develop`
- Pull requests hacia `main`

**Validaciones**:
- Sintaxis PHP
- Estructura de archivos requeridos
- Formato de versión semántica
- Verificación de changelog
- Análisis básico de seguridad

## 🚀 Cómo Hacer un Release

### Proceso Automático (Recomendado)

1. **Actualizar versión** en `version.php`:
   ```php
   $plugin->version = 2025092307;     // Incrementar timestamp
   $plugin->release = '1.3.0';       // Nueva versión semántica
   ```

2. **Actualizar CHANGELOG.md**:
   ```markdown
   ## [1.3.0] - 2025-01-23
   
   ### Añadido
   - Nueva funcionalidad X
   - Mejora Y
   
   ### Cambiado
   - Modificación Z
   
   ### Corregido
   - Bug fix A
   ```

3. **Commit y push**:
   ```bash
   git add version.php CHANGELOG.md
   git commit -m "chore: bump version to 1.3.0
   
   - Descripción de cambios principales
   - Lista de nuevas funcionalidades
   - Correcciones importantes"
   git push origin main
   ```

4. **Automático**: GitHub Actions se encarga del resto
   - En ~2-5 minutos verás el nuevo release en GitHub
   - Incluirá el ZIP del plugin listo para descargar

### Proceso Manual

Si necesitas forzar un release o hay algún problema:

1. Ve a **Actions** en GitHub
2. Selecciona **Automated Release**
3. Haz clic en **Run workflow**
4. Marca **Force release** si la versión ya existe
5. Haz clic en **Run workflow**

## 📁 Estructura del Release

Cada release incluye:

```
chaside-v1.2.1.zip
├── block_chaside.php          # Clase principal del bloque
├── version.php                 # Metadata del plugin
├── db/                        # Esquemas de base de datos
├── lang/                      # Archivos de idioma
├── classes/                   # Clases PHP del plugin
├── view.php                   # Vista principal del test
├── view_results.php           # Vista de resultados
├── manage.php                 # Gestión administrativa
├── export.php                 # Exportación de datos
├── README.md                  # Documentación
└── CHANGELOG.md               # Historial de cambios
```

## 🐛 Solución de Problemas

### Release No Se Crea Automáticamente

**Posibles causas**:
1. **Error de sintaxis PHP**: Revisa los logs de CI
2. **Versión ya existe**: Verifica que la versión sea nueva
3. **Formato incorrecto**: Usa formato semántico (X.Y.Z)

**Soluciones**:
```bash
# Verificar sintaxis localmente
find . -name "*.php" -exec php -l {} \;

# Verificar estructura
./scripts/check-structure.sh

# Ver releases existentes
git tag --list
```

### Error en GitHub Actions

**Ver logs**:
1. Ve a **Actions** en GitHub
2. Haz clic en el workflow fallido
3. Revisa los logs detallados de cada step

**Errores comunes**:
- **Permisos**: Asegúrate de que `GITHUB_TOKEN` tenga permisos
- **Sintaxis**: Valida PHP localmente primero
- **Archivos faltantes**: Usa el script `check-structure.sh`

### Forzar Release

Si necesitas recrear un release:

```bash
# Eliminar tag existente (si es necesario)
git tag -d v1.2.1
git push origin :refs/tags/v1.2.1

# Crear nuevo release manualmente
# Luego usar "Run workflow" con "Force release"
```

## ✅ Buenas Prácticas

### Versionado

Usar **Semantic Versioning**:
- `MAJOR.MINOR.PATCH` (ej: 1.2.1)
- **MAJOR**: Cambios incompatibles
- **MINOR**: Nueva funcionalidad compatible
- **PATCH**: Correcciones de bugs

### Commits

Formato recomendado:
```bash
# Nuevas funcionalidades
git commit -m "feat: agregar guardado automático"

# Correcciones
git commit -m "fix: corregir error de navegación"

# Cambios de versión
git commit -m "chore: bump version to 1.3.0"

# Documentación
git commit -m "docs: actualizar README con nuevas instrucciones"
```

### Testing

Antes de hacer release:
```bash
# Validar estructura
./scripts/check-structure.sh

# Probar en entorno local
# Verificar funcionalidades críticas
# Revisar changelog
```

## 📞 Soporte

Si tienes problemas con las GitHub Actions:

1. **Revisa este documento** primero
2. **Verifica los logs** en GitHub Actions
3. **Usa el script local** de validación
4. **Consulta la documentación** de GitHub Actions

Para problemas específicos del plugin, consulta el README.md principal.