<?php
global $wpdb;

// Handle deletion
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    searchplugin_delete_article($delete_id);
    echo "<script>location.replace('" . admin_url('admin.php?page=searchplugin-view-articles') . "');</script>";
}

// Fetch all articles using the function
$articles = searchplugin_get_all_articles();
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
            <?php if ($articles) {
                foreach ($articles as $article) { ?>
                    <tr>
                        <td><?php echo esc_html($article->id); ?></td>
                        <td><?php echo esc_html($article->name); ?></td>
                        <td><?php echo esc_html($article->category); ?></td>
                        <td><?php echo esc_html($article->brand); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=searchplugin-add-article&edit_id=' . esc_attr($article->id)); ?>" class="button">Edit</a>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo esc_attr($article->id); ?>" />
                                <input type="submit" value="Delete" class="button button-secondary" onclick="return confirm('Are you sure you want to delete this article?');" />
                            </form>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5">No articles found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="<?php echo admin_url('admin.php?page=searchplugin-add-article'); ?>" class="button button-primary">Add Article</a>
</div>
