<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');


use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\ScheduleMeeting;

// Function to Call SDK Request for Instant Page Creation
function mhvconf_meethour_Instant_page()
{
    // Get access token
    $meetHourApiService = new MHApiService();

    $access_token = get_option('meethour_access_token', '');

    // Handle instant meeting request
    if (isset($_POST['meethour_instant_submit'])) {
        // Verify security nonce
        $nonce = isset($_POST['meethour_instant_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['meethour_instant_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, 'meethour_instant_meeting')) {
            wp_die('Nonce verification failed for instant meeting.');
        }

        $meeting_name = sanitize_text_field(wp_unslash($_POST['meeting_name']) ?? '');
        $passcode = sanitize_text_field(wp_unslash($_POST['meeting_passcode']) ?? '');
        $utc_timestamp = current_time('timestamp', true);
        $meeting_time = mhvconf_meethour_format_utc_time($utc_timestamp, 'h:i');
        $meeting_meridiem = mhvconf_meethour_format_utc_time($utc_timestamp, 'a');
        $meeting_date = mhvconf_meethour_format_utc_time($utc_timestamp, 'Y-m-d');;
        $timezone = !empty(get_option('meethour_timezone_value')) ? get_option('meethour_timezone_value') : 'Asia/Kolkata';
        $options = array("ALLOW_GUEST", "JOIN_ANYTIME", "ENABLE_LOBBY", "LIVEPAD", "ENABLE_RECORDING", "WHITE_BOARD", "ENABLE_BREAKOUT_ROOMS", "ENABLE_LIVESTREAM");

        $body = new ScheduleMeeting($meeting_name, $passcode, $meeting_time, $meeting_meridiem, $meeting_date, $timezone);
        $body->is_show_portal = 1;
        $body->options = $options;
        $body->duration_hr = 1;
        $body->duration_min = 0;
        $body->send_calendar_invite = 0;
        $response = $meetHourApiService->scheduleMeeting($access_token, $body);
        $data = $response->data;
        $meet = json_decode(json_encode($response->data), true);

        if (isset($data->meeting_id)) {
            wp_insert_post([
                'post_title'   => $meet['topic'],
                'post_content' => $meet['topic'],
                'post_type'    => 'mhvconf_meetings',
                'post_status'  => 'publish',
                'meta_input'   => [
                    'id'         => $meet['id'],
                    'meeting_id' => $meet['meeting_id'],
                    'meeting_date'       => $meeting_date,
                    'meeting_time'       => $meeting_time,
                    'duration_hr'   => explode(":", $meet['duration'])[0],
                    'duration_min'  => explode(":", $meet['duration'])[1],
                    'meeting_name'      => $meet['topic'],
                    'meeting_agenda'     => $meet['agenda'],
                    'timezone'   => $meet['timezone'],
                    'join_url'   => $meet['joinURL'],
                    'meeting_passcode'   => $meet['passcode'],
                    'options'    => json_encode($options),
                    'instructions' => $meet['instructions']
                ],
            ]);
        }

        $message = isset($data->meeting_id) ? $response->message : 'No message available';
        $meeting_link = isset($data->meeting_id) ? get_option('siteurl') . '/join-meeting/' . $data->meeting_id : 'No link available';
        if ($response->success == false) {
            add_settings_error('meethour_messages', 'meethour_success', esc_html($response->message), 'error');
        } else {
            add_settings_error('meethour_messages', 'meethour_success', esc_html($message) . ' Meeting Link: ' . esc_html($meeting_link), 'success');
        }
    }

    settings_errors('meethour_messages');
?>
    <div class="wrap">
        <h1>Quick Meet Hour Meeting</h1>
        <div class="card">
            <form method="POST">
                <?php wp_nonce_field('meethour_instant_meeting', 'meethour_instant_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th style=''><label for="meeting_name">Meeting Name</label></th>
                        <td><input type="text" id="meeting_name" name="meeting_name" class="regular-text" required></td>
                    </tr>
                    <?php wp_nonce_field('meethour_instant_meeting', 'meethour_instant_nonce'); ?>
                    <tr>
                        <th><label for="meeting_name">Meeting Passcode</label></th>
                        <td><input type="password" id="meeting_passcode" name="meeting_passcode" class="regular-text" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="meethour_instant_submit" class="button button-primary" value="Create Quick Meeting">
                </p>
                <?php if (isset($meeting_link)) : ?>
                    <button style="margin-right: 10px;" class="button button-small copy-shortcode"
                        data-shortcode=<?php echo esc_attr($meeting_link); ?>>
                        Copy Meeting Link
                    </button>
                    <button class="button button-small copy-shortcode"
                        data-shortcode='[meethour meeting_id="<?php echo esc_attr($data->meeting_id); ?>"]'>
                        Copy Shortcode
                    </button>
            </form>

        </div>


    <?php endif; ?>

    </div>
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
                                button.text('Copy');
                            }, 2000);
                        }.bind(this));
                    });
                });
        ";
    wp_add_inline_script('mhvconf-instant-meeting', $inline_script, 'after');
}
