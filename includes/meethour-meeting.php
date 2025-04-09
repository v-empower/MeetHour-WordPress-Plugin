<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');


use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\UpcomingMeetings;
use MeetHourApp\Types\ArchiveMeeting;
use MeetHourApp\Types\DeleteMeeting;
use MeetHourApp\Types\DeleteRecording;

// Function creates an Custom Post Type with mhvconf_meetings
function meethour_register_meeting_post_type()
{
    // $slug = get_option('meethour_permalink') ? 'join-meeting' : '';
    register_post_type('mhvconf_meetings', [
        'labels' => [
            'name' => 'Meetings',
            'singular_name' => 'Meetings',
            'add_new_item' => ('Schedule Meeting'),
            'add_new' => ('Schedule Meetings'),
        ],
        'public' => true,
        'rewrite' => [
            'slug' => 'join-meeting',
            'with_front' => false
        ],
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'show_in_menu' => false,
        'position'

    ]);
}
add_action('init', 'meethour_register_meeting_post_type');
add_filter('manage_mhvconf_meetings_posts_columns', 'meethour_add_custom_columns');

// Functions add Custom Columns in Custom Post Type
function meethour_add_custom_columns($columns)
{
    $columns = [
        'cb' => '<input type="checkbox" />',
        'title' => 'Meeting Name',
        'meeting_id' => 'Meeting ID',
        'date_time' => 'Date & Time',
        'duration' => 'Duration',
        'agenda' => 'Agenda',
        'shortcode' => 'Shortcode',
        'meeting_link' => 'Meeting Link',
        'meethour_link' => 'External Link',
    ];
    return $columns;
}

// This Function add Content to Custom Post Type. Title and Cb are Default cant change them 
function meethour_custom_column_content($column, $post_id)
{

    $start_time = get_post_meta($post_id, 'meeting_date', true);
    $utc_timestamp = current_time('timestamp', true);
    $current_datetime = mhvconf_meethour_format_utc_time($utc_timestamp, 'Y-m-d');
    $current_status = get_post_status($post_id);

    if ($current_status !== 'trash') {
        if ($start_time < $current_datetime) {
            $my_post = array(
                'ID'          => $post_id,
                'post_status' => 'missed',
            );
        } elseif ($start_time >= $current_datetime) {
            $my_post = array(
                'ID'          => $post_id,
                'post_status' => 'upcoming',
            );
        }
        wp_update_post($my_post);
    }


    switch ($column) {
        case 'meeting_id':
            echo esc_html(get_post_meta($post_id, 'meeting_id', true));
            break;
        case 'date_time':
            $start_time = mhvconf_meethour_format_utc_time(strtotime(get_post_meta($post_id, 'meeting_date', true)), 'M d, Y') . " " . date_format(date_create(get_post_meta($post_id, 'meeting_time', true)), "h:ia");
            echo esc_html($start_time);
            break;
        case 'duration':
            echo esc_html(get_post_meta($post_id, 'duration_hr', true) . 'h' . " " . get_post_meta($post_id, 'duration_min', true) . 'm');
            break;
        case 'agenda':
            $post = get_post($post_id);
            echo esc_html($post->post_content);
            break;
        case 'shortcode':
            echo '<button class="button button-small copy-shortcode" data-shortcode="[meethour meeting_id=' . esc_html(get_post_meta($post_id, 'meeting_id', true)) . ']">Copy Shortcode</button>';
            wp_register_script(
                'mhvconf-meetings-copy-shortcode',
                false,
                array('jquery'),
                '1.0.0',
                true
            );
            wp_enqueue_script('mhvconf-meetings-copy-shortcode');
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
            wp_add_inline_script('mhvconf-meetings-copy-shortcode', $inline_script, 'after');
            break;
        case 'meeting_link':
            echo '<a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">Join Meeting</a>';
            break;
        case 'meethour_link':
            $meeting_link = get_post_meta($post_id, 'join_url', true);
            if (empty($meeting_link)) {
                echo '<a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">Join Meeting</a>';
            } else {
                echo '<a href="' . esc_url($meeting_link) . '" target="_blank">Join Meeting 🔗</a>';
            }
            break;
    }
}

