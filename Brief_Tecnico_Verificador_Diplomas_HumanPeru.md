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
| **Versión del brief** | 1.1 — Marzo 2026 |

---

## Registro de cambios

| Versión | Fecha | Cambios |
|---|---|---|
| 1.0 | Marzo 2026 | Versión inicial del brief técnico |
| 1.1 | Marzo 2026 | Actualización de estructura de tabla MySQL: separación de tipo y número de documento de identificación, fechas de inicio y fin de actividad, campo `nombre_empresa` para certificados empresariales, campo `nota_adicional` opcional |

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

### 3.3 Diseño de la tabla MySQL (v1.1)

La tabla deberá llamarse `wp_human_diplomas` y contener los siguientes campos:

| Campo | Tipo | Descripción | Ejemplo |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT PK | Identificador interno | 1 |
| `numero_serie` | VARCHAR(30) UNIQUE NOT NULL | Código único del diploma | TG-2026-02-001 |
| `tipo_doc_emitido` | VARCHAR(30) NOT NULL | Tipo de documento emitido | Certificado |
| `nombre_curso` | VARCHAR(250) NOT NULL | Nombre de la actividad académica | Dependencia emocional... |
| `tipo_doc_identificacion` | VARCHAR(30) NOT NULL | Tipo de doc. del titular | DNI / CE / RUC / Pasaporte |
| `numero_doc_identificacion` | VARCHAR(30) NOT NULL | Número del documento del titular | 12345678 |
| `nombre_completo` | VARCHAR(150) NOT NULL | Nombre del titular o razón social | Jaime Napán Ramírez |
| `nombre_empresa` | VARCHAR(150) DEFAULT NULL | Razón social (solo si es empresarial) | Empresa SAC |
| `horas_academicas` | INT NOT NULL | Carga lectiva en horas | 3 |
| `modalidad` | VARCHAR(20) NOT NULL | Virtual / Presencial | Virtual |
| `instructor` | VARCHAR(100) NOT NULL | Nombre del docente o facilitador | Rolando Salazar Benítez |
| `lugar_emision` | VARCHAR(50) NOT NULL | Ciudad de emisión | Lima |
| `fecha_inicio_actividad` | DATE NOT NULL | Fecha de inicio de la actividad | 2026-02-26 |
| `fecha_fin_actividad` | DATE NOT NULL | Fecha de fin de la actividad | 2026-02-26 |
| `fecha_emision` | DATE NOT NULL | Fecha de emisión del diploma | 2026-02-27 |
| `estado` | TINYINT(1) DEFAULT 1 | 1 = activo / 0 = anulado | 1 |
| `nota_adicional` | VARCHAR(200) DEFAULT NULL | Nota libre opcional | Representa a Empresa SAC |
| `creado_en` | TIMESTAMP DEFAULT NOW() | Fecha de carga al sistema | — |

**Script SQL de creación (v1.1):**

```sql
-- =============================================
-- Human Perú — Verificador de Diplomas
-- Script de creación de tabla MySQL / MariaDB
-- Versión 1.2 — Marzo 2026
-- Ejecutar en phpMyAdmin del hosting Yachay
-- =============================================

CREATE TABLE IF NOT EXISTS wp_human_diplomas (
    id                          INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie                VARCHAR(30)  NOT NULL UNIQUE,        -- Ej: TG-2026-02-001
    tipo_doc_emitido            VARCHAR(30)  NOT NULL,               -- Diploma / Certificado / Constancia
    nombre_curso                VARCHAR(250) NOT NULL,
    tipo_doc_identificacion     VARCHAR(30)  NOT NULL,               -- DNI / CE / RUC / Pasaporte
    numero_doc_identificacion   VARCHAR(30)  NOT NULL,
    nombre_completo             VARCHAR(150) NOT NULL,               -- Persona natural o razón social
    nombre_empresa              VARCHAR(150) DEFAULT NULL,           -- Solo si el doc. es empresarial
    horas_academicas            INT          NOT NULL,
    modalidad                   VARCHAR(20)  NOT NULL,               -- Virtual / Presencial
    instructor                  VARCHAR(100) NOT NULL,
    lugar_emision               VARCHAR(50)  NOT NULL,
    fecha_inicio_actividad      DATE         NOT NULL,
    fecha_fin_actividad         DATE         NOT NULL,
    fecha_emision               DATE         NOT NULL,
    estado                      TINYINT(1)   DEFAULT 1,              -- 1=activo / 0=anulado
    nota_adicional              VARCHAR(200) DEFAULT NULL,           -- Campo libre opcional
    creado_en                   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Índice para búsquedas rápidas por número de serie (obligatorio)
CREATE INDEX IF NOT EXISTS idx_numero_serie
ON wp_human_diplomas(numero_serie);

-- Índice para búsquedas por documento de identificación (recomendado)
CREATE INDEX IF NOT EXISTS idx_num_doc
ON wp_human_diplomas(numero_doc_identificacion);
```

