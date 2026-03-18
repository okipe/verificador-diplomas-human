<?php
/**
 * Plugin Name: Verificador de Diplomas HUMAN
 * Description: Permite verificar la autenticidad de diplomas emitidos
 *              por la Asociación Human Perú mediante número de serie.
 * Version:     1.1
 * Author:      Human Perú
 * Text Domain: human-verificador
 */

// ─────────────────────────────────────────────────────────────────
// SEGURIDAD: bloquear acceso directo al archivo PHP
// ─────────────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────────────────────────
// CONSTANTES DEL PLUGIN
// ─────────────────────────────────────────────────────────────────

// Ruta y URL base del plugin (para cargar assets)
define( 'HUMAN_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'HUMAN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Nombre de la tabla en la base de datos
define( 'HUMAN_TABLE', 'wp_human_diplomas' );

// Nombre del nonce de seguridad
define( 'HUMAN_NONCE', 'human_verificar_nonce' );

// Límite de consultas por IP por minuto (rate limiting)
define( 'HUMAN_RATE_LIMIT', 10 );


// ═════════════════════════════════════════════════════════════════
// SECCIÓN 1 — ENCOLAR SCRIPTS Y ESTILOS
// ═════════════════════════════════════════════════════════════════

/**
 * Registra y encola el CSS y JS del plugin en el frontend.
 * wp_localize_script pasa variables de PHP a JavaScript de forma segura.
 */
function human_encolar_assets() {

    // Hoja de estilos del formulario verificador
    wp_enqueue_style(
        'human-verificador-css',
        HUMAN_PLUGIN_URL . 'assets/style.css',
        [],
        '1.1'
    );

    // Script principal del verificador
    wp_enqueue_script(
        'human-verificador-js',
        HUMAN_PLUGIN_URL . 'assets/js/verifier.js',
        [],      // Sin dependencias (no usa jQuery)
        '1.1',
        true     // Cargar en el footer para mejor rendimiento
    );

    // Pasar variables de PHP al script JS de forma segura
    // Estas variables quedan disponibles en JS como: human_ajax.ajax_url, etc.
    wp_localize_script( 'human-verificador-js', 'human_ajax', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( HUMAN_NONCE ),
    ]);
}
add_action( 'wp_enqueue_scripts', 'human_encolar_assets' );


// ═════════════════════════════════════════════════════════════════
// SECCIÓN 2 — SHORTCODE DEL FORMULARIO
// ═════════════════════════════════════════════════════════════════

/**
 * Genera el HTML del formulario verificador.
 * Se inserta en cualquier página de WordPress / Divi con:
 *   [verificador_diplomas]
 */
function human_shortcode_verificador() {

    // ob_start() captura el HTML en un buffer en lugar de imprimirlo
    // Necesario para que el shortcode devuelva el HTML correctamente
    ob_start();
    ?>

    <div class="human-verificador-wrapper">

        <!-- Encabezado del formulario -->
        <div class="human-form-header">
            <h3>🔍 Verifica la autenticidad de tu diploma</h3>
            <p>Ingresa el código que aparece impreso en tu certificado.<br>
               Ejemplo: <strong>TG-2026-02-001</strong></p>
        </div>

        <!-- Formulario de verificación -->
        <!-- id="human-verifier-form" es el selector que usa el JS -->
        <form id="human-verifier-form" autocomplete="off" novalidate>

            <div class="human-input-group">
                <input
                    type="text"
                    id="human-codigo"
                    name="codigo"
                    placeholder="TG-2026-02-001"
                    maxlength="20"
                    required
                    aria-label="Código del diploma"
                />

                <button type="submit" id="human-btn-verificar">
                    Verificar diploma
                </button>
            </div>

            <!-- Mensaje de error de formato (aparece antes de consultar el servidor) -->
            <div id="human-error-formato" class="human-msg human-msg-error" style="display:none;">
                ⚠️ Formato inválido. El código debe tener el formato
                <strong>XX-AAAA-MM-NNN</strong> (ej: TG-2026-02-001).
            </div>

        </form>

        <!-- Área donde se muestra el resultado (vacía al inicio) -->
        <!-- El JS escribe aquí el resultado de la consulta -->
        <div id="human-resultado"></div>

    </div>

    <?php
    // Devolver el HTML capturado
    return ob_get_clean();
}
add_shortcode( 'verificador_diplomas', 'human_shortcode_verificador' );


// ═════════════════════════════════════════════════════════════════
// SECCIÓN 3 — ENDPOINT AJAX
// ═════════════════════════════════════════════════════════════════

/**
 * Registrar el endpoint AJAX para usuarios NO autenticados (visitantes del sitio)
 * y también para usuarios autenticados (administradores).
 *
 * wp_ajax_nopriv_ → cualquier visitante (sin sesión)
 * wp_ajax_        → usuarios logueados en WordPress
 */
add_action( 'wp_ajax_nopriv_verificar_diploma', 'human_ajax_verificar_diploma' );
add_action( 'wp_ajax_verificar_diploma',        'human_ajax_verificar_diploma' );