add_action('pre_get_posts', 'meethour_exclude_past_meetings');

function meethour_exclude_past_meetings($query)
{
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'mhvconf_meetings') {

        $meta_query = [
            [
                'key'     => 'meeting_id',
                'value'   => '',
                'compare' => '!=',
            ],
            [
                'key'     => 'meeting_id',
                'compare' => 'EXISTS',
            ],

        ];

        $query->set('meta_query', $meta_query);
    }
}

function meethour_fetch_upcoming_meetings()
{
    $meetHourApiService = new MHApiService();
    $access_token = get_option('meethour_access_token', '');

    $current_page = get_option('mhvconf_meetings_current_page', 1);
    $total_pages  = get_option('mhvconf_meetings_total_pages', null);

    // If for some reason our current page isn’t set properly, reset it.
    if (empty($current_page)) {
        $current_page = 1;
        update_option('mhvconf_meetings_current_page', $current_page);
    }

    $posts_per_page = 20;

    $body = new UpcomingMeetings();
    $body->limit = $posts_per_page;
    $body->page = $current_page;
    $response = $meetHourApiService->upcomingMeetings($access_token, $body);
    if ($response->success == false) {
        set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
        return;
    }

    if (is_null($total_pages)) {
        $total_pages = $response->total_pages;
        update_option('mhvconf_meetings_total_pages', $total_pages);
    }

    $meetings_array = json_decode(json_encode($response->meetings), true);
    foreach ($meetings_array as $meet) {
        $args = array(
            'meta_key' => 'meeting_id',
            'meta_value' => $meet['meeting_id'],
            'post_type' => 'mhvconf_meetings',
            'post_status' => 'any',
            'posts_per_page' => -1
        );
        $existing_posts = get_posts($args);
        if (empty($existing_posts)) {
            wp_insert_post([
                'post_title'   => $meet['topic'],
                'post_name' => $meet['meeting_id'],
                'post_content' => empty($meet['agenda']) ? $meet['topic'] : $meet['agenda'],
                'post_type'    => 'mhvconf_meetings',
                'post_status'  => 'publish',
                'meta_input'   => [
                    'id'         => $meet['id'],
                    'meeting_id' => $meet['meeting_id'],
                    'meeting_date'       => explode(" ", $meet['start_time'])[0],
                    'meeting_time'       => explode(" ", $meet['start_time'])[1],
                    'duration_hr'   => explode(":", $meet['duration'])[0],
                    'duration_min'  => explode(":", $meet['duration'])[1],
                    'meeting_name'      => $meet['topic'],
                    'meeting_agenda'     => $meet['agenda'],
                    'timezone'   => $meet['timezone'],
                    'join_url'   => $meet['joinURL'],
                    'meeting_passcode'   => $meet['passcode'],
                    'options'    => $meet['settings'],
                    'instructions' => $meet['instructions']
                ],
            ]);
        }
    }

    if ($current_page < $total_pages) {
        $current_page++;
        update_option('mhvconf_meetings_current_page', $current_page);

        $start = (($current_page - 1) * $posts_per_page) + 1;
        $end   = $current_page * $posts_per_page;
        $next_page_limit = "( {$start}-{$end} )";
        update_option('mhvconf_meetings_post_limit', $next_page_limit);
    } else {
        delete_option('mhvconf_meetings_current_page');
        delete_option('mhvconf_meetings_total_pages');
        delete_option('mhvconf_meetings_post_limit', ''); // Optional: mark completion.
    }
    return $response;
}

add_action('wp_ajax_meethour_fetch_upcoming_meetings', 'meethour_fetch_upcoming_meetings');
add_action('wp_ajax_nopriv_meethour_fetch_upcoming_meetings', 'meethour_fetch_upcoming_meetings');


