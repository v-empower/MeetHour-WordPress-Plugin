<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');

use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\GetSingleRecording;
use MeetHourApp\Types\RecordingsList;


function meethour_register_recordings_post_type()
{
    register_post_type('mhvconf_recordings', [
        'labels' => [
            'name' => 'Recordings',
            'singular_name' => 'Recordings',
            'add_new_item' => 'Recordings Menu',
            'add_new' => 'Recordings',
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'show_in_menu' => false,
    ]);
}
add_action('init', 'meethour_register_recordings_post_type');

add_filter('manage_mhvconf_recordings_posts_columns', 'meethour_recordings_columns');
function meethour_recordings_columns($columns)
{
    $columns = [
        'cb' => '<input type="checkbox" />',
        'title' => 'Recording Name',
        'recording_date' => 'Recording Date',
        'recording_size' => 'Size',
        'duration' => 'Duration',
        'shortcode' => 'Shortcode',
        'Link' => 'Link',
        'RefreshShortcode' => 'Refresh Shortcode'
    ];
    return $columns;
}


add_action('manage_mhvconf_recordings_posts_custom_column', 'meethour_recordings_custom_columns', 10, 2);
function meethour_recordings_custom_columns($column, $post_id)
{
    switch ($column) {
        case 'recording_date':
            echo esc_html(get_post_meta($post_id, 'recording_date', true));
            break;
        case 'recording_size':
            $size = get_post_meta($post_id, 'recording_size', true);
            echo number_format($size / 1024 / 1024, 2) . ' MB';
            break;
        case 'duration':
            $duration = get_post_meta($post_id, 'duration', true);
            $duration_parts = explode(':', $duration);
            $hours = str_pad(intval($duration_parts[0]), 2, '0', STR_PAD_LEFT);
            $minutes = str_pad(intval($duration_parts[1]), 2, '0', STR_PAD_LEFT);
            $seconds = explode('.', $duration_parts[2])[0];
            $seconds = str_pad(intval($seconds), 2, '0', STR_PAD_LEFT);
            $formatted_duration = $hours . ':' . $minutes . ':' . $seconds;
            echo esc_html($formatted_duration);
            break;
        case 'shortcode':
            echo '<button class="button button-small copy-shortcode" data-shortcode="[meethour recording_id=' . esc_html($post_id) . ']">Copy Shortcode</button>';
            wp_register_script(
                'mhvconf-recording-shortcode',
                false,
                array('jquery'),
                '1.0.0',
                true
            );
            wp_enqueue_script('mhvconf-recording-shortcode');
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
            wp_add_inline_script('mhvconf-recording-shortcode', $inline_script, 'after');
            break;
        case 'Link':
            $url = get_permalink($post_id);
            $parsed_url = wp_parse_url($url);
			// Trim leading and trailing slashes from the URL path.
			$path = isset($parsed_url['path']) ? trim($parsed_url['path'], '/') : '';

			// Define the custom prefix without a leading slash.
			$search_prefix = 'mhvconf_recordings/';

			// Check if the normalized path starts with our custom prefix.
			if (strpos($path, $search_prefix) === 0) {
				// Extract the part after 'mhvconf_recordings/'
				$extracted_value = substr($path, strlen($search_prefix));
				$base_url = untrailingslashit(home_url());

				// Build the new URL with the extracted segment as a query parameter.
				$recording_url = $base_url . '/?mhvconf_recordings=' . urlencode($extracted_value);

				echo '<a href="' . esc_url($recording_url) . '" target="_blank">View Recording</a>';
			} else {
				// Fallback: simply use the original permalink.
				echo '<a href="' . esc_url($url) . '" target="_blank">View Recording</a>';
			}
            break;
        case 'RefreshShortcode':
            $recording_id = get_post_meta($post_id, 'recording_id', true);
            $recording_expiry = get_post_meta($post_id, 'recording_expiry', true);
            echo '<div id="countdown-' . esc_html($recording_id) . '"></div>';
            echo '<button id="refreshButton-' . esc_html($recording_id) . '" style="display:none;" >Refresh video link</button>';
            wp_register_script(
                'mhvconf-recording-expiry',
                false,
                array('jquery'),
                '1.0.0',
                true
            );
            wp_enqueue_script('mhvconf-recording-expiry');
            $inline_script = '
      function startCountdown(expiryDateStr, recordingId) {
            var countdownElement = document.getElementById("countdown-" + recordingId);
            var refreshButton = document.getElementById("refreshButton-" + recordingId);
            var expiryDate = new Date(expiryDateStr);
            var countdownInterval = setInterval(function () {
                var now = new Date();
                var distance = expiryDate - now;
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdownElement.style.display = "none";
                    refreshButton.style.display = "inline-block";
                    return;
                } else {
                    refreshButton.style.display = "none";
                    countdownElement.style.display = "inline-block"; 
                }
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                days = days < 10 ? "0" + days : days;
                hours = hours < 10 ? "0" + hours : hours;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                countdownElement.textContent = "Expires in: " + days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
            }, 1000);
        }

        document.addEventListener("DOMContentLoaded", function() {
            var expiryTime = "' . esc_html($recording_expiry) . '";
            startCountdown(expiryTime, ' . esc_html($recording_id) . ');
        });
        ';
            wp_add_inline_script('mhvconf-recording-expiry', $inline_script, 'after');

            break;
    }
}
$updating_recording_id = get_option('updating_recording_id');
if (!empty($updating_recording_id)) {
    delete_option('updating_recording_id');
}



