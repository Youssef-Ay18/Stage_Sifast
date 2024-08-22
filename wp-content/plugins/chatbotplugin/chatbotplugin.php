<?php
/*
Plugin Name: Chatbot Plugin
Description: Un plugin pour rechercher et afficher des résidences à l'aide d'un chatbot.
Version: 1.0
Author: Youssef
*/

function chatbot_plugin_enqueue_scripts() {
    // Enqueue CSS file
    wp_enqueue_style('chatbotplugin-css', plugin_dir_url(__FILE__) . 'css/chatbotplugin.css', array(), '1.0', 'all');
    
    // Enqueue JS file
    wp_enqueue_script('chatbotplugin-js', plugin_dir_url(__FILE__) . 'js/chatbotplugin.js', array('jquery'), '1.0', true);

    // Localize script with AJAX URL
    wp_localize_script('chatbotplugin-js', 'chatbotplugin_ajax', array('url' => admin_url('admin-ajax.php')));
    
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null);
}

add_action('wp_enqueue_scripts', 'chatbot_plugin_enqueue_scripts');

function chatbot_plugin_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'views/chatbot_plugin_shortcode.php';
    return ob_get_clean();
}

add_shortcode('chatbot_plugin', 'chatbot_plugin_shortcode');

function call_api($url) {
    $args = array(
        'headers' => array(
            'X-Auth-Key' => 'wordpress',
            'X-Auth-Secret' => 'f4ae4d1a35cf653bed2e78623cc1cfd0'
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}



function fetch_residences_by_query($query, $option) {
    $url = 'https://admin.arpej.fr/api/wordpress/residences/';
    $data = call_api($url);

    if (!$data) {
        return false;
    }

    $results = array();
    foreach ($data as $residence) {
        if ($option === 'name' && stripos($residence['title'], $query) !== false) {
            $results[] = array(
                'id' => $residence['id'],
                'title' => $residence['title'],
                'address' => $residence['address'],
                'price' => $residence['preview']['rent_amount_from'],
                'picture' => isset($residence['pictures'][0]['url']) ? $residence['pictures'][0]['url'] : '',
                'city' => $residence['city']
            );
        } elseif ($option === 'city' && stripos($residence['city'], $query) !== false) {
            $results[] = array(
                'id' => $residence['id'],
                'title' => $residence['title'],
                'address' => $residence['address'],
                'price' => $residence['preview']['rent_amount_from'],
                'picture' => isset($residence['pictures'][0]['url']) ? $residence['pictures'][0]['url'] : '',
                'city' => $residence['city']
            );
        }
    }

    return $results;
}


function fetch_residences_by_budget($budget) {
    $url = 'https://admin.arpej.fr/api/wordpress/residences/';
    $data = call_api($url);

    if (!$data) {
        return false;
    }

    $results = array();
    foreach ($data as $residence) {
        if (isset($residence['preview']['rent_amount_from']) && $residence['preview']['rent_amount_from'] <= $budget) {
            $results[] = array(
                'id' => $residence['id'],
                'title' => $residence['title'],
                'address' => $residence['address'],
                'price' => $residence['preview']['rent_amount_from'],
                'picture' => isset($residence['pictures'][0]['url']) ? $residence['pictures'][0]['url'] : '',
                'city' => $residence['city']
            );
        }
    }

    return $results;
}

function chatbot_plugin_fetch_details() {
    if (!isset($_POST['residence_id'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $residence_id = sanitize_text_field($_POST['residence_id']);
    $url = 'https://admin.arpej.fr/api/wordpress/residences/' . $residence_id;
    $residence = call_api($url);

    if (!$residence) {
        wp_send_json_error(array('message' => 'Details not found.'));
    }

    $details = array(
        'title' => $residence['title'],
        'address' => $residence['address'],
        'city' => $residence['city'],
        'picture' => $residence['pictures'][0]['url'],
        'offers' => array_column($residence['offers'], 'optional_comment_equipped'),
        'preview' => array(
            'surface_from' => $residence['preview']['surface_from'],
            'surface_to' => $residence['preview']['surface_to'],
            'rent_amount_from' => $residence['preview']['rent_amount_from'],
            'quantity' => $residence['preview']['quantity'],
            'residence_services' => array_map(function ($service) {
                return array(
                    'title' => $service['title'],
                    'description' => $service['description'],
                    'price' => $service['price']
                );
            }, $residence['preview']['residence_services'])
        )
    );

    ob_start();
    include plugin_dir_path(__FILE__) . 'views/residence_details.php';
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}

add_action('wp_ajax_chatbot_plugin_fetch_details', 'chatbot_plugin_fetch_details');
add_action('wp_ajax_nopriv_chatbot_plugin_fetch_details', 'chatbot_plugin_fetch_details');


function chatbot_plugin_handle_message() {
    if (!isset($_POST['query']) || !isset($_POST['option'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $query = sanitize_text_field($_POST['query']);
    $option = sanitize_text_field($_POST['option']);

    if ($option === 'budget') {
        $results = fetch_residences_by_budget($query);
    } elseif ($option === 'name' || $option === 'city') {
        $results = fetch_residences_by_query($query, $option);
    } else {
        wp_send_json_error(array('message' => 'Invalid option.'));
    }

    if ($results) {
        wp_send_json_success(array('results' => $results));
    } else {
        wp_send_json_error(array('message' => 'No residences found.'));
    }
}

add_action('wp_ajax_chatbot_plugin_handle_message', 'chatbot_plugin_handle_message');
add_action('wp_ajax_nopriv_chatbot_plugin_handle_message', 'chatbot_plugin_handle_message');


// Add this function to handle details requests

function chatbot_plugin_handle_details() {
    if (!isset($_POST['residence_id'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $residence_id = sanitize_text_field($_POST['residence_id']);
    $url = 'https://admin.arpej.fr/api/wordpress/residences/' . $residence_id;
    $residence = call_api($url);

    if (!$residence) {
        wp_send_json_error(array('message' => 'Details not found.'));
    }

    $details = array(
        'title' => $residence['title'],
        'address' => $residence['address'],
        'city' => $residence['city'],
        'picture' => $residence['pictures'][0]['url'],
        'offers' => $residence['offers'],
        'preview' => $residence['preview']
    );

    wp_send_json_success($details);
}

add_action('wp_ajax_chatbot_plugin_handle_details', 'chatbot_plugin_handle_details');
add_action('wp_ajax_nopriv_chatbot_plugin_handle_details', 'chatbot_plugin_handle_details');


// Fonction pour obtenir le HTML de la carte
function get_card_html($residence) {
    ob_start();
    include plugin_dir_path(__FILE__) . 'views/chatbot_card.php';
    return ob_get_clean();
}

// Point de terminaison pour gérer la requête AJAX pour la carte
add_action('wp_ajax_chatbot_get_card_html', 'chatbot_get_card_html');
add_action('wp_ajax_nopriv_chatbot_get_card_html', 'chatbot_get_card_html');

function chatbot_get_card_html() {
    $residence = isset($_POST['residence']) ? $_POST['residence'] : array();
    echo get_card_html($residence);
    wp_die();
}