add_action('wp_trash_post', 'mhvconf_Archive_Meethour_Post', 1, 1);
function mhvconf_Archive_Meethour_Post($post_id)
{
    $http_host   = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    $currentPageUrl = esc_url_raw('http://' . $http_host . $request_uri);
    $parsed_url = wp_parse_url($currentPageUrl);
    $query = $parsed_url['query'];
    parse_str($query, $params);
    $meeting_archive = $params['meeting_trash'];
    if ($meeting_archive == 'yes') {
        $meetHourApiService = new MHApiService();
        $post = get_post($post_id);
        $post_type = $post->post_type;
        $token = get_option('meethour_access_token', '');
        if ($post_type == 'mhvconf_meetings') {
            $meeting_id = get_post_meta($post_id, 'meeting_id', true);
            $body = new ArchiveMeeting($meeting_id);
            $response = $meetHourApiService->archiveMeeting($token, $body);
            if ($response->success == false) {
                set_transient('meethour_error_message', $response->message, 30);
                return;
            }
        }
    }
}

add_action('before_delete_post', 'mhvconf_erase_meet_post');
function mhvconf_erase_meet_post($post_id)
{
    $http_host   = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    $currentPageUrl = esc_url_raw('https://' . $http_host . $request_uri);
    $parsed_url = wp_parse_url($currentPageUrl);
    $query = $parsed_url['query'];
    parse_str($query, $params);
    $meeting_delete = $params['meeting_delete'];
    $recording_delete = $params['recording_delete'];

    $meetHourApiService = new MHApiService();
    $post = get_post($post_id);
    $post_type = $post->post_type;
    $token = get_option('meethour_access_token', '');
    $meeting_id = get_post_meta($post_id, 'meeting_id', true);

    if ($meeting_delete == 'yes') {
        if ($post_type == 'mhvconf_meetings') {
            $body = new DeleteMeeting($meeting_id);
            $meeting_response = $meetHourApiService->deleteMeeting($token, $body);
            if ($meeting_response->success == false) {
                set_transient('meethour_error_message', $meeting_response->message, 30);
                return;
            }
        }
    }
    if ($recording_delete == 'yes') {
        if ($post_type == 'mhvconf_recordings') {
            $recording_id = get_post_meta($post_id, 'recording_id', true);
            $main = new DeleteRecording($recording_id);
            $response = $meetHourApiService->deleteRecording($token, $main);
            set_transient('meethour_error_message', $response->message, 30);
            return;
        }
    }
}

