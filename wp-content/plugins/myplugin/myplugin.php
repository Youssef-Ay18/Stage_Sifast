<?php
/*
Plugin Name: My Plugin
Description: A simple WordPress plugin with CRUD functionalities for managing user data.
Version: 1.0
Author: Your Name
License: GPL2
*/

function myplugin_add_menu() {
    add_menu_page(
        'View Data',        // Page title
        'My Plugin',        // Menu title
        'manage_options',   // Capability
        'myplugin-view-data', // Menu slug
        'myplugin_view_data_page', // Callback function
        'dashicons-admin-plugins', // Icon (optional)
        30 // Position (optional)
    );
    add_submenu_page(
        'myplugin-view-data', // Parent slug
        'Add Person',       // Page title
        'Add Person',       // Menu title
        'manage_options',   // Capability
        'myplugin-add-person', // Menu slug
        'myplugin_add_person_page' // Callback function
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

function myplugin_add_person_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    if (isset($_POST['submit'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $age = intval($_POST['age']);
        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $wpdb->update($table_name, compact('first_name', 'last_name', 'age'), ['id' => $edit_id]);
        } else {
            $wpdb->insert($table_name, compact('first_name', 'last_name', 'age'));
        }
        echo "<script>location.replace('" . admin_url('admin.php?page=myplugin-view-data') . "');</script>";
    }
    $edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
    $first_name = $last_name = $age = '';
    if ($edit_id) {
        $person = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $edit_id");
        if ($person) {
            $first_name = $person->first_name;
            $last_name = $person->last_name;
            $age = $person->age;
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo $edit_id ? 'Edit Person' : 'Add Person'; ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="first_name">First Name</label></th>
                    <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>" required /></td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name</label></th>
                    <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>" required /></td>
                </tr>
                <tr>
                    <th><label for="age">Age</label></th>
                    <td><input type="number" id="age" name="age" value="<?php echo esc_attr($age); ?>" required /></td>
                </tr>
            </table>
            <input type="hidden" name="edit_id" value="<?php echo esc_attr($edit_id); ?>" />
            <input type="submit" name="submit" value="Save Changes" class="button button-primary" />
        </form>
    </div>
    <?php
}

function myplugin_view_data_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>View Data</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Age</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results) {
                    foreach ($results as $row) { ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->first_name); ?></td>
                            <td><?php echo esc_html($row->last_name); ?></td>
                            <td><?php echo esc_html($row->age); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=myplugin-add-person&edit_id=' . esc_attr($row->id)); ?>" class="button">Edit</a>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo esc_attr($row->id); ?>" />
                                    <input type="submit" name="delete" value="Delete" class="button button-secondary" />
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
        <a href="<?php echo admin_url('admin.php?page=myplugin-add-person'); ?>" class="button button-primary">Add Person</a>
    </div>
    <?php
    if (isset($_POST['delete'])) {
        $delete_id = intval($_POST['delete_id']);
        $wpdb->delete($table_name, ['id' => $delete_id]);
        echo "<script>location.replace('" . admin_url('admin.php?page=myplugin-view-data') . "');</script>";
    }
}

function myplugin_form_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'myplugin_data';
    if (isset($_POST['submit'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $age = intval($_POST['age']);
        $wpdb->insert($table_name, compact('first_name', 'last_name', 'age'));
    }
    ob_start();
    ?>
    <div class="myplugin-form">
        <form method="post">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>
    <?php
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


