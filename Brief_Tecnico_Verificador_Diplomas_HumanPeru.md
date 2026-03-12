# BRIEF TÉCNICO
## Plugin WordPress: Verificador de Diplomas Digitales
### Asociación Human Perú — Documento para Practicante de Ingeniería de Sistemas

---

| Campo | Detalle |
|---|---|
| **Proyecto** | Plugin WordPress — Verificador de Diplomas Digitales |
| **Organización** | Asociación Human Perú (AHPERU) |
| **Sitio web** | humanperu.org.pe |
| **Plataforma** | WordPress + Divi (hosting compartido Yachay) |
| **Tipo de entregable** | Mini-plugin WordPress (.php) + tabla MySQL |
| **Campo de búsqueda** | Un solo campo: Número de serie del diploma |
| **Modalidad** | Presencial / remoto con acceso a credenciales |
| **Duración estimada** | 3 a 4 días hábiles de desarrollo |
| **Versión del brief** | 1.0 — Marzo 2026 |

---

## 1. Contexto del Proyecto

Human Perú es una ONG peruana dedicada a la promoción de la salud mental a través de educación, sensibilización y apoyo comunitario. Desde su fundación en 2024, organiza talleres, charlas y capacitaciones para las cuales emite certificados y diplomas digitales firmados por su presidente.

Actualmente, los diplomas emitidos se registran en una hoja de cálculo y se publican en el sitio web usando el plugin TablePress, el cual muestra una tabla pública con todos los registros. Esto presenta dos problemas principales:

- La lista completa de participantes es visible para cualquier visitante, lo que expone datos personales innecesariamente.
- No existe un mecanismo formal de verificación que permita a empleadores o instituciones confirmar la autenticidad de un diploma presentado.

El objetivo de este proyecto es construir un verificador profesional que permita consultar la validez de un diploma ingresando únicamente su código de serie (ej: `TG-2026-02-001`), sin exponer ningún otro dato del registro.

> 💡 **Dato de contexto:** el código de serie ya aparece impreso en cada diploma físico/digital. El caso de uso principal es: un empleador recibe un diploma de un candidato y quiere verificar su autenticidad ingresando el código en el sitio web de Human Perú.

---

## 2. Objetivo del Entregable

### 2.1 Objetivo general

Desarrollar un mini-plugin de WordPress que permita verificar la autenticidad de diplomas emitidos por Human Perú mediante la consulta por número de serie, almacenando los datos en una tabla MySQL propia dentro del hosting de la organización.

### 2.2 Objetivos específicos

1. Crear una tabla MySQL dedicada (`wp_human_diplomas`) en la base de datos del hosting Yachay.
2. Migrar los registros existentes del CSV actual a la nueva tabla MySQL.
3. Desarrollar un endpoint AJAX seguro en WordPress que reciba el código de serie y devuelva el resultado.
4. Construir el formulario HTML/CSS/JS del verificador, replicando el diseño del mockup provisto.
5. Registrar el shortcode `[verificar_diploma_human]` para insertarlo en cualquier página de WordPress/Divi.
6. Asegurar que el plugin sea independiente del tema activo (Divi) y de sus actualizaciones.
7. Documentar el código y entregar instrucciones de uso para el equipo de Human Perú.

---

## 3. Especificaciones Técnicas

### 3.1 Stack tecnológico

| Capa | Tecnología | Versión mínima |
|---|---|---|
| CMS | WordPress | 6.0+ |
| Tema activo | Divi (Elegant Themes) | Cualquiera |
| Lenguaje backend | PHP | 8.0+ |
| Base de datos | MySQL / MariaDB (hosting Yachay) | 5.7+ / 10.3+ |
| API de BD en WP | `$wpdb` (WordPress nativa) | Incluida en WP |
| Frontend | HTML5 + CSS3 + JavaScript ES6 | — |
| Comunicación async | WordPress AJAX (`wp_ajax` / `wp_ajax_nopriv`) | Incluida en WP |
| Seguridad | WordPress Nonces + `$wpdb->prepare()` | Incluida en WP |
| Panel DB | phpMyAdmin (cPanel Yachay) | — |

### 3.2 Estructura del plugin

El plugin se entregará como una carpeta con la siguiente estructura mínima:

