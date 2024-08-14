<?php
global $wpdb;

$data = searchplugin_manage_categories();
$categories = $data['categories'];
?>

<div class="wrap">
    <h1>Manage Categories</h1>

    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="category_name">Category Name</label></th>
                <td><input type="text" id="category_name" name="category_name" required /></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" value="Add Category" class="button button-primary" />
        </p>
    </form>

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
