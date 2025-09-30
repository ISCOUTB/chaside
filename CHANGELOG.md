# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere al [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [1.6.3] - 2025-09-30

### Mejorado
- Informe de cadenas y traducciones repetidas generado y verificado para limpieza futura.
- Preparación para actualización de áreas y descripciones según nueva taxonomía (pendiente de confirmación).
- Actualización de versión y release para mantener consistencia con mejoras recientes.

### Técnico
- Bump de versión a 1.6.3 para despliegue y pruebas.

## [Unreleased]

### Planeado
- Integración con sistemas de orientación vocacional externos
- Exportación de resultados a PDF
- Notificaciones automáticas para estudiantes

## [1.5.0] - 2025-09-26

### Agregado
- **Panel de Administración Completo**: Implementado panel de administración con gestión completa de respuestas de estudiantes
- **Estadísticas Avanzadas**: Dashboard con estadísticas detalladas por área CHASIDE y métricas de participación
- **Funcionalidad de Eliminación**: Capacidad para que profesores/administradores eliminen respuestas de estudiantes
- **Vista de Resultados para Profesores**: Profesores pueden ahora ver resultados detallados de estudiantes individuales
- **Interface Mejorada**: Diseño modernizado con cards responsive y visualizaciones mejoradas
- **Estadísticas por Área**: Análisis detallado de puntuaciones promedio y preferencias por cada área vocacional

### Corregido
- **Permisos de Acceso**: Solucionado problema donde profesores no podían ver resultados de estudiantes
- **Variables Indefinidas**: Corregido error de variable $blockid indefinida en admin_view.php
- **Capability Names**: Unificados nombres de capacidades para consistencia (viewreports vs view_reports)
- **Gestión de Parámetros**: Mejorado manejo de parámetros userid y blockid en URLs
- **GitHub Actions**: Corregidos falsos positivos en verificaciones de seguridad para patrones estándar de Moodle

### Mejorado
- **Traducciones Completas**: Agregadas todas las cadenas de traducción faltantes en español e inglés
- **UI/UX**: Interface completamente rediseñada con Bootstrap y FontAwesome
- **Navegación**: Enlaces y navegación mejorados entre vistas del panel de administración
- **Responsividad**: Diseño completamente responsive para dispositivos móviles

### Técnico
- **Capacidades**: Implementado sistema completo de capacidades y permisos
- **Base de Datos**: Optimizadas consultas para mejor rendimiento
- **Arquitectura**: Código reorganizado para mejor mantenibilidad
- **CI/CD**: Mejoradas verificaciones de seguridad para reconocer patrones estándar de Moodle
- **Testing**: Eliminados falsos positivos en GitHub Actions para funciones `require_login`, `required_param`, etc.

## [1.2.7] - 2025-01-23

### Corregido
- **Traducciones faltantes**: Agregadas traducciones para area_C, area_H, etc. (versiones en mayúsculas)
- **Texto hardcoded**: Eliminado texto hardcoded "puntos", "preguntas sin responder", "preguntas restantes"
- **Compatibilidad de áreas**: Agregadas descripciones desc_C, desc_H, etc. para compatibilidad completa
- **Consistencia**: Uso consistente de strtolower() en todas las referencias a áreas
- **Multiidioma completo**: Todos los mensajes de error y etiquetas ahora son traducibles

### Agregado  
- **Strings adicionales**: questions_unanswered, questions_remaining, points para mensajes dinámicos
- **Compatibilidad doble**: Soporte tanto para áreas en mayúsculas como minúsculas
- **Mensajes mejorados**: Conteo de preguntas faltantes ahora completamente traducible

## [1.2.6] - 2025-01-23

### Corregido
- **Error de sintaxis**: Corregido "syntax error, unexpected token '+'" en view_results.php línea 120
- **Expresiones PHP**: Movida la evaluación de expresiones matemáticas fuera de las cadenas interpoladas
- **Visualización de resultados**: Los resultados del test ahora se muestran correctamente sin errores de sintaxis

## [1.2.5] - 2025-01-23

### Corregido
- **Error de tipos**: Corregido error "Cannot use object of type stdClass as array" al calcular puntuaciones
- **Compatibilidad**: Conversión automática de objetos stdClass a arrays en todas las funciones de cálculo
- **Estabilidad**: Corregido en block_chaside.php, manage.php y export.php para manejo consistente de datos

## [1.2.4] - 2025-01-23

### Agregado
- **Validación de formulario**: Los botones de navegación y guardar ahora se deshabilitan hasta que todas las preguntas de la página actual estén respondidas
- **Validación JavaScript**: Validación en tiempo real que verifica que todos los campos estén completos antes de permitir navegación
- **Validación servidor**: Verificación del lado del servidor para evitar navegación o guardado con campos incompletos

### Mejorado
- **Experiencia del usuario**: Los estudiantes ahora deben completar todas las preguntas de una página antes de poder continuar
- **Interfaz**: Los botones deshabilitados se muestran visualmente con opacidad reducida
- **Mensajes de error**: Nuevos mensajes informativos que indican cuántas preguntas faltan por responder

## [1.2.3] - 2025-01-23

