<?php
if (!isset($filtered_residences) || empty($filtered_residences)) {
    echo '<p>Aucune résidence trouvée.</p>';
    return;
}

foreach ($filtered_residences as $residence) {
    $title = esc_html($residence->title);
    $address = esc_html($residence->address . ', ' . $residence->zip_code . ' ' . $residence->city);
    $picture_url = !empty($residence->pictures) ? esc_url($residence->pictures[0]->url) : 'https://via.placeholder.com/150';

    echo '<div class="apisearchplugin-card">';
        echo '<img src="' . $picture_url . '" alt="' . $title . '" class="apisearchplugin-card-img">';
        echo '<div class="apisearchplugin-card-body">';
            echo '<h3 class="apisearchplugin-card-title">' . $title . '</h3>';
            echo '<p class="apisearchplugin-card-address">' . $address . '</p>';
            echo '<a href="#" class="apisearchplugin-card-button" data-id="' . esc_attr($residence->id) . '">Voir les détails</a>';
        echo '</div>';
    echo '</div>';
}
?>
