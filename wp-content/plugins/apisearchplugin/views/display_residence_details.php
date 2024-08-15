<?php
$residences = call_api_and_get_residences();
$residence = null;

foreach ($residences as $res) {
    if ($res->id == $residence_id) {
        $residence = $res;
        break;
    }
}

if (!$residence) {
    echo '<p>Résidence non trouvée.</p>';
    return;
}

$title = esc_html($residence->title);
$address = esc_html($residence->address . ', ' . $residence->zip_code . ' ' . $residence->city);
$picture_url = !empty($residence->pictures) ? esc_url($residence->pictures[0]->url) : 'https://via.placeholder.com/150';
?>
<div class="apisearchplugin-details">
    <h2><?php echo $title; ?></h2>
    <img src="<?php echo $picture_url; ?>" alt="<?php echo $title; ?>">
    <p>Adresse : <?php echo $address; ?></p>
    <h3>Offres</h3>
    <ul class="apisearchplugin-details-offers">
        <?php foreach ($residence->offers as $offer): ?>
            <li><?php echo esc_html($offer->optional_comment_equipped); ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Aperçu</h3>
    <div class="apisearchplugin-details-preview">
        <h3>Surface</h3>
        <p><?php echo esc_html($residence->preview->surface_from); ?> - <?php echo esc_html($residence->preview->surface_to); ?> m²</p>
        <h3>Loyer à partir de</h3>
        <p><?php echo esc_html($residence->preview->rent_amount_from); ?> €</p>
        <h3>Nombre de logements</h3>
        <p><?php echo esc_html($residence->preview->quantity); ?></p>
        <h3>Services</h3>
        <ul>
            <?php foreach ($residence->preview->residence_services as $service): ?>
                <li><?php echo esc_html($service->title); ?>: <?php echo esc_html($service->description); ?> (<?php echo esc_html($service->price); ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
