<?php
/*
Plugin Name: My Plugin
Description: A simple WordPress plugin with CRUD functionalities for managing user data.
Version: 1.0
Author: Youssef
License: GPL2
*/

function myplugin_add_menu() {
    add_menu_page(
        'View Data',        
        'My Plugin',        
        'manage_options',   
        'myplugin-view-data',
        'myplugin_view_data_page',
        'dashicons-admin-plugins', 
        30
    );
    add_submenu_page(
        'myplugin-view-data', 
        'Add Person',       
        'Add Person',       
        'manage_options',   
        'myplugin-add-person', 
        'myplugin_add_person_page' 
    );
}
add_action('admin_menu', 'myplugin_add_menu');

function myplugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(255) NOT NULL,
        last_name varchar(255) NOT NULL,
        age int NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'myplugin_create_table');

// Function to Add a Person
function myplugin_add_person($first_name, $last_name, $age) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    $wpdb->insert($table_name, [
        'first_name' => sanitize_text_field($first_name),
        'last_name' => sanitize_text_field($last_name),
        'age' => intval($age)
    ]);
}

// Function to Edit a Person
function myplugin_edit_person($id, $first_name, $last_name, $age) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    $wpdb->update($table_name, [
        'first_name' => sanitize_text_field($first_name),
        'last_name' => sanitize_text_field($last_name),
        'age' => intval($age)
    ], ['id' => intval($id)]);
}

// Function to Delete a Person
function myplugin_delete_person($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    $wpdb->delete($table_name, ['id' => intval($id)]);
}

// Include HTML Pages
function myplugin_add_person_page() {
    include plugin_dir_path(__FILE__) . 'admin/add_person_page.php';
}

function myplugin_view_data_page() {
    include plugin_dir_path(__FILE__) . 'admin/view_data_page.php';
}

// Shortcode
function myplugin_form_shortcode($atts) {
    ob_start();
    include plugin_dir_path(__FILE__) . 'shortcode/form_shortcode.php';
    return ob_get_clean();
}
add_shortcode('myplugin_form', 'myplugin_form_shortcode');


function myplugin_enqueue_admin_styles() {
    wp_enqueue_style('myplugin-admin-style', plugin_dir_url(__FILE__) . 'myplugin-style.css');
}
add_action('admin_enqueue_scripts', 'myplugin_enqueue_admin_styles');

function myplugin_enqueue_frontend_styles() {
    wp_enqueue_style('myplugin-frontend-style', plugin_dir_url(__FILE__) . 'myplugin-style.css');
}
add_action('wp_enqueue_scripts', 'myplugin_enqueue_frontend_styles');
?>