> 💡 **Campo `estado`:** permite **anular diplomas sin borrarlos**. Si alguien consulta un código anulado, el sistema responde "Diploma anulado" en lugar de "No encontrado".

> 🏢 **Campo `nombre_empresa`:** se llena solo cuando el certificado es emitido a nombre de una empresa. Para personas naturales debe quedar en `NULL`.

> 📝 **Campo `nota_adicional`:** campo libre y opcional. Al ser `DEFAULT NULL` no es obligatorio llenarlo en cada registro.

### 3.4 Valores válidos por campo

| Campo | Valores válidos |
|---|---|
| `tipo_doc_emitido` | `Diploma`, `Certificado`, `Constancia`, `Reconocimiento` |
| `tipo_doc_identificacion` | `DNI`, `CE` (Carné de Extranjería), `RUC`, `Pasaporte` |
| `modalidad` | `Virtual`, `Presencial`, `Semipresencial` |
| `estado` | `1` (activo), `0` (anulado) |

### 3.5 Patrón del número de serie

```
Formato:   XX-YYYY-MM-NNN
Ejemplo:   TG-2026-02-001  /  CH-2026-01-003

Donde:
  XX   = Prefijo de 2 letras mayúsculas (TG = Taller / CH = Charla)
  YYYY = Año de 4 dígitos
  MM   = Mes de 2 dígitos (01-12)
  NNN  = Número correlativo de 3 dígitos

Expresión regular: /^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/
```

---

## 4. Requerimientos Funcionales

### 4.1 Flujo de verificación (caso exitoso)

| Paso | Actor | Acción / Resultado |
|---|---|---|
| 1 | Visitante | Ingresa a `humanperu.org.pe/verificar` |
| 2 | Sistema | Muestra formulario con un campo: Código del diploma |
| 3 | Visitante | Escribe el código (ej: `TG-2026-02-001`) y hace clic en **VERIFICAR** |
| 4 | JavaScript | Valida el formato (regex). Si no cumple, muestra error **sin consultar al servidor** |
| 5 | JavaScript | Envía petición AJAX con el código y el nonce de seguridad |
| 6 | PHP | Valida el nonce, sanitiza el input con `$wpdb->prepare()` |
| 7 | MySQL | Ejecuta `SELECT` en `wp_human_diplomas WHERE numero_serie = [codigo]` |
| 8 | PHP | Arma la respuesta JSON con los campos permitidos |
| 9 | JavaScript | Muestra tarjeta de resultado (personal o empresarial según corresponda) |

### 4.2 Estados de respuesta del sistema

| Estado | Condición | Mensaje al usuario |
|---|---|---|
| ✅ VÁLIDO | Diploma encontrado y `estado = 1` | Tarjeta completa con datos del diploma |
| ❌ NO ENCONTRADO | Código no existe en la base de datos | "El código ingresado no corresponde a ningún diploma emitido por Human Perú." |
| ⚠️ ANULADO | Código existe pero `estado = 0` | "Este diploma ha sido anulado. Contáctenos en mesadepartes@humanperu.org.pe" |
| 🔴 FORMATO INVÁLIDO | El código no cumple el patrón | "El formato del código no es válido. Debe tener el formato XX-AAAA-MM-NNN (ej: TG-2026-02-001)" |
| 🔄 CARGANDO | Mientras se realiza la consulta | Indicador visual de carga (spinner) |

### 4.3 Datos a mostrar en resultado válido

**Campos siempre visibles:**
- Tipo de documento emitido
- Nombre del titular o razón social (`nombre_completo`)
- Nombre de la actividad o curso
- Instructor / facilitador
- Horas académicas
- Fechas de la actividad (inicio y fin)
- Modalidad
- Lugar de emisión
- Número de serie
- Nota adicional *(solo si no está vacía)*

**Campo condicional:**
- Nombre de la empresa *(solo si `nombre_empresa` no es NULL)*

> 🔒 **Datos que NO deben mostrarse nunca:** tipo y número de documento de identificación (DNI/CE/RUC), correo, celular, ID interno, fecha de carga al sistema.

### 4.4 Lógica personal vs empresarial

