# 🚀 Pasos para Implementar el Verificador de Diplomas

¡Exacto! GitHub es el primer paso y la columna vertebral de todo el proyecto. Te explico por qué y cómo hacerlo todo en orden.

---

## 🤔 ¿Por qué GitHub primero?

```
SIN GitHub:                    CON GitHub:
─────────────────────          ─────────────────────────────
El practicante trabaja         Tú ves cada cambio en tiempo real
en su PC local                         ↓
        ↓                      Puedes comentar línea por línea
Te manda un ZIP por            el código del practicante
WhatsApp                               ↓
        ↓                      Si algo se rompe en producción,
No sabes qué cambió            revertir es un clic
        ↓                              ↓
Si su PC se daña,              El código queda respaldado
el código se pierde            para siempre en la nube
```

---

## 📋 Hoja de ruta completa

```
FASE 0 — Preparación (Tú + Óscar)        1-2 horas
FASE 1 — Repositorio GitHub               30 minutos
FASE 2 — Entorno local del practicante    2-3 horas
FASE 3 — Base de datos MySQL              1-2 horas
FASE 4 — Desarrollo del plugin            2-3 días
FASE 5 — Pruebas y ajustes               1 día
FASE 6 — Despliegue en producción         2-3 horas
```

---

## FASE 0 — Preparación previa ⚙️

Antes de abrir GitHub, reúne todo lo que necesitas. Esto lo hace Óscar o quien gestione la tecnología.

### Lista de verificación:

- [ ] Credenciales de WordPress (usuario administrador + contraseña)
- [ ] Credenciales FTP del hosting Yachay (host, usuario, contraseña, puerto)
- [ ] Acceso a cPanel de Yachay (para phpMyAdmin)
- [ ] Nombre exacto de la base de datos MySQL del sitio WordPress
- [ ] El CSV `Registro_de_diplomas_-_Registro.csv` actualizado
- [ ] Imagen del mockup del formulario
- [ ] Logo de Human Perú en PNG o SVG
- [ ] Crear una cuenta de Gmail institucional si no existe (para GitHub y otras herramientas)

> 💡 **Tip:** Guarda todas las credenciales en un gestor seguro como Bitwarden (gratuito) antes de compartirlas con el practicante. Nunca las envíes por WhatsApp sin cifrar.

---

## FASE 1 — Crear el Repositorio en GitHub 🐙

### Paso 1.1 — Crear cuenta organizacional

Ve a github.com y crea una cuenta con el correo institucional de Human Perú.

```
Nombre de usuario sugerido:  humanperu  o  human-peru-ong
Correo:                      mesadepartes@humanperu.org.pe  (o uno dedicado)
Plan:                        Free (es suficiente para este proyecto)
```

> 🔑 El plan **Free de GitHub** incluye repositorios privados ilimitados. No necesitas pagar nada.

### Paso 1.2 — Crear el repositorio

Desde tu cuenta de GitHub, crea un nuevo repositorio con esta configuración:

| Campo | Valor |
|---|---|
| Nombre | `human-verificador-diplomas` |
| Visibilidad | **Privado** (solo tú y el practicante lo ven) |
| Inicializar con README | ✅ Sí |
| Añadir .gitignore | Seleccionar **WordPress** de la lista |
| Licencia | Ninguna (es código privado de la ONG) |

### Paso 1.3 — Estructura inicial del repositorio

Una vez creado, el repositorio debe tener esta estructura. El practicante la crea en su primer commit:

```
human-verificador-diplomas/
    ├── README.md                    ← El brief técnico en Markdown (ya lo tienes)
    ├── .gitignore                   ← Archivos que Git ignorará
    ├── plugin/
    │     └── human-verificador/
    │             ├── human-verificador.php
    │             ├── readme.txt
    │             └── assets/
    │                     ├── style.css
    │                     └── script.js
    ├── sql/
    │     └── crear_tabla.sql        ← Script de creación de tabla MySQL
    └── docs/
            ├── brief_tecnico.md     ← Copia del brief (ya lo tienes)
            └── instrucciones_uso.md ← El practicante lo escribe al final
```

### Paso 1.4 — Invitar al practicante como colaborador

```
Repositorio → Settings → Collaborators → Add people
→ Buscar por usuario o email del practicante
→ Rol: Write (puede subir código pero no borrar el repo)
```

### Paso 1.5 — Configurar ramas (branches)

Esta es la parte más importante de GitHub para trabajo colaborativo:

```
RAMA main (principal)
    │
    │  Solo código revisado y aprobado por ti
    │  Nunca se modifica directamente
    │
    ├── RAMA develop
    │       │
    │       │  Código en desarrollo, aún no aprobado
    │       │  El practicante trabaja aquí
    │       │
    │       ├── feature/crear-tabla-mysql
    │       ├── feature/endpoint-ajax
    │       ├── feature/formulario-html
    │       └── feature/integracion-final
```

**¿Cómo funciona esto en la práctica?**

```
1. Practicante crea rama:    feature/crear-tabla-mysql
2. Trabaja y sube su código  a esa rama
3. Abre un Pull Request      "Quiero fusionar esto en develop"
4. Óscar / tú revisan        el código en GitHub (con comentarios)
5. Si está bien → Aprobar    y fusionar
6. Al final del proyecto     develop se fusiona en main
```

---

## FASE 2 — Entorno local del practicante 💻

El practicante configura su PC para desarrollar y probar antes de tocar el servidor real.

### Paso 2.1 — Instalar herramientas locales

```
Software necesario en la PC del practicante:

1. Local by Flywheel (localwp.com)     ← WordPress local, gratuito, sin configuración
   O alternativa: XAMPP / Laragon

2. Visual Studio Code                  ← Editor de código

3. Git                                 ← Control de versiones (git-scm.com)

4. GitHub Desktop (opcional)           ← Interfaz visual para Git, más fácil para iniciados
```

### Paso 2.2 — Clonar el repositorio

```bash
# En la terminal de su PC:
git clone https://github.com/humanperu/human-verificador-diplomas.git

# Entrar a la carpeta:
cd human-verificador-diplomas

# Crear rama de desarrollo:
git checkout -b develop
```

### Paso 2.3 — Conectar el plugin al WordPress local

```
1. Copiar la carpeta plugin/human-verificador/
   al WordPress local en:
   /wp-content/plugins/human-verificador/

2. Activar el plugin desde el panel de WordPress local

3. Crear una página "Verificar" y agregar el shortcode:
   [verificar_diploma_human]
```

---

## FASE 3 — Base de datos MySQL 🗄️

### Paso 3.1 — En entorno LOCAL (practicante)

```sql
-- Ejecutar en phpMyAdmin del WordPress local
-- Crear la tabla de prueba con datos del CSV

CREATE TABLE wp_human_diplomas (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  numero_serie     VARCHAR(30)  NOT NULL UNIQUE,
  -- ... (script completo del brief técnico)
);
```

El practicante carga 5-10 registros del CSV para hacer pruebas.

### Paso 3.2 — En PRODUCCIÓN (Yachay) — solo al final

```
1. Abrir cPanel de Yachay
2. Ir a phpMyAdmin
3. Seleccionar la base de datos de WordPress
4. Ejecutar el script SQL del archivo sql/crear_tabla.sql
5. Importar el CSV completo
6. Verificar conteo de registros
```

> ⚠️ **Regla de oro:** La base de datos de producción se toca SOLO cuando el plugin ya está probado y aprobado. Nunca experimentes directamente en el servidor real.

---

## FASE 4 — Desarrollo del Plugin 🔧

El practicante trabaja siguiendo este flujo con GitHub:

```
┌─────────────────────────────────────────────────────┐
│           CICLO DE TRABAJO DIARIO                   │
│                                                     │
│  1. git pull          ← Bajar últimos cambios       │
│         ↓                                           │
│  2. Escribir código   ← Desarrollar la tarea        │
│         ↓                                           │
│  3. Probar local      ← Verificar que funciona      │
│         ↓                                           │
│  4. git add .         ← Preparar cambios            │
│  5. git commit -m     ← Guardar con mensaje claro   │
│     "feat: agrega validación de formato regex"      │
│         ↓                                           │
│  6. git push          ← Subir a GitHub              │
│         ↓                                           │
│  7. Pull Request      ← Pedir revisión              │
│         ↓                                           │
│  8. Tú revisas        ← Aprobar o comentar          │
└─────────────────────────────────────────────────────┘
```

### Convención de mensajes de commit

Pídele al practicante que use este formato para que los cambios sean rastreables:

```
feat:  agrega endpoint AJAX para verificación
fix:   corrige validación de formato de código
style: ajusta colores del formulario a marca Human Perú
db:    agrega script SQL de creación de tabla
docs:  actualiza instrucciones de uso en readme.txt
test:  prueba con código TG-2026-02-001
```

---

## FASE 5 — Revisión y Pruebas ✅

### Paso 5.1 — Lista de pruebas antes de ir a producción