```
/wp-content/plugins/
    └── human-verificador/
            ├── human-verificador.php    ← Archivo principal (lógica PHP + shortcode)
            ├── readme.txt               ← Documentación y registro de cambios
            └── assets/
                    ├── style.css        ← Estilos del formulario
                    └── script.js        ← Lógica AJAX en JavaScript
```

> ⚠️ **IMPORTANTE:** Al ser un plugin independiente (no un tema hijo), el código **NO se verá afectado** por actualizaciones de Divi ni de WordPress. El plugin puede activarse y desactivarse desde el panel de administración sin perder datos.

### 3.3 Diseño de la tabla MySQL

La tabla deberá llamarse `wp_human_diplomas` y contener los siguientes campos:

| Campo | Tipo | Descripción | Ejemplo |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT PK | Identificador interno | 1 |
| `numero_serie` | VARCHAR(30) UNIQUE NOT NULL | Código único del diploma | TG-2026-02-001 |
| `tipo_documento` | VARCHAR(30) NOT NULL | Diploma / Certificado / Constancia | Certificado |
| `nombre_completo` | VARCHAR(150) NOT NULL | Nombre completo del participante | Jaime Napán Ramírez |
| `nombre_curso` | VARCHAR(250) NOT NULL | Nombre de la actividad académica | Dependencia emocional... |
| `horas_academicas` | INT NOT NULL | Carga lectiva en horas | 3 |
| `modalidad` | VARCHAR(20) NOT NULL | Virtual / Presencial | Virtual |
| `instructor` | VARCHAR(100) NOT NULL | Nombre del docente o facilitador | Rolando Salazar Benítez |
| `lugar_emision` | VARCHAR(50) NOT NULL | Ciudad de emisión | Lima |
| `fecha_actividad` | DATE NOT NULL | Fecha en que se realizó la actividad | 2026-02-26 |
| `fecha_emision` | DATE NOT NULL | Fecha de emisión del diploma | 2026-02-27 |
| `estado` | TINYINT(1) DEFAULT 1 | 1 = activo / 0 = anulado | 1 |
| `creado_en` | TIMESTAMP DEFAULT NOW() | Fecha de carga al sistema | — |

**Script SQL de creación:**

```sql
CREATE TABLE wp_human_diplomas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie     VARCHAR(30)  NOT NULL UNIQUE,
    tipo_documento   VARCHAR(30)  NOT NULL,
    nombre_completo  VARCHAR(150) NOT NULL,
    nombre_curso     VARCHAR(250) NOT NULL,
    horas_academicas INT          NOT NULL,
    modalidad        VARCHAR(20)  NOT NULL,
    instructor       VARCHAR(100) NOT NULL,
    lugar_emision    VARCHAR(50)  NOT NULL,
    fecha_actividad  DATE         NOT NULL,
    fecha_emision    DATE         NOT NULL,
    estado           TINYINT(1)   DEFAULT 1,
    creado_en        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Índice para búsquedas ultrarrápidas por número de serie
CREATE INDEX idx_numero_serie ON wp_human_diplomas(numero_serie);
```

> 💡 El campo `estado` permite **anular diplomas sin borrarlos**. Si alguien consulta un código anulado, el sistema responde "Diploma anulado" en lugar de "No encontrado", lo que ayuda a detectar uso fraudulento.

### 3.4 Patrón del número de serie

Los códigos siguen este patrón fijo que el sistema debe validar **antes** de consultar la base de datos:

```
Formato:   XX-YYYY-MM-NNN
Ejemplo:   TG-2026-02-001
           CH-2026-01-003

Donde:
  XX   = Prefijo de 2 letras mayúsculas (ej: TG = Taller / CH = Charla)
  YYYY = Año de 4 dígitos
  MM   = Mes de 2 dígitos (01-12)
  NNN  = Número correlativo de 3 dígitos

Expresión regular sugerida: /^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/
```

---

## 4. Requerimientos Funcionales

### 4.1 Flujo de verificación (caso exitoso)