function mhvconf_confirm_deletion()
{
    global $pagenow, $typenow;
    if ($pagenow == 'edit.php' && $typenow == 'mhvconf_meetings') {
        wp_register_script(
            'mhvconf_confirm_deletion_meeting',
            false,
            array('jquery'),
            '1.0.0',
            true
        );
        wp_enqueue_script('mhvconf_confirm_deletion_meeting');
        $mhvconf_confirm_deletion_script_meeting = "
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll(`a.submitdelete, .bulkactions option[value='trash']`).forEach(function (element) {
                    element.addEventListener('click', function (e) {
                        e.preventDefault();
                        const url = element.getAttribute('href');
                        const action = new URL(url).searchParams.get('action');

                        function showConfirmDialog(message, callback) {
                            const confirmBox = document.createElement('div');
                            confirmBox.style.position = 'fixed';
                            confirmBox.style.top = '0';
                            confirmBox.style.left = '0';
                            confirmBox.style.width = '100%';
                            confirmBox.style.height = '100%';
                            confirmBox.style.backgroundColor = 'rgba(0,0,0,0.5)';
                            confirmBox.style.display = 'flex';
                            confirmBox.style.justifyContent = 'center';
                            confirmBox.style.alignItems = 'center';
                            confirmBox.style.zIndex = '10000';

                            const dialog = document.createElement('div');
                            dialog.style.backgroundColor = 'white';
                            dialog.style.padding = '20px';
                            dialog.style.borderRadius = '5px';
                            dialog.style.textAlign = 'center';
                            dialog.innerHTML = `<p style='font-size: 18px;'>Would you like to take action on this Meeting from Meet Hour Portal as well?</p>`;

                            const yesButton = document.createElement('button');
                            yesButton.textContent = 'Yes, In Meet Hour also';
                            yesButton.style.margin = '5px';
                            yesButton.style.padding = '10px';
                            yesButton.style.border = 'none';
                            yesButton.style.backgroundColor = '#0085ba';
                            yesButton.style.color = 'white';
                            yesButton.style.cursor = 'pointer';
                            yesButton.addEventListener('click', function () {
                                confirmBox.remove();
                                callback(true);
                            });

                            const noButton = document.createElement('button');
                            noButton.textContent = 'No, Only in WordPress';
                            noButton.style.margin = '5px';
                            noButton.style.padding = '10px';
                            noButton.style.backgroundColor = 'lightgray';
                            noButton.style.border = 'none';
                            noButton.style.cursor = 'pointer';
                            noButton.addEventListener('click', function () {
                                confirmBox.remove();
                                callback(false);
                            });

                            // **Add the Cancel button**
                            const cancelButton = document.createElement('button');
                            cancelButton.textContent = 'Cancel';
                            cancelButton.style.margin = '5px';
                            cancelButton.style.padding = '10px 30px';
                            cancelButton.style.backgroundColor = 'white';
                            cancelButton.style.border = 'solid 0.5px #aaaaaa';
                            cancelButton.style.color = '#aaaaaa';
                            cancelButton.style.borderRadius = '3px';
                            cancelButton.style.cursor = 'pointer';
                            cancelButton.addEventListener('click', function () {
                                confirmBox.remove();
                                // No action needed
                            });

                            dialog.appendChild(yesButton);
                            dialog.appendChild(noButton);
                            dialog.appendChild(cancelButton); // **Append the Cancel button**
                            confirmBox.appendChild(dialog);
                            document.body.appendChild(confirmBox);
                        }

                        if (action === 'trash') {
                            showConfirmDialog('Would you like to trash this Meeting from Meet Hour Portal as well?', function (confirmed) {
                                let newUrl = url + '&meeting_trash=' + (confirmed ? 'yes' : 'no');
                                window.location.href = newUrl;
                            });
                        } else if (action === 'delete') {
                            showConfirmDialog('Would you like to delete this Meeting from Meet Hour Portal as well?', function (confirmed) {
                                let newUrl = url + '&meeting_delete=' + (confirmed ? 'yes' : 'no');
                                window.location.href = newUrl;
                            });
                        } else {
                            // For other actions, proceed as normal
                            window.location.href = url;
                        }
                    });
                });
            });
        ";
        wp_add_inline_script('mhvconf_confirm_deletion_meeting', $mhvconf_confirm_deletion_script_meeting, 'after');
    }
    if ($pagenow == 'edit.php' && $typenow == 'mhvconf_recordings') {
        wp_register_script(
            'mhvconf_confirm_deletion_recordings',
            false,
            array('jquery'),
            '1.0.0',
            true
        );
        wp_enqueue_script('mhvconf_confirm_deletion_recordings');
        $mhvconf_confirm_deletion_script_recording = "
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('a.submitdelete').forEach(function (element) {
                    element.addEventListener('click', function (e) {
                        e.preventDefault();
                        const url = element.getAttribute('href');

                        function showConfirmDialog(callback) {
                            const confirmBox = document.createElement('div');
                            confirmBox.style.position = 'fixed';
                            confirmBox.style.top = '0';
                            confirmBox.style.left = '0';
                            confirmBox.style.width = '100%';
                            confirmBox.style.height = '100%';
                            confirmBox.style.backgroundColor = 'rgba(0,0,0,0.5)';
                            confirmBox.style.display = 'flex';
                            confirmBox.style.justifyContent = 'center';
                            confirmBox.style.alignItems = 'center';
                            confirmBox.style.zIndex = '10000';

                            const dialog = document.createElement('div');
                            dialog.style.backgroundColor = 'white';
                            dialog.style.padding = '20px';
                            dialog.style.borderRadius = '5px';
                            dialog.style.textAlign = 'center';
                            dialog.innerHTML = `<p style='font-size: 18px;'>Are you sure you want to delete this Recording from Meet Hour Portal as well?</p>`;

                            const yesButton = document.createElement('button');
                            yesButton.textContent = 'Yes. In Meet Hour also';
                            yesButton.style.margin = '5px';
                            yesButton.style.padding = '10px';
                            yesButton.style.border = 'none';
                            yesButton.style.backgroundColor = '#0085ba';
                            yesButton.style.color = 'white';
                            yesButton.style.cursor = 'pointer';
                            yesButton.addEventListener('click', function () {
                                confirmBox.remove();
                                callback(true);
                            });

                            const noButton = document.createElement('button');
                            noButton.textContent = 'No, Only in WordPress';
                            noButton.style.margin = '5px';
                            noButton.style.padding = '10px';
                            noButton.style.backgroundColor = 'lightgray';
                            noButton.style.border = 'none';
                            noButton.style.cursor = 'pointer';
                            noButton.addEventListener('click', function () {
                                confirmBox.remove();
                                callback(false);
                            });

                            // **Add the Cancel button**
                            const cancelButton = document.createElement('button');
                            cancelButton.textContent = 'Cancel';
                            cancelButton.style.margin = '5px';
                            cancelButton.style.padding = '10px 30px';
                            cancelButton.style.backgroundColor = 'white';
                            cancelButton.style.border = 'solid 0.5px #aaaaaa';
                            cancelButton.style.color = '#aaaaaa';
                            cancelButton.style.borderRadius = '3px';
                            cancelButton.style.cursor = 'pointer';
                            cancelButton.addEventListener('click', function () {
                                confirmBox.remove();
                                // No action needed
                            });

                            dialog.appendChild(yesButton);
                            dialog.appendChild(noButton);
                            dialog.appendChild(cancelButton); // **Append the Cancel button**
                            confirmBox.appendChild(dialog);
                            document.body.appendChild(confirmBox);
                        }

                        showConfirmDialog(function (confirmed) {
                            let newUrl = url + '&recording_delete=' + (confirmed ? 'yes' : 'no');
                            window.location.href = newUrl;
                        });
                    });
                });
            });
        ";
        wp_add_inline_script('mhvconf_confirm_deletion_recordings', $mhvconf_confirm_deletion_script_recording, 'after');
    }
}
add_action('admin_footer', 'mhvconf_confirm_deletion');




