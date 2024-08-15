<?php
/*
Plugin Name: Search Plugin
Description: A simple WordPress plugin with CRUD functionalities for managing articles and categories.
Version: 1.0
Author: Youssef
License: GPL2
*/

// Register the menu
function searchplugin_add_menu() {
    add_menu_page(
        'View Articles',
        'Search Plugin',
        'manage_options',
        'searchplugin-view-articles',
        'searchplugin_view_articles_page',
        'dashicons-admin-plugins',
        30
    );
    add_submenu_page(
        'searchplugin-view-articles',
        'Add Article',
        'Add Article',
        'manage_options',
        'searchplugin-add-article',
        'searchplugin_add_article_page'
    );
    add_submenu_page(
        'searchplugin-view-articles',
        'Manage Categories',
        'Manage Categories',
        'manage_options',
        'searchplugin-manage-categories',
        'searchplugin_manage_categories_page'
    );
}

add_action('admin_menu', 'searchplugin_add_menu');

// Create or update the database table
function searchplugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        category varchar(255) DEFAULT NULL,
        brand varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'searchplugin_create_table');

function searchplugin_add_article_page() {
    include plugin_dir_path(__FILE__) . 'views/add_article.php';
}

function searchplugin_manage_categories_page() {
    include plugin_dir_path(__FILE__) . 'views/manage_categories.php';
}

function searchplugin_view_articles_page() {
    include plugin_dir_path(__FILE__) . 'views/view_articles.php';
}

function searchplugin_add_article($name, $category, $brand) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $wpdb->insert($table_name, compact('name', 'category', 'brand'));

}

function searchplugin_edit_article($edit_id, $name, $category, $brand) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $edit_id = intval($_POST['edit_id']);
    $wpdb->update($table_name, compact('name', 'category', 'brand'), ['id' => $edit_id]);
}

function searchplugin_delete_article($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $wpdb->delete($table_name, ['id' => intval($id)]);
}

function searchplugin_get_all_articles() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    return $wpdb->get_results("SELECT * FROM $table_name");
}

function searchplugin_manage_categories() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';

    // Handle form submission for adding a new category
    if (isset($_POST['submit'])) {
        $category_name = sanitize_text_field($_POST['category_name']);
        
        // Insert the new category if it does not already exist
        $existing_category = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE category = %s",
            $category_name
        ));
        
        if ($existing_category == 0) {
            $wpdb->insert($table_name, array('category' => $category_name));
            echo "<div class='notice notice-success'><p>Category added successfully!</p></div>";
        } else {
            echo "<div class='notice notice-error'><p>Category already exists.</p></div>";
        }
    }

    // Fetch all categories
    $categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL");

    return array('categories' => $categories);
}


// Function to include the template for the shortcode page
function searchplugin_search_shortcode_page($atts) {
    include plugin_dir_path(__FILE__) . 'views/search_shortcode_template.php';
}


// Function to handle the shortcode logic without HTML
function searchplugin_search_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $selected_categories = isset($_POST['categories']) ? array_map('sanitize_text_field', $_POST['categories']) : array();

    $sql = "SELECT * FROM $table_name WHERE 1=1";

    if (!empty($search_query)) {
        $sql .= $wpdb->prepare(" AND name LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
    }
    if (!empty($selected_categories)) {
        $categories_in = "'" . implode("','", $selected_categories) . "'";
        $sql .= " AND category IN ($categories_in)";
    }

    $results = $wpdb->get_results($sql);

    // Use output buffering to capture the template output
    ob_start();
    searchplugin_search_shortcode_page($atts);
    $html = ob_get_clean();

    return $html;
}

add_shortcode('searchplugin_search', 'searchplugin_search_shortcode');

// Autocomplete handler
function searchplugin_autocomplete() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if (empty($search_query)) {
        wp_send_json([]);
        return;
    }

    $sql = $wpdb->prepare("SELECT name FROM $table_name WHERE name LIKE %s LIMIT 10", '%' . $wpdb->esc_like($search_query) . '%');
    $results = $wpdb->get_results($sql);

    wp_send_json($results);
}

add_action('wp_ajax_searchplugin_autocomplete', 'searchplugin_autocomplete');
add_action('wp_ajax_nopriv_searchplugin_autocomplete', 'searchplugin_autocomplete');

// Enqueue admin styles and scripts
function searchplugin_enqueue_admin_styles() {
    wp_enqueue_style('searchplugin-admin-style', plugins_url('searchplugin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'searchplugin_enqueue_admin_styles');

// Enqueue public styles and scripts
function searchplugin_enqueue_scripts() {
    wp_enqueue_style('searchplugin-style', plugins_url('css/searchplugin.css', __FILE__));
    wp_enqueue_script('searchplugin-script', plugins_url('js/searchplugin.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('searchplugin-script', 'searchplugin_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'searchplugin_enqueue_scripts');

?>