| Paso | Actor | Acción / Resultado |
|---|---|---|
| 1 | Visitante | Ingresa a `humanperu.org.pe/verificar` |
| 2 | Sistema | Muestra formulario con un campo: Código del diploma |
| 3 | Visitante | Escribe el código (ej: `TG-2026-02-001`) y hace clic en **VERIFICAR** |
| 4 | JavaScript | Valida el formato del código (regex). Si no cumple, muestra error **sin consultar al servidor** |
| 5 | JavaScript | Envía petición AJAX a WordPress con el código y el nonce de seguridad |
| 6 | PHP (WordPress) | Valida el nonce, sanitiza el input con `$wpdb->prepare()` |
| 7 | MySQL | Ejecuta `SELECT` en `wp_human_diplomas WHERE numero_serie = [codigo]` |
| 8 | PHP | Recibe el resultado y arma la respuesta JSON |
| 9 | JavaScript | Muestra tarjeta con los datos del diploma: nombre, curso, horas, fecha, modalidad |

### 4.2 Estados de respuesta del sistema

| Estado | Condición | Mensaje al usuario |
|---|---|---|
| ✅ VÁLIDO | Diploma encontrado y `estado = 1` | Muestra tarjeta completa con datos del diploma |
| ❌ NO ENCONTRADO | Código no existe en la base de datos | "El código ingresado no corresponde a ningún diploma emitido por Human Perú. Verifique el código e intente nuevamente." |
| ⚠️ ANULADO | Código existe pero `estado = 0` | "Este diploma ha sido anulado. Para más información, contáctenos en mesadepartes@humanperu.org.pe" |
| 🔴 FORMATO INVÁLIDO | El código no cumple el patrón `XX-YYYY-MM-NNN` | "El formato del código no es válido. Debe tener el formato XX-AAAA-MM-NNN (ej: TG-2026-02-001)" |
| 🔄 CARGANDO | Mientras se realiza la consulta | Indicador visual de carga (spinner o texto animado) |

### 4.3 Datos a mostrar en resultado válido

Cuando el diploma es válido, la tarjeta de resultado debe mostrar:

- Nombre completo del participante
- Tipo de documento (Diploma / Certificado / Constancia)
- Nombre de la actividad o curso
- Nombre del instructor / facilitador
- Horas académicas
- Fecha de realización de la actividad
- Modalidad (Virtual / Presencial)
- Lugar de emisión
- Número de serie del diploma

> 🔒 **Datos que NO deben mostrarse nunca:** DNI, correo electrónico, número de celular, ID interno, fecha de carga al sistema.

---

## 5. Requerimientos de Seguridad

La seguridad es un aspecto crítico. El practicante debe implementar **obligatoriamente** las siguientes medidas:

| N° | Medida | Cómo implementarla | Nivel |
|---|---|---|---|
| 1 | **Prepared Statements** | Usar `$wpdb->prepare()` en TODAS las consultas SQL. NUNCA concatenar el input del usuario directamente en el SQL. | OBLIGATORIO |
| 2 | **WordPress Nonces** | Generar un nonce con `wp_create_nonce()` en el formulario y verificarlo con `wp_verify_nonce()` en el handler PHP antes de procesar. | OBLIGATORIO |
| 3 | **Sanitización de input** | Usar `sanitize_text_field()` en PHP para limpiar el código antes de consultarlo. | OBLIGATORIO |
| 4 | **Validación de formato** | Verificar con regex en JavaScript **Y** en PHP que el código cumpla el patrón antes de ejecutar la consulta. | OBLIGATORIO |
| 5 | **Respuesta controlada** | El JSON de respuesta solo debe contener los campos definidos en §4.3. Nunca exponer el objeto completo de la base de datos. | OBLIGATORIO |
| 6 | **Rate limiting básico** | Limitar a 10 consultas por IP por minuto usando WordPress Transients. Responder HTTP 429 si se supera el límite. | RECOMENDADO |

---

## 6. Diseño del Formulario

El formulario debe replicar visualmente el mockup provisto por Human Perú. Los lineamientos de diseño son:

### 6.1 Colores de la marca Human Perú

| Elemento | Color HEX | Uso |
|---|---|---|
| Azul principal | `#1B3A6B` | Títulos, botón principal, bordes de tarjeta resultado |
| Amarillo acento | `#F5A623` | Borde superior de tarjeta de resultado válido, detalles |
| Fondo formulario | `#FFFFFF` | Fondo del card del formulario |
| Texto principal | `#222222` | Texto del formulario y resultados |
| Verde éxito | `#1A7A4A` | Encabezado de resultado VÁLIDO |
| Rojo error | `#8B0000` | Encabezado de resultado NO ENCONTRADO |
| Naranja advertencia | `#CC6600` | Encabezado de resultado ANULADO |