/**
 * Lógica principal del endpoint AJAX.
 * Recibe el código del diploma y devuelve un JSON con el resultado.
 *
 * Flujo:
 *  1. Validar nonce de seguridad
 *  2. Verificar rate limiting (máx. 10 consultas/IP/min)
 *  3. Sanitizar y recibir el código
 *  4. Validar formato con regex
 *  5. Consultar la base de datos con prepare()
 *  6. Devolver respuesta JSON según el resultado
 */
function human_ajax_verificar_diploma() {

    // ── PASO 1: Validar el nonce de seguridad ─────────────────────
    // Si el nonce no es válido, alguien está enviando peticiones
    // fuera del formulario (CSRF, bots, etc.)
    if ( ! check_ajax_referer( HUMAN_NONCE, 'security', false ) ) {
        wp_send_json_error([
            'status'  => 'error_seguridad',
            'message' => 'Error de seguridad. Recarga la página e intenta nuevamente.',
        ], 403 );
    }

    // ── PASO 2: Rate limiting (anti-abuso) ───────────────────────
    // Limitar a HUMAN_RATE_LIMIT consultas por IP por minuto
    // usando WordPress Transients como almacén temporal
    $ip         = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
    $clave_rate = 'human_rl_' . md5( $ip );               // Clave única por IP
    $contador   = (int) get_transient( $clave_rate );      // Consultas en el último minuto

    if ( $contador >= HUMAN_RATE_LIMIT ) {
        wp_send_json_error([
            'status'  => 'limite_excedido',
            'message' => 'Has realizado demasiadas consultas. Espera un momento e intenta de nuevo.',
        ], 429 );
    }

    // Incrementar el contador. set_transient expira en 60 segundos.
    set_transient( $clave_rate, $contador + 1, 60 );


    // ── PASO 3: Sanitizar y recibir el código ────────────────────
    // sanitize_text_field elimina HTML, tags y espacios extras
    $codigo = isset( $_POST['codigo'] )
        ? strtoupper( sanitize_text_field( $_POST['codigo'] ) )
        : '';

    if ( empty( $codigo ) ) {
        wp_send_json_error([
            'status'  => 'vacio',
            'message' => 'Por favor ingresa el código del diploma.',
        ]);
    }

    // ── PASO 4: Validar formato con regex ───────────────────────
    // El formato válido es: 2 letras - 4 dígitos - 2 dígitos - 3 dígitos
    // Ejemplo: TG-2026-02-001
    if ( ! preg_match( '/^[A-Z]{2}-\d{4}-\d{2}-\d{3}$/', $codigo ) ) {
        wp_send_json_error([
            'status'  => 'formato_invalido',
            'message' => 'El formato del código no es válido.',
        ]);
    }

    // ── PASO 5: Consultar la base de datos ──────────────────────
    global $wpdb;
    $tabla = $wpdb->prefix . 'human_diplomas'; // Resultado: wp_human_diplomas

    // $wpdb->prepare() previene inyección SQL.
    // NUNCA concatenar $codigo directamente en la consulta.
    $diploma = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$tabla} WHERE numero_serie = %s LIMIT 1",
            $codigo
        )
    );

    // ── PASO 6: Armar y devolver la respuesta ───────────────────

    // Caso A: No se encontró el código en la base de datos
    if ( is_null( $diploma ) ) {
        wp_send_json_error([
            'status'  => 'no_encontrado',
            'message' => 'El código ingresado no corresponde a ningún diploma emitido por Human Perú. Verifica el código e intenta nuevamente.',
        ]);
    }

    // Caso B: El diploma existe pero fue anulado
    if ( (int) $diploma->estado === 0 ) {
        wp_send_json_error([
            'status'  => 'anulado',
            'message' => 'Este diploma ha sido anulado. Para más información contáctanos en mesadepartes@humanperu.org.pe',
        ]);
    }

    // Caso C: Diploma válido y activo — devolver solo los campos permitidos
    // IMPORTANTE: NO incluir tipo/numero_doc_identificacion, correo, celular
    // ni ningún dato sensible del titular.
    wp_send_json_success([
        'status'                => 'valido',
        'numero_serie'          => esc_html( $diploma->numero_serie ),
        'tipo_doc_emitido'      => esc_html( $diploma->tipo_doc_emitido ),
        'tipo_doc_identificacion' => esc_html( $diploma->tipo_doc_identificacion ),
        'nombre_completo'       => esc_html( $diploma->nombre_completo ),
        'nombre_curso'          => esc_html( $diploma->nombre_curso ),
        'horas_academicas'      => (int) $diploma->horas_academicas,
        'modalidad'             => esc_html( $diploma->modalidad ),
        'instructor'            => esc_html( $diploma->instructor ),
        'lugar_emision'         => esc_html( $diploma->lugar_emision ),
        'fecha_inicio'          => esc_html( $diploma->fecha_inicio_actividad ),
        'fecha_fin'             => esc_html( $diploma->fecha_fin_actividad ),
        'fecha_emision'         => esc_html( $diploma->fecha_emision ),
        'nota_adicional'        => esc_html( $diploma->nota_adicional ?? '' ),
    ]);

    // wp_die() es obligatorio al final de todo handler AJAX en WordPress
    wp_die();
}
