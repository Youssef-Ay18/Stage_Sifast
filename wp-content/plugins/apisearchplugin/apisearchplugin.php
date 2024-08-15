<?php

/*
Plugin Name: API Search Plugin
Description: Un plugin pour rechercher et afficher des résidences.
Version: 1.0
Author: Youssef
*/



// Ajouter la page d'administration du plugin

function apisearchplugin_add_admin_page() {
    add_menu_page(
        'API Search Plugin',                 // Titre de la page
        'API Search Plugin',                 // Titre du menu
        'manage_options',                    // Capacité
        'apisearchplugin',                   // Slug du menu
        'apisearchplugin_render_admin_page', // Fonction de callback
        'dashicons-search',                  // Icône du menu
        6                                    // Position du menu
    );
}

add_action('admin_menu', 'apisearchplugin_add_admin_page');



// Ajouter le shortcode

function apisearchplugin_shortcode() {

    ob_start();
        echo '<div id="apisearchplugin">';
            $search_query = apisearchplugin_render_search_bar_page();
            apisearchplugin_display_residences_page($search_query);
        echo '</div>';
    return ob_get_clean();
}

add_shortcode('apisearchplugin', 'apisearchplugin_shortcode');



// Fonction pour appeler l'API et récupérer les résidences

function call_api_and_get_residences() {
    // Les détails d'authentification et l'URL de l'API
   
    $auth_key = 'wordpress';
    $auth_secret = 'f4ae4d1a35cf653bed2e78623cc1cfd0';
    $api_url = 'https://admin.arpej.fr/api/wordpress/residences/';

    // Configurer la requête vers l'API
   
    $args = array(
        'headers' => array(
            'X-Auth-Key' => $auth_key,
            'X-Auth-Secret' => $auth_secret,
        ),
    );

    // Faire la requête à l'API
    $response = wp_remote_get($api_url, $args);

    // Vérifier si la requête a réussi
   
    if (is_wp_error($response)) {
        return null; // Gérer les erreurs ici si nécessaire
    }

    // Décoder la réponse JSON
   
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    // Vérifier si les données sont valides
   
    if (empty($data) || !is_array($data)) {
        return null;
    }

    // Retourner les données décodées
    return $data;
}

// Fonction pour afficher la barre de recherche et traiter la recherche

function apisearchplugin_render_search_bar() {
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    return $search_query;
}

function apisearchplugin_render_search_bar_page() {
    include plugin_dir_path(__FILE__) . 'views/search_bar.php';
}

// Fonction pour afficher les résidences en format carte
function apisearchplugin_display_residences($search_query = '') {
    $residences = call_api_and_get_residences();
    return $residences;
}

function apisearchplugin_display_residences_page($search_query = '') {
    include plugin_dir_path(__FILE__) . 'views/display_residences.php';
}


// Fonction pour afficher les détails d'une résidence
function apisearchplugin_display_residence_details($residence_id) {
    $residences = call_api_and_get_residences();
    $residence = null;

    foreach ($residences as $res) {
        if ($res->id == $residence_id) {
            $residence = $res;
            break;
        }
    }

    include plugin_dir_path(__FILE__) . 'views/display_residence_details.php';
}

// Fonction pour rendre la page d'administration du plugin

function apisearchplugin_render_admin_page() {

    echo '<div class="wrap">';
         echo '<h1>API Search Plugin</h1>';

        // Récupérer l'ID de la résidence depuis les paramètres de l'URL
         $residence_id = isset($_GET['residence_id']) ? intval($_GET['residence_id']) : 0;

         if ($residence_id) {
             apisearchplugin_display_residence_details($residence_id);
        } else {
             $search_query = apisearchplugin_render_search_bar_page();
             apisearchplugin_display_residences_page($search_query);
             }
    echo '</div>';
}


function apisearchplugin_admin_enqueue_scripts() {
    // Enqueue the new JS file for autocomplete
    wp_enqueue_script('apisearchplugin-autocomplete', plugin_dir_url(__FILE__) . 'js/apisearchplugin-autocomplete.js', array('jquery'), null, true);
    
    // Localize the script for AJAX
    wp_localize_script('apisearchplugin-autocomplete', 'apisearchplugin_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('apisearchplugin_nonce'),
        'is_admin' => true // Set a flag to indicate admin area
    ));
    
}

