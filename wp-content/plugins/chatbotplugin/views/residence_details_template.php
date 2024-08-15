<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Résidence</title>
    <style>
        .apisearchplugin-details {
          max-width: 800px;
          margin: 0 auto;
          padding: 40px;
          background-color: #fff;
          border: 1px solid #ddd;
          border-radius: 12px;
          box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
          display: flex;
          flex-direction: column;
          align-items: center;
          font-family: 'Open Sans', sans-serif;
        }

        .apisearchplugin-details h2 {
          font-size: 36px;
          font-weight: bold;
          margin-top: 0;
          color: #333;
          text-align: center;
          margin-bottom: 20px;
          font-family: 'Montserrat', sans-serif;
        }

        .apisearchplugin-details img {
          width: 100%;
          height: auto;
          margin-bottom: 40px;
          border-radius: 12px;
          box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
        }

        .apisearchplugin-details-offers,
        .apisearchplugin-details-services {
          list-style: none;
          padding-left: 0;
          margin-bottom: 40px;
        }

        .apisearchplugin-details-offers li,
        .apisearchplugin-details-services li {
          margin-bottom: 20px;
          padding: 20px;
          border-bottom: 1px solid #ddd;
          border-radius: 12px;
          background-color: #f7f7f7;
          box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
          font-size: 16px;
          font-family: 'Lato', sans-serif;
        }

        .apisearchplugin-details-preview {
          margin-bottom: 40px;
          padding: 20px;
          border: 1px solid #ddd;
          border-radius: 12px;
          background-color: #f7f7f7;
          box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
          font-size: 16px;
          font-family: 'Lato', sans-serif;
        }

        .apisearchplugin-details-preview h3 {
          font-size: 24px;
          font-weight: bold;
          margin-top: 0;
          color: #333;
          margin-bottom: 10px;
          font-family: 'Montserrat', sans-serif;
        }

        .apisearchplugin-details-preview p {
          font-size: 16px;
          color: #666;
          margin-bottom: 20px;
        }

    </style>
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