### 6.2 Maqueta de los estados del formulario

**Estado 1 — Formulario inicial:**
```
┌──────────────────────────────────────────────┐
│                                              │
│   🔍 Verifica la autenticidad de tu diploma  │
│   Ingresa el código que aparece en tu        │
│   certificado (ej: TG-2026-02-001)           │
│                                              │
│   ┌────────────────────────────────────────┐ │
│   │  TG-2026-02-001                        │ │
│   └────────────────────────────────────────┘ │
│                                              │
│   [        VERIFICAR DIPLOMA        ]        │
│                                              │
└──────────────────────────────────────────────┘
```

**Estado 2 — Resultado VÁLIDO:**
```
┌──────────────────────────────────────────────┐
│  ✅  DIPLOMA AUTÉNTICO Y VÁLIDO              │
│  ──────────────────────────────────────────  │
│  👤 Titular:    Jaime Napán Ramírez          │
│  📄 Tipo:       Certificado                  │
│  📚 Actividad:  Dependencia emocional...     │
│  🎓 Instructor: Rolando Salazar Benítez      │
│  🕐 Horas:      3 horas académicas           │
│  📅 Realizado:  26 de febrero de 2026        │
│  🖥️  Modalidad:  Virtual                     │
│  📍 Lugar:      Lima                         │
│  🔑 Código:     TG-2026-02-001               │
│                                              │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

**Estado 3 — NO encontrado:**
```
┌──────────────────────────────────────────────┐
│  ❌  No se encontró ningún diploma           │
│                                              │
│  El código ingresado no corresponde a        │
│  ningún certificado emitido por Human Perú.  │
│                                              │
│  ¿Tienes dudas? Escríbenos a:               │
│  mesadepartes@humanperu.org.pe               │
│                                              │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

