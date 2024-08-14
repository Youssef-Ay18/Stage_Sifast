<?php
global $wpdb;
$table_name = $wpdb->prefix . 'myplugin_data';

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

if (isset($_POST['submit'])) {
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $age = intval($_POST['age']);

    if ($edit_id) {
        myplugin_edit_person($edit_id, $first_name, $last_name, $age);
    } else {
        myplugin_add_person($first_name, $last_name, $age);
    }

    echo "<script>location.replace('" . admin_url('admin.php?page=myplugin-view-data') . "');</script>";
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
