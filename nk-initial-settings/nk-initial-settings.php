<?php
    /*              
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
    if (!defined('ABSPATH'))  exit; // Exit if accessed directly 

    // Initial check for php files
    $required_files = [
        plugin_dir_path(__FILE__) . 'includes/nk-initiate-setup.php',
        plugin_dir_path(__FILE__) . 'includes/nk_admin_page.php',
        plugin_dir_path(__FILE__) . 'includes/nk-admin-notices.php',
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            wp_die('Plugin installation is corrupted. Missing file: ' . esc_html(basename($file)));
        }
        require_once $file;
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. plugin activation hook: set transient
    register_activation_hook(__FILE__, 'nk_is_plugin_activation');
    function nk_is_plugin_activation() {
        if (function_exists('nk_initiate_setup')) {
            $message = 'Plugin activated<br />' . nk_initiate_setup();
            // Add message in option and display by admin notice
            add_option('nk_is_plugin_activation_message',$message);
            set_transient('nk_is_plugin_just_activated', true, 30);
        }
        else
            wp_die('Some files are missing in plugin. Delete and upload plugin again','warning');
    }

    // 2. admin_init hook : Redirect to admin page after activation
    add_action('admin_init', 'nk_is_plugin_redirect_after_activation');
    function nk_is_plugin_redirect_after_activation() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (get_transient('nk_is_plugin_just_activated')) {
            delete_transient('nk_is_plugin_just_activated');
            wp_safe_redirect(admin_url('admin.php?page=nk_is_form'));
            exit;
        }
    }

    //3.  add links on plugin page
    add_filter('plugin_row_meta', function ($links, $file) {
        if ($file === plugin_basename(__FILE__)) {
            $readme_url = plugins_url('readme.html', __FILE__);
            $links[] = '<a href="' . esc_url($readme_url) . '" target="_blank" rel="noopener noreferrer">View Details</a>';
        }
        return $links;
    }, 10, 2);

?>