```
SI nombre_empresa = NULL  →  Certificado PERSONAL
    Mostrar: "Otorgado a: Jaime Napán Ramírez"
    Ocultar: fila de empresa

SI nombre_empresa ≠ NULL  →  Certificado EMPRESARIAL
    Mostrar: "Otorgado a: Empresa SAC"
    Mostrar: "Representado por: Jaime Napán Ramírez"
```

---

## 5. Requerimientos de Seguridad

| N° | Medida | Cómo implementarla | Nivel |
|---|---|---|---|
| 1 | **Prepared Statements** | Usar `$wpdb->prepare()` en TODAS las consultas. NUNCA concatenar el input en el SQL. | OBLIGATORIO |
| 2 | **WordPress Nonces** | Generar con `wp_create_nonce()` y verificar con `wp_verify_nonce()` antes de procesar. | OBLIGATORIO |
| 3 | **Sanitización de input** | Usar `sanitize_text_field()` en PHP antes de la consulta. | OBLIGATORIO |
| 4 | **Validación de formato** | Verificar regex en JavaScript **Y** en PHP antes de ejecutar la consulta. | OBLIGATORIO |
| 5 | **Respuesta controlada** | El JSON solo expone los campos de §4.3. Nunca el objeto completo de la base de datos. | OBLIGATORIO |
| 6 | **Rate limiting básico** | Máx. 10 consultas por IP por minuto con WordPress Transients. HTTP 429 si se supera. | RECOMENDADO |

---

## 6. Diseño del Formulario

### 6.1 Colores de la marca Human Perú

| Elemento | Color HEX | Uso |
|---|---|---|
| Azul principal | `#1B3A6B` | Títulos, botón, bordes de tarjeta |
| Amarillo acento | `#F5A623` | Borde superior tarjeta válida |
| Verde éxito | `#1A7A4A` | Encabezado resultado VÁLIDO |
| Rojo error | `#8B0000` | Encabezado NO ENCONTRADO |
| Naranja advertencia | `#CC6600` | Encabezado ANULADO |

### 6.2 Maqueta de estados del formulario

**Estado 1 — Formulario inicial:**
```
┌──────────────────────────────────────────────┐
│   🔍 Verifica la autenticidad de tu diploma  │
│   Ingresa el código de tu certificado        │
│   (ej: TG-2026-02-001)                       │
│   ┌────────────────────────────────────────┐ │
│   │  TG-2026-02-001                        │ │
│   └────────────────────────────────────────┘ │
│   [        VERIFICAR DIPLOMA        ]        │
└──────────────────────────────────────────────┘
```

**Estado 2a — VÁLIDO (personal):**
```
┌──────────────────────────────────────────────┐
│  ✅  DIPLOMA AUTÉNTICO Y VÁLIDO              │
│  📄 Tipo:       Certificado                  │
│  👤 Otorgado a: Jaime Napán Ramírez          │
│  📚 Actividad:  Dependencia emocional...     │
│  🎓 Instructor: Rolando Salazar Benítez      │
│  🕐 Horas:      3 horas académicas           │
│  📅 Realizado:  26 al 26 de febrero de 2026  │
│  🖥️  Modalidad:  Virtual                     │
│  📍 Lugar:      Lima                         │
│  🔑 Código:     TG-2026-02-001               │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

**Estado 2b — VÁLIDO (empresarial):**
```
┌──────────────────────────────────────────────┐
│  ✅  DIPLOMA AUTÉNTICO Y VÁLIDO              │
│  📄 Tipo:          Certificado               │
│  🏢 Otorgado a:    Empresa SAC               │
│  👤 Representado:  Jaime Napán Ramírez        │
│  📚 Actividad:     Dependencia emocional...  │
│  🎓 Instructor:    Rolando Salazar Benítez   │
│  🕐 Horas:         3 horas académicas        │
│  📅 Realizado:     26 al 26 de feb. 2026     │
│  🖥️  Modalidad:     Virtual                  │
│  📍 Lugar:         Lima                      │
│  🔑 Código:        TG-2026-02-001            │
│  📝 Nota:          Participa como empresa    │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

