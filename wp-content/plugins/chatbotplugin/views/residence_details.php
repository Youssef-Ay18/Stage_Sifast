<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Résidence</title>
    <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . '../css/chatbot-details.css'; ?>">
    </head>
<body>
    <div class="apisearchplugin-details">
        <h2><?php echo esc_html($details['title']); ?></h2>
        <img src="<?php echo esc_url($details['picture']); ?>" alt="<?php echo esc_attr($details['title']); ?>">
        <p>Adresse : <?php echo esc_html($details['address']); ?></p>
        <h3>Offres</h3>
        <ul class="apisearchplugin-details-offers">
            <?php foreach ($details['offers'] as $offer): ?>
                <li><?php echo esc_html($offer); ?></li>
            <?php endforeach; ?>
        </ul>
        <h3>Aperçu</h3>
        <div class="apisearchplugin-details-preview">
            <h3>Surface</h3>
            <p><?php echo esc_html($details['preview']['surface_from']); ?> - <?php echo esc_html($details['preview']['surface_to']); ?> m²</p>
            <h3>Loyer à partir de</h3>
            <p><?php echo esc_html($details['preview']['rent_amount_from']); ?> €</p>
            <h3>Nombre de logements</h3>
            <p><?php echo esc_html($details['preview']['quantity']); ?></p>
            <h3>Services</h3>
            <ul>
                <?php foreach ($details['preview']['residence_services'] as $service): ?>
                    <li><?php echo esc_html($service['title']); ?>: <?php echo esc_html($service['description']); ?> (<?php echo esc_html($service['price']); ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
