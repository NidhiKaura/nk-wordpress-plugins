<?php

// 3. admin_menu hook : Create link in admin left menu
    add_action('admin_menu', function () {
        add_menu_page(
            'Initial Settings',
             'Initial Settings', 
             'manage_options', 
            'nk_is_form', 
            'nk_is_page_callback');
    });

    
    // Called when page loads
    function nk_is_page_callback() {
        if (!current_user_can('manage_options')) {
            wp_die('You are not allowed here.','warning');
        }

        // Start the session if it's not already started
        if (!session_id()) {
            session_start();
        } 
    
        $template_path = plugin_dir_path(__FILE__) . '..\templates\nk-is-form-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="notice notice-error">Form template not found!</div>';
        }
    }

    // Called  when submit button is called.
    // Do all logics in this since post variables will be lost outside it.
    add_action('admin_post_nk_is_submit', function () {
        if (!session_id()) session_start();

        if (!isset($_POST['nk_is_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nk_is_nonce'])), 'nk_is_nonce_action')) {
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
            if (function_exists('nk_is_add_page')) {
                foreach($names_array as $name)
                {
                    $message .= nk_is_add_page($name);
                }
            }
            if (function_exists('nk_is_create_menu')) 
                $message .= nk_is_create_menu();

            $message .= 'You can now deactivate or delete plugin, if you wish, as it was for initail setups only.<br />';
            // Add message in option and display by admin notice
            update_option('nk_is_all_done_message', $message);
            
            $redirect_url = admin_url('plugins.php?all_done=1&_wpnonce=' . wp_create_nonce('all_done_nonce'));
            wp_safe_redirect($redirect_url);
            exit;
        } 
        else {
            wp_die('Please enter at least one name.');
        }
    });