**Estado 4 — Diploma ANULADO:**
```
┌──────────────────────────────────────────────┐
│  ⚠️   DIPLOMA ANULADO                        │
│                                              │
│  Este código existe en nuestros registros    │
│  pero el diploma ha sido revocado.           │
│  Contáctanos: mesadepartes@humanperu.org.pe  │
│                                              │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

### 6.3 Comportamiento del formulario

- El campo de código debe convertir automáticamente a mayúsculas mientras el usuario escribe.
- El botón VERIFICAR debe deshabilitarse mientras se procesa la consulta (evitar doble envío).
- La tarjeta de resultado aparece **debajo del formulario sin recargar la página** (AJAX).
- Incluir botón "Nueva consulta" que limpie el formulario para ingresar otro código.
- El diseño debe ser **responsive**: funcionar correctamente en móviles (375px), tablets y desktop (1280px+).

### 6.4 Inserción en WordPress/Divi

Una vez desarrollado, el verificador se inserta en cualquier página usando el shortcode:

```
[verificar_diploma_human]
```

Este shortcode se pega en un **módulo de Texto** o **módulo de Código** de Divi. No requiere configuración adicional.

---

## 7. Migración de Datos

El CSV actual (`Registro_de_diplomas_-_Registro.csv`) contiene los registros existentes. La migración se realiza **una sola vez** durante la implementación.

### 7.1 Mapeo de columnas CSV → MySQL

| Columna en CSV | Campo en MySQL | Observaciones |
|---|---|---|
| `id` | *(ignorar)* | El ID de MySQL se genera automáticamente |
| `numero_serie` | `numero_serie` | Usar tal cual |
| `tipo_documento` | `tipo_documento` | Usar tal cual |
| `nombre_completo` | `nombre_completo` | Usar tal cual |
| `dni` | *(ignorar)* | ❌ NO migrar. No se mostrará al público |
| `nombre_curso` | `nombre_curso` | Usar tal cual |
| `horas_academicas` | `horas_academicas` | Convertir a INTEGER |
| `Correo electrónico` | *(ignorar)* | ❌ NO migrar. Dato sensible |
| `Celular` | *(ignorar)* | ❌ NO migrar. Dato sensible |
| `modalidad` | `modalidad` | Usar tal cual |
| `fecha_inicio` | `fecha_actividad` | Usar `fecha_inicio` como `fecha_actividad` |
| `fecha_fin` | *(ignorar)* | No es necesaria para el verificador |
| `fecha_emision` | `fecha_emision` | Convertir formato `DD/MM/YYYY` → `YYYY-MM-DD` |
| `instructor` | `instructor` | Usar tal cual |
| `lugar_emision` | `lugar_emision` | Usar tal cual |

### 7.2 Pasos de migración recomendados

1. Abrir **phpMyAdmin** desde el cPanel de Yachay.
2. Ejecutar el script SQL de creación de tabla (ver §3.3).
3. Usar la función de importación de phpMyAdmin para cargar el CSV, mapeando las columnas según la tabla anterior.
4. Verificar que todos los registros se importaron correctamente revisando el conteo total.
5. Realizar una consulta de prueba con el código `TG-2026-02-001` para confirmar que el sistema funciona.

> 📌 **Para futuros diplomas:** el equipo de Human Perú agregará nuevas filas directamente en phpMyAdmin, o el practicante puede implementar una página de administración mínima dentro de WordPress visible solo para administradores.

---

## 8. Plan de Trabajo Sugerido

| Día | Turno | Tarea | Entregable |
|---|---|---|---|
| **1** | Mañana | Revisar brief. Configurar entorno: acceso WordPress, FTP, phpMyAdmin. Crear carpeta del plugin. | Accesos confirmados, carpeta del plugin creada |
| **1** | Tarde | Diseñar y crear tabla MySQL. Migrar datos del CSV a MySQL usando phpMyAdmin. | Tabla `wp_human_diplomas` con datos reales cargados |
| **2** | Mañana | Desarrollar función PHP de búsqueda con `$wpdb->prepare()`. Registrar endpoint AJAX con `wp_ajax_nopriv`. Implementar nonce y sanitización. | Endpoint AJAX funcional (testeable con Postman o curl) |
| **2** | Tarde | Construir HTML y CSS del formulario según mockup. Implementar JavaScript con `fetch()` para llamar al endpoint AJAX. | Formulario visible y conectado con el backend |
| **3** | Mañana | Integrar flujo completo: formulario → AJAX → PHP → MySQL → respuesta. Mostrar tarjeta de resultado (válido / no encontrado / anulado). | Flujo end-to-end funcionando en entorno de prueba |
| **3** | Tarde | Pruebas con diplomas reales. Ajustes de diseño responsive. Verificar seguridad (nonce, prepare, validación de formato). | Sistema probado y sin errores visibles |
| **4** | Mañana | Documentar código con comentarios. Escribir instrucciones de uso para el equipo. Crear página `/verificar` en WordPress e insertar shortcode. | Plugin en producción. Página verificar activa |
| **4** | Tarde | Pruebas finales con el equipo de Human Perú. Correcciones menores. Entrega formal del código. | ✅ Proyecto entregado y aprobado |

---

## 9. Criterios de Aceptación

El proyecto se considerará completado cuando cumpla **TODOS** los siguientes criterios:

| # | Criterio | Cómo se verifica |
|---|---|---|
| 1 | El plugin está instalado y activo en WordPress sin errores | Panel → Plugins → Human Verificador → Activo |
| 2 | El shortcode `[verificar_diploma_human]` funciona en una página Divi | Insertar en módulo de código. Ver formulario correctamente |
| 3 | Consultar `TG-2026-02-001` devuelve datos correctos de Jaime Napán Ramírez | Ingresar código. Ver tarjeta verde con datos correctos |
| 4 | Consultar un código inexistente muestra mensaje de error claro | Ingresar `XX-9999-99-999`. Ver mensaje rojo de no encontrado |
| 5 | Un código con formato inválido muestra error **sin consultar al servidor** | Ingresar `12345`. Ver mensaje de formato sin llamada al servidor |
| 6 | La lista completa de diplomas **NO** es visible públicamente en ningún momento | Inspeccionar tráfico de red. Solo se ve la respuesta puntual del código consultado |
| 7 | El plugin **NO** se rompe al actualizar Divi | Actualizar Divi desde el panel. Volver a la página verificar. Todo sigue funcionando |
| 8 | El código está comentado y es legible | Revisión de código por el supervisor técnico |
| 9 | Existe un documento de instrucciones para agregar nuevos diplomas | Archivo `readme.txt` o página interna de admin |
| 10 | El diseño es responsive en móvil (375px) y desktop (1280px) | Probar en Chrome DevTools con viewport de 375px |

---

## 10. Accesos y Recursos Necesarios

Para iniciar el desarrollo, el practicante necesita que Human Perú le proporcione:

| Recurso | Para qué se usa | Prioridad |
|---|---|---|
| Usuario y contraseña de WordPress (rol Administrador) | Instalar el plugin, crear páginas | 🔴 URGENTE |
| Credenciales FTP del hosting Yachay | Subir archivos del plugin si es necesario | 🔴 URGENTE |
| Acceso a phpMyAdmin (cPanel Yachay) | Crear tabla MySQL, importar CSV | 🔴 URGENTE |
| Archivo CSV: `Registro_de_diplomas_-_Registro.csv` | Migración inicial de datos | 🔴 URGENTE |
| Imagen de referencia del mockup del formulario | Diseño del verificador | 🟡 IMPORTANTE |
| Logotipo de Human Perú en PNG o SVG | Incluir en la tarjeta de resultado (opcional) | 🟢 OPCIONAL |
| Acceso al repositorio Git (si existe) | Control de versiones del código | 🟢 RECOMENDADO |

---

## 11. Preguntas para Evaluar al Practicante

Antes de asignar el proyecto, se recomienda hacer estas preguntas para confirmar que el practicante tiene el nivel técnico mínimo requerido.

### Preguntas básicas (debe responder todas)

1. ¿Sabes crear un plugin básico de WordPress? ¿Cuál es la estructura mínima de un plugin?
2. ¿Qué es un shortcode en WordPress y cómo se registra con `add_shortcode()`?
3. ¿Qué es `$wpdb` en WordPress? ¿Por qué se usa `$wpdb->prepare()` en lugar de concatenar strings en SQL?
4. ¿Qué es AJAX? ¿Cómo se usa `fetch()` en JavaScript para enviar datos a un servidor?
5. ¿Qué es un nonce en seguridad web? ¿Para qué sirve en WordPress?

### Preguntas intermedias (si responde bien, puede trabajar de forma autónoma)

1. ¿Puedes explicar la diferencia entre `wp_ajax_` y `wp_ajax_nopriv_` en WordPress?
2. ¿Qué es `sanitize_text_field()` y cuándo se usa?
3. ¿Cómo crearías un índice en MySQL para optimizar búsquedas por una columna específica?

> 📋 Si el practicante responde las preguntas básicas pero no las intermedias, puede realizar el proyecto **con guía y documentación de apoyo**. Si no responde las básicas, se recomienda que primero complete un tutorial de desarrollo de plugins en WordPress antes de iniciar.

---

## 12. Información de Contacto del Proyecto

| Rol | Nombre | Contacto |
|---|---|---|
| Supervisor del proyecto / Presidente | Rolando Salazar Benítez | humanperu.org.pe |
| Directora Ejecutiva | Isabel Orbegoso Delgado | — |
| Gestor de Tecnología e Información | Óscar Román Quispe | — |
| Mesa de partes / consultas técnicas | — | mesadepartes@humanperu.org.pe |
| Teléfono de contacto | — | 923 322 521 |
| Dirección | Av. Universitaria 2017 Of. 705 | San Miguel, Lima — Perú |

---

## 13. Referencias y Recursos de Apoyo

### Documentación oficial de WordPress

- Plugin Handbook: https://developer.wordpress.org/plugins/
- Referencia `$wpdb`: https://developer.wordpress.org/reference/classes/wpdb/
- WordPress Nonces: https://developer.wordpress.org/plugins/security/nonces/
- WordPress AJAX: https://developer.wordpress.org/plugins/javascript/ajax/

### Archivos de referencia provistos por Human Perú

- CSV de diplomas: `Registro_de_diplomas_-_Registro.csv`
- Ejemplo de diploma en PDF: `TG-2026-02-001.pdf` (Jaime Napán Ramírez)
- Mockup del formulario: imagen de referencia del diseño deseado
- Este documento: `Brief_Tecnico_Verificador_Diplomas_HumanPeru.md`

---

*Asociación Human Perú — Promoviendo la Salud Mental*  
*humanperu.org.pe · mesadepartes@humanperu.org.pe · 923 322 521*  
*Av. Universitaria 2017 Of. 705, San Miguel, Lima — Perú*  
*Versión 1.0 — Marzo 2026*