function meethour_display_error_message_meeting()
{
    $error_message = get_transient('meethour_error_message');
    if ($error_message) {
?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php
        delete_transient('meethour_error_message');
    }
}
add_action('admin_notices', 'meethour_display_error_message_meeting');

// Register custom post statuses
function mhvconf_meetings_register_post_statuses()
{
    // Upcoming Status
    register_post_status('upcoming', array(
        'label'                     => _x('Upcoming', 'post status label', 'meet-hour-video-conference'),
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'exclude_from_search'       => false,
        // Translators: %s is the number of upcoming meetings.
        'label_count'               => _n_noop('Upcoming (%s)', 'Upcoming (%s)', 'meet-hour-video-conference'),
    ));

    // Missed Status
    register_post_status('missed', array(
        'label'                     => _x('Missed', 'post status label', 'meet-hour-video-conference'),
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'exclude_from_search'       => false,
        // Translators: %s is the number of missed meetings.
        'label_count'               => _n_noop('Missed (%s)', 'Missed (%s)', 'meet-hour-video-conference'),
    ));
}
add_action('init', 'mhvconf_meetings_register_post_statuses');
function mhvconf_meetings_update_post_type_args($args, $post_type)
{
    if ('mhvconf_meetings' === $post_type) {
        $args['capability_type'] = 'post';
        $args['capabilities']    = array();
        $args['map_meta_cap']    = true;
    }
    return $args;
}
add_filter('register_post_type_args', 'mhvconf_meetings_update_post_type_args', 10, 2);

