# 📜 Documentación Técnica — Plugin Verificador de Diplomas

## Asociación Human Perú — `human-verificador`

> **Versión:** 1.1 | **Última actualización:** Marzo 2026  
> **Autor:** Equipo de Tecnología — Human Perú  
> **Shortcode:** `[verificador_diplomas]`

---

## Tabla de contenidos

1. [Descripción general](#1-descripción-general)
2. [Estructura de archivos](#2-estructura-de-archivos)
3. [Instalación](#3-instalación)
4. [Base de datos](#4-base-de-datos)
5. [Documentación del código](#5-documentación-del-código)
   - [PHP — human-verificador.php](#51-php--human-verificadorphp)
   - [JavaScript — verifier.js](#52-javascript--verifierjs)
   - [CSS — style.css](#53-css--stylecss)
6. [Flujo completo del sistema](#6-flujo-completo-del-sistema)
7. [Estados de respuesta](#7-estados-de-respuesta)
8. [Seguridad implementada](#8-seguridad-implementada)
9. [Cómo agregar nuevos diplomas](#9-cómo-agregar-nuevos-diplomas)
10. [Pruebas realizadas](#10-pruebas-realizadas)
11. [Registro de cambios](#11-registro-de-cambios)

---

## 1. Descripción general

Plugin de WordPress que permite verificar la autenticidad de diplomas y certificados emitidos por la Asociación Human Perú. El visitante ingresa el número de serie que aparece impreso en su diploma y el sistema confirma si es válido, está anulado o no existe en los registros.

### Problema que resuelve

| Antes (TablePress)                                   | Después (este plugin)                                     |
| ---------------------------------------------------- | --------------------------------------------------------- |
| Lista completa de participantes visible públicamente | Solo se muestra el diploma consultado                     |
| Sin mecanismo formal de verificación                 | Verificación instantánea por código de serie              |
| Datos personales expuestos (DNI, correo, celular)    | Solo se exponen datos del diploma, nunca datos personales |

### Caso de uso principal

```
Empleador recibe diploma de un candidato
        ↓
Ingresa el código impreso en el diploma (ej: TG-2026-02-001)
        ↓
El sistema confirma: nombre, curso, horas, fecha, instructor
        ↓
Empleador verifica autenticidad sin contactar a Human Perú
```

---

## 2. Estructura de archivos

```
wp-content/plugins/
└── human-verificador/
        ├── human-verificador.php   ← Lógica principal del plugin (PHP)
        ├── readme.txt              ← Información del plugin para WordPress
        └── assets/
                ├── style.css       ← Estilos del formulario y resultados
                └── js/
                     └── verifier.js ← Lógica del frontend (JavaScript)
```

---

## 3. Instalación

### Requisitos

| Componente      | Versión mínima                   |
| --------------- | -------------------------------- |
| WordPress       | 6.0+                             |
| PHP             | 8.0+                             |
| MySQL / MariaDB | 5.7+ / 10.3+                     |
| Tema            | Cualquiera (compatible con Divi) |

### Pasos

**1. Subir el plugin**

```
WordPress Admin → Plugins → Añadir nuevo → Subir plugin
→ Comprimir carpeta human-verificador/ en .zip
→ Subir y activar
```

O vía FTP: copiar la carpeta `human-verificador/` a `/wp-content/plugins/`.

**2. Crear la tabla en la base de datos**

Ejecutar el script `sql/crear_tabla.sql` en phpMyAdmin del hosting.

**3. Insertar el shortcode en una página**

```
WordPress → Páginas → Nueva página
→ Título: "Verificar Diploma"
→ Slug: /verificar
→ Insertar bloque de código o módulo Divi
→ Pegar: [verificador_diplomas]
→ Publicar
```

---

## 4. Base de datos

### Tabla: `wp_human_diplomas`

```sql
CREATE TABLE IF NOT EXISTS wp_human_diplomas (
    id                          INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie                VARCHAR(30)  NOT NULL UNIQUE,
    tipo_doc_emitido            VARCHAR(30)  NOT NULL,
    nombre_curso                VARCHAR(250) NOT NULL,
    tipo_doc_identificacion     VARCHAR(30)  NOT NULL,
    numero_doc_identificacion   VARCHAR(30)  NOT NULL,
    nombre_completo             VARCHAR(150) NOT NULL,
    horas_academicas            INT          NOT NULL,
    modalidad                   VARCHAR(20)  NOT NULL,
    instructor                  VARCHAR(100) NOT NULL,
    lugar_emision               VARCHAR(50)  NOT NULL,
    fecha_inicio_actividad      DATE         NOT NULL,
    fecha_fin_actividad         DATE         NOT NULL,
    fecha_emision               DATE         NOT NULL,
    estado                      TINYINT(1)   DEFAULT 1,
    nota_adicional              VARCHAR(200) DEFAULT NULL,
    creado_en                   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_numero_serie ON wp_human_diplomas(numero_serie);
CREATE INDEX idx_num_doc ON wp_human_diplomas(numero_doc_identificacion);
```

### Descripción de campos

| Campo                       | Tipo         | Descripción                                  | Ejemplo                          |
| --------------------------- | ------------ | -------------------------------------------- | -------------------------------- |
| `id`                        | INT PK       | Identificador interno autoincremental        | 1                                |
| `numero_serie`              | VARCHAR(30)  | Código único del diploma — clave de búsqueda | TG-2026-02-001                   |
| `tipo_doc_emitido`          | VARCHAR(30)  | Tipo de documento emitido                    | Diploma, Certificado, Constancia |
| `nombre_curso`              | VARCHAR(250) | Nombre completo de la actividad académica    | Productividad Consciente...      |
| `tipo_doc_identificacion`   | VARCHAR(30)  | Tipo de documento del titular                | DNI, CE, RUC, Pasaporte          |
| `numero_doc_identificacion` | VARCHAR(30)  | Número del documento del titular             | 12345678                         |
| `nombre_completo`           | VARCHAR(150) | Nombre del titular o razón social si es RUC  | Angelly Arias                    |
| `horas_academicas`          | INT          | Carga lectiva de la actividad en horas       | 2                                |
| `modalidad`                 | VARCHAR(20)  | Formato de la actividad                      | Virtual, Presencial              |
| `instructor`                | VARCHAR(100) | Nombre del docente o facilitador             | Rolando Salazar Benítez          |
| `lugar_emision`             | VARCHAR(50)  | Ciudad donde se emitió el diploma            | Lima                             |
| `fecha_inicio_actividad`    | DATE         | Fecha de inicio de la actividad              | 2025-12-29                       |
| `fecha_fin_actividad`       | DATE         | Fecha de fin de la actividad                 | 2025-12-29                       |
| `fecha_emision`             | DATE         | Fecha en que se emitió el diploma            | 2025-12-30                       |
| `estado`                    | TINYINT      | Estado del diploma: 1=activo / 0=anulado     | 1                                |
| `nota_adicional`            | VARCHAR(200) | Nota libre opcional, puede quedar NULL       | —                                |
| `creado_en`                 | TIMESTAMP    | Fecha y hora de carga al sistema             | automático                       |

### Valores válidos por campo

| Campo                     | Valores permitidos                                       |
| ------------------------- | -------------------------------------------------------- |
| `tipo_doc_emitido`        | `Diploma`, `Certificado`, `Constancia`, `Reconocimiento` |
| `tipo_doc_identificacion` | `DNI`, `CE`, `RUC`, `Pasaporte`                          |
| `modalidad`               | `Virtual`, `Presencial`, `Semipresencial`                |
| `estado`                  | `1` (activo), `0` (anulado)                              |

### Lógica empresarial vs personal

El campo `tipo_doc_identificacion` determina cómo se presenta el titular en la tarjeta de resultado:

```
tipo_doc_identificacion = RUC  →  nombre_completo = Razón social de la empresa
                                   El JS muestra: 🏢 Empresa: [nombre_completo]

tipo_doc_identificacion = DNI  →  nombre_completo = Nombre de la persona
   CE / Pasaporte                  El JS muestra: 👤 Otorgado a: [nombre_completo]
```

---

## 5. Documentación del código

### 5.1 PHP — `human-verificador.php`

Archivo principal del plugin. Contiene cuatro secciones:

#### Constantes

```php
define( 'HUMAN_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );  // URL base para assets
define( 'HUMAN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // Ruta física del plugin
define( 'HUMAN_TABLE', 'wp_human_diplomas' );               // Nombre de la tabla
define( 'HUMAN_NONCE', 'human_verificar_nonce' );           // Nombre del nonce
define( 'HUMAN_RATE_LIMIT', 10 );                           // Consultas máx/IP/min
```

#### Función: `human_encolar_assets()`

**Hook:** `wp_enqueue_scripts`  
**Propósito:** Registrar y cargar el CSS y JS en el frontend del sitio.

```php
// Encola el CSS del formulario
wp_enqueue_style('human-verificador-css', HUMAN_PLUGIN_URL . 'assets/style.css', [], '1.1');

// Encola el JS del verificador
wp_enqueue_script('human-verificador-js', HUMAN_PLUGIN_URL . 'assets/js/verifier.js', [], '1.1', true);

// Pasa variables de PHP al JS de forma segura
wp_localize_script('human-verificador-js', 'human_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),  // URL del endpoint AJAX de WordPress
    'nonce'    => wp_create_nonce(HUMAN_NONCE), // Token de seguridad único por sesión
]);
```

> **Por qué `true` en `wp_enqueue_script`:** el tercer parámetro `true` carga el script en el `</footer>` en lugar del `<head>`, asegurando que el DOM esté listo antes de que el JS intente leer los elementos.

#### Función: `human_shortcode_verificador()`

**Hook:** `add_shortcode('verificador_diplomas', ...)`  
**Propósito:** Generar el HTML del formulario cuando WordPress encuentra `[verificador_diplomas]` en una página.

```php
// ob_start() y ob_get_clean() capturan el HTML en un buffer
// sin imprimirlo directamente, permitiendo que el shortcode
// devuelva el HTML como string (comportamiento requerido por WordPress)
ob_start();
// ... HTML del formulario ...
return ob_get_clean();
```

**IDs de elementos HTML generados:**

| ID                    | Elemento   | Usado por                                            |
| --------------------- | ---------- | ---------------------------------------------------- |
| `human-verifier-form` | `<form>`   | JS — punto de entrada del evento submit              |
| `human-codigo`        | `<input>`  | JS — lee el valor ingresado por el usuario           |
| `human-btn-verificar` | `<button>` | JS — se deshabilita durante la consulta              |
| `human-error-formato` | `<div>`    | JS — muestra error de formato sin consultar BD       |
| `human-resultado`     | `<div>`    | JS — área donde se renderiza la tarjeta de resultado |

#### Función: `human_ajax_verificar_diploma()`

**Hooks:** `wp_ajax_nopriv_verificar_diploma` (visitantes) + `wp_ajax_verificar_diploma` (admins)  
**Propósito:** Endpoint AJAX que recibe el código, lo valida y consulta la base de datos.

**Flujo interno paso a paso:**

```
1. check_ajax_referer()     → Validar nonce. Si falla → HTTP 403
        ↓
2. get_transient() / set_transient()
                            → Rate limiting por IP (máx. 10/min). Si supera → HTTP 429
        ↓
3. sanitize_text_field()    → Limpiar el input. strtoupper() → convertir a mayúsculas
        ↓
4. preg_match(regex)        → Validar formato XX-YYYY-MM-NNN. Si falla → JSON error
        ↓
5. $wpdb->prepare()         → Consulta segura a la BD (previene SQL injection)
   $wpdb->get_row()         → Ejecutar SELECT y obtener una fila
        ↓
6a. $diploma === null       → wp_send_json_error (no_encontrado)
6b. $diploma->estado === 0  → wp_send_json_error (anulado)
6c. estado === 1            → wp_send_json_success (campos permitidos únicamente)
```

**Campos que devuelve el JSON en caso exitoso:**

```php
wp_send_json_success([
    'status', 'numero_serie', 'tipo_doc_emitido', 'tipo_doc_identificacion',
    'nombre_completo', 'nombre_curso', 'horas_academicas', 'modalidad',
    'instructor', 'lugar_emision', 'fecha_inicio', 'fecha_fin',
    'fecha_emision', 'nota_adicional'
]);
// ⛔ NUNCA incluir: numero_doc_identificacion, correo, celular, id, creado_en
```

---

### 5.2 JavaScript — `verifier.js`

Toda la lógica está encapsulada dentro de `document.addEventListener('DOMContentLoaded', ...)` para garantizar que el DOM esté completamente cargado antes de ejecutarse.

#### Estructura general

```
DOMContentLoaded
    │
    ├── Referencias al DOM (form, input, botón, divs)
    ├── Guardia: if (!form) return  ← sale si no hay formulario en la página
    │
    ├── Evento: input en #human-codigo
    │       → Convierte a mayúsculas
    │       → Oculta mensajes de error previos
    │
    ├── Evento: submit en #human-verifier-form
    │       → Validación regex en cliente
    │       → mostrarCargando()
    │       → bloquearBoton(true)
    │       → fetch() AJAX → .then() → mostrarResultadoValido() o mostrarResultadoError()
    │       → .finally() → bloquearBoton(false)
    │
    ├── mostrarCargando()         → Spinner animado en #human-resultado
    ├── mostrarResultadoValido()  → Tarjeta verde con tabla de datos
    ├── mostrarResultadoError()   → Tarjeta roja/naranja según tipo de error
    ├── bloquearBoton()           → Deshabilita/habilita el botón
    └── formatearFecha()          → "2025-12-29" → "29 de diciembre de 2025"

humanNuevaConsulta()              ← Función global (fuera del DOMContentLoaded)
                                     Limpia el formulario y el área de resultado
```

#### Función: `mostrarResultadoValido(d)`

Recibe el objeto `data.data` del JSON de WordPress y construye la tarjeta de resultado.

```javascript
// Detección de empresa basada en tipo_doc_identificacion
const esEmpresa = d.tipo_doc_identificacion === "RUC";

// Si inicio === fin → mostrar una sola fecha
// Si son distintas  → mostrar rango "X al Y"
const rangoFechas =
  d.fecha_inicio === d.fecha_fin
    ? fechaInicio
    : `${fechaInicio} al ${fechaFin}`;

// Nota adicional: solo renderiza la fila si el campo tiene contenido
const filaNota = d.nota_adicional ? `<tr>...</tr>` : "";
```

#### Función: `formatearFecha(fechaISO)`

```javascript
// El sufijo T00:00:00 es crítico para evitar desfase de zona horaria.
// Sin él, "2025-12-29" puede interpretarse como "2025-12-28" en zonas UTC-X.
const fecha = new Date(fechaISO + "T00:00:00");
return fecha.toLocaleDateString("es-PE", {
  day: "numeric",
  month: "long",
  year: "numeric",
});
// Resultado: "29 de diciembre de 2025"
```

#### Variable global `human_ajax`

Inyectada por `wp_localize_script()` en PHP. Disponible en todo el JS del sitio:

```javascript
human_ajax.ajax_url; // → "http://sitio.com/wp-admin/admin-ajax.php"
human_ajax.nonce; // → "a014c45323" (token único por sesión)
```

---

### 5.3 CSS — `style.css`

#### Variables de color usadas

```css
/* Colores de marca Human Perú */
#1B3A6B   /* Azul principal   — botones, títulos, bordes activos     */
#F5A623   /* Amarillo acento  — borde superior de tarjeta válida      */
#1A7A4A   /* Verde éxito      — encabezado resultado VÁLIDO           */
#8B0000   /* Rojo error       — encabezado NO ENCONTRADO              */
#CC6600   /* Naranja alerta   — encabezado ANULADO                    */
```

#### Clases principales

| Clase                        | Elemento          | Descripción                            |
| ---------------------------- | ----------------- | -------------------------------------- |
| `.human-verificador-wrapper` | Contenedor raíz   | Centra y limita el ancho a 640px       |
| `.human-form-header`         | Encabezado        | Bloque azul claro con instrucciones    |
| `.human-input-group`         | Fila input+botón  | Flexbox que apila en móvil             |
| `.human-resultado`           | Tarjeta resultado | Base compartida por todos los estados  |
| `.human-resultado-valido`    | Tarjeta verde     | Borde amarillo superior + header verde |
| `.human-resultado-error`     | Tarjeta roja      | No encontrado                          |
| `.human-resultado-anulado`   | Tarjeta naranja   | Diploma anulado                        |
| `.human-resultado-cargando`  | Fila spinner      | Visible durante el fetch               |
| `.human-spinner`             | Ícono giratorio   | Animación CSS `humanSpin`              |
| `.human-tabla-datos`         | Tabla de datos    | Filas alternas, hover azul claro       |
| `.human-codigo-serie`        | Celda del código  | Fuente monoespaciada, azul, bold       |
| `.human-btn-nueva`           | Botón secundario  | Borde azul, relleno al hover           |

#### Breakpoint responsive

```css
@media (max-width: 480px) {
  /* Input y botón pasan de flex-row a flex-column */
  .human-input-group {
    flex-direction: column;
  }
}
```

---

## 6. Flujo completo del sistema

```
USUARIO                    JAVASCRIPT               PHP/WordPress             MySQL
   │                           │                         │                      │
   │ Escribe código            │                         │                      │
   │ "TG-2026-02-001" ────────>│                         │                      │
   │                           │ toUpperCase()           │                      │
   │                           │ Valida regex            │                      │
   │                           │ Si falla → muestra      │                      │
   │                           │ error inline            │                      │
   │ Clic "Verificar" ────────>│                         │                      │
   │                           │ mostrarCargando()       │                      │
   │                           │ bloquearBoton(true)     │                      │
   │                           │ fetch(POST) ───────────>│                      │
   │                           │                         │ check_ajax_referer() │
   │                           │                         │ Rate limiting        │
   │                           │                         │ sanitize_text_field()│
   │                           │                         │ preg_match(regex)    │
   │                           │                         │ $wpdb->prepare() ───>│
   │                           │                         │                      │ SELECT
   │                           │                         │<─────────────────────│ resultado
   │                           │                         │ Armar JSON           │
   │                           │<────────────────────────│ wp_send_json_*()     │
   │                           │ bloquearBoton(false)    │                      │
   │                           │ Renderizar tarjeta      │                      │
   │<──────────────────────────│ en #human-resultado     │                      │
   │ Ve resultado              │                         │                      │
```

---

## 7. Estados de respuesta

| Estado              | Condición                  | Color                | Datos que muestra                                |
| ------------------- | -------------------------- | -------------------- | ------------------------------------------------ |
| ✅ VÁLIDO           | Encontrado + estado=1      | Verde `#1A7A4A`      | Tarjeta completa con todos los campos permitidos |
| ❌ NO ENCONTRADO    | Código no existe en BD     | Rojo `#8B0000`       | Mensaje + correo de contacto                     |
| ⚠️ ANULADO          | Existe + estado=0          | Naranja `#CC6600`    | Mensaje + correo de contacto                     |
| 🔴 FORMATO INVÁLIDO | No cumple `XX-YYYY-MM-NNN` | Inline (sin tarjeta) | Mensaje de formato sin consultar el servidor     |
| ⏳ LÍMITE EXCEDIDO  | > 10 consultas/IP/min      | Naranja              | Mensaje de espera                                |
| 🔒 ERROR SEGURIDAD  | Nonce inválido             | Rojo                 | Mensaje + sugerencia de recargar                 |
| 🔄 CARGANDO         | Durante el fetch           | Azul claro           | Spinner animado                                  |

---

## 8. Seguridad implementada

### 1. Nonce de WordPress

```php
// PHP genera el token al cargar la página
wp_create_nonce('human_verificar_nonce')

// PHP lo verifica antes de procesar cualquier petición
check_ajax_referer('human_verificar_nonce', 'security')
```

Previene ataques CSRF — peticiones forjadas desde otros sitios.

### 2. Prepared Statements

```php
$wpdb->prepare("SELECT * FROM {$tabla} WHERE numero_serie = %s LIMIT 1", $codigo)
```

Previene inyección SQL — el input del usuario nunca se concatena en la query.

### 3. Sanitización de input

```php
sanitize_text_field($_POST['codigo'])  // Elimina HTML, scripts y espacios extra
strtoupper(...)                        // Normaliza a mayúsculas
```

### 4. Validación de formato doble (cliente + servidor)

```javascript
// JavaScript (antes del fetch — ahorra peticiones innecesarias)
const regex = /^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/;
```

```php
// PHP (segunda barrera — por si el JS fuera desactivado)
preg_match('/^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/', $codigo)
```

### 5. Respuesta controlada

El JSON exitoso solo expone los campos definidos explícitamente.
**Nunca se exponen:** `numero_doc_identificacion`, correo, celular, `id`, `creado_en`.

### 6. Rate limiting

```php
// Máximo HUMAN_RATE_LIMIT (10) consultas por IP por minuto
// Implementado con WordPress Transients (sin dependencias externas)
$clave_rate = 'human_rl_' . md5($ip);
set_transient($clave_rate, $contador + 1, 60); // Expira en 60 segundos
```

---

## 9. Cómo agregar nuevos diplomas

### Opción A — phpMyAdmin (recomendado para cargas masivas)

1. Abrir cPanel de Yachay → phpMyAdmin
2. Seleccionar la base de datos de WordPress
3. Clic en la tabla `wp_human_diplomas`
4. Pestaña **Insertar** → completar los campos → clic en **Continuar**

O para importar varios registros desde CSV:

1. Preparar el CSV con las columnas en el mismo orden que la tabla
2. phpMyAdmin → tabla `wp_human_diplomas` → pestaña **Importar**
3. Seleccionar el archivo CSV → configurar separador (`,`) → Continuar

### Opción B — Desde WordPress Admin (próxima versión)

Está planificado un formulario de carga dentro del panel de administración de WordPress para que el equipo pueda ingresar diplomas sin acceder a phpMyAdmin.

### Anular un diploma

```sql
-- Anular (el código seguirá existiendo pero mostrará "Diploma anulado")
UPDATE wp_human_diplomas SET estado = 0 WHERE numero_serie = 'TG-2026-02-001';

-- Reactivar
UPDATE wp_human_diplomas SET estado = 1 WHERE numero_serie = 'TG-2026-02-001';
```

> ⚠️ **Nunca borrar registros** de la tabla. Usar `estado = 0` para anular.  
> Si se borra un código y alguien lo consulta, el sistema dirá "No encontrado" en vez de "Anulado", lo que podría dar una falsa sensación de que el diploma nunca existió.

---

## 10. Pruebas realizadas

### Formato y validación cliente

| #   | Input                       | Resultado obtenido                        | ¿Correcto? |
| --- | --------------------------- | ----------------------------------------- | ---------- |
| 1   | Campo vacío                 | Error de formato (sin consultar servidor) | ✅         |
| 2   | `12345`                     | Error de formato                          | ✅         |
| 3   | `tg-2026-02-001`            | Se convierte a mayúsculas automáticamente | ✅         |
| 4   | `TG-2026-2-1`               | Error de formato                          | ✅         |
| 5   | `TG-2026-02-0001`           | Error de formato                          | ✅         |
| 6   | `TG 2026 02 001`            | Error de formato                          | ✅         |
| 7   | `TG-2026-02-001 ` (espacio) | Funciona — `.trim()` elimina el espacio   | ✅         |

### Consultas a la base de datos

| #   | Input                                   | Resultado obtenido                                                  | ¿Correcto? |
| --- | --------------------------------------- | ------------------------------------------------------------------- | ---------- |
| 8   | `TG-2025-12-002`                        | Tarjeta verde — Angelly Arias, Diploma, Productividad Consciente... | ✅         |
| 9   | `XX-9999-99-999`                        | Tarjeta roja — No encontrado + correo de contacto                   | ✅         |
| 10  | Código con `estado=0`                   | Tarjeta naranja — Diploma anulado                                   | ✅         |
| 11  | Después de anular: reactivar `estado=1` | Tarjeta verde nuevamente                                            | ✅         |

### Comportamiento del formulario

| #   | Acción                   | Resultado obtenido                             | ¿Correcto? |
| --- | ------------------------ | ---------------------------------------------- | ---------- |
| 12  | Clic "Nueva consulta"    | Campo limpio, resultado oculto, foco en input  | ✅         |
| 13  | Doble clic en Verificar  | Botón se deshabilita — solo envía una petición | ✅         |
| 14  | Corregir código inválido | Mensaje de error desaparece al escribir        | ✅         |

### Seguridad

| #   | Prueba                                   | Resultado obtenido                                | ¿Correcto? |
| --- | ---------------------------------------- | ------------------------------------------------- | ---------- |
| 15  | `<script>alert('xss')</script>` en input | No ejecuta — error de formato                     | ✅         |
| 16  | Inspeccionar JSON de respuesta           | Solo campos permitidos — sin DNI, correo, celular | ✅         |

### Responsive

| #   | Viewport         | Resultado obtenido                    | ¿Correcto? |
| --- | ---------------- | ------------------------------------- | ---------- |
| 17  | 375px (móvil)    | Input y botón apilados, tabla legible | ✅         |
| 18  | 768px (tablet)   | Formulario proporcional               | ✅         |
| 19  | 1280px (desktop) | Diseño en fila, tarjeta centrada      | ✅         |

---

## 11. Registro de cambios

### v1.1 — Marzo 2026

- Estructura de tabla MySQL actualizada: se elimina `nombre_empresa` y se reemplaza por lógica de tipo de documento (`RUC` = empresa)
- Se agregan campos `tipo_doc_identificacion`, `numero_doc_identificacion`
- Se separan `fecha_inicio_actividad` y `fecha_fin_actividad`
- Se agrega campo `nota_adicional` (opcional, DEFAULT NULL)
- JS: lógica de presentación empresarial basada en `tipo_doc_identificacion = RUC`
- JS: animación de carga, estados de error diferenciados, botón "Nueva consulta"
- CSS: diseño completo con colores de marca, responsive, animaciones

### v1.0 — Marzo 2026

- Versión inicial del plugin
- Endpoint AJAX con nonce y sanitización básica
- Shortcode `[verificador_diplomas]`
- Formulario HTML básico funcional

---

_Asociación Human Perú — Promoviendo la Salud Mental_  
_humanperu.org.pe · mesadepartes@humanperu.org.pe · 923 322 521_
