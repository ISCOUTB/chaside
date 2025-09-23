# CHASIDE - Test Vocacional para Moodle

<div align="center">

![CHASIDE Logo](https://img.shields.io/badge/CHASIDE-Vocational%20Test-blue?style=for-the-badge)

**Plugin para Moodle que implementa el test vocacional CHASIDE para orientación profesional**

[![Moodle](https://img.shields.io/badge/Moodle-4.1%2B-orange?style=flat-square)](https://moodle.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v3-green?style=flat-square)](LICENSE)
[![Release](https://img.shields.io/github/v/release/ISCOUTB/chaside?style=flat-square)](https://github.com/ISCOUTB/chaside/releases)

</div>

## 📋 ¿Qué es CHASIDE?

CHASIDE es un test vocacional científicamente validado que evalúa las preferencias e intereses profesionales de los estudiantes en **7 áreas vocacionales**:

| Área | Nombre | Descripción | Carreras Ejemplo |
|------|--------|-------------|------------------|
| **C** | Cálculo | Matemáticas y análisis numérico | Ingeniería, Matemáticas, Física |
| **H** | Humanística | Ciencias sociales y humanas | Psicología, Historia, Derecho |
| **A** | Artística | Expresión creativa y estética | Diseño, Arte, Música, Arquitectura |
| **S** | Servicio Social | Ayuda y servicio a la comunidad | Trabajo Social, Enfermería, Medicina |
| **I** | Trabajo de Oficina | Actividades administrativas | Administración, Contabilidad, Secretariado |
| **D** | Persuasiva | Influencia y liderazgo | Ventas, Marketing, Política, Periodismo |
| **E** | Científica | Investigación y experimentación | Biología, Química, Investigación |

## ✨ Características Principales

- 🎯 **98 preguntas** cuidadosamente diseñadas
- � **Interfaz responsive** - Funciona en móviles y tablets
- 🔄 **Navegación por páginas** - 10 preguntas por página
- 💾 **Guardado automático** - El progreso se guarda en cada página
- � **Resultados visuales** - Gráficos de barras con puntuaciones
- � **Control de acceso** - Solo estudiantes pueden tomar el test
- 🌐 **Multiidioma** - Disponible en Español e Inglés
- � **Dashboard para profesores** - Estadísticas y exportación de datos

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

### �‍🏫 Para Profesores
1. Añade el bloque a tu curso desde **"Activar edición"**
2. Visualiza estadísticas de participación
3. Exporta resultados en formato CSV
4. Revisa el progreso de tus estudiantes

### 👨‍💼 Para Administradores
- Gestiona permisos y configuraciones globales
- Accede a reportes del sistema
- Configura aspectos técnicos del plugin

## � Estructura del Proyecto

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
└── � docs/                  # Documentación adicional
```

## 📝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia GPL v3. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👥 Autores

- **ISCOUTB** - *Universidad Tecnológica de Bolívar*

## 🙏 Agradecimientos

- Al equipo de desarrollo de Moodle por proporcionar la plataforma
- A los investigadores que desarrollaron el modelo CHASIDE original
- A todos los contribuidores que ayudan a mejorar este proyecto

## 📞 Soporte

- 🐛 **Issues**: [GitHub Issues](https://github.com/ISCOUTB/chaside/issues)
- 📧 **Email**: soporte@utb.edu.co
- 📖 **Documentación**: [Wiki del proyecto](https://github.com/ISCOUTB/chaside/wiki)

---

<div align="center">

**[⬆ Volver al inicio](#chaside---test-vocacional-para-moodle)**

Hecho con ❤️ por [ISCOUTB](https://github.com/ISCOUTB)

</div>



## Características



### Áreas Vocacionales EvaluadasEl bloque CHASIDE es una herramienta de orientación vocacional implementada en Moodle que permite a los estudiantes realizar un test de 98 preguntas para evaluar sus intereses en 7 áreas profesionales.## Descripción

- **C** - Área Administrativa

- **H** - Humanidades y Ciencias Sociales/Jurídicas  

- **A** - Área Artística

- **S** - Ciencias de la Salud## Características

- **I** - Enseñanzas Técnicas/Ingenierías

- **D** - Defensa y Seguridad

- **E** - Ciencias Experimentales

### Áreas Vocacionales EvaluadasEl bloque CHASIDE es una herramienta de orientación vocacional implementada en Moodle que permite a los estudiantes realizar un test de 98 preguntas para evaluar sus intereses en 7 áreas profesionales.El bloque CHASIDE es una herramienta de orientación vocacional implementada en Moodle que permite a los estudiantes realizar un test de 98 preguntas para evaluar sus intereses en 7 áreas profesionales.[![Build Status](https://github.com/ISCOUTB/personality_test/actions/workflows/build.yml/badge.svg)](https://github.com/ISCOUTB/personality_test/actions)

### Funcionalidades del Test

- 98 preguntas con respuestas Sí/No- **C** - Área Administrativa

- Cálculo automático de puntajes por área

- Identificación de las top 2 áreas vocacionales- **H** - Humanidades y Ciencias Sociales/Jurídicas  

- Manejo de empates en puntuaciones

- Resultados detallados con descripciones de áreas- **A** - Área Artística

- Barra de progreso en tiempo real

- Validación completa de respuestas- **S** - Ciencias de la Salud## Características[![Latest Release](https://img.shields.io/github/v/release/ISCOUTB/personality_test)](https://github.com/ISCOUTB/personality_test/releases/latest)

- Interfaz responsive y moderna

- **I** - Enseñanzas Técnicas/Ingenierías

### Funcionalidades para Estudiantes

- Realización del test CHASIDE- **D** - Defensa y Seguridad

- Vista de resultados personales

- Descripciones detalladas de áreas vocacionales- **E** - Ciencias Experimentales

- Identificación de orientaciones principales

### Áreas Vocacionales Evaluadas## Descripción

### Funcionalidades para Profesores

- Vista de todos los resultados de estudiantes### Funcionalidades del Test

- Estadísticas del curso

- Exportación de datos (CSV y JSON)- 98 preguntas con respuestas Sí/No- **C** - Área Administrativa

- Análisis de distribución por áreas

- Tasas de finalización del test- Cálculo automático de puntajes por área



## Instalación- Identificación de las top 2 áreas vocacionales- **H** - Humanidades y Ciencias Sociales/Jurídicas  ## Descripción General



### Desde Archivo ZIP- Manejo de empates en puntuaciones

1. Descargar el archivo del bloque CHASIDE

2. Ir a `Administración del sitio > Plugins > Instalar plugins`- Resultados detallados con descripciones de áreas- **A** - Área Artística

3. Subir el archivo ZIP

4. Seguir las instrucciones de instalación- Barra de progreso en tiempo real



### Instalación Manual- Validación completa de respuestas- **S** - Ciencias de la SaludCHASIDE es un test de orientación vocacional que evalúa los intereses del estudiante en 7 áreas principales:

1. Descomprimir el archivo en `/path/to/moodle/blocks/chaside/`

2. Visitar la página de administración para completar la instalación- Interfaz responsive y moderna

3. Configurar permisos si es necesario

- **I** - Enseñanzas Técnicas/Ingenierías

## Estructura del Bloque

### Funcionalidades para Estudiantes

```

blocks/chaside/- Realización del test CHASIDE- **D** - Defensa y SeguridadEl bloque `personality_test` permite a los estudiantes de un curso realizar un test de personalidad tipo MBTI y visualizar sus resultados, mientras que los profesores pueden ver estadísticas agregadas y exportar los datos en formatos CSV y PDF. El bloque es completamente internacionalizable, responsivo y sigue las buenas prácticas de desarrollo para Moodle.

├── db/

│   ├── access.php              # Definición de capacidades- Vista de resultados personales

│   └── install.xml             # Esquema de base de datos

├── lang/- Descripciones detalladas de áreas vocacionales- **E** - Ciencias Experimentales

│   ├── en/

│   │   └── block_chaside.php   # Idioma inglés- Identificación de orientaciones principales

│   └── es/

│       └── block_chaside.php   # Idioma español- **C** - Área Administrativa/Contable

├── admin_view.php              # Panel de administración

├── block_chaside.php           # Lógica principal del bloque### Funcionalidades para Profesores

├── individual_result.php       # Vista de resultado individual

├── lib.php                     # Funciones auxiliares- Vista de todos los resultados de estudiantes### Funcionalidades del Test

├── README.md                   # Documentación

├── version.php                 # Información de versión- Estadísticas del curso

├── view.php                    # Formulario del test

└── view_results.php            # Vista de resultados del estudiante- Exportación de datos (CSV y JSON)- 98 preguntas con respuestas Sí/No- **H** - Área de Humanidades  ## 🚀 Instalación Rápida

```

- Análisis de distribución por áreas

## Uso

- Tasas de finalización del test- Cálculo automático de puntajes por área

### Para Estudiantes

1. Acceder al curso donde está habilitado el bloque

2. Hacer clic en "Realizar Test" en el bloque CHASIDE

3. Responder las 98 preguntas con Sí o No## Instalación- Identificación de las top 2 áreas vocacionales- **A** - Área Artística

4. Ver los resultados al completar el test



### Para Profesores

1. Acceder al enlace "Ver Todos los Resultados" en el bloque### Desde Archivo ZIP- Manejo de empates en puntuaciones

2. Revisar estadísticas y resultados de estudiantes

3. Exportar datos según sea necesario1. Descargar el archivo del bloque CHASIDE

4. Analizar distribución vocacional del grupo

2. Ir a `Administración del sitio > Plugins > Instalar plugins`- Resultados detallados con descripciones de áreas- **S** - Área de Ciencias de la Salud### Desde GitHub Releases (Recomendado)

## Base de Datos

3. Subir el archivo ZIP

El bloque crea una tabla `chaside` con los siguientes campos principales:

- Campos de metadatos (id, user, course, completed, timestamps)4. Seguir las instrucciones de instalación- Barra de progreso en tiempo real

- 98 campos para respuestas individuales (item1-item98)

- 7 campos para puntajes por área (score_c, score_h, score_a, score_s, score_i, score_d, score_e)

- Campos para top areas y manejo de empates

### Instalación Manual- Validación completa de respuestas- **I** - Área de Ingeniería y Tecnología1. Ve a [Releases](https://github.com/ISCOUTB/personality_test/releases/latest)

## Capacidades

1. Descomprimir el archivo en `/path/to/moodle/blocks/chaside/`

- `block/chaside:addinstance` - Agregar el bloque a un curso

- `block/chaside:myaddinstance` - Agregar el bloque al dashboard personal2. Visitar la página de administración para completar la instalación- Interfaz responsive y moderna

- `block/chaside:take_test` - Realizar el test CHASIDE

- `block/chaside:view_reports` - Ver reportes y administrar resultados3. Configurar permisos si es necesario



## Desarrollo- **D** - Área de Defensa y Seguridad2. Descarga el archivo `block_personality_test_vX.X.X.zip`



### Arquitectura## Estructura del Bloque

- **ChasideFacade**: Clase principal con lógica de negocio del test

- **Patrón MVC**: Separación clara entre vista, lógica y datos### Funcionalidades para Estudiantes

- **Responsive Design**: Compatible con dispositivos móviles

- **Internacionalización**: Soporte completo para múltiples idiomas```



### Tecnologíasblocks/chaside/- Realización del test CHASIDE- **E** - Área de Ciencias Experimentales3. Extrae el contenido en tu directorio `blocks/` de Moodle

- PHP 7.4+

- Moodle 3.9+├── db/

- JavaScript (ES6)

- CSS3 con Flexbox│   ├── access.php              # Definición de capacidades- Vista de resultados personales

- Base de datos MySQL/PostgreSQL

│   └── install.xml             # Esquema de base de datos

## Licencia

├── lang/- Descripciones detalladas de áreas vocacionales4. Visita la página de administración de Moodle para completar la instalación

Este proyecto está licenciado bajo la GNU General Public License v3.0.

│   ├── en/

## Contribuciones

│   │   └── block_chaside.php   # Idioma inglés- Identificación de orientaciones principales

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto│   └── es/

2. Crear una rama para la nueva funcionalidad

3. Hacer commit de los cambios│       └── block_chaside.php   # Idioma español## Características

4. Push a la rama

5. Abrir un Pull Request├── admin_view.php              # Panel de administración



## Soporte├── block_chaside.php           # Lógica principal del bloque### Funcionalidades para Profesores



Para reportar problemas o solicitar nuevas funcionalidades, por favor contactar al equipo de desarrollo.├── individual_result.php       # Vista de resultado individual

├── lib.php                     # Funciones auxiliares- Vista de todos los resultados de estudiantes### Desde Código Fuente

├── README.md                   # Documentación

├── version.php                 # Información de versión- Estadísticas del curso

├── view.php                    # Formulario del test

└── view_results.php            # Vista de resultados del estudiante- Exportación de datos (CSV y JSON)### Para Estudiantes```bash

```

- Análisis de distribución por áreas

## Uso

- Tasas de finalización del test- Test interactivo de 98 preguntas con respuestas Sí/Nocd /path/to/moodle/blocks/

### Para Estudiantes

1. Acceder al curso donde está habilitado el bloque

2. Hacer clic en "Realizar Test" en el bloque CHASIDE

3. Responder las 98 preguntas con Sí o No## Instalación- Barra de progreso en tiempo realgit clone https://github.com/ISCOUTB/personality_test.git

4. Ver los resultados al completar el test



### Para Profesores

1. Acceder al enlace "Ver Todos los Resultados" en el bloque### Desde Archivo ZIP- Resultados inmediatos con las 2 áreas principales de interés# Luego visita la página de administración de Moodle

2. Revisar estadísticas y resultados de estudiantes

3. Exportar datos según sea necesario1. Descargar el archivo del bloque CHASIDE

4. Analizar distribución vocacional del grupo

2. Ir a `Administración del sitio > Plugins > Instalar plugins`- Visualización detallada de puntuaciones por área```

## Base de Datos

3. Subir el archivo ZIP

El bloque crea una tabla `chaside` con los siguientes campos principales:

- Campos de metadatos (id, user, course, completed, timestamps)4. Seguir las instrucciones de instalación- Descripción de cada área profesional

- 98 campos para respuestas individuales (item1-item98)

- 7 campos para puntajes por área (score_c, score_h, score_a, score_s, score_i, score_d, score_e)

- Campos para top areas y manejo de empates

### Instalación Manual- Recomendaciones basadas en los resultados---

## Capacidades

1. Descomprimir el archivo en `/path/to/moodle/blocks/chaside/`

- `block/chaside:addinstance` - Agregar el bloque a un curso

- `block/chaside:myaddinstance` - Agregar el bloque al dashboard personal2. Visitar la página de administración para completar la instalación- Exportación de resultados en formato JSON y CSV

- `block/chaside:take_test` - Realizar el test CHASIDE

- `block/chaside:view_reports` - Ver reportes y administrar resultados3. Configurar permisos si es necesario



## Desarrollo## Estructura de Archivos



### Arquitectura## Estructura del Bloque

- **ChasideFacade**: Clase principal con lógica de negocio del test

- **Patrón MVC**: Separación clara entre vista, lógica y datos### Para Profesores/Administradores

- **Responsive Design**: Compatible con dispositivos móviles

- **Internacionalización**: Soporte completo para múltiples idiomas```



### Tecnologíasblocks/chaside/- Panel de administración con estadísticas del curso```

- PHP 7.4+

- Moodle 3.9+├── db/

- JavaScript (ES6)

- CSS3 con Flexbox│   ├── access.php              # Definición de capacidades- Vista de todos los resultados de estudiantespersonality_test/

- Base de datos MySQL/PostgreSQL

│   └── install.xml             # Esquema de base de datos

## Licencia

├── lang/- Distribución visual de áreas vocacionales del grupo│

Este proyecto está licenciado bajo la GNU General Public License v3.0.

│   ├── en/

## Contribuciones

│   │   └── block_chaside.php   # Idioma inglés- Exportación masiva de datos del curso├── amd/

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto│   └── es/

2. Crear una rama para la nueva funcionalidad

3. Hacer commit de los cambios│       └── block_chaside.php   # Idioma español- Vista detallada de resultados individuales│   ├── src/

4. Push a la rama

5. Abrir un Pull Request├── admin_view.php              # Panel de administración



## Soporte├── block_chaside.php           # Lógica principal del bloque- Análisis de respuestas por pregunta│   │   └── charts.js         # Lógica JS para gráficas (Chart.js, AMD)



Para reportar problemas o solicitar nuevas funcionalidades, por favor contactar al equipo de desarrollo.├── individual_result.php       # Vista de resultado individual

├── lib.php                     # Funciones auxiliares│   └── build/

├── README.md                   # Documentación

├── version.php                 # Información de versión## Instalación│       └── charts.min.js     # Versión minificada del JS

├── view.php                    # Formulario del test

└── view_results.php            # Vista de resultados del estudiante│

```

1. Copiar el directorio `chaside` a `/path/to/moodle/blocks/`├── db/

## Uso

2. Acceder como administrador a Moodle│   ├── access.php            # Definición de capacidades y permisos

### Para Estudiantes

1. Acceder al curso donde está habilitado el bloque3. Ir a "Administración del sitio" > "Notificaciones"│   └── install.xml           # Estructura de la base de datos

2. Hacer clic en "Realizar Test" en el bloque CHASIDE

3. Responder las 98 preguntas con Sí o No4. Seguir el proceso de instalación del bloque│

4. Ver los resultados al completar el test

├── lang/

### Para Profesores

1. Acceder al enlace "Ver Todos los Resultados" en el bloque## Estructura del Bloque│   ├── es/

2. Revisar estadísticas y resultados de estudiantes

3. Exportar datos según sea necesario│   │   └── block_personality_test.php  # Idioma español

4. Analizar distribución vocacional del grupo

```│   └── en/

## Base de Datos

blocks/chaside/│       └── block_personality_test.php  # Idioma inglés

El bloque crea una tabla `chaside` con los siguientes campos principales:

- Campos de metadatos (id, user, course, completed, timestamps)├── block_chaside.php          # Clase principal del bloque│

- 98 campos para respuestas individuales (item1-item98)

- 7 campos para puntajes por área (score_c, score_h, score_a, score_s, score_i, score_d, score_e)├── view.php                   # Interfaz para tomar el test├── pix/                      # Imágenes e íconos del bloque

- Campos para top areas y manejo de empates

├── view_results.php           # Visualización de resultados│

## Capacidades

├── admin_view.php             # Panel de administración├── block_personality_test.php # Lógica principal del bloque (PHP, usa patrón Facade)

- `block/chaside:addinstance` - Agregar el bloque a un curso

- `block/chaside:myaddinstance` - Agregar el bloque al dashboard personal├── individual_result.php      # Vista detallada individual├── styles.css                # Estilos CSS, responsivo y adaptado a SAVIO UTB

- `block/chaside:take_test` - Realizar el test CHASIDE

- `block/chaside:view_reports` - Ver reportes y administrar resultados├── version.php                # Información de versión├── view.php                  # Vista del formulario del test para estudiantes



## Desarrollo├── db/├── save.php                  # Lógica de guardado de respuestas



### Arquitectura│   ├── install.xml           # Esquema de base de datos├── lib.php                   # Funciones auxiliares (guardar resultados)

- **ChasideFacade**: Clase principal con lógica de negocio del test

- **Patrón MVC**: Separación clara entre vista, lógica y datos│   └── access.php            # Definición de capacidades├── download_csv.php          # Exportación profesional de resultados en CSV

- **Responsive Design**: Compatible con dispositivos móviles

- **Internacionalización**: Soporte completo para múltiples idiomas└── lang/├── download_pdf.php          # Exportación profesional de resultados en PDF



### Tecnologías    ├── es/├── edit_form.php             # Formulario de edición/configuración del bloque

- PHP 7.4+

- Moodle 3.9+    │   └── block_chaside.php # Strings en español├── version.php               # Versión y metadatos del plugin

- JavaScript (ES6)

- CSS3 con Flexbox    └── en/└── README.md                 # Documentación básica y créditos

- Base de datos MySQL/PostgreSQL

        └── block_chaside.php # Strings en inglés```

## Licencia

```

Este proyecto está licenciado bajo la GNU General Public License v3.0.

---

## Contribuciones

## Base de Datos

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto## Principales Componentes y Responsabilidades

2. Crear una rama para la nueva funcionalidad

3. Hacer commit de los cambiosEl bloque crea una tabla `chaside` con los siguientes campos:

4. Push a la rama

5. Abrir un Pull Request- **block_personality_test.php**  



## Soporte- `id`: ID único del registro  - Controlador principal del bloque.



Para reportar problemas o solicitar nuevas funcionalidades, por favor contactar al equipo de desarrollo.- `user`: ID del usuario  - Usa el patrón **Facade** para separar la lógica de negocio (cálculo MBTI, conteos, explicaciones).

- `course`: ID del curso  - Presenta diferentes vistas según el rol (estudiante, profesor, otros).

- `item1` a `item98`: Respuestas individuales (0=No, 1=Sí)  - Llama a los módulos JS para mostrar gráficas.

- `score_c` a `score_e`: Puntuaciones por área

- `top_area_1`, `top_area_2`: Áreas principales- **PersonalityTestFacade (en block_personality_test.php)**  

- `has_ties`: Indicador de empates  - Encapsula la lógica de negocio: cálculo de tipo MBTI, explicaciones, conteos de tipos y rasgos.

- `completed`: Estado de completado  - Facilita la mantenibilidad y escalabilidad.

- `timecreated`, `timemodified`: Timestamps

- **charts.js (AMD)**  

## Algoritmo de Puntuación  - Renderiza las gráficas usando Chart.js.

  - Recibe datos desde PHP y los muestra de forma responsiva y profesional.

### Mapeo de Preguntas  - Fácil de modificar para cambiar tipos de gráficas o librería.

Cada área tiene exactamente 14 preguntas asignadas:

- **save.php / lib.php**  

- **Área C**: Preguntas 1-14  - Procesan y guardan las respuestas del test en la base de datos.

- **Área H**: Preguntas 15-28    - Validan y aseguran la integridad de los datos.

- **Área A**: Preguntas 29-42

- **Área S**: Preguntas 43-56- **download_csv.php / download_pdf.php**  

- **Área I**: Preguntas 57-70  - Exportan los resultados de forma profesional, con metadatos y estructura clara.

- **Área D**: Preguntas 71-84

- **Área E**: Preguntas 85-98- **styles.css**  

  - Estilos modernos, responsivos y adaptados a la identidad visual de SAVIO UTB.

### Cálculo de Puntuaciones

- Cada respuesta "Sí" suma 1 punto al área correspondiente- **Archivos de idioma**  

- Cada respuesta "No" suma 0 puntos  - Permiten la traducción completa del bloque.

- Puntuación máxima por área: 14 puntos

- Puntuación mínima por área: 0 puntos---



### Determinación de Áreas Principales## Buenas Prácticas y Estándares Cumplidos

1. Se calculan las puntuaciones de las 7 áreas

2. Se ordenan de mayor a menor puntuación- **Internacionalización**: Todos los textos están en archivos de idioma.

3. Se seleccionan las 2 áreas con mayor puntuación- **Seguridad**: Uso de permisos, validación de parámetros, y control de acceso.

4. En caso de empate, se usa el orden alfabético como criterio de desempate- **Separación de responsabilidades**: Lógica de negocio separada de la presentación.

- **Responsividad**: CSS y JS adaptados a cualquier dispositivo y nivel de zoom.

## Capacidades de Moodle- **Extensibilidad**: Fácil de modificar o ampliar (por ejemplo, cambiar gráficas).

- **Compatibilidad Moodle**: Uso de AMD para JS, helpers de Moodle para HTML, y API de base de datos.

El bloque define las siguientes capacidades:

---

- `block/chaside:addinstance`: Agregar instancia del bloque al curso

- `block/chaside:myaddinstance`: Agregar instancia al dashboard personal## Evaluación ATAM (Architecture Tradeoff Analysis Method)

- `block/chaside:take_test`: Realizar el test CHASIDE

- `block/chaside:view_reports`: Ver reportes administrativos### A. Atributos de Calidad Evaluados

- **Mantenibilidad**

## Roles y Permisos- **Escalabilidad**

- **Seguridad**

| Rol | Realizar Test | Ver Reportes | Administrar |- **Internacionalización**

|-----|---------------|--------------|-------------|- **Usabilidad**

| Estudiante | ✓ | ✗ | ✗ |- **Rendimiento**

| Profesor | ✓ | ✓ | ✗ |- **Extensibilidad**

| Profesor Editor | ✓ | ✓ | ✓ |

| Manager | ✓ | ✓ | ✓ |### B. Riesgos Identificados

- **Dependencia de Chart.js**: Si se quiere cambiar la librería de gráficas, hay que modificar el JS, pero la separación actual lo facilita.

## Uso del Bloque- **Crecimiento de lógica de negocio**: Si la lógica de personalidad crece mucho, podría ser necesario migrar la fachada a un archivo propio o incluso a un servicio.

- **Validación de datos**: Si se agregan más tipos de tests, habría que generalizar la lógica de guardado y cálculo.

### Para Estudiantes

### C. Trade-offs (Compromisos)

1. **Acceder al Test**- **Simplicidad vs. Escalabilidad**:  

   - Hacer clic en el bloque CHASIDE en el curso  El uso de una fachada y separación de JS añade un poco de complejidad inicial, pero permite escalar y mantener el bloque fácilmente en el futuro.

   - Leer las instrucciones- **Flexibilidad vs. Rendimiento**:  

   - Hacer clic en "Comenzar Test"  El uso de Chart.js y renderizado en el cliente es flexible y visualmente atractivo, pero puede ser menos eficiente con grandes volúmenes de datos (no es un problema en el contexto actual).

- **Internacionalización vs. Facilidad de edición**:  

2. **Completar el Test**  Todo texto está en archivos de idioma, lo que es excelente para traducción, pero requiere editar varios archivos para cambios de texto.

   - Responder las 98 preguntas con Sí/No

   - Observar el progreso en la barra superior### D. Escenarios de Cambio y Facilidad de Adaptación

   - Hacer clic en "Enviar Respuestas"- **Cambiar el tipo de gráficas**:  

  Solo se modifica el archivo `charts.js` (y su minificado). No afecta la lógica de negocio ni la base de datos.

3. **Ver Resultados**- **Agregar nuevos idiomas**:  

   - Ver las áreas principales identificadas  Solo se agregan archivos en la carpeta `lang/`.

   - Revisar puntuaciones detalladas- **Modificar la lógica de cálculo**:  

   - Leer recomendaciones vocacionales  Solo se modifica la clase `PersonalityTestFacade`.

   - Exportar resultados si es necesario- **Agregar nuevos tipos de test**:  

  Requiere ampliar la base de datos y la fachada, pero la estructura modular lo facilita.

### Para Profesores

### E. Conclusión ATAM

1. **Acceder al Panel de Administración**- **El diseño actual es robusto, seguro y preparado para el crecimiento.**

   - Hacer clic en "Ver Resultados del Curso" en el bloque- **Los riesgos son bajos y los trade-offs están bien balanceados para el contexto educativo de Moodle.**

   - O acceder directamente a `admin_view.php?cid=COURSE_ID`- **La arquitectura favorece la mantenibilidad, la internacionalización y la experiencia de usuario.**



2. **Analizar Resultados**---

   - Ver estadísticas generales del curso

   - Analizar distribución de áreas vocacionales## 🔄 Desarrollo y Releases

   - Revisar resultados individuales

   - Exportar datos para análisis externo### Sistema de Releases Automatizado

Este proyecto utiliza GitHub Actions para generar releases automáticamente:

## Exportación de Datos

- **Releases Oficiales**: Se crean cuando se actualiza la versión en `version.php` y se hace push a `main`

### Formato JSON- **Builds de Desarrollo**: Se generan automáticamente en cada push para testing

```json- **Packages**: Cada release incluye un ZIP listo para instalar en Moodle

{

  "user_info": {Ver [RELEASES.md](RELEASES.md) para más detalles sobre el sistema de releases.

    "id": 123,

    "fullname": "Juan Pérez",### Contribuir al Proyecto

    "email": "juan@example.com"1. Fork el repositorio

  },2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)

  "test_info": {3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)

    "completed": "2024-01-15 10:30:00",4. Push a la rama (`git push origin feature/nueva-funcionalidad`)

    "course_id": 55. Crea un Pull Request

  },

  "responses": {### Versionado

    "1": 1, "2": 0, "3": 1, ...Seguimos [Semantic Versioning](https://semver.org/):

  },- **MAJOR**: Cambios incompatibles

  "scores": {- **MINOR**: Nueva funcionalidad compatible

    "C": 8, "H": 12, "A": 3, "S": 14, "I": 6, "D": 2, "E": 9- **PATCH**: Bug fixes compatibles

  },

  "top_areas": ["S", "H"],---

  "has_ties": false

}## Recomendaciones Futuras

```

- Si el bloque crece mucho, migrar la fachada a un archivo/clase independiente.

### Formato CSV- Considerar agregar pruebas automáticas (PHPUnit, QUnit).

El CSV incluye columnas para información del usuario, fecha, puntuaciones por área y áreas principales.- Documentar con más detalle las funciones auxiliares en `lib.php`.

- Si se agregan más tipos de test, generalizar la lógica de guardado y cálculo.

## Solución de Problemas

---

### Test No Se Muestra

- Verificar que el usuario tenga la capacidad `block/chaside:take_test`## 📄 Licencia

- Comprobar que el bloque esté agregado al curso

- Revisar logs de Moodle para errores PHPEste proyecto está licenciado bajo los términos de la licencia GPL v3. Ver el archivo LICENSE para más detalles.



### Errores de Base de Datos## 👥 Créditos y Contacto

- Verificar que la tabla `chaside` existe

- Comprobar permisos de base de datosDesarrollado para la plataforma SAVIO UTB, siguiendo estándares de calidad y buenas prácticas de Moodle.

- Revisar el esquema en `db/install.xml`

- **Organización**: [ISCOUTB](https://github.com/ISCOUTB)

### Problemas de Exportación- **Repositorio**: [personality_test](https://github.com/ISCOUTB/personality_test)

- Verificar configuración de PHP para límites de memoria- **Issues**: [Reportar problemas](https://github.com/ISCOUTB/personality_test/issues)

- Comprobar permisos de escritura temporal

- Revisar configuración de headers HTTP---



## Desarrollo## 📊 Stats



### Requisitos![GitHub release](https://img.shields.io/github/v/release/ISCOUTB/personality_test)

- Moodle 3.5 o superior![GitHub issues](https://img.shields.io/github/issues/ISCOUTB/personality_test)

- PHP 7.2 o superior![GitHub stars](https://img.shields.io/github/stars/ISCOUTB/personality_test)

- Base de datos compatible con Moodle![GitHub forks](https://img.shields.io/github/forks/ISCOUTB/personality_test)


### Estructura del Código
- `ChasideFacade`: Clase principal con lógica del test
- Métodos estáticos para cálculos y utilidades
- Seguimiento de estándares de Moodle
- Documentación completa en código

## Licencia

Este bloque está licenciado bajo GPL v3 o posterior, compatible con Moodle.

## Historial de Versiones

### v1.0.0 (2024-01-15)
- Implementación inicial del test CHASIDE
- 98 preguntas en 7 áreas vocacionales
- Panel de administración completo
- Exportación JSON/CSV
- Soporte multiidioma (ES/EN)
- Interfaz responsiva