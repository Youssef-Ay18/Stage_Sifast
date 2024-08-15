<?php
$search_query = apisearchplugin_render_search_bar();
?>

<form method="GET" id="apisearchplugin-search-form" class="apisearchplugin-search-bar">
    <input type="hidden" name="page" value="apisearchplugin">
    <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Rechercher une résidence...">
    <input type="submit" value="Rechercher">
</form>

<!-- Container for autocomplete suggestions -->
<div id="autocomplete-container"></div>
