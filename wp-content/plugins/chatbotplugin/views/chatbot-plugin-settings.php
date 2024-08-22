<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1>Chatbot Plugin Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('chatbot_plugin_settings_group');
        do_settings_sections('chatbot-plugin');

        // Fetching existing values
        $url = esc_attr(get_option('chatbot_plugin_api_url'));
        $key = esc_attr(get_option('chatbot_plugin_api_key'));
        $secret = esc_attr(get_option('chatbot_plugin_api_secret'));

        // Outputting fields
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">API URL</th>
                <td><input type="text" name="chatbot_plugin_api_url" value="<?php echo $url; ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">X-Auth-Key</th>
                <td><input type="text" name="chatbot_plugin_api_key" value="<?php echo $key; ?>" class="regular-text" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">X-Auth-Secret</th>
                <td><input type="text" name="chatbot_plugin_api_secret" value="<?php echo $secret; ?>" class="regular-text" /></td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>