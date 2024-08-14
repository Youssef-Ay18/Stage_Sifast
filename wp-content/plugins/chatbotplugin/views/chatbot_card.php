<?php
// Assurez-vous que les données sont correctement échappées pour éviter les problèmes de sécurité
$residence = isset($residence) ? $residence : [];
?>

<div class="card">
    <img src="<?php echo esc_url($residence['picture']); ?>" alt="<?php echo esc_attr($residence['title']); ?>" class="card-img">
    <div class="card-body">
        <h3 class="card-title"><?php echo esc_html($residence['title']); ?></h3>
        <p class="card-address"><?php echo esc_html($residence['address']); ?></p>
        <p class="card-price"><?php echo esc_html($residence['price']); ?></p>
        <button class="card-details-button" data-id="<?php echo esc_attr($residence['id']); ?>">Voir les détails</button>
    </div>
</div>
