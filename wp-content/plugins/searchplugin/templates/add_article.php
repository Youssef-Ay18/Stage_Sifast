<?php
global $wpdb;
$table_name = $wpdb->prefix . 'searchplugin_data';

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$name = $category = $brand = '';

if ($edit_id) {
    $article = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $edit_id");
    if ($article) {
        $name = $article->name;
        $category = $article->category;
        $brand = $article->brand;
    }
}

if (isset($_POST['submit'])) {
    $name = sanitize_text_field($_POST['name']);
    $category = sanitize_text_field($_POST['category']);
    $brand = sanitize_text_field($_POST['brand']);

    if ($edit_id) {
        searchplugin_edit_article($edit_id, $name, $category, $brand);
    } else {
        searchplugin_add_article($name, $category, $brand);
    }

    echo "<script>location.replace('" . admin_url('admin.php?page=searchplugin-view-articles') . "');</script>";
}
?>

<div class="wrap">
    <h1><?php echo $edit_id ? 'Edit Article' : 'Add Article'; ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="name">Article Name</label></th>
                <td><input type="text" id="name" name="name" value="<?php echo esc_attr($name); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="category">Category</label></th>
                <td><input type="text" id="category" name="category" value="<?php echo esc_attr($category); ?>" /></td>
            </tr>
            <tr>
                <th><label for="brand">Brand</label></th>
                <td><input type="text" id="brand" name="brand" value="<?php echo esc_attr($brand); ?>" required /></td>
            </tr>
        </table>
        <input type="hidden" name="edit_id" value="<?php echo esc_attr($edit_id); ?>" />
        <input type="submit" name="submit" value="Save Changes" class="button button-primary" />
    </form>
</div>
