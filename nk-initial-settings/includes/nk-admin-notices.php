<?php 

add_action('admin_notices', 'nk_is_plugin_activation_callback');
function nk_is_plugin_activation_callback() {
    $message = get_option('nk_is_plugin_activation_message');
    if ($message) {
        echo '<div class="notice notice-success"><br />' . wp_kses_post($message) . '<br /></div>';
        delete_option('nk_is_plugin_activation_message');
    }
}

add_action('admin_notices', 'nk_is_all_done_callback');
function nk_is_all_done_callback() {
     if (  get_current_screen()->base == 'plugins' && 
        isset($_GET['all_done']) &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_text_field( wp_unslash($_GET['_wpnonce'])), 'all_done_nonce')
        ) { // Make sure we're on the plugins page
        $message = get_option('nk_is_all_done_message');
        if ($message) {
            echo '<div class="notice notice-success"><br />' . wp_kses_post($message) . '<br /></div>';
            delete_option('nk_is_all_done_message');
        }
    }
}