# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere al [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planeado
- Integración con sistemas de orientación vocacional externos
- Dashboard de estadísticas para administradores
- Exportación de resultados a PDF

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