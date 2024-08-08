<?php
/*
Plugin Name: Chatbot Plugin
Description: Un plugin pour rechercher et afficher des résidences à l'aide d'un chatbot.
Version: 1.0
Author: Youssef
*/

function chatbot_plugin_enqueue_scripts() {
    // Enqueue CSS file
    wp_enqueue_style('chatbotplugin-css', plugin_dir_url(__FILE__) . 'chatbotplugin.css', array(), '1.0', 'all');
    
    // Enqueue JS file
    wp_enqueue_script('chatbotplugin-js', plugin_dir_url(__FILE__) . 'chatbotplugin.js', array('jquery'), '1.0', true);

    // Localize script with AJAX URL
    wp_localize_script('chatbotplugin-js', 'chatbotplugin_ajax', array('url' => admin_url('admin-ajax.php')));
    
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null);
}

add_action('wp_enqueue_scripts', 'chatbot_plugin_enqueue_scripts');

function chatbot_plugin_shortcode() {
    ob_start();
    ?>
    <div class="chat-container">
        <div class="chat-box" id="chat-box">
            <div class="message bot-message">
                Hi there!<br>How can I help you today?
            </div>
        </div>
        <div class="options">
            <button class="option-button" data-option="name">
                <i class="fas fa-home"></i> Nom de la résidence
            </button>
            <button class="option-button" data-option="city">
                <i class="fas fa-city"></i> Ville
            </button>
            <button class="option-button" data-option="budget">
                <i class="fas fa-dollar-sign"></i> Budget
            </button>
        </div>
        <div class="input-container">
            <input type="text" id="user-input" class="chat-input" placeholder="Type a message..." />
            <button onclick="sendMessage()" class="send-button">
                <i class="fas fa-paper-plane"></i> Envoyer
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('chatbot_plugin', 'chatbot_plugin_shortcode');

function fetch_residences_by_query($query, $option) {
    $url = 'https://admin.arpej.fr/api/wordpress/residences/';
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
    $data = json_decode($body, true);

    $results = array();
    foreach ($data as $residence) {
        if ($option === 'name') {
            if (stripos($residence['title'], $query) !== false) {
                $results[] = array(
                    'title' => $residence['title'],
                    'address' => $residence['address'],
                    'price' => isset($residence['preview']['rent_amount_from']) ? $residence['preview']['rent_amount_from'] : 'N/A',
                    'picture' => isset($residence['pictures'][0]['url']) ? $residence['pictures'][0]['url'] : '',
                    'url' => $residence['url']
                );
            }
        } elseif ($option === 'city') {
            if (stripos($residence['city'], $query) !== false) {
                $results[] = array(
                    'title' => $residence['title'],
                    'address' => $residence['address'],
                    'price' => isset($residence['preview']['rent_amount_from']) ? $residence['preview']['rent_amount_from'] : 'N/A',
                    'picture' => isset($residence['pictures'][0]['url']) ? $residence['pictures'][0]['url'] : '',
                    'url' => $residence['url']
                );
            }
        }
    }

    return $results;
}

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

function fetch_residences_by_budget($budget) {
    $url = 'https://admin.arpej.fr/api/wordpress/residences/';
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
    $data = json_decode($body, true);

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
    if (!isset($_POST['id'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $id = sanitize_text_field($_POST['id']);

    $url = 'https://admin.arpej.fr/api/wordpress/residences/' . $id;
    $args = array(
        'headers' => array(
            'X-Auth-Key' => 'wordpress',
            'X-Auth-Secret' => 'f4ae4d1a35cf653bed2e78623cc1cfd0'
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to fetch details.'));
    }

    $body = wp_remote_retrieve_body($response);
    $residence = json_decode($body);

    if (!$residence) {
        wp_send_json_error(array('message' => 'No details found.'));
    }

    $details = array(
        'picture' => isset($residence->pictures[0]->url) ? $residence->pictures[0]->url : '',
        'title' => $residence->title,
        'address' => $residence->address,
        'city' => $residence->city,
        'offers' => array_map(function($offer) {
            return esc_html($offer->optional_comment_equipped);
        }, $residence->offers),
        'surface_from' => esc_html($residence->preview->surface_from),
        'surface_to' => esc_html($residence->preview->surface_to),
        'rent_amount_from' => esc_html($residence->preview->rent_amount_from),
        'quantity' => esc_html($residence->preview->quantity),
        'services' => array_map(function($service) {
            return (object)array(
                'title' => esc_html($service->title),
                'description' => esc_html($service->description),
                'price' => esc_html($service->price)
            );
        }, $residence->preview->residence_services)
    );

    wp_send_json_success($details);
}

add_action('wp_ajax_chatbot_plugin_fetch_details', 'chatbot_plugin_fetch_details');
add_action('wp_ajax_nopriv_chatbot_plugin_fetch_details', 'chatbot_plugin_fetch_details');

// Add this function to handle details requests
function chatbot_plugin_handle_details() {
    if (!isset($_POST['residence_id'])) {
        wp_send_json_error(array('message' => 'Invalid request.'));
    }

    $residence_id = sanitize_text_field($_POST['residence_id']);
    $url = 'https://admin.arpej.fr/api/wordpress/residences/' . $residence_id;

    $args = array(
        'headers' => array(
            'X-Auth-Key' => 'wordpress',
            'X-Auth-Secret' => 'f4ae4d1a35cf653bed2e78623cc1cfd0'
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Error fetching details.'));
    }

    $body = wp_remote_retrieve_body($response);
    $residence = json_decode($body);

    if (!$residence) {
        wp_send_json_error(array('message' => 'Details not found.'));
    }

    $details = array(
        'title' => $residence->title,
        'address' => $residence->address,
        'city' => $residence->city,
        'picture' => $residence->pictures[0]->url,
        'offers' => $residence->offers,
        'preview' => $residence->preview
    );

    wp_send_json_success($details);
}

add_action('wp_ajax_chatbot_plugin_handle_details', 'chatbot_plugin_handle_details');
add_action('wp_ajax_nopriv_chatbot_plugin_handle_details', 'chatbot_plugin_handle_details');

