<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\GenerateJwt;

require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');

$user = get_user(get_current_user_id());
$post_id = get_the_ID();
$meetHourApiService = new MHApiService();
$token = get_option('meethour_access_token', '');
$meeting_id = get_post_meta($post_id, 'meeting_id', true);

$join_url = get_post_meta($post_id, 'join_url', true);
$meethour_user_id = get_user_meta($user->ID, 'meethour_user_id', true);
$meeting_passcode = get_post_meta(get_current_user_id(), 'meeting_passcode');
$parsed_url = wp_parse_url($join_url);
$query = $parsed_url['query'];
parse_str($query, $params);
$pcode = $params['pcode'];

$current_user = $user->user_email;
$attendes_response = (get_post_meta($post_id, 'attendes', true));
$attendes = json_decode($attendes_response, true);
$attendes_ids = [];
foreach ($attendes as $attendes) {
    array_push($attendes_ids, $attendes['contact_id']);
}
$response = '';
// for meeting owner to login in meeting 
if ($current_user == get_option('meethour_main_user', '')) {
    $body = new GenerateJwt($meeting_id);
    $response = $meetHourApiService->generateJwt($token, $body);
} elseif (in_array($meethour_user_id, $attendes_ids) == 1) {
    $body = new GenerateJwt($meeting_id);
    $body->contact_id = $meethour_user_id;
    $response = $meetHourApiService->generateJwt($token, $body);
}


if ($current_user == get_option('meethour_main_user', '')) {
    $jwt_token = $response->jwt;
} elseif (!empty($response) && $response->success == false) {
    $jwt_token = '';
} else {
    $jwt_token = isset($response->jwt) ? $response->jwt : '';
}

if (!empty($meeting_id)) {
    $plugin_url = MEETHOUR_PLUGIN_FILE;
    wp_register_script(
        'mhvconf-meethour-sdk',
        "https://api.meethour.io/libs/v2.4.6/external_api.min.js?apiKey='" . esc_html(get_option('meethour_api_key', '')) . "'",
        array(),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf-meethour-sdk');

    wp_register_script(
        'join-meeting-script',
        false,
        array(),
        '1.0.0',
        true
    );
    wp_enqueue_script('join-meeting-script');
    $inline_script = "
    var domain = 'meethour.io';
            var options = {
                roomName: '" . esc_html($meeting_id) . "',
                width: '100%',
                height: '100%',
                parentNode: document.querySelector('#conference'),
                jwt: '" . esc_html($jwt_token) . "',
                apiKey: '" . esc_html(get_option('meethour_api_key', '')) . "',
                pcode: '" . esc_html(isset($pcode) ?  $pcode : '') . "',
                configOverwrite: {
                    prejoinPageEnabled: true,
                    disableInviteFunctions: true,
                },
                interfaceConfigOverwrite: {
                    applyMeetingSettings: true,
                    disablePrejoinHeader: true,
                    disablePrejoinFooter: true,
                    SHOW_MEET_HOUR_WATERMARK: false,
                    ENABLE_DESKTOP_DEEPLINK: false,
                    HIDE_DEEP_LINKING_LOGO: true,
                    MOBILE_APP_PROMO: false,
                    ENABLE_MOBILE_BROWSER: true
                },
            };
            var api = new MeetHourExternalAPI(domain, options);
            // To close the window once hangup is done
            api.addEventListener('readyToClose', () => {
                window.location.href = '/';
            });
    ";
    wp_add_inline_script('join-meeting-script', $inline_script);
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Join Meeting</title>
        <?php wp_head()  ?>
    </head>

    <body style="margin: 0px;">

        <div id="conference" style="height: 100vh;">

        </div>
        <?php wp_footer()     ?>
    </body>

    </html>

<?php } else {
    echo '<div>No Meeting ID Found</div>';
} ?>