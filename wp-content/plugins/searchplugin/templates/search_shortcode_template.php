<?php
// Retrieve global search query and selected categories
global $wpdb;
$table_name = $wpdb->prefix . 'searchplugin_data';
$search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
$selected_categories = isset($_POST['categories']) ? array_map('sanitize_text_field', $_POST['categories']) : array();

// Fetch all categories
$all_categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL");

// Fetch search results based on the query and selected categories
$sql = "SELECT * FROM $table_name WHERE 1=1";
if (!empty($search_query)) {
    $sql .= $wpdb->prepare(" AND name LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
}
if (!empty($selected_categories)) {
    $categories_in = "'" . implode("','", $selected_categories) . "'";
    $sql .= " AND category IN ($categories_in)";
}
$results = $wpdb->get_results($sql);
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
                <?php if ($all_categories) {
                    foreach ($all_categories as $cat) {
                        $checked = in_array($cat->category, $selected_categories) ? 'checked' : '';
                        echo '<input type="checkbox" name="categories[]" value="' . esc_attr($cat->category) . '" ' . $checked . '> ' . esc_html($cat->category) . '<br>';
                    }
                } ?>
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
