<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');

use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\Login;


function meethour_token_page()
{
    $meetHourApiService = new MHApiService();
    $wordpress_user_data = mhvconf_get_wordpress_users();

    // Handle form submission for token generation
    if (isset($_POST['meethour_generate_token'])) {
        // Verify nonce
        $nonce = isset($_POST['meethour_token_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['meethour_token_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, 'meethour_generate_token_action')) {
            wp_die('Security check failed.');
        }

        $client_id = sanitize_text_field($_POST['client_id']);
        $client_secret = sanitize_text_field($_POST['client_secret']);
        $api_key = sanitize_text_field($_POST['api_key']);
        update_option('meethour_api_key', $api_key);
        $username = sanitize_email($_POST['username']);
        $login_page_url = sanitize_url($_POST['login-page']);
        update_option('mhvconf_login_page_url', $login_page_url);
        $password = isset($_POST['password'])
            ? sanitize_text_field(wp_unslash($_POST['password']))
            : '';
        $grant_type = "password";
        $main_user = sanitize_text_field($_POST['main_user']);
        update_option('meethour_main_user', $main_user);

        $body = new Login($client_id, $client_secret, $grant_type, $username, $password);
        $response = $meetHourApiService->login($body);
        wp_register_script('my_plugin_reload', false);
        wp_add_inline_script('my_plugin_reload', 'window.location.reload();');

        $body = json_decode(wp_remote_retrieve_body($response->access_token), true);
        if (isset($response->access_token)) {
            update_option('meethour_access_token', $response->access_token);
            update_option('meethour_access_token_expirey', $response->expires_in);
            update_option('meethour_client_id', $client_id);
            update_option('meethour_client_secret', $client_secret);
            update_option('meethour_username', $username);
            update_option('meethour_password', $password);
            wp_enqueue_script('my_plugin_reload');
        } else {
            set_transient('meethour_error_message', 'Access Token Could not be Generated : ' . $response->message, 30);
            wp_enqueue_script('my_plugin_reload');
        }
    }
    $access_token = get_option('meethour_access_token', '');

?>

    <div class="wrap">
        <h1 class="generate-h1">Generate MeetHour Access Token
        </h1>
        <div class="card">
            <form method="post" action="">
                <?php wp_nonce_field('meethour_generate_token_action', 'meethour_token_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="client_id">Client ID</label></th>
                        <td><input type="text" id="client_id" value='<?php echo esc_html(get_option('meethour_client_id', '')); ?>' name="client_id" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="client_secret">Client Secret</label></th>
                        <td><input type="password" id="client_secret" value='<?php echo esc_html(get_option('meethour_client_secret', '')); ?>' name="client_secret" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="api_key">API Key</label></th>
                        <td><input type="text" id="api_key" value='<?php echo esc_html(get_option('meethour_api_key', '')); ?>' name="api_key" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="username">Username (Email)</label></th>
                        <td><input type="email" id="username" value='<?php echo esc_html(get_option('meethour_username', '')); ?>' name="username" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="password">Password</label></th>
                        <td><input type="password" id="password" value='<?php echo esc_html(get_option('meethour_password', '')); ?>' name="password" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="main_user">Owners</label></th>
                        <td> <select name="main_user" id="main_user" class="select2-select req select">
                                <?php foreach ($wordpress_user_data as $user) { ?>
                                    <?php $selected = ($user->user_email == get_option('meethour_main_user', '')) ? 'selected' : ''; ?>
                                    <option value='<?php echo esc_html($user->user_email); ?>' <?php echo esc_html($selected) ?>>
                                        <?php echo esc_html($user->user_email); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="login">Login URL </label></th>
                        <td><input type="text" id="login-page" placeholder="Website User Login Page URL" value="<?php echo esc_url(get_option('login-page-url', '')); ?>" name="login-page" class="regular-text"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="meethour_generate_token" class="button button-primary"
                        value="Generate Access Token">
                </p>
                <?php $access_token = get_option('meethour_access_token', ''); ?>
                <?php if (!empty($access_token)): ?>

                    <div class="access-tkn notice notice-success">
                        <p><strong>Access Token:</strong> <?php echo esc_html($access_token); ?></p>
                        <button id="copy-shortcode" class="button access-tkn-btn button-small copy-shortcode"
                            data-shortcode='<?php echo esc_attr($access_token); ?>'>
                            Copy Access Token
                        </button>
                        <br />
                    </div>

                <?php endif; ?>
            </form>
            <?php
            wp_register_script(
                'mhvconf-instant-meeting',
                false,
                array('jquery'),
                '1.0.0',
                true
            );
            wp_enqueue_script('mhvconf-instant-meeting');
            $inline_script = "
                jQuery(document).ready(function($) {
                    $('.copy-shortcode').click(function(event) {
                        event.preventDefault();
                        var shortcode = $(this).data('shortcode');
                        navigator.clipboard.writeText(shortcode).then(function() {
                            var button = $(this);
                            button.text('Copied!');
                            setTimeout(function() {
                                button.text('Copy Access Token');
                            }, 2000);
                        }.bind(this));
                    });
                });
            ";
            wp_add_inline_script('mhvconf-instant-meeting', $inline_script, 'after');
            ?>
        </div>
    </div>
    <?php
}





function meethour_display_error_message_token()
{
    $error_message = get_transient('meethour_error_message');
    if ($error_message) {
    ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
<?php
        delete_transient('meethour_error_message'); // delete the transient
    }
}
add_action('admin_notices', 'meethour_display_error_message_token');