**Estado 3 — NO encontrado:**
```
┌──────────────────────────────────────────────┐
│  ❌  No se encontró ningún diploma           │
│  El código no corresponde a ningún           │
│  certificado emitido por Human Perú.         │
│  Escríbenos: mesadepartes@humanperu.org.pe   │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

**Estado 4 — ANULADO:**
```
┌──────────────────────────────────────────────┐
│  ⚠️   DIPLOMA ANULADO                        │
│  Este diploma ha sido revocado.              │
│  Contáctanos: mesadepartes@humanperu.org.pe  │
│  [       Nueva consulta       ]              │
└──────────────────────────────────────────────┘
```

### 6.3 Comportamiento del formulario

- Convertir automáticamente el código a mayúsculas mientras el usuario escribe.
- Deshabilitar el botón VERIFICAR mientras se procesa la consulta.
- Mostrar resultado debajo del formulario **sin recargar la página** (AJAX).
- Botón "Nueva consulta" que limpia el formulario y oculta el resultado.
- Diseño **responsive**: móvil (375px), tablet y desktop (1280px+).
- Ocultar la fila `nota_adicional` si el campo es NULL o está vacío.
- Ocultar la fila `nombre_empresa` si el campo es NULL.

### 6.4 Inserción en WordPress/Divi

```
[verificar_diploma_human]
```

Pegar en un **módulo de Texto** o **módulo de Código** de Divi. Sin configuración adicional.

---

## 7. Migración de Datos

### 7.1 Mapeo CSV → MySQL (v1.1)

| Columna en CSV | Campo en MySQL | Observaciones |
|---|---|---|
| `id` | *(ignorar)* | El ID se genera automáticamente |
| `numero_serie` | `numero_serie` | Usar tal cual |
| `tipo_documento` | `tipo_doc_emitido` | Renombrado en v1.1 |
| `nombre_curso` | `nombre_curso` | Usar tal cual |
| `dni` | `numero_doc_identificacion` | Migrar valor. Si es `ND`, conservar como `ND` |
| *(no existía)* | `tipo_doc_identificacion` | Colocar `DNI` por defecto para registros actuales |
| `nombre_completo` | `nombre_completo` | Usar tal cual |
| *(no existía)* | `nombre_empresa` | Colocar `NULL` para todos los registros actuales |
| `horas_academicas` | `horas_academicas` | Convertir a INTEGER |
| `Correo electrónico` | *(ignorar)* | ❌ NO migrar. Dato sensible |
| `Celular` | *(ignorar)* | ❌ NO migrar. Dato sensible |
| `modalidad` | `modalidad` | Usar tal cual |
| `fecha_inicio` | `fecha_inicio_actividad` | Convertir `DD/MM/YYYY` → `YYYY-MM-DD` |
| `fecha_fin` | `fecha_fin_actividad` | Ahora se migra. Convertir `DD/MM/YYYY` → `YYYY-MM-DD` |
| `fecha_emision` | `fecha_emision` | Convertir `DD/MM/YYYY` → `YYYY-MM-DD` |
| `instructor` | `instructor` | Usar tal cual |
| `lugar_emision` | `lugar_emision` | Usar tal cual |
| *(no existía)* | `nota_adicional` | Colocar `NULL` para todos los registros actuales |

> 📌 **Registros con `dni = ND`:** conservar `ND` en `numero_doc_identificacion` y completar manualmente cuando sea posible.

### 7.2 Pasos de migración

1. Abrir **phpMyAdmin** desde el cPanel de Yachay.
2. Ejecutar el script SQL v1.1 (ver §3.3).
3. Agregar al CSV las columnas faltantes: `tipo_doc_identificacion` (valor `DNI`), `nombre_empresa` (vacío) y `nota_adicional` (vacío).
4. Importar el CSV en phpMyAdmin mapeando columnas según §7.1.
5. Verificar conteo total de registros importados.
6. Prueba con código `TG-2026-02-001` para confirmar funcionamiento.

---

## 8. Plan de Trabajo Sugerido

| Día | Turno | Tarea | Entregable |
|---|---|---|---|
| **1** | Mañana | Revisar brief. Configurar entorno: WordPress, FTP, phpMyAdmin. Crear carpeta del plugin. | Accesos confirmados, carpeta creada |
| **1** | Tarde | Crear tabla MySQL v1.1. Preparar y migrar datos del CSV. | Tabla con datos reales cargados |
| **2** | Mañana | Función PHP de búsqueda + endpoint AJAX + nonce + sanitización. | Endpoint funcional (testeable con Postman) |
| **2** | Tarde | HTML/CSS del formulario. Lógica personal vs empresarial. JavaScript AJAX. | Formulario visible y conectado |
| **3** | Mañana | Integración completa + tarjeta de resultado con campos condicionales. | Flujo end-to-end funcionando |
| **3** | Tarde | Pruebas con diplomas reales. Ajustes responsive. Verificación de seguridad. | Sistema probado sin errores |
| **4** | Mañana | Documentar código. Crear página `/verificar`. Insertar shortcode. | Plugin en producción |
| **4** | Tarde | Pruebas finales con el equipo. Correcciones. Entrega formal. | ✅ Proyecto entregado |

---

## 9. Criterios de Aceptación

| # | Criterio | Cómo se verifica |
|---|---|---|
| 1 | Plugin instalado y activo sin errores | Panel → Plugins → Human Verificador → Activo |
| 2 | Shortcode `[verificar_diploma_human]` funciona en página Divi | Insertar en módulo de código. Ver formulario |
| 3 | `TG-2026-02-001` devuelve datos correctos de Jaime Napán Ramírez | Ver tarjeta verde con datos correctos |
| 4 | Código inexistente muestra mensaje de error | Ingresar `XX-9999-99-999`. Ver mensaje rojo |
| 5 | Formato inválido muestra error **sin consultar servidor** | Ingresar `12345`. Sin llamada al servidor |
| 6 | Certificado empresarial muestra empresa y representante | Probar código con `nombre_empresa` lleno |
| 7 | `nota_adicional` aparece solo si tiene contenido | Probar con registro con nota y sin nota |
| 8 | Lista de diplomas NO es visible públicamente | Inspeccionar tráfico de red. Solo respuesta puntual |
| 9 | Plugin NO se rompe al actualizar Divi | Actualizar Divi. Verificar que sigue funcionando |
| 10 | Código comentado y legible | Revisión por supervisor técnico |
| 11 | Instrucciones para agregar nuevos diplomas | Archivo `readme.txt` o página de admin |
| 12 | Diseño responsive en móvil (375px) y desktop (1280px) | Probar en Chrome DevTools |

---

## 10. Accesos y Recursos Necesarios

| Recurso | Para qué se usa | Prioridad |
|---|---|---|
| WordPress (rol Administrador) | Instalar plugin, crear páginas | 🔴 URGENTE |
| FTP del hosting Yachay | Subir archivos del plugin | 🔴 URGENTE |
| phpMyAdmin (cPanel Yachay) | Crear tabla MySQL, importar CSV | 🔴 URGENTE |
| CSV: `Registro_de_diplomas_-_Registro.csv` | Migración inicial de datos | 🔴 URGENTE |
| Mockup del formulario (imagen) | Referencia de diseño | 🟡 IMPORTANTE |
| Logotipo de Human Perú (PNG/SVG) | Tarjeta de resultado | 🟢 OPCIONAL |
| Repositorio GitHub | Control de versiones | 🟢 RECOMENDADO |

---

## 11. Preguntas para Evaluar al Practicante

### Preguntas básicas (debe responder todas)

1. ¿Sabes crear un plugin básico de WordPress? ¿Cuál es la estructura mínima?
2. ¿Qué es un shortcode y cómo se registra con `add_shortcode()`?
3. ¿Qué es `$wpdb`? ¿Por qué se usa `$wpdb->prepare()` en lugar de concatenar strings en SQL?
4. ¿Qué es AJAX? ¿Cómo se usa `fetch()` para enviar datos a un servidor?
5. ¿Qué es un nonce en seguridad web? ¿Para qué sirve en WordPress?

### Preguntas intermedias (si responde bien, trabaja de forma autónoma)

1. ¿Diferencia entre `wp_ajax_` y `wp_ajax_nopriv_` en WordPress?
2. ¿Qué es `sanitize_text_field()` y cuándo se usa?
3. ¿Cómo crearías un índice en MySQL para optimizar búsquedas?
4. ¿Cómo mostrarías u ocultarías un elemento HTML condicionalmente en JavaScript?

> 📋 Si responde las básicas pero no las intermedias: puede trabajar **con guía**. Si no responde las básicas: completar primero un tutorial de plugins WordPress.

---

## 12. Información de Contacto del Proyecto

| Rol | Nombre | Contacto |
|---|---|---|
| Supervisor del proyecto / Presidente | Rolando Salazar Benítez | humanperu.org.pe |
| Directora Ejecutiva | Isabel Orbegoso Delgado | — |
| Gestor de Tecnología e Información | Óscar Román Quispe | — |
| Mesa de partes / consultas técnicas | — | mesadepartes@humanperu.org.pe |
| Teléfono | — | 923 322 521 |
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
- Ejemplo de diploma PDF: `TG-2026-02-001.pdf`
- Mockup del formulario: imagen de referencia
- Script SQL: `sql/crear_tabla.sql` (v1.1)
- Este documento: `Brief_Tecnico_Verificador_Diplomas_HumanPeru.md`

---

*Asociación Human Perú — Promoviendo la Salud Mental*  
*humanperu.org.pe · mesadepartes@humanperu.org.pe · 923 322 521*  
*Av. Universitaria 2017 Of. 705, San Miguel, Lima — Perú*  
*Versión 1.2 — Marzo 2026*
