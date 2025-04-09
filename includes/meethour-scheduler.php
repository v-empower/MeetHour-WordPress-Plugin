<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');

use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\ScheduleMeeting;
use MeetHourApp\Types\EditMeeting;
use MeetHourApp\Types\ViewMeeting;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\ParticipationStatus;
use Spatie\IcalendarGenerator\Properties\TextProperty;

function mhvconf_fetch_timeZone()
{
    $access_token = get_option('meethour_access_token', '');
    $meetHourApiService = new MHApiService();
    $response = $meetHourApiService->timezone($access_token);
    if ($response->success == false) {
        set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
    }
    return $response->timezones;
};


add_action('add_meta_boxes', 'meethour_add_meeting_details_meta_box');

function meethour_add_meeting_details_meta_box()
{
    add_meta_box(
        'meeting_details_meta_box', // Unique ID
        'Meeting Details', // Title of the meta box
        'meethour_render_meeting_details_meta_box', // Callback function to render the content
        'mhvconf_meetings', // Post type to attach the meta box to
        'normal', // Context (normal, side, advanced)
        'high' // Priority (high, core, default, low)
    );
}
add_action('manage_mhvconf_meetings_posts_custom_column', 'meethour_custom_column_content', 10, 2);

function mhvconf_change_default_title($title)
{
    $screen = get_current_screen();
    if ('mhvconf_meetings' == $screen->post_type) {
        $title = 'Meeting Name';
    }
    return $title;
}
add_filter('enter_title_here', 'mhvconf_change_default_title');
function meethour_render_meeting_details_meta_box($post)
{
    $post_id = $post->ID;
    $wordpress_user_data = mhvconf_get_wordpress_users();
    $timeZones = mhvconf_fetch_timeZone();
    $meeting_id = get_post_meta($post_id, 'meeting_id', true);

    if (empty(get_post_meta($post_id, 'attendes', true)) && !empty($meeting_id)) {
        $access_token = get_option('meethour_access_token', '');
        $meetHourApiService = new MHApiService();
        $body = new ViewMeeting($meeting_id);
        $response = $meetHourApiService->viewMeeting($access_token, $body);
        if ($response->success == false) {
            set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
        } else {
            update_post_meta($post_id, 'attendes', json_encode($response->meeting_attendees));
        }
    }

    $attendes_response = json_decode(get_post_meta($post_id, 'attendes', true));
    $wp_emails = array_column($wordpress_user_data, 'user_email');
    $newArray = array();

    if (!empty($attendes_response)) {
        foreach ($attendes_response as $user) {
            if (!in_array($user->email, $wp_emails)) {
                $newArray[] = $user;
            }
        }
    }

    if (!empty($attendes_response)) {
        $selected_attendes_emails = array_column($attendes_response, 'email');
    }
    $utc_timestamp = (current_time('timestamp', true));

    wp_nonce_field('meethour_save_meeting_details', 'meethour_meeting_details_nonce');
?>
    <table style="display: flex;" class="form-table">
        <?php if (!empty($meeting_id)) { ?>
            <tr>
                <th><label for="meeting_name">Meeting ID :</label></th>
                <td>
                    <p><strong><?php echo esc_html($meeting_id) ?></strong></p>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th><label for="meeting_passcode">Meeting Passcode</label></th>
            <th><input autocomplete="off" type="text" value="<?php echo esc_html(get_post_meta($post_id, 'meeting_passcode', true)) ?>" id="meeting_passcode" name="meeting_passcode" class="regular-text" minlength="6" maxlength="16" required></th>
        </tr>
        <tr>
            <th><label for="meeting_description">Meeting Description</label></th>
            <th><input type="text" id="meeting_description" value="<?php echo esc_html(get_post_meta($post_id, 'meeting_agenda', true)) ?>" name="meeting_description" class="regular-text" required></th>
        </tr>
        <tr>
            <th><label for="meeting_date">Meeting Date</label></th>
            <td><input type="date" id="meeting_date" value="<?php
                                                            echo esc_html(!empty(get_post_meta($post_id, 'meeting_date', true)) ? get_post_meta($post_id, 'meeting_date', true) : mhvconf_meethour_format_utc_time($utc_timestamp, 'Y-m-d')) ?>" name="meeting_date" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="meeting_time">Meeting Time</label></th>
            <td><input type="time" id="meeting_time" value="<?php
                                                            echo esc_html(!empty(get_post_meta($post_id, 'meeting_time', true)) ? get_post_meta($post_id, 'meeting_time', true) : mhvconf_meethour_format_utc_time($utc_timestamp, 'H:i')) ?>" name="meeting_time" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="duration_hr">Duration (H:MM)</label></th>
            <td><input type="number" id="duration_hr" name="duration_hr" value="<?php echo esc_html(!empty(get_post_meta($post_id, 'duration_hr', true)) ? get_post_meta($post_id, 'duration_hr', true) : 01) ?>" min="0" max="24" class="small-text">
                <input type="number" id="duration_min" name="duration_min" value="<?php echo esc_html(get_post_meta($post_id, 'duration_min', true)) ? esc_html(get_post_meta($post_id, 'duration_min', true)) : 00 ?>" min="0" max="59" class="small-text">
            </td>
        </tr>

        <tr>
            <th><label for="timezone">Timezone</label></th>
            <td>
                <select style="width:100%" id="timezone" name="meethour_timezone">
                    <?php foreach ($timeZones as $timezone) {
                        $value = esc_html($timezone->value) . '|' . esc_html($timezone->name);
                        if (empty(get_post_meta($post_id, 'meethour_timezone_value', true))) {
                            $selected = ($value == get_option('meethour_timezone_value') . '|' . get_option('meethour_timezone_name')) ? 'selected' : '';
                        } else {
                            $selected = ($value == get_post_meta($post_id, 'meethour_timezone_value', true) . '|' . get_post_meta($post_id, 'meethour_timezone_name', true)) ? 'selected' : '';
                        }
                        echo '<option value="' . esc_attr($value) . '" ' . esc_html($selected) . '>' . esc_html($timezone->name) . '</option>';
                    } ?>
                </select>
            </td>
        <tr>
            <th>Select Attendes</th>
            <td>
                <select name="attendes[]" id="attendes" class="select2-select req" style="width: 100%;" onChange="getSelectedAttendes(this)" type="multiselect" multiple>
                    <?php foreach ($wordpress_user_data as $user) {
                        $firstName = isset($user->usermeta["first_name"]) ? $user->usermeta["first_name"] : '';
                        $lastName = isset($user->usermeta["last_name"]) ? $user->usermeta["last_name"] : '';
                        $email = isset($user->user_email) ? $user->user_email : '';

                        if (!empty($attendes_response)) {
                            $selected = in_array($email, $selected_attendes_emails) ? 'selected' : '';
                        } else {
                            $selected = '';
                        }
                    ?>
                        <option value='<?php echo esc_html(htmlspecialchars(json_encode(array("firstName" => $firstName, "lastName" => $lastName, "email" => $email)), ENT_QUOTES)); ?>' <?php echo esc_html($selected) ?>>
                            <?php echo esc_html(htmlspecialchars($email)); ?>
                        </option>
                    <?php };
                    foreach ($newArray as $user) {
                        $firstName = isset($user->firs_name) ? $user->firs_name : '';
                        $lastName = isset($user->last_name) ? $user->last_name : '';
                        $email = isset($user->email) ? $user->email : '';
                    ?>
                        <option value='<?php echo esc_html(htmlspecialchars(json_encode(array("firstName" => $firstName, "lastName" => $lastName, "email" => $email)), ENT_QUOTES)); ?>' selected>
                            <?php echo esc_html(htmlspecialchars($email)); ?>
                        </option>
                    <?php } ?>

                </select>
            </td>
        </tr>
        <p style="display: none;" id="attendes-response"><?php echo esc_html(get_post_meta($post_id, 'attendes', true)); ?></p>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th>Pick Moderator</th>
            <td id="Moderator"></td>
        </tr>
        <tr>
            <th><label>Instructions</label></th>
            <td>
                <textarea style="width: 100%;" name="comment" placeholder="Type your Instructions" minlength="10" value="<?php echo esc_html(get_post_meta($post_id, 'instructions', true)) ?>" id="instructions" rows="4"></textarea>
            </td>
        </tr>
    </table>
    <table style="display: flex;" class="form-table">
        <tr>
            <th>General Options </th>
            <td>
                <input type="checkbox" name="options[]" value="ALLOW_GUEST" id="vertical-checkbox-guest-user" checked="">
                <label for="vertical-checkbox-guest-user">Guest user can join meeting</label>
                <br />
                <input type="checkbox" name="options[]" value="JOIN_ANYTIME" id="vertical-checkbox-allow-anytime" checked="true">
                <label for="vertical-checkbox-allow-anytime">Allow participants to join anytime</label>
                <br />
                <input type="checkbox" name="options[]" value="ENABLE_LOBBY" id="vertical-checkbox-enable-lobby" checked="">
                <label for="vertical-checkbox-enable-lobby">Enable Lobby</label>
                <br />
                <input type="checkbox" name="options[]" value="LIVEPAD" id="vertical-checkbox-live-pad" checked="">
                <label for="vertical-checkbox-live-pad">LivePad</label>
                <br />
                <input type="checkbox" name="options[]" value="WHITE_BOARD" id="vertical-checkbox-WhiteBoard" checked="">
                <label for="vertical-checkbox-WhiteBoard">WhiteBoard</label>
            </td>
        </tr>
        <p id="api-options" style="display: none;"><?php echo esc_html(get_post_meta($post_id, 'options', true)) ?></p>
        <tr>
            <th><label for="recurring_meetings">Moderator Options</label></th>
            <td>
                <input type="checkbox" name="options[]" value="ENABLE_EMBEED_MEETING" id="vertical-checkbox-enable-embed-meeting" checked="">
                <label for="vertical-checkbox-enable-embed-meeting">Enable Embed Meeting</label><br />
                <input type="checkbox" name="options[]" value="DONOR_BOX" id="vertical-checkbox-donorbox" checked="">
                <label for="vertical-checkbox-donorbox">Donorbox visibilty</label><br />
                <input type="checkbox" name="options[]" value="CP_CONNECT" id="vertical-checkbox-click-pledge" checked="">
                <label for="vertical-checkbox-click-pledge">Click&amp;Pledge Connect</label><br />
                <input type="checkbox" name="options[]" value="DISABLE_SCREEN_SHARING_FOR_GUEST" id="vertical-checkbox-disable-screen-sharing-guest">
                <label for="vertical-checkbox-disable-screen-sharing-guest">Disable Screen Sharing for Guest</label><br />
                <input type="checkbox" name="options[]" value="DISABLE_JOIN_LEAVE_NOTIFICATIONS" id="vertical-checkbox-disable-toast-for-participant-entry-and-exit">
                <label for="vertical-checkbox-disable-toast-for-participant-entry-and-exit">Disable Entry/Exit Toast Notifications</label>
            </td>
        </tr>
        <tr>
            <th><label for="sound_controls">Sound Controls</label></th>
            <td>
                <input type="checkbox" name="options[]" value="PARTICIPANT_JOINED_SOUND_ID" class="input border mr-2" id="vertical-checkbox-participant-joined-sound-id">
                <label for="vertical-checkbox-participant-joined-sound-id">Turn off Participant Entry Sound</label><br />
                <input type="checkbox" name="options[]" value="PARTICIPANT_LEFT_SOUND_ID" class="input border mr-2" id="vertical-checkbox-participant-left-sound-id">
                <label for="vertical-checkbox-participant-left-sound-id">Turn off Participant Exit Sound</label><br />
                <input type="checkbox" name="options[]" value="INCOMING_USER_REQ_SOUND_ID" class="input border mr-2" id="vertical-checkbox-incoming-user-req-sound-id">
                <label for="vertical-checkbox-incoming-user-req-sound-id">Turn off Lobby Sound for Moderator</label><br />
                <input type="checkbox" name="options[]" value="USER_WAITING_REGISTER" class="input border mr-2" id="vertical-checkbox-user-waiting-register">
                <label for="vertical-checkbox-user-waiting-register">Turn off Background Music in Waiting Room</label><br />
            </td>
        </tr>
        <tr>
            <th><label for="calender_invite">Send Calendar Invite</label></th>
            <td>
                <?php
                $calendar_invite_status = esc_attr(get_post_meta($post_id, 'calendar_invite', true));
                ?>
                <input
                    id="calendar_invite"
                    type="checkbox"
                    name="calendar_invite"
                    value="invite"
                    checked data-invite-status="<?php echo esc_html($calendar_invite_status); ?>" />
            </td>
        </tr>
    </table>

    <?php
}

add_action('save_post_mhvconf_meetings', 'meethour_save_meeting_details');
function meethour_save_meeting_details($post_id)
{
    remove_action('save_post_mhvconf_meetings', 'meethour_save_meeting_details');
    $meetHourApiService = new MHApiService();

    $post_meeting_id = get_post_meta($post_id, 'meeting_id', true);

    wp_update_post(array(
        'ID'        => $post_id,
        'post_name' => $post_meeting_id
    ));

    $access_token = get_option('meethour_access_token', '');
    if (isset($_POST['meethour_meeting_details_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['meethour_meeting_details_nonce']));
        if (! wp_verify_nonce($nonce, 'meethour_save_meeting_details')) {
            wp_die('Nonce verification failed for meeting details.');
        }
        update_post_meta($post_id, 'meeting_agenda', sanitize_text_field(wp_unslash($_POST['meeting_description'])));
        update_post_meta($post_id, 'meeting_passcode', sanitize_text_field(wp_unslash($_POST['meeting_passcode'])));
        update_post_meta($post_id, 'meeting_date', sanitize_text_field(wp_unslash($_POST['meeting_date'])));
        update_post_meta($post_id, 'meeting_time', sanitize_text_field(wp_unslash($_POST['meeting_time'])));
        update_post_meta($post_id, 'duration_hr', sanitize_text_field(wp_unslash($_POST['duration_hr'])));
        update_post_meta($post_id, 'duration_min', sanitize_text_field(wp_unslash($_POST['duration_min'])));
        // update_post_meta($post_id, 'timezone', sanitize_text_field($_POST['timezone']));
        $timezone_value = sanitize_text_field(wp_unslash($_POST['meethour_timezone']));
        list($timezone_val, $timezone_name) = explode('|', $timezone_value);
        update_option('meethour_timezone_value', $timezone_val);
        update_option('meethour_timezone_name', $timezone_name);
        update_post_meta($post_id, 'meethour_timezone_value', $timezone_val);
        update_post_meta($post_id, 'meethour_timezone_name', $timezone_name);
        update_post_meta($post_id, 'instructions', sanitize_text_field(wp_unslash($_POST['comment'])));
        if (isset($_POST['options']) && is_array($_POST['options'])) {
            $options = wp_unslash($_POST['options']);
            $sanitized_options = map_deep($options, 'sanitize_text_field');
            update_post_meta($post_id, 'options', $sanitized_options);
        }
        update_post_meta($post_id, 'calendar_invite', sanitize_text_field($_POST['calendar_invite'] ? wp_unslash($_POST['calendar_invite']) : 'NotInvited'));

        $mainAttendes = array();
        if (isset($_POST['attendes']) && is_array($_POST['attendes'])) {
            foreach ($_POST['attendes'] as $item) {
                $item = wp_unslash($item);
                $decoded = json_decode($item, true);
                if (is_array($decoded)) {
                    $sanitized = array();
                    $sanitized['firstName'] = isset($decoded['firstName']) ? sanitize_text_field($decoded['firstName']) : '';
                    $sanitized['email'] = isset($decoded['email']) ? sanitize_email($decoded['email']) : '';
                    $mainAttendes[] = $sanitized;
                }
            }
        }
        update_post_meta($post_id, 'attendes', json_encode($mainAttendes));
        $mainHostUsers = array();
        if (isset($_POST['hosts']) && is_array($_POST['hosts'])) {
            foreach ($_POST['hosts'] as $item) {
                $item = wp_unslash($item);
                $decoded = json_decode($item, true);
                if (is_array($decoded)) {
                    $sanitized = array();
                    // Assuming each host has 'name' and 'email'
                    $sanitized['name'] = isset($decoded['name']) ? sanitize_text_field($decoded['name']) : '';
                    $sanitized['email'] = isset($decoded['email']) ? sanitize_email($decoded['email']) : '';
                    // Add more fields as needed
                    $mainHostUsers[] = $sanitized;
                }
            }
        }
        // Use $hostUsers directly as the sanitized array of host data




        update_post_meta($post_id, 'hosts', json_encode($mainHostUsers));
        $meetingName = get_the_title($post_id);
        $meeting_agenda = sanitize_text_field(wp_unslash($_POST['meeting_description']) ?? '');
        $passcode = sanitize_text_field(wp_unslash($_POST['meeting_passcode']) ?? '');
        $meetingDate = sanitize_text_field(wp_unslash($_POST['meeting_date']) ?? '');
        //Fix Time
        $Time = sanitize_text_field(wp_unslash($_POST['meeting_time']) ?? '');
        $meetingTime =  gmdate('h:i', strtotime($Time));
        $meetingMeridiem = gmdate('a', strtotime($Time));

        $timezone = $timezone_val;
        $duration_hr = absint($_POST['duration_hr'] ?? 1);
        $duration_min = absint($_POST['duration_min'] ?? 30);
        $options = map_deep(wp_unslash($_POST['options']), 'sanitize_text_field');
        $instructions = sanitize_text_field(wp_unslash($_POST['comment']));

        if ($post_meeting_id == NULL) {
            $uid = uniqid();
            update_post_meta($post_id, 'uid', $uid);
            $scheduleBody = new ScheduleMeeting($meetingName, $passcode, $meetingTime, $meetingMeridiem, $meetingDate, $timezone);
            $scheduleBody->attend = $mainAttendes;
            $scheduleBody->hostusers = $mainHostUsers;
            $scheduleBody->options = $options;
            $scheduleBody->is_show_portal = 1;
            $scheduleBody->send_calendar_invite = 0; //change calender invite to none
            $scheduleBody->agenda = $meeting_agenda;
            $scheduleBody->duration_hr = $duration_hr;
            $scheduleBody->duration_min = $duration_min;
            $scheduleBody->instructions = $instructions;
            $scheduleresponse = $meetHourApiService->scheduleMeeting(
                $access_token,
                $scheduleBody
            );
            $meeting_id = $scheduleresponse->data->meeting_id;
            $attendees = $scheduleresponse->data->meeting_attendees;

            if ($scheduleresponse->success == false) {
                set_transient('meethour_error_message', $scheduleresponse->message, 30); // store the error message for 30 seconds
                return;
            } else {
                update_post_meta($post_id, 'meeting_id', $meeting_id);
                update_post_meta($post_id, 'join_url', $scheduleresponse->data->joinURL);
                update_post_meta($post_id, 'attendes', json_encode($scheduleresponse->data->meeting_attendees));
                foreach ($attendees as $attendee) {
                    // Find the user by email
                    $user = get_user_by('email', $attendee->email);
                    if ($user) {
                        // Check if the user doesn't have a 'meethour_user_id' yet
                        if (!get_user_meta($user->ID, 'meethour_user_id', true)) {
                            update_user_meta($user->ID, 'meethour_user_id', $attendee->contact_id);
                        }
                    }
                }
            }
            $organizer_name = get_option('meethour_main_user');
            $meeting_description = (isset($meeting_agenda) && !empty($meeting_agenda) ? $meeting_agenda . "\n\n" : "") . $user->name . " is inviting you to a meeting on Meet Hour.\n\nJoin the meeting:\n" . get_option('siteurl') . "/join-meeting/" .  $scheduleresponse->data->meeting_id . "\n\nMeeting ID: " . $scheduleresponse->data->meeting_id . "\nPasscode: " . $scheduleresponse->data->passcode . " \n\nDate & Time: " . mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'M d, Y h:i A') . ' - '  . mhvconf_meethour_format_utc_time(strtotime($scheduleresponse->data->end_date_time), 'h:i A')  . "\n\n" . str_replace(array("\r", "\n"), "\n", $scheduleresponse->data->instructions);
            //Calender Code Start Here
            if (empty($attendees) && get_post_meta($post_id, 'calendar_invite', true) == 'invite') {
                set_transient('meethour_error_message', 'Meeting Created Successfully but Invitation Could not be Send "No Attendes Selected"', 30); // store the error message for 30 seconds
            }
            if (get_post_meta($post_id, 'calendar_invite', true) == 'invite' && !empty($attendees)) {
                $uid = get_post_meta($post_id, 'uid', true);

                $eventobj = Event::create($meetingName)
                    ->startsAt(new DateTime(mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'd M Y h:i:s a')))
                    ->endsAt(new DateTime(mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'd M Y h:i:s a')));
                $eventobj->description($meeting_description);
                $eventobj->address(get_option('siteurl') . "/join-meeting/" .  $scheduleresponse->data->meeting_id);
                $eventobj->addressName('Meet Hour Video Meeting');
                $eventobj->uniqueIdentifier($uid);
                $eventobj->organizer(get_option('meethour_main_user'), $user->email);
                $eventobj->attendee($user->email, $user->name, ParticipationStatus::needs_action(), true);
                $eventobj->alertMinutesBefore(10, $meetingName . ' is going to start in 10 minutes');
                $eventobj->appendProperty(TextProperty::create('SEQUENCE', '0'));
                $eventobj->appendProperty(TextProperty::create('STATUS', 'CONFIRMED'));
                $eventobj->appendProperty(TextProperty::create('TRANSP', 'OPAQUE'));
                //print_r($meeting_attendees ); exit;
                foreach ($attendees as $val) {
                    if (isset($val->email)) {
                        $eventobj->attendee($val->email, isset($val->first_name) ? $val->first_name : (isset($val->name) ? $val->name : ''), ParticipationStatus::needs_action(), true);
                    }
                }
                $calender = Calendar::create('Meethour Meeting Schedule')
                    ->event(
                        $eventobj
                    )->get();





                $upload_dir = wp_upload_dir();
                $file = $upload_dir['basedir'] . '/event.ics';
                file_put_contents($file, $calender);
                $attachments = array($file);
                $attendes = json_decode(get_post_meta($post_id, "attendes", true));
                $attendes_mail = [];
                foreach ($attendes as $attendee) {
                    array_push($attendes_mail, $attendee->email);
                }
                // Email headers
                $site_name  = get_bloginfo('name'); // Get your site's name
                $site_email = get_option('meethour_main_user'); // Or your preferred email option

                $css = "
                    body { font-family: Arial, sans-serif; }
                    .container { width: 600px; margin: 0 auto; }
                    .header { background-color: #4b6790; color: white; text-align: center; padding: 10px 0; }
                    .content { padding: 20px; background-color: white; border-radius: 10px; }
                    .footer { background-color: #f1f1f1; text-align: center; padding: 10px 0; margin-top: 20px; }
                    .button { background-color: #4b6790; color: #ffffff; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 4px; }
                ";

                wp_register_style('email-style', false);
                wp_add_inline_style('email-style', $css);
                ob_start();
                wp_print_styles('email-style');
                $style_tag = ob_get_clean();

                $message_template = "
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <title>Meeting Invitation</title>
                            {style_tag}
                        </head>
                        <body>
                            <div class='container'>
                            <div class='header'>
                            <h1>{meeting_name}</h1>
                            </div>
                            <div class='content'>
                            <p>Hi there,</p>
                            <p>{organizer_name} is inviting you to a meeting on {blogName}.</p>
                            <p>Description: {description}</p>
                            <p><strong>Join the meeting:</strong></p>
                            <p><a href='{join_url}' target='_blank' class='button'>Join Meeting</a></p>
                            <p>Meeting ID: {meeting_id}</p>
                            <p>Passcode: {passcode}</p>
                            <p>Date & Time: {meeting_date} {meeting_time} {meeting_meridiem}</p>
                            <p>Instructions: {instructions}</p>
                            </div>
                            <div class='footer'>
                            <p>Thank you for using {blogName}</p>
                            </div>
                            </div>
                        </body>
                    </html>
                ";
                // Define variables
                $blogName = get_option('blogname');
                $meeting_name = get_the_title($post_id);
                $organizer_name = get_option('meethour_main_user');
                $join_url = get_option('siteurl') . '/join-meeting/' . $meeting_id;
                $meeting_id = $scheduleresponse->data->meeting_id;
                $passcode = $scheduleresponse->data->passcode;
                $meeting_date = mhvconf_meethour_format_utc_time(strtotime($meetingDate), 'M d, Y');
                $meeting_time = $meetingTime;
                $meeting_meridiem = $meetingMeridiem;
                $instructions = $scheduleresponse->data->instructions;
                $description = $meeting_agenda;

                // Replace placeholders
                $message = str_replace(
                    ['{style_tag}', '{meeting_name}', '{organizer_name}', '{join_url}', '{meeting_id}', '{passcode}', '{meeting_date}', '{meeting_time}', '{meeting_meridiem}', '{instructions}', '{blogName}', '{description}'],
                    [$style_tag, $meeting_name, $organizer_name, $join_url, $meeting_id, $passcode, $meeting_date, $meeting_time, $meeting_meridiem, $instructions, $blogName, $description],
                    $message_template
                );

                // Email headers and sending
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $headers[] = "From: {$site_name} <{$site_email}>\r\n";
                $subject = "Meeting Invitation: $meetingName";
                $to = implode(',', $attendes_mail);
                $sent = wp_mail($to, $subject, $message, $headers, $attachments);
                if ($sent) {
                    wp_delete_file($file);
                    set_transient('meethour_success_message', 'Meeting Scheduled and Invited Successfully', 30);
                } else {
                    wp_delete_file($file);
                }
            }
        } else {
            //Updated Meeting API
            $updateBody = new EditMeeting($post_meeting_id);
            $updateBody->meeting_time = $meetingTime;
            $updateBody->meeting_meridiem = $meetingMeridiem;
            $updateBody->meeting_date = $meetingDate;
            $updateBody->timezone = $timezone;
            $updateBody->passcode = $passcode;
            $updateBody->meeting_name = $meetingName;
            $updateBody->attend = $mainAttendes;
            $updateBody->hostusers = $mainHostUsers;
            $updateBody->options = $options;
            $updateBody->is_show_portal = 1;
            $updateBody->agenda = $meeting_agenda;
            $updateBody->duration_hr = $duration_hr;
            $updateBody->duration_min = $duration_min;
            $updateBody->instructions = $instructions;
            $editresponse = $meetHourApiService->editMeeting(
                $access_token,
                $updateBody
            );

            $joinURL = $editresponse->data->joinURL;
            $meetingID = $editresponse->data->meeting_id;
            update_post_meta($post_id, 'meeting_id', $meetingID);
            update_post_meta($post_id, 'join_url', $joinURL);
            update_post_meta($post_id, 'attendes', json_encode($editresponse->data->meeting_attendees));
            $attendees = $editresponse->data->meeting_attendees;
            if ($editresponse->code == 1039) {
                set_transient('meethour_error_message', 'You dont have access to edit this meeting, as owner is someone else', 30); // store the error message for 30 seconds
            } elseif ($editresponse->success == false) {
                set_transient('meethour_error_message', $editresponse->message, 30); // store the error message for 30 seconds
            } else {
                foreach ($attendees as $attendee) {
                    // Find the user by email
                    $user = get_user_by('email', $attendee->email);
                    if ($user) {
                        // Check if the user doesn't have a 'meethour_user_id' yet
                        if (!get_user_meta($user->ID, 'meethour_user_id', true)) {
                            update_user_meta($user->ID, 'meethour_user_id', $attendee->contact_id);
                        }
                    }
                }

                if (get_post_meta($post_id, 'calendar_invite', true) == 'invite' && !empty($attendees)) {
                    $uid = get_post_meta($post_id, 'uid', true);
                    $meeting_description = (isset($meeting_agenda) && !empty($meeting_agenda) ? $meeting_agenda . "\n\n" : "") . $user->name . " is inviting you to a meeting on Meet Hour.\n\nJoin the meeting:\n" . get_option('siteurl') . "/join-meeting/" .  $editresponse->data->meeting_id . "\n\nMeeting ID: " . $editresponse->data->meeting_id . "\nPasscode: " . $editresponse->data->passcode . " \n\nDate & Time: " . mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'M d, Y h:i A') . ' - '  . mhvconf_meethour_format_utc_time(strtotime($editresponse->data->end_date_time), 'h:i A')  . "\n\n" . str_replace(array("\r", "\n"), "\n", $editresponse->data->instructions);
                    //Calender Code Start Here
                    $eventobj = Event::create($meetingName)
                        ->startsAt(new DateTime(mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'd M Y h:i:s a')))
                        ->endsAt(new DateTime(mhvconf_meethour_format_utc_time(strtotime($meetingDate . ' ' . $meetingTime . ' ' . $meetingMeridiem), 'd M Y h:i:s a')));
                    $eventobj->description($meeting_description);
                    $eventobj->address(get_option('siteurl') . "/join-meeting/" .  $editresponse->data->meeting_id);
                    $eventobj->addressName('Meet Hour Video Meeting');
                    $eventobj->uniqueIdentifier($uid);
                    $eventobj->organizer(get_option('meethour_main_user'), $user->email);
                    $eventobj->attendee($user->email, $user->name, ParticipationStatus::needs_action(), true);
                    $eventobj->alertMinutesBefore(10, $meetingName . ' is going to start in 10 minutes');
                    $eventobj->appendProperty(TextProperty::create('SEQUENCE', '0'));
                    $eventobj->appendProperty(TextProperty::create('STATUS', 'CONFIRMED'));
                    $eventobj->appendProperty(TextProperty::create('TRANSP', 'OPAQUE'));
                    //print_r($meeting_attendees ); exit;
                    foreach ($attendees as $val) {
                        if (isset($val->email)) {
                            $eventobj->attendee($val->email, isset($val->first_name) ? $val->first_name : (isset($val->name) ? $val->name : ''), ParticipationStatus::needs_action(), true);
                        }
                    }
                    $calender = Calendar::create('Meethour Meeting Schedule')
                        ->event(
                            $eventobj
                        )->get();
                    $upload_dir = wp_upload_dir();
                    $file = $upload_dir['basedir'] . '/event.ics';
                    file_put_contents($file, $calender);
                    $attachments = array($file);
                    $attendes = json_decode(get_post_meta($post_id, "attendes", true));
                    $attendes_mail = [];
                    foreach ($attendes as $attendee) {
                        array_push($attendes_mail, $attendee->email);
                    }
                    $site_name  = get_bloginfo('name'); // Get your site's name
                    $site_email = get_option('meethour_main_user'); // Or your preferred email option

                    $css = "
                    body { font-family: Arial, sans-serif; }
                    .container { width: 600px; margin: 0 auto; }
                    .header { background-color: #4b6790; color: white; text-align: center; padding: 10px 0; }
                    .content { padding: 20px; background-color: white; border-radius: 10px; }
                    .footer { background-color: #f1f1f1; text-align: center; padding: 10px 0; margin-top: 20px; }
                    .button { background-color: #4b6790; color: #ffffff; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 4px; }
                ";

                    wp_register_style('email-style', false);
                    wp_add_inline_style('email-style', $css);
                    ob_start();
                    wp_print_styles('email-style');
                    $style_tag = ob_get_clean();

                    $message_template = "
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <title>Meeting Invitation</title>
                            {style_tag}
                        </head>
                        <body>
                            <div class='container'>
                            <div class='header'>
                            <h1>{meeting_name}</h1>
                            </div>
                            <div class='content'>
                            <p>Hi there,</p>
                            <p>{organizer_name} is inviting you to a meeting on {blogName}.</p>
                            <p>Description: {description}</p>
                            <p><strong>Join the meeting:</strong></p>
                            <p><a href='{join_url}' target='_blank' class='button'>Join Meeting</a></p>
                            <p>Meeting ID: {meeting_id}</p>
                            <p>Passcode: {passcode}</p>
                            <p>Date & Time: {meeting_date} {meeting_time} {meeting_meridiem}</p>
                            <p>Instructions: {instructions}</p>
                            </div>
                            <div class='footer'>
                            <p>Thank you for using {blogName}</p>
                            </div>
                            </div>
                        </body>
                    </html>
                ";
                    // Define variables
                    $blogName = get_option('blogname');
                    $meeting_name = get_the_title($post_id);
                    $organizer_name = get_option('meethour_main_user');
                    $meeting_id = $editresponse->data->meeting_id;
                    $join_url = get_option('siteurl') . '/join-meeting/' . $meeting_id;
                    $passcode = $editresponse->data->passcode;
                    $meeting_date = mhvconf_meethour_format_utc_time(strtotime($meetingDate), 'M d, Y');
                    $meeting_time = $meetingTime;
                    $meeting_meridiem = $meetingMeridiem;
                    $instructions = $editresponse->data->instructions;
                    $description = $meeting_agenda;

                    // Replace placeholders
                    $message = str_replace(
                        ['{style_tag}', '{meeting_name}', '{organizer_name}', '{join_url}', '{meeting_id}', '{passcode}', '{meeting_date}', '{meeting_time}', '{meeting_meridiem}', '{instructions}', '{blogName}', '{description}'],
                        [$style_tag, $meeting_name, $organizer_name, $join_url, $meeting_id, $passcode, $meeting_date, $meeting_time, $meeting_meridiem, $instructions, $blogName, $description],
                        $message_template
                    );

                    // Email headers and sending
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                    $headers[] = "From: {$site_name} <{$site_email}>\r\n";
                    $subject = "Meeting Invitation: $meetingName";
                    $to = implode(',', $attendes_mail);
                    $sent = wp_mail($to, $subject, $message, $headers, $attachments);

                    if ($sent) {
                        wp_delete_file($file);
                        set_transient('meethour_success_message', 'Meeting Edited and Updated Invitation Send Successfully', 30);
                    } else {
                        set_transient('meethour_error_message', 'Meeting Edited and Updated Invitation Couldnt be Send', 30);
                        wp_delete_file($file);
                    }
                }
            }
        }
    }

    add_action('save_post_mhvconf_meetings', 'meethour_save_meeting_details');
}


function mhvconf_inject_js()
{
    $page_limit_meetings = get_option('mhvconf_meetings_post_limit', '');
    $page_limit_recordings = get_option('mhvconf_recordings_post_limit', '1-20');
    $meeting_limit = get_option('mhvconf_meetings_post_limit', '');
    $meeting_limit = empty($meeting_limit) ? '( 1-20 )' : $meeting_limit;
    $upcomming_meetings_nonce = wp_create_nonce('upcomming_meetings_nonce');
    $recording_nonce = wp_create_nonce('recording_nonce');
    $reset_plugin_nonce = wp_create_nonce('reset_plugin_nonce');

    // Register and enqueue the script
    wp_register_script(
        'mhvconf-inject-js',
        false,
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize the AJAX URL
    wp_localize_script('mhvconf-inject-js', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('mhvconf-inject-js');

    // Inline JavaScript
    $inline_script = "
        jQuery(function($) {
            console.log('This is Test');
            const meeting_limit = '" . esc_js($meeting_limit) . "';

            // Add Sync Meetings button
            $('body.post-type-mhvconf_meetings .wrap h1').append(
                `<button id='sync-meetings' style='margin-left:10px' class='page-title-action my-btn'>Sync Upcoming Meetings from Meet Hour <strong>" . esc_html($page_limit_meetings) . "</strong></button>`
            );

            // Button loader function
            $.fn.buttonLoader = function(action) {
                var self = $(this);
                if (action === 'start') {
                    if (self.attr('disabled') === 'disabled') return false;
                    $('.has-spinner').attr('disabled', true);
                    self.attr('data-btn-text', self.text());
                    var text = self.attr('data-load-text') || 'Fetching Data...';
                    self.html(`<span class='spinner'><i class='fa fa-spinner fa-spin' title='button-loader'></i></span> ` + text);
                    self.addClass('active');
                }
                if (action === 'stop') {
                    self.html(self.attr('data-btn-text'));
                    self.removeClass('active');
                    $('.has-spinner').attr('disabled', false);
                }
            };

            // Reset button loader function
            $.fn.resetbuttonLoader = function(action) {
                var self = $(this);
                if (action === 'start') {
                    if (self.attr('disabled') === 'disabled') return false;
                    $('.has-spinner').attr('disabled', true);
                    self.attr('data-btn-text', self.text());
                    var text = self.attr('data-load-text') || 'Reseting Data...';
                    self.html(`<span class='spinner'><i class='fa fa-spinner fa-spin' title='button-loader'></i></span> ` + text);
                    self.addClass('active');
                }
                if (action === 'stop') {
                    self.html(self.attr('data-btn-text'));
                    self.removeClass('active');
                    $('.has-spinner').attr('disabled', false);
                }
            };

            // Sync Meetings button click
            $('#sync-meetings').on('click', function(event) {
                event.preventDefault();
                $('.my-btn').buttonLoader('start');
                console.log('Sync Meetings button clicked');

                $.ajax({
                    url: my_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'meethour_fetch_upcoming_meetings',
                        nonce: '" . esc_js($upcomming_meetings_nonce) . "'
                    },
                    success: function(response) {
                        console.log('Meeting Limit: ', meeting_limit);
                        alert('Meetings synced successfully! ' + meeting_limit);
                        location.reload();
                        $('.my-btn').buttonLoader('stop');
                    },
                    error: function(xhr, status, error) {
                        $('.my-btn').buttonLoader('stop');
                        alert('Failed to sync meetings: ' + error);
                        console.log('Fetch upcoming meetings error:', error, status, xhr);
                    }
                });
            });

            // Add Fetch Recordings button
            $('body.post-type-mhvconf_recordings .wrap h1').append(
                `<a href='#' id='sync-recordings' style='margin-left:10px' class='page-title-action my-btn'>Fetch Recordings from Meet Hour <strong>(" . esc_html($page_limit_recordings) . ")</strong></a>`
            );

            // Fetch Recordings button click
            $('#sync-recordings').on('click', function(event) {
                event.preventDefault();
                $('.my-btn').buttonLoader('start');
                console.log('Fetch Recordings button clicked');

                $.ajax({
                    url: my_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'meethour_fetch_recordings',
                        nonce: '" . esc_js($recording_nonce) . "'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Recordings fetched successfully!');
                            location.reload();
                            console.log('Fetch recordings success:', response.data);
                        } else {
                            alert('Failed to fetch recordings: ' + response.data);
                            console.log('Fetch recordings error:', response.data);
                        }
                        $('.my-btn').buttonLoader('stop');
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to fetch recordings: ' + error);
                        console.log('Fetch recordings error:', error, status, xhr);
                        $('.my-btn').buttonLoader('stop');
                    }
                });
            });

            // Add Reset Plugin button
            $('body.toplevel_page_meethour-settings .wrap h1').append(
                `<a href='#' id='reset-plugin' class='page-title-action my-btn'>Reset Plugin</a>`
            );

            // Reset Plugin button click
            $('#reset-plugin').on('click', function(event) {
                event.preventDefault();
                $('.my-btn').resetbuttonLoader('start');

                $.ajax({
                    url: my_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mhvconf_meethour_deactivate',
                        nonce: '" . esc_js($reset_plugin_nonce) . "'
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Plugin Reset success:', response.data);
                            // Reload after a successful response
                            window.location.reload();
                        } else {
                            console.log('Reset Plugin error:', response.data);
                        }
                        $('.my-btn').resetbuttonLoader('stop');
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to reset plugin: ' + error);
                        console.log('Reset Plugin error:', error, status, xhr);
                        $('.my-btn').resetbuttonLoader('stop');
                    }
                });
            });
        });
    ";

    // Add the inline script
    wp_add_inline_script('mhvconf-inject-js', $inline_script, 'after');
}
add_action('admin_head', 'mhvconf_inject_js');

// Enqueue your script and localize the AJAX URL



function meethour_display_error_message()
{
    $error_message = get_transient('meethour_error_message');
    $success_message = get_transient('meethour_success_message');
    if ($error_message) {
    ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php
        delete_transient('meethour_error_message'); // delete the transient
    } elseif ($success_message) {
    ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
<?php
        delete_transient('meethour_success_message'); // delete the transient
    }
}
add_action('admin_notices', 'meethour_display_error_message');


add_filter('redirect_post_location', 'mhvconf_post_redirection');
function mhvconf_post_redirection($location)
{

    if ('mhvconf_meetings' == get_post_type()) {
        if (isset($_POST['save']) || isset($_POST['publish']))
            return admin_url("edit.php?post_type=mhvconf_meetings");
    }
    return $location;
}

add_action('wp_ajax_mhvconf_set_permalink_option', 'mhvconf_set_permalink_option');
function mhvconf_set_permalink_option()
{
    update_option('meethour_permalink', true);
    wp_send_json_success();
}