### Corregido
- **Error CSRF**: Agregado campo 'sesskey' faltante en formulario del test
- **Seguridad**: Corregido error 'missingparam' al enviar respuestas como estudiante
- **Navegación**: Los botones Anterior/Siguiente ahora funcionan correctamente

### Técnico
- Añadido token de seguridad CSRF (`sesskey()`) al formulario en view.php línea 203
- Previene errores de "Un parámetro necesario (sesskey) faltaba"

## [1.2.2] - 2025-01-23

### Corregido
- **Error de permisos**: Agregada traducción faltante para capacidad 'chaside:manage_responses'
- **Compatibilidad**: Corregido error en archivo manage.php que impedía el acceso a profesores
- **Traducciones**: Añadidas cadenas de texto en inglés y español para nuevas capacidades

### Técnico
- Actualizada versión del plugin para forzar reinstalación de capacidades
- Verificadas todas las traducciones de capacidades en archivos de idioma

## [1.2.1] - 2025-01-23

### Añadido
- **GitHub Actions**: Sistema de release automático
- **Validación CI/CD**: Integración continua con validación de código
- **Script de validación**: Herramienta local para verificar estructura del plugin
- **Documentación**: Guía completa de desarrollo y release automático

### Cambiado
- **README.md**: Documentación expandida con información de GitHub Actions
- **Workflow automático**: Release se genera automáticamente al actualizar version.php

### Corregido
- Configuración de GitHub Actions para release automático
- Scripts de validación y empaquetado del plugin

## [1.2.0] - 2025-01-23

### Añadido
- **Guardado Progresivo por Página**: Cada página del test ahora guarda el progreso automáticamente
- **Navegación Mejorada**: Botones independientes para anterior, guardar, siguiente y finalizar
- **Preservación de Respuestas**: Las respuestas previas se mantienen al navegar entre páginas
- **Validación Inteligente**: El sistema solo permite finalizar cuando todas las preguntas están contestadas
- **Mensajes de Estado**: Notificaciones claras sobre el progreso guardado
- **Detección de Completitud**: Contador de preguntas faltantes en mensajes de error

### Cambiado
- Refactorizada completamente la lógica de procesamiento de formularios
- Implementado sistema de acciones múltiples (`previous`, `next`, `save`, `finish`)
- Mejorada la estructura de datos para preservar estado entre páginas

### Corregido
- Arreglados errores de sintaxis en navegación
- Corregida lógica de redirección en la última página
- Mejorada la interfaz de usuario con botones más descriptivos

## [1.1.2] - 2025-01-22

### Corregido
- Arreglados errores de sintaxis PHP
- Corregida navegación entre páginas
- Mejorada compatibilidad con Moodle 4.1

## [1.1.1] - 2025-01-21

### Añadido
- Implementado control de acceso basado en roles
- Solo estudiantes pueden tomar el test
- Profesores y administradores ven estadísticas
- Soporte para múltiples idiomas (Español/Inglés)

## [1.0.0] - 2024-09-23

### Añadido
- Implementación inicial del test CHASIDE con 98 preguntas
- Sistema de evaluación en 7 áreas vocacionales (C.H.A.S.I.D.E)
- Interfaz paginada con 10 preguntas por página
- Visualización de resultados con gráficos de barras
- Almacenamiento persistente de respuestas en base de datos
- Sistema de permisos integrado con Moodle
- Capacidades para diferentes roles (estudiante, profesor, administrador)
- Páginas de resultados con recomendaciones vocacionales
- Diseño responsive compatible con dispositivos móviles
- Documentación completa del proyecto

### Características Técnicas
- Compatibilidad con Moodle 3.9+
- Soporte para PHP 7.4+
- Schema de base de datos con XMLDB
- Estructura de plugin estándar de Moodle
- Clases y funciones bien documentadas

### Base de Datos
- Tabla `block_chaside_responses` con 111 campos
- Almacenamiento de respuestas individuales (q1-q98)
- Cálculo y almacenamiento de puntuaciones por área
- Campos de auditoría (timecreated, timemodified)
- Índices optimizados para consultas rápidas

### Interfaz de Usuario
- Página principal del test con instrucciones claras
- Navegación intuitiva entre páginas del test
- Barra de progreso visual
- Página de resultados con interpretación de puntuaciones
- Descripción detallada de cada área vocacional

### Documentación
- README.md completo con instrucciones de instalación
- Ejemplos de uso para diferentes roles
- Documentación de la estructura del proyecto
- Guía de contribución para desarrolladores

## [0.1.0] - 2024-09-20

### Añadido
- Estructura inicial del proyecto
- Archivos básicos de configuración
- Primeras pruebas de concepto

---

## Tipos de Cambios

- `Añadido` para nuevas funcionalidades
- `Cambiado` para cambios en funcionalidades existentes
- `Obsoleto` para funcionalidades que serán removidas próximamente
- `Removido` para funcionalidades removidas
- `Arreglado` para corrección de bugs
- `Seguridad` para vulnerabilidades

## Versionado

Este proyecto usa [Semantic Versioning](https://semver.org/):

- **MAJOR** version: cambios incompatibles en la API
- **MINOR** version: funcionalidades añadidas de manera compatible
- **PATCH** version: correcciones de bugs compatibles