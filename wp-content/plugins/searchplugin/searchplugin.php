<?php
/*
Plugin Name: Search Plugin
Description: A simple WordPress plugin with CRUD functionalities for managing articles and categories.
Version: 1.0
Author: Your Name
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

    // SQL to create table if not exists

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

// Add/Edit Article Page 

function searchplugin_add_article_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';

    // Handle form submission
    if (isset($_POST['submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $brand = sanitize_text_field($_POST['brand']);
        $categories = isset($_POST['categories']) ? array_map('sanitize_text_field', $_POST['categories']) : array();

        // Combine categories into a comma-separated string
        $category = implode(', ', $categories);

        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $wpdb->update($table_name, compact('name', 'category', 'brand'), ['id' => $edit_id]);
        } else {
            $wpdb->insert($table_name, compact('name', 'category', 'brand'));
        }

        echo "<script>location.replace('" . admin_url('admin.php?page=searchplugin-view-articles') . "');</script>";
    }

    $edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
    $name = $brand = '';
    $selected_categories = array();

    if ($edit_id) {
        $article = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $edit_id");
        if ($article) {
            $name = $article->name;
            $brand = $article->brand;
            $selected_categories = explode(', ', $article->category);
        }
    }
    ?>
    <div class="wrap searchplugin-admin">
        <h1><?php echo $edit_id ? 'Edit Article' : 'Add Article'; ?></h1>
        <form method="post">
            <table class="form-table searchplugin-form-table">
                <tr>
                    <th><label for="name">Article Name</label></th>
                    <td><input type="text" id="name" name="name" value="<?php echo esc_attr($name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="brand">Brand</label></th>
                    <td><input type="text" id="brand" name="brand" value="<?php echo esc_attr($brand); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Category</label></th>
                    <td>
                        <?php
                        $all_categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL");
                        if ($all_categories) {
                            foreach ($all_categories as $cat) {
                                $checked = in_array($cat->category, $selected_categories) ? 'checked' : '';
                                echo '<input type="checkbox" name="categories[]" value="' . esc_attr($cat->category) . '" ' . $checked . '> ' . esc_html($cat->category) . '<br>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php if ($edit_id) { ?>
                <input type="hidden" name="edit_id" value="<?php echo esc_attr($edit_id); ?>" />
            <?php } ?>
            <p class="submit">
                <input type="submit" name="submit" value="<?php echo $edit_id ? 'Update' : 'Add'; ?>" class="button button-primary">
                <a href="<?php echo admin_url('admin.php?page=searchplugin-view-articles'); ?>" class="button button-secondary">Cancel</a>
            </p>
        </form>
    </div>
    <?php
}


// View Articles Page
function searchplugin_view_articles_page() {
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    
    <div class="wrap">
        <h1>View Articles</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>

                    <th scope="col" class="manage-column">ID</th>
                    <th scope="col" class="manage-column">Article Name</th>
                    <th scope="col" class="manage-column">Category</th>
                    <th scope="col" class="manage-column">Brand</th>
                    <th scope="col" class="manage-column">Actions</th>

                </tr>
            </thead>
            <tbody>

                <?php if ($results) {

                    foreach ($results as $row) { ?>
                        <tr>

                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->category); ?></td>
                            <td><?php echo esc_html($row->brand); ?></td>
                            <td>

                                <a href="<?php echo admin_url('admin.php?page=searchplugin-add-article&edit_id=' . esc_attr($row->id)); ?>" class="button">Edit</a>
                                <form method="post" style="display:inline;">

                                    <input type="hidden" name="delete_id" value="<?php echo esc_attr($row->id); ?>" />
                                    <input type="submit" name="delete" value="Delete" class="button button-secondary" onclick="return confirm('Are you sure you want to delete this article?');" />

                                </form>
                            </td>
                        </tr>

                    <?php }
                } else { ?>

                    <tr>
                        <td colspan="5">No data found.</td>
                    </tr>

                <?php } ?>
            </tbody>
        </table>

        <a href="<?php echo admin_url('admin.php?page=searchplugin-add-article'); ?>" class="button button-primary">Add Article</a>

    </div>
    <?php
    if (isset($_POST['delete'])) {

        $delete_id = intval($_POST['delete_id']);
        $wpdb->delete($table_name, ['id' => $delete_id]);
        echo "<script>location.replace('" . admin_url('admin.php?page=searchplugin-view-articles') . "');</script>";

    }
}

// Manage Categories Page
function searchplugin_manage_categories_page() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'searchplugin_data';

    // Handle form submission for adding a new category
    if (isset($_POST['submit'])) {

        $category_name = sanitize_text_field($_POST['category_name']);
        $wpdb->insert($table_name, array('category' => $category_name));
        echo "<div class='notice notice-success'><p>Category added successfully!</p></div>";

    }

    // Fetch all categories
    $categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL");

    ?>
    <div class="wrap">
        <h1>Manage Categories</h1>

        <!-- Form to add a new category -->
        <form method="post">
            <table class="form-table">
                <tr>

                    <th><label for="category_name">Category Name</label></th>
                    <td><input type="text" id="category_name" name="category_name" required></td>

                </tr>
            </table>
            <p class="submit">

                <input type="submit" name="submit" value="Add Category" class="button button-primary">

            </p>
        </form>

        <!-- Display existing categories -->
        <h2>Existing Categories</h2>

        <?php if ($categories) { ?>

            <ul class="category-list">
                <?php foreach ($categories as $category) { ?>
                    <li><?php echo esc_html($category->category); ?></li>
                <?php } ?>
            </ul>

        <?php } else { ?>

            <p>No categories found.</p>

        <?php } ?>
    </div>
    <?php
}

// Search Shortcode

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

    ob_start();
    ?>
    <div class="searchplugin-shortcode">
        <div class="search-form">
            <form method="post">
                <div class="form-group">
                    <label for="search">Search:</label>
                    <input type="text" id="search" name="search" value="<?php echo esc_attr($search_query); ?>" class="search-input" autocomplete="off">
                </div>
                <div class="form-group categories">
                    <label>Category:</label><br>
                    <?php
                    $all_categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL");
                    if ($all_categories) {
                        foreach ($all_categories as $cat) {
                            $checked = in_array($cat->category, $selected_categories) ? 'checked' : '';
                            echo '<input type="checkbox" name="categories[]" value="' . esc_attr($cat->category) . '" ' . $checked . '> ' . esc_html($cat->category) . '<br>';
                        }
                    }
                    ?>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <?php if (!empty($search_query) || !empty($selected_categories)) { ?>
            <div class="search-results">
                <?php if (!empty($results)) { ?>
                    <h2>Search Results:</h2>
                    <ul class="search-results-list">
                        <?php foreach ($results as $result) { ?>
                            <li class="search-result-item">
                                <?php echo esc_html($result->name); ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No results found.</p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}


add_shortcode('searchplugin_search', 'searchplugin_search_shortcode');

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


function searchplugin_enqueue_admin_styles() {
    wp_enqueue_style('searchplugin-admin-style', plugins_url('searchplugin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'searchplugin_enqueue_admin_styles');

function searchplugin_enqueue_styles() {
    wp_enqueue_style('searchplugin-style', plugins_url('searchplugin.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'searchplugin_enqueue_styles');


function searchplugin_enqueue_scripts() {
    wp_enqueue_style('searchplugin-style', plugins_url('searchplugin.css', __FILE__));
    wp_enqueue_script('searchplugin-script', plugins_url('searchplugin.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('searchplugin-script', 'searchplugin_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'searchplugin_enqueue_scripts');


?>
