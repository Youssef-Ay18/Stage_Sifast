<?php
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$residences = apisearchplugin_display_residences($search_query);
if (!$residences || !is_array($residences)) {
    echo '<p>Aucune résidence trouvée.</p>';
    return;
}
?>

<div class="apisearchplugin-cards-container">
    <?php foreach ($residences as $residence) : ?>
        <?php
        if ($search_query && stripos($residence->title, $search_query) === false && stripos($residence->city, $search_query) === false) {
            continue;
        }

        $title = esc_html($residence->title);
        $address = esc_html($residence->address . ', ' . $residence->zip_code . ' ' . $residence->city);
        $price = isset($residence->preview->rent_amount_from) ? esc_html($residence->preview->rent_amount_from) : 'Prix non disponible';
        $picture_url = !empty($residence->pictures) ? esc_url($residence->pictures[0]->url) : 'https://via.placeholder.com/150';

        $details_url = add_query_arg('residence_id', $residence->id, admin_url('admin.php?page=apisearchplugin'));
        ?>
        <div class="apisearchplugin-card">
            <img src="<?php echo $picture_url; ?>" alt="<?php echo $title; ?>" class="apisearchplugin-card-img">
            <div class="apisearchplugin-card-body">
                <h3 class="apisearchplugin-card-title"><?php echo $title; ?></h3>
                <p class="apisearchplugin-card-address"><?php echo $address; ?></p>
                <p class="apisearchplugin-card-price"><?php echo $price; ?> €</p>
                <a href="<?php echo esc_url($details_url); ?>" class="apisearchplugin-card-button" data-id="<?php echo esc_attr($residence->id); ?>">Voir les détails</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