add_action('admin_enqueue_scripts', 'apisearchplugin_admin_enqueue_scripts');

// Enqueue the JavaScript file for AJAX

function apisearchplugin_enqueue_scripts() {
    wp_enqueue_script('apisearchplugin-ajax', plugin_dir_url(__FILE__) . 'js/apisearchplugin.js', array('jquery'), null, true);
    wp_localize_script('apisearchplugin-ajax', 'apisearchplugin_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('apisearchplugin_nonce'),
        'is_admin' => is_admin() // Add this line to indicate if it's in the admin area
    ));

    // Enqueue front-end styles
    wp_enqueue_style('apisearchplugin-styles', plugin_dir_url(__FILE__) . 'css/front_apisearchplugin.css');
}
add_action('wp_enqueue_scripts', 'apisearchplugin_enqueue_scripts');
add_action('admin_enqueue_scripts', 'api_searchplugin_add_styles');



function apisearchplugin_autocomplete_residences() {
   
    check_ajax_referer('apisearchplugin_nonce', 'nonce');

    $search_query = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    $residences = call_api_and_get_residences();
    $suggestions = array();

    if ($residences && is_array($residences)) {
        foreach ($residences as $residence) {
            if (stripos($residence->title, $search_query) !== false || stripos($residence->city, $search_query) !== false) {
                $suggestions[] = esc_html($residence->title);
            }
        }
    }
    wp_send_json_success($suggestions);
}

add_action('wp_ajax_autocomplete_residences', 'apisearchplugin_autocomplete_residences');
add_action('wp_ajax_nopriv_autocomplete_residences', 'apisearchplugin_autocomplete_residences');



// AJAX function to search for residences :mettre à jour dynamiquement le contenu de la page sans recharger la page entière
function apisearchplugin_ajax_search_residences() {
    check_ajax_referer('apisearchplugin_nonce', 'nonce');
    $search_query = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    $residences = call_api_and_get_residences();

    if (!$residences || !is_array($residences)) {
        wp_send_json_error('No residences found.');
        return;
    }

    // Filtrer les résidences en fonction de la recherche
    $filtered_residences = array_filter($residences, function($residence) use ($search_query) {
        return stripos($residence->title, $search_query) !== false ||
               stripos($residence->city, $search_query) !== false;
    });

    ob_start();

    // Inclure le template avec les résidences filtrées
    include plugin_dir_path(__FILE__) . 'views/ajax_search_residences.php';

    $output = ob_get_clean();

    wp_send_json_success($output);
}

add_action('wp_ajax_search_residences', 'apisearchplugin_ajax_search_residences');
add_action('wp_ajax_nopriv_search_residences', 'apisearchplugin_ajax_search_residences');


add_action('wp_ajax_search_residences', 'apisearchplugin_ajax_search_residences');
add_action('wp_ajax_nopriv_search_residences', 'apisearchplugin_ajax_search_residences');

// AJAX function to get residence details

function apisearchplugin_ajax_residence_details() {

    check_ajax_referer('apisearchplugin_nonce', 'nonce');

    $residence_id = isset($_POST['residence_id']) ? intval($_POST['residence_id']) : 0;
    ob_start();
    apisearchplugin_display_residence_details($residence_id);
    $output = ob_get_clean();

    wp_send_json_success($output);
}

add_action('wp_ajax_residence_details', 'apisearchplugin_ajax_residence_details');
add_action('wp_ajax_nopriv_residence_details', 'apisearchplugin_ajax_residence_details');


function api_searchplugin_add_styles() {
    wp_enqueue_style('apisearchplugin-styles', plugin_dir_url(__FILE__) . 'css/front_apisearchplugin.css');
}

add_action('wp_enqueue_scripts', 'api_searchplugin_add_styles');
add_action('admin_enqueue_scripts', 'api_searchplugin_add_styles');

?>