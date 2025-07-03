<?php

use Dom\Element;
function nk_initiate_setup() : string {
    $message = '';
    if (is_admin() && current_user_can('activate_plugins')) {
        $message .= nk_set_permalink();
        $message .= nk_delete_default_posts_pages();
        $message .= nk_delete_hello_dolly_plugin();
        $message .= nk_add_home_page();
        $message .= nk_update_default_image_sizes();
    }
    else
        $message .= 'You are not autorized to activate the plugin.<br />';
    return $message;
}

function nk_set_permalink() : string{
    if (get_option('permalink_structure') !== '/%postname%/') {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
        return 'Permalink Updated to Post Name<br />';
    }
    return '';
}

function nk_delete_hello_dolly_plugin(): string {
    $message = '';
    $deleted = false;
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    include_once ABSPATH . 'wp-admin/includes/file.php';

    $plugin_file = 'hello.php';
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

    // Check if the file exists
    if (file_exists($plugin_path)) {

        // Deactivate if active
        if (is_plugin_active($plugin_file)) {
            deactivate_plugins($plugin_file);
        }

        // Initialize WP_Filesystem
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $creds = request_filesystem_credentials('', '', false, false, null);

        if (!WP_Filesystem($creds)) {
            return 'Could not access the filesystem.';
        }

        global $wp_filesystem;

        // Check write permission and delete
        if ($wp_filesystem->exists($plugin_path) && $wp_filesystem->is_writable($plugin_path)) {
            if ($wp_filesystem->delete($plugin_path)) {
                $deleted = true;
                $message = 'Hello Dolly plugin deleted successfully.';
            } else {
                $message = 'Could not delete Hello Dolly plugin using WP_Filesystem.';
            }
        } else {
            $message = 'Plugin file is not writable or does not exist.';
        }

        // Fallback: Try WordPress's native delete_plugins()
        if ($deleted === false && function_exists('delete_plugins') && is_admin()) {
            $result = delete_plugins([$plugin_file]);
            if ($result === true) {
                $message = 'Hello Dolly plugin deleted successfully using delete_plugins().';
            } elseif (is_wp_error($result)) {
                $message = 'Error deleting plugin: ' . $result->get_error_message();
            }
        }
    } else {
        $message = 'Hello Dolly plugin does not exist.';
    }

    return $message . '<br />';
}

function nk_delete_default_posts_pages():string {
    $posts_to_delete = ['Hello World!'];
    foreach ($posts_to_delete as $title) {
        $post = get_posts(['post_type' => 'post', 'title' => $title, 'numberposts' => 1]);
        if (!empty($post)) wp_delete_post($post[0]->ID, true);
    }

    $pages_to_delete = ['Sample Page'];
    foreach ($pages_to_delete as $title) {
        $page = get_posts(['post_type' => 'page', 'title' => $title, 'numberposts' => 1]);
        if (!empty($page)) wp_delete_post($page[0]->ID, true);
    }

    $pages_to_delete = ['Privacy Policy'];
    foreach ($pages_to_delete as $title) {
        $page = get_posts(['post_type' => 'page', 'title' => $title, 'post_status' => 'draft', 'numberposts' => 1]);
        if (!empty($page)) wp_delete_post($page[0]->ID, true);
    }
    return 'Default posts and pages deleted.<br />';
}

function nk_add_home_page():string {
    $existing_home = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'title' => 'Home', 'numberposts' => 1]);
    if (empty($existing_home)) {
        $home_page_id = wp_insert_post(['post_title' => 'Home', 'post_status' => 'publish', 'post_type' => 'page']);
        update_option('page_on_front', $home_page_id);
        update_option('show_on_front', 'page');
        return 'Home Page craeted and set as front page.<br />';
    } 
    return '';
}
function   nk_add_page($page_title) : string {
   $page_title = trim($page_title);
    $existing_page = get_posts(['post_type' => 'page','post_status' => 'publish', 'title' => $page_title, 'numberposts' => 1]);
    if (empty($existing_page)) {
        $content = '<p>This text is to demo your website and should be replaced with your own website content. This content can be written by our professionals if you would prefer. The text on your website can make a huge difference to the websites success. If you are not comfortable writing the content yourself, or do not have the time, we would suggest our Content Writer package.</p>';
        $content .= '<p>Our ContentWriter service is a great enhancement to your website, our professional content writers can create all text content for your website. Many clients have taken advantage of this service, especially as it’s heavily discounted for ElevateOM clients – speak to us today to find out more.This text is to demo your website and should be replaced with your own website content. This content can be written by our professionals if you would prefer. The text on your website can make a huge difference to the websites success.</p><p> If you are not comfortable writing the content yourself, or do not have the time, we would suggest our Content Writer package.</p>';

        wp_insert_post([
            'post_title' => $page_title, 
            'post_status' => 'publish', 
            'post_type' => 'page',
            'post_content' => $content
        ]);
        return "$page_title page created.<br />";
    } else {
        return "$page_title Already exists.<br />";
    }
}

function nk_create_menu() : string {
    $menu_name = 'Main Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        foreach (get_pages(['post_status' => 'publish', 'sort_column' => 'post_date']) as $page) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id' => $page->ID,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish'
            ]);
        }
        set_theme_mod('nav_menu_locations', ['primary' => $menu_id]);
        return 'Main Menu Created.<br />';
    } 
    return 'Menu already exists.<br />';
}

function nk_update_default_image_sizes() {
    update_option('thumbnail_size_w', 400);
    update_option('thumbnail_size_h', 400);
    update_option('thumbnail_crop', 1);
    update_option('medium_size_w', 800);
    update_option('medium_size_h', 800);
    update_option('large_size_w', 1600);
    update_option('large_size_h', 1600);
    return 'Default image sizes updated.<br />';
}