function mhvconf_meetings_post_status_filter()
{
    global $post_type;
    global $post_status;

    if ('mhvconf_meetings' === $post_type) {
        $statuses = array(
            'all'      => __('All statuses', 'meet-hour-video-conference'),
            'publish'  => __('Published', 'meet-hour-video-conference'),
            'upcoming' => __('Upcoming', 'meet-hour-video-conference'),
            'missed'   => __('Missed', 'meet-hour-video-conference'),
            'trash'    => __('Trash', 'meet-hour-video-conference'),
        );
    ?>
        <select name="post_status">
            <?php foreach ($statuses as $status => $label) : ?>
                <option value="<?php echo esc_attr($status); ?>" <?php selected($post_status, $status); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
<?php
    }
}
add_action('restrict_manage_posts', 'mhvconf_meetings_post_status_filter');

function mhvconf_meetings_include_custom_statuses($query)
{
    global $post_type, $pagenow;


    if (is_admin() && 'edit.php' === $pagenow && 'mhvconf_meetings' === $post_type && !isset($_GET['post_status'])) {
        $query->set('post_status', array('publish', 'upcoming', 'missed'));
    }
}
add_action('pre_get_posts', 'mhvconf_meetings_include_custom_statuses');

function mhvconf_meetings_append_post_status_list()
{
    global $post;
    if ('mhvconf_meetings' !== get_post_type($post)) {
        return;
    }
    wp_register_script(
        'mhvconf-append-post-status',
        false,
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf-append-post-status');
    $inline_script = "
        jQuery(document).ready(function($) {
            var select = $('#post_status');
            var statuses = {
                'upcoming': " . esc_js(__('Upcoming', 'meet-hour-video-conference')) . ",
                'missed': " . esc_js(__('Missed', 'meet-hour-video-conference')) . "
            };

            $.each(statuses, function(value, text) {
                select.append($('<option>').val(value).text(text));
            });

            $('#post_status option[value=" . esc_html($post->post_status) . "]').attr('selected', 'selected');
        });
        ";
    wp_add_inline_script('mhvconf-append-post-status', $inline_script, 'after');
}
add_action('post_submitbox_misc_actions', 'mhvconf_meetings_append_post_status_list');



function mhvconf_meetings_quick_edit_custom_status($column_name, $post_type)
{
    if ('mhvconf_meetings' !== $post_type) {
        return;
    }
    wp_register_script(
        'mhvconf-quick-edit-custom-status',
        false,
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf-quick-edit-custom-status');
    $inline_script = "
            jQuery(document).ready(function($) {
            $(document).on('click', '.editinline', function() {
                var post_id = $(this).closest('tr').attr('id').replace('post-', '');
                var status = $('#inline_' + post_id + ' .post_status').text();

                // Add custom statuses to the Quick Edit dropdown
                var select = $(`select[name='_status']`);
                var statuses = {
                    'upcoming': " . esc_js(__('Upcoming', 'meet-hour-video-conference')) . ",
                    'missed': " . esc_js(__('Missed', 'meet-hour-video-conference')) . "
                };

                $.each(statuses, function(value, text) {
                    if (select.find(`option[value='` + value + `']`).length === 0) {
                        select.append($('<option>').val(value).text(text));
                    }
                });

                // Set the selected status
                select.val(status);
            });
        });
    ";
    wp_add_inline_script('mhvconf-quick-edit-custom-status', $inline_script, 'after');
}
add_action('quick_edit_custom_box', 'mhvconf_meetings_quick_edit_custom_status', 10, 2);



function mhvconf_custom_template_include($template)
{
    if (get_query_var('post_type') === 'mhvconf_meetings') {
        return $template = dirname(__FILE__) . '/single-mh_meetings.php';
    }
    if (is_singular('mhvconf_recordings')) {
        return $template = dirname(__FILE__) . '/single-mh_recordings.php';
    }
    return $template;
}
add_filter('template_include', 'mhvconf_custom_template_include');


add_action('init', function () {
    global $wp_rewrite;
    if (empty(get_option('permalink_structure'))) {
        $wp_rewrite->set_permalink_structure('?p=/%post_id%/');
    }
});