function meethour_fetch_recordings()
{
    $meetHourApiService = new MHApiService();
    $access_token = get_option('meethour_access_token', '');

    $current_page = get_option('mhvconf_recordings_current_page', 1);
    $total_pages = get_option('mhvconf_recordings_total_pages', null);

    if (empty($current_page)) {
        $current_page = 1;
        update_option('mhvconf_recordings_current_page', $current_page);
    };

    $posts_per_page = 20;


    $main = new RecordingsList();
    $main->limit = $posts_per_page;
    $main->page = $current_page;
    $response = $meetHourApiService->recordingsList($access_token, $main);
    if ($response->success == false) {
        set_transient('meethour_error_message', $response->message, 30);
        return;
    }

    if (is_null($total_pages)) {
        $total_pages = $response->total_pages;
        update_option('mhvconf_recordings_total_pages', $total_pages);
    }

    $recordings = json_encode($response->meethour_recordings);
    $recording_array = json_decode($recordings, true);

    if (!$recording_array) {
        wp_send_json_error('Failed to decode recordings JSON');
        return;
    }

    foreach ($recording_array as $record) {
        $args = array(
            'meta_key' => 'recording_id',
            'meta_value' => $record['recording_id'],
            'post_type' => 'mhvconf_recordings',
            'post_status' => 'any',
            'posts_per_page' => -1
        );
        $existing_posts = get_posts($args);

        if (empty($existing_posts)) {
            $post_id = wp_insert_post([
                'post_title' => $record['recording_name'],
                'post_content' => $record['topic'],
                'post_type' => 'mhvconf_recordings',
                'post_status' => 'publish',
                'meta_input' => [
                    'recording_date' => $record['recording_date'],
                    'recording_size' => $record['recording_size'],
                    'recording_path' => $record['recording_path'],
                    'duration' => $record['duration'],
                    'recording_id' => $record['recording_id'],
                    'recording_expiry' => $record['recording_expiry'],
                ]
            ]);
        }
    }

    if ($current_page < $total_pages) {
        $current_page++;
        update_option('mhvconf_recordings_current_page', $current_page);

        $start = (($current_page - 1) * $posts_per_page) + 1;
        $end = $current_page * $posts_per_page;
        $post_limit = "{$start}-{$end}";
        update_option('mhvconf_recordings_post_limit', $post_limit);
    } else {
        delete_option('mhvconf_recordings_current_page');
        delete_option('mhvconf_recordings_total_pages');
        update_option('mhvconf_recordings_post_limit', '');
    }

    wp_send_json_success($recording_array);
}
add_action('wp_ajax_meethour_fetch_recordings', 'meethour_fetch_recordings');

function meethour_hide_add_new_post_button()
{
    global $pagenow;
    if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'mhvconf_recordings') {
        wp_register_style('meethour_hide_add_new_post_button', false);
        wp_enqueue_style('meethour_hide_add_new_post_button');
        $css_custom = '.wrap .wp-heading-inline+.page-title-action{
                    display: none;
                };
                #sync-recordings{
                    margin-left:20px;
                }
                a.row-title{
                    pointer-events: none;
                    cursor: default;
                }';
        wp_add_inline_style('meethour_hide_add_new_post_button', $css_custom);
    }
}
add_action('admin_head', 'meethour_hide_add_new_post_button');


function meethour_display_error_message_recording()
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
add_action('admin_notices', 'meethour_display_error_message_recording');
