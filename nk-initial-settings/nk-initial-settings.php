<?php

/**
 * Plugin Name: NK Initial Settings
 * Description: Automate common WordPress setup steps like permalinks, default cleanup, homepage creation, menu setup and media default sizes — all in one click.
 * Version: 1.0.0
 * Author: Nidhi Kaura
 * Author URI: 
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nk-initial-settings
 */


// Ensure WordPress is running. This prevents direct access to the file from outside WordPress, improving security.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Initial check for php files
$required_files = [
    plugin_dir_path(__FILE__) . 'nk-initiate-setup.php',
    plugin_dir_path(__FILE__) . 'nk-admin-notices.php',
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        wp_die('Plugin installation is corrupted. Please reinstall the plugin.');
    }
    require_once $file;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. plugin activation hook: set transient
register_activation_hook(__FILE__, 'my_plugin_activation');
function my_plugin_activation() {
    if (function_exists('nk_initiate_setup')) {
        $message = 'Plugin activated<br />' . nk_initiate_setup();
        // Add message in option and display by admin notice
        add_option('plugin_activation_message',$message);
        set_transient('my_plugin_just_activated', true, 30);
    }
    else
        wp_die('Some files are missing in plugin. Delete and upload plugin again','warning');
}

// 2. admin_init hook : Redirect to admin page after activation
add_action('admin_init', 'my_plugin_redirect_after_activation');
function my_plugin_redirect_after_activation() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (get_transient('my_plugin_just_activated')) {
        delete_transient('my_plugin_just_activated');
        wp_safe_redirect(admin_url('admin.php?page=popup-form'));
        exit;
    }
}

// 3. admin_menu hook : Create link in admin left menu
add_action('admin_menu', function () {
    add_menu_page('Initial Settings', 'Initial Settings', 'manage_options', 
    'popup-form', 'popup_form_callback');
});

// 4. enqueue hook - Enqueue styles/scripts
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_popup-form') { // toplevel_page_{menu_slug}

        $style_path  = plugin_dir_path(__FILE__) . 'assets/style.scss';
        $script_path = plugin_dir_path(__FILE__) . 'assets/script.js';

        wp_enqueue_style(
            'popup-form-style',
            plugin_dir_url(__FILE__) . 'assets/style.scss',
            [],
            file_exists($style_path) ? filemtime($style_path) : '1.0.0',
            'all' 
        );

        wp_enqueue_script(
            'popup-form-script',
            plugin_dir_url(__FILE__) . 'assets/script.js',
            ['jquery'],
            file_exists($script_path) ? filemtime($script_path) : '1.0.0',
            true
        );
    }
}, 20); // Ensure it's loaded after WordPress's default admin styles

// Called when page loads
function popup_form_callback() {
    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed here.','warning');
    }

     // Start the session if it's not already started
    if (!session_id()) {
        session_start();
    } 
   
    $template_path = plugin_dir_path(__FILE__) . 'templates/popup-form-template.php';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo 'Form template not found!';
    }
}

// Called  when submit button is called.
// Do all logics in this since post variables will be lost outside it.
add_action('admin_post_save_popup_form', function () {
    if (!session_id()) session_start();

    if (!isset($_POST['popup_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['popup_form_nonce'])), 'save_popup_form_nonce')) {
        wp_die('Security check failed.');
    }

    // Assuming the names come from a textarea input named 'names'
    if (isset($_POST['names']) && !empty($_POST['names'])) {
        $raw_names = sanitize_textarea_field(wp_unslash($_POST['names']));

        // Convert newline-separated names into an array
        $names_array = array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $raw_names)));
        
         // Check if names are valid
        if (empty($names_array)) {
            wp_die('Please enter at least one valid name.');
        }

        $message = '';
        //   need to save in sesssion since on redirect, $_POST will be lost
        if (function_exists('nk_add_page')) {
            foreach($names_array as $name)
            {
                $message .= nk_add_page($name);
            }
        }
        if (function_exists('nk_create_menu')) 
            $message .= nk_create_menu();

        $message .= 'You can now deactivate or delete plugin, as it was for initail setups only.';
        // Add message in option and display by admin notice
        update_option('all_done_message', $message);
        
        $redirect_url = admin_url('plugins.php?all_done=1&_wpnonce=' . wp_create_nonce('all_done_nonce'));
        wp_safe_redirect($redirect_url);
        exit;
    } 
     else {
        wp_die('Please enter at least one name.');
    }
});

//  add links on plugin page
add_filter('plugin_row_meta', function ($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $readme_url = plugins_url('readme.html', __FILE__);
        $links[] = '<a href="' . esc_url($readme_url) . '" target="_blank" rel="noopener noreferrer">View Details</a>';
    }
    return $links;
}, 10, 2);

?>
