<?php
global $wpdb;
$table_name = $wpdb->prefix . 'myplugin_data';
$results = $wpdb->get_results("SELECT * FROM $table_name");

if (isset($_POST['delete'])) {
    $delete_id = intval($_POST['delete_id']);
    myplugin_delete_person($delete_id);
    echo "<script>location.replace('" . admin_url('admin.php?page=myplugin-view-data') . "');</script>";
}
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
