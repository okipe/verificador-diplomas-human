/**
 * Human Perú — Verificador de Diplomas
 * Archivo: assets/js/verifier.js
 * Versión: 1.1
 *
 * Flujo:
 *  1. Usuario escribe el código (se convierte a mayúsculas automáticamente)
 *  2. Al enviar el formulario: validar formato con regex
 *  3. Si el formato es válido: enviar petición AJAX al servidor
 *  4. Mostrar el resultado en #human-resultado según la respuesta
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────
    // REFERENCIAS A ELEMENTOS DEL DOM
    // ─────────────────────────────────────────────────────────────
    const form          = document.getElementById('human-verifier-form');
    const inputCodigo   = document.getElementById('human-codigo');
    const btnVerificar  = document.getElementById('human-btn-verificar');
    const errorFormato  = document.getElementById('human-error-formato');
    const divResultado  = document.getElementById('human-resultado');

    // Si el formulario no está en la página, no hacer nada
    // (el script se carga en todo el sitio, solo actúa cuando existe el form)
    if (!form) return;

    // ─────────────────────────────────────────────────────────────
    // COMPORTAMIENTO 1 — Convertir a mayúsculas mientras se escribe
    // ─────────────────────────────────────────────────────────────
    inputCodigo.addEventListener('input', function () {
        // Guardar posición del cursor para no perderla al modificar el valor
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);

        // Ocultar mensajes de error previos mientras el usuario corrige
        errorFormato.style.display = 'none';
        divResultado.innerHTML = '';
    });

    // ─────────────────────────────────────────────────────────────
    // COMPORTAMIENTO 2 — Envío del formulario
    // ─────────────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Evitar recarga de la página

        const codigo = inputCodigo.value.trim();

        // ── PASO 1: Validación de formato en el cliente ───────────
        // Patrón: 2 letras mayúsculas - 4 dígitos - 2 dígitos - 3 dígitos
        // Ejemplos válidos: TG-2026-02-001 / CH-2026-01-003
        const regex = /^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/;

        if (!regex.test(codigo)) {
            // Mostrar error de formato SIN consultar al servidor
            errorFormato.style.display = 'block';
            divResultado.innerHTML = '';
            inputCodigo.focus();
            return; // Detener aquí, no llegar al fetch
        }

        // Ocultar error de formato si el código ahora es válido
        errorFormato.style.display = 'none';

        // ── PASO 2: Mostrar indicador de carga ───────────────────
        mostrarCargando();
        bloquearBoton(true);

        // ── PASO 3: Enviar petición AJAX al servidor ─────────────
        // human_ajax es inyectado por wp_localize_script en el PHP
        fetch(human_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action:   'verificar_diploma', // Debe coincidir con wp_ajax_{action}
                codigo:   codigo,
                security: human_ajax.nonce,    // Nonce de seguridad generado por PHP
            }),
        })
        .then(function (res) {
            // Verificar que la respuesta HTTP sea correcta
            if (!res.ok) {
                throw new Error('Error de red: ' + res.status);
            }
            return res.json();
        })
        .then(function (data) {
            // ── PASO 4: Renderizar el resultado ──────────────────
            // WordPress wp_send_json_success() devuelve { success: true,  data: {...} }
            // WordPress wp_send_json_error()   devuelve { success: false, data: {...} }
            if (data.success) {
                mostrarResultadoValido(data.data);
            } else {
                mostrarResultadoError(data.data);
            }
        })
        .catch(function (err) {
            // Error de red o servidor caído
            mostrarResultadoError({
                status:  'error_red',
                message: 'No se pudo conectar con el servidor. Verifica tu conexión e intenta nuevamente.',
            });
            console.error('Human Verificador — Error:', err);
        })
        .finally(function () {
            // Siempre desbloquear el botón al terminar (éxito o error)
            bloquearBoton(false);
        });
    });


    // ═════════════════════════════════════════════════════════════
    // FUNCIONES DE RENDERIZADO
    // ═════════════════════════════════════════════════════════════

    /**
     * Muestra un spinner mientras se procesa la consulta.
     */
    function mostrarCargando() {
        divResultado.innerHTML = `
            <div class="human-resultado human-resultado-cargando">
                <span class="human-spinner"></span>
                Verificando diploma...
            </div>
        `;
    }

    /**
     * Muestra la tarjeta de resultado cuando el diploma es VÁLIDO.
     * Aplica lógica especial si el titular es una empresa (RUC).
     *
     * @param {Object} d - Datos del diploma devueltos por el servidor
     */
    function mostrarResultadoValido(d) {

        // Determinar si es persona natural o empresa según tipo de documento
        const esEmpresa = (d.tipo_doc_identificacion === 'RUC');

        // Construir fila del titular según tipo
        const filaTitular = esEmpresa
            ? `<tr>
                   <td class="human-label">🏢 Empresa</td>
                   <td class="human-valor">${d.nombre_completo}</td>
               </tr>`
            : `<tr>
                   <td class="human-label">👤 Otorgado a</td>
                   <td class="human-valor">${d.nombre_completo}</td>
               </tr>`;

        // Formatear fechas de DD a formato legible
        const fechaInicio = formatearFecha(d.fecha_inicio);
        const fechaFin    = formatearFecha(d.fecha_fin);

        // Construir rango de fechas
        // Si inicio y fin son iguales, mostrar una sola fecha
        const rangoFechas = (d.fecha_inicio === d.fecha_fin)
            ? fechaInicio
            : `${fechaInicio} al ${fechaFin}`;

        // Mostrar fila de nota solo si tiene contenido
        const filaNota = d.nota_adicional
            ? `<tr>
                   <td class="human-label">📝 Nota</td>
                   <td class="human-valor">${d.nota_adicional}</td>
               </tr>`
            : '';

        divResultado.innerHTML = `
            <div class="human-resultado human-resultado-valido">

                <div class="human-resultado-header">
                    <span class="human-icono">✅</span>
                    <div>
                        <strong>DIPLOMA AUTÉNTICO Y VÁLIDO</strong>
                        <small>Emitido por la Asociación Human Perú</small>
                    </div>
                </div>

                <table class="human-tabla-datos">
                    <tbody>
                        <tr>
                            <td class="human-label">📄 Tipo</td>
                            <td class="human-valor">${d.tipo_doc_emitido}</td>
                        </tr>
                        ${filaTitular}
                        <tr>
                            <td class="human-label">📚 Actividad</td>
                            <td class="human-valor">${d.nombre_curso}</td>
                        </tr>
                        <tr>
                            <td class="human-label">🎓 Instructor</td>
                            <td class="human-valor">${d.instructor}</td>
                        </tr>
                        <tr>
                            <td class="human-label">🕐 Horas</td>
                            <td class="human-valor">${d.horas_academicas} horas académicas</td>
                        </tr>
                        <tr>
                            <td class="human-label">📅 Realizado</td>
                            <td class="human-valor">${rangoFechas}</td>
                        </tr>
                        <tr>
                            <td class="human-label">🖥️ Modalidad</td>
                            <td class="human-valor">${d.modalidad}</td>
                        </tr>
                        <tr>
                            <td class="human-label">📍 Lugar</td>
                            <td class="human-valor">${d.lugar_emision}</td>
                        </tr>
                        <tr>
                            <td class="human-label">🔑 Código</td>
                            <td class="human-valor human-codigo-serie">${d.numero_serie}</td>
                        </tr>
                        ${filaNota}
                    </tbody>
                </table>

                <button class="human-btn-nueva" onclick="humanNuevaConsulta()">
                    🔄 Nueva consulta
                </button>

            </div>
        `;
    }

    /**
     * Muestra un mensaje de error según el tipo de fallo.
     * Maneja: no_encontrado, anulado, limite_excedido, error_seguridad, error_red.
     *
     * @param {Object} d - Objeto con status y message del servidor
     */
    function mostrarResultadoError(d) {

        // Definir estilo y contenido según el tipo de error
        let clase   = 'human-resultado-error';
        let icono   = '❌';
        let titulo  = 'No se encontró ningún diploma';
        let mensaje = d.message || 'Ocurrió un error inesperado.';

        if (d.status === 'anulado') {
            clase  = 'human-resultado-anulado';
            icono  = '⚠️';
            titulo = 'Diploma anulado';
        } else if (d.status === 'limite_excedido') {
            clase  = 'human-resultado-anulado';
            icono  = '⏳';
            titulo = 'Demasiadas consultas';
        } else if (d.status === 'error_seguridad') {
            clase  = 'human-resultado-error';
            icono  = '🔒';
            titulo = 'Error de seguridad';
        }

        // Mostrar enlace de contacto en errores que lo requieren
        const mostrarContacto = ['no_encontrado', 'anulado'].includes(d.status);
        const filaContacto = mostrarContacto
            ? `<p class="human-contacto">¿Tienes dudas? Escríbenos a
               <a href="mailto:mesadepartes@humanperu.org.pe">
               mesadepartes@humanperu.org.pe</a></p>`
            : '';

        divResultado.innerHTML = `
            <div class="human-resultado ${clase}">

                <div class="human-resultado-header">
                    <span class="human-icono">${icono}</span>
                    <div>
                        <strong>${titulo}</strong>
                    </div>
                </div>

                <p class="human-mensaje-error">${mensaje}</p>
                ${filaContacto}

                <button class="human-btn-nueva" onclick="humanNuevaConsulta()">
                    🔄 Nueva consulta
                </button>

            </div>
        `;
    }


    // ═════════════════════════════════════════════════════════════
    // FUNCIONES DE UTILIDAD
    // ═════════════════════════════════════════════════════════════

    /**
     * Bloquea o desbloquea el botón de verificar durante la consulta.
     * Evita que el usuario envíe múltiples peticiones simultáneas.
     */
    function bloquearBoton(bloquear) {
        btnVerificar.disabled = bloquear;
        btnVerificar.textContent = bloquear ? 'Verificando...' : 'Verificar diploma';
    }

    /**
     * Convierte una fecha en formato YYYY-MM-DD a formato legible en español.
     * Ejemplo: "2026-02-26" → "26 de febrero de 2026"
     *
     * @param {string} fechaISO - Fecha en formato YYYY-MM-DD
     * @returns {string} Fecha formateada en español
     */
    function formatearFecha(fechaISO) {
        if (!fechaISO) return '';
        // Agregar T00:00:00 para evitar problemas de zona horaria
        const fecha = new Date(fechaISO + 'T00:00:00');
        return fecha.toLocaleDateString('es-PE', {
            day:   'numeric',
            month: 'long',
            year:  'numeric',
        });
    }

}); // Fin DOMContentLoaded


// ─────────────────────────────────────────────────────────────────
// FUNCIÓN GLOBAL — Nueva consulta
// ─────────────────────────────────────────────────────────────────
// Declarada fuera del DOMContentLoaded para que el onclick del HTML
// pueda invocarla. Limpia el formulario y oculta el resultado.
function humanNuevaConsulta() {
    const input     = document.getElementById('human-codigo');
    const resultado = document.getElementById('human-resultado');
    const btnForm   = document.getElementById('human-btn-verificar');

    if (input)     input.value = '';
    if (resultado) resultado.innerHTML = '';
    if (btnForm)   btnForm.disabled = false;

    // Enfocar el campo para que el usuario pueda escribir de inmediato
    if (input) input.focus();
}
