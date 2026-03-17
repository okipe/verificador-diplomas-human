<?php
/*
Plugin Name: Verificador de Diplomas HUMAN
Description: Verifica diplomas mediante número de serie.
Version: 1.0
Author: HUMAN
*/

if (!defined('ABSPATH')) {
    exit;
}
// AJAX Endpoint
add_action('wp_ajax_verificar_diploma', 'human_verificar_diploma');
add_action('wp_ajax_nopriv_verificar_diploma', 'human_verificar_diploma');

function human_verificar_diploma() {

    // seguridad (nonce)
    check_ajax_referer('human_nonce', 'security');

    // obtener el código
    $codigo = isset($_POST['codigo']) 
        ? sanitize_text_field($_POST['codigo']) 
        : '';

    // validación básica
    if (empty($codigo)) {
        wp_send_json([
            'status' => 'error',
            'message' => 'Código vacío'
        ]);
    }
    wp_send_json([
        'status' => 'ok',
        'codigo_recibido' => $codigo
    ]);

    wp_die();

}
// SCRIPT
function human_verifier_scripts() {

    wp_enqueue_script(
        'human-verifier',
        plugin_dir_url(__FILE__) . 'assets/js/verifier.js',
        [],
        '1.0',
        true
    );

    wp_localize_script('human-verifier', 'human_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('human_nonce')
    ]);
}

add_action('wp_enqueue_scripts', 'human_verifier_scripts');

function human_verifier_shortcode(){

    ob_start();

    ?>
    <form id="human-verifier-form">

        <input
        type="text"
        id="codigo"
        placeholder="Ingrese número de serie"
        required
        >

        <button type="submit">
            Verificar diploma
        </button>

        <div id="resultado"></div>

    </form>
    <?php

    return ob_get_clean();
}

add_shortcode('verificador_diplomas','human_verifier_shortcode');