El practicante debe documentar en GitHub (como Issue o comentario) que pasó cada prueba:

```
□ TG-2026-02-001       → Resultado: VÁLIDO con datos correctos
□ TG-2025-12-001       → Resultado: VÁLIDO (primer diploma del CSV)
□ XX-9999-99-999       → Resultado: NO ENCONTRADO
□ 12345                → Resultado: ERROR DE FORMATO (sin consultar servidor)
□ TG-2026-02-001       → Con estado=0 en BD: ANULADO
□ (vacío)              → Resultado: ERROR — campo requerido
□ Actualizar Divi      → Plugin sigue activo y funcionando
□ Vista móvil 375px    → Formulario y resultado se ven bien
□ Vista desktop 1280px → Formulario y resultado se ven bien
□ 11+ consultas/min    → Sistema responde con límite de velocidad
```

### Paso 5.2 — Code Review en GitHub

Antes de aprobar el merge a `main`, revisa en GitHub:

```
¿Qué revisar aunque no seas programador?

✅ ¿El archivo readme.txt explica cómo agregar nuevos diplomas?
✅ ¿El código tiene comentarios en español que expliquen qué hace cada parte?
✅ ¿No hay contraseñas ni credenciales escritas en el código?
✅ ¿Los mensajes de error que ve el usuario están en español correcto?
✅ ¿El formulario tiene el logo y colores de Human Perú?
```

---

## FASE 6 — Despliegue en Producción 🌐

Solo se hace cuando TODAS las pruebas de la Fase 5 están aprobadas.

### Paso 6.1 — Subir el plugin al servidor

```
Opción A (recomendada): Desde WordPress
→ Panel → Plugins → Añadir nuevo → Subir plugin
→ Comprimir la carpeta human-verificador/ en .zip
→ Subir el .zip
→ Activar

Opción B: Vía FTP
→ Conectar con FileZilla usando credenciales Yachay
→ Navegar a /wp-content/plugins/
→ Arrastrar la carpeta human-verificador/
→ Activar desde el panel de WordPress
```

### Paso 6.2 — Crear tabla en producción

```
1. phpMyAdmin en cPanel de Yachay
2. Seleccionar base de datos de WordPress
3. Ejecutar sql/crear_tabla.sql
4. Importar CSV completo
5. Verificar: SELECT COUNT(*) FROM wp_human_diplomas;
```

### Paso 6.3 — Crear la página en WordPress

```
1. WordPress → Páginas → Añadir nueva
2. Título: "Verificar Diploma"
3. URL (slug): /verificar
4. Insertar módulo de Código en Divi
5. Pegar: [verificar_diploma_human]
6. Publicar
```

### Paso 6.4 — Prueba final en producción

```
1. Ir a humanperu.org.pe/verificar
2. Consultar TG-2026-02-001
3. Confirmar que aparece: Jaime Napán Ramírez ✅
4. Agregar enlace "Verificar diploma" en el menú principal
   o en la página de inicio del sitio
```

---

## 📊 Resumen visual de todo el proceso

```
SEMANA 1
─────────────────────────────────────────────────────
Lun  │ FASE 0: Recopilar accesos y materiales
Mar  │ FASE 1: Crear repo GitHub + invitar practicante
Mié  │ FASE 2: Practicante configura entorno local
Jue  │ FASE 3: Crear tabla MySQL local + migrar CSV
Vie  │ FASE 4 día 1: Endpoint AJAX + lógica PHP

SEMANA 2
─────────────────────────────────────────────────────
Lun  │ FASE 4 día 2: Formulario HTML/CSS/JS
Mar  │ FASE 4 día 3: Integración completa + ajustes
Mié  │ FASE 5: Pruebas exhaustivas + code review
Jue  │ FASE 6: Despliegue en producción
Vie  │ ✅ Entrega formal + documentación final
```

---

## 🎯 Tu rol como responsable del proyecto

No necesitas programar nada. Tu rol es:

```
📋 Dar el brief técnico al practicante (ya lo tienes)
🔑 Proporcionar los accesos de la Fase 0
👀 Revisar los Pull Requests en GitHub (sin código, solo leer)
✅ Aprobar cada fase antes de continuar
🧪 Hacer las pruebas de aceptación de la Fase 5
📢 Dar feedback sobre el diseño visual del formulario
```

---

¿Quieres que te prepare una **guía paso a paso para crear el repositorio en GitHub** con capturas de pantalla describiendo cada clic, o prefieres que avancemos con el código del plugin para tenerlo listo antes de que llegue el practicante? 🙌
