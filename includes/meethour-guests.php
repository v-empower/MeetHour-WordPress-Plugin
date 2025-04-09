<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');

use MeetHourApp\Services\MHApiService;
use MeetHourApp\Types\ContactsList;
use MeetHourApp\Types\DeleteContact;
use MeetHourApp\Types\AddContact;
use MeetHourApp\Types\EditContact;

// Fetches all Wordpress users with thier metadata
function mhvconf_get_wordpress_users()
{
    $members = get_users(
        array(
            'orderby' => 'ID',
            'order'   => 'ASC'
        )
    );
    $members = array_map(function ($member) {
        $member->usermeta = array_map(function ($data) {
            return reset($data);
        }, get_user_meta($member->ID));
        return $member;
    }, $members);

    $user_data = array_map(function ($member) {
        return $member->data;
    }, $members);
    return $user_data;
}


// Fetches all users from the MeetHour platform.
function mhvconf_meethour_fetch_users()
{
    check_ajax_referer('meethour_fetch_contacts', 'nonce');
    $meetHourApiService = new MHApiService();
    $access_token = get_option('meethour_access_token', '');
    // calling contactlist sdk
    $body = new ContactsList();
    $response = $meetHourApiService->ContactsList($access_token, $body);
    //showing the error while fetching the contact list
    if ($response->success == false) {
        if ($response->message == 'Your contact already added.') {
        }
        set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
    }
    // Adds the Response data into the database
    $data = $response->contacts;
    foreach ($data as $contact) {
        $username = $contact->first_name . " " . $contact->last_name;
        $firstname = empty($contact->first_name) ? '' : $contact->first_name;
        $lastname = empty($contact->last_name) ? '' : $contact->last_name;
        $email = $contact->email;
        $users = get_user_by('email', $email);
        $meta_key = 'meethour_user_id';
        $meta_value = $contact->id;
        if ($users === false) {
            $user_id = wp_insert_user(array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => wp_generate_password(),
                'first_name' => $firstname,
                'last_name' => $lastname,
                'display_name' => $username,
                'role' => 'meethour'
            ));
            update_user_meta($user_id, $meta_key, $meta_value, '');
        } else {
            $user = $users; // Since get_user_by returns a single user object
            update_user_meta($user->ID, $meta_key, $meta_value);
        }
    }


    if (!empty($data)) {
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No users found');
    }

    wp_die(); // Always call this at the end of an AJAX handler


    return $data ?? [];
}

// checks if access token is generated or not if not generated then i wont show the fetch contact button
$access_token = get_option('meethour_access_token', '');
if (!empty($access_token)) {
    add_action('admin_head', 'mhvconf_generate_contacts_button');
}

// This Function adds "Fetch Meet Hour Contacts Button" using jquery
function mhvconf_generate_contacts_button()
{
    $nonce = wp_create_nonce('meethour_fetch_contacts');
    wp_register_script(
        'mhvconf_generate_contacts_button',
        false,
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf_generate_contacts_button');
    $inline_script = "
jQuery(document).ready(function ($) {
    jQuery('body.users-php .wrap h1').append(`<a style='margin-left:10px' href='#' id='sync-contacts' class='page-title-action my-btn'>Fetch Meet Hour Contacts</a>`);
    $.fn.buttonLoader = function (action) {
        var self = $(this);
        if (action == 'start') {
            if ($(self).attr('disabled') == 'disabled') {
                return false;
            }
            $('.has-spinner').attr('disabled', true);
            $(self).attr('data-btn-text', $(self).text());
            var text = 'Fetching Data...';
            if ($(self).attr('data-load-text') !== undefined && $(self).attr('data-load-text') !== '') {
                text = $(self).attr('data-load-text');
            }
            $(self).html(`<span class='spinner'><i class='fa fa-spinner fa-spin' title='button-loader'></i></span> ` + text);
            $(self).addClass('active');
        }
        if (action == 'stop') {
            $(self).html($(self).attr('data-btn-text'));
            $(self).removeClass('active');
            $('.has-spinner').attr('disabled', false);
        }
    };
    jQuery('#sync-contacts').on('click', function (event) {
        event.preventDefault();
        $('.my-btn').buttonLoader('start');
        console.log('Fetch contacts button clicked');
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'meethour_fetch_contacts',
                nonce: '" . esc_js($nonce) . "'
            },
            success: function (response) {
                if (response.success) {
                    alert('Contacts fetched successfully!');
                    console.log('Fetch contacts success:', response.data);
                    location.reload();
                    $('.my-btn').buttonLoader('stop');
                } else {
                    alert('Failed to fetch contacts: ' + response.data);
                    console.log('Fetch contacts error 1:', response);
                    location.reload();
                    $('.my-btn').buttonLoader('stop');
                }
            },
            error: function (xhr, status, error) {
                alert('Failed to fetch contacts: ' + error);
                console.log('Fetch contacts error 2:', error, status, xhr);
                location.reload();
                $('.my-btn').buttonLoader('stop');
            }
        });
    });
});
    ";
    wp_add_inline_script('mhvconf_generate_contacts_button', $inline_script, 'after');
}
add_action('wp_ajax_meethour_fetch_contacts', 'mhvconf_meethour_fetch_users');

// checks if access token is generated or not if not generated then wont show delete option for meethour users role
$access_token = get_option('meethour_access_token', '');
if (!empty($access_token)) {
    add_action('delete_user_form', 'mhvconf_meethour_delete_user_form', 10, 2);
}

// This Function is use to show a validation form before deleting the user from meet hour
function mhvconf_meethour_delete_user_form($user, $userids)
{
    wp_nonce_field('meethour_delete_user', 'meethour_delete_user_nonce');
    if (!empty($userids)) {
        $users_with_meethour_id = array();
        // checks if the user has meethour id, if not then it will not show the meethour options
        foreach ($userids as $user_id) {
            $meethour_user_id = get_user_meta($user_id, 'meethour_user_id', true);

            if (!empty($meethour_user_id)) {
                $users_with_meethour_id[] = $user_id;
            }
        }
        if (!empty($users_with_meethour_id)) {
?>
            <h2><?php echo esc_html('Meet Hour Options'); ?></h2>
            <?php wp_nonce_field('mhvconf_erase_meethour_user_nonce', 'meethour_delete_user_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row" style="padding-top: 5px;">
                        <label for="delete_meethour"><?php echo esc_html('Delete from Meet Hour'); ?></label>
                    </th>
                    <td style="padding-top: 5px;">
                        <label for="delete_meethour">
                            <input type="checkbox" name="delete_meethour" id="delete_meethour" value="yes" />
                            <?php echo esc_html('I Agree to delete these users from the Meet Hour Portal as well.'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        <?php
        }
    }
}

// This Function is called when User Delete Form Validation is True
add_action('delete_user', 'mhvconf_erase_meethour_user');
function mhvconf_erase_meethour_user($user_id)
{
    if (
        ! isset($_POST['meethour_delete_user_nonce']) ||
        ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['meethour_delete_user_nonce'])), 'mhvconf_erase_meethour_user_nonce')
    ) {
        return;
    }
    settings_errors('meethour_messages');
    $delete_meethour = isset($_POST['delete_meethour']) ? sanitize_text_field(wp_unslash($_POST['delete_meethour'])) : '';
    // checks the post request if delete_meethour is yes then it will execute the function
    if ($delete_meethour === 'yes') {
        $meetHourApiService = new MHApiService();
        $access_token = get_option('meethour_access_token', '');
        $meethour_user_id = get_user_meta($user_id, 'meethour_user_id', true);
        if (!empty($meethour_user_id)) {
            $body = new DeleteContact($meethour_user_id);
            $response = $meetHourApiService->deleteContact($access_token, $body);
            if ($response->success == false) {
                set_transient('meethour_error_message', $response->message, 30);
                return;
            }
        }
    }
}

// When user is created in wordpress this functions helps to create user in meethour portal and adds its contact id in backend with meethour_contact_id
add_action('user_register', 'mhvconf_register_user_in_app', 10, 2);
function mhvconf_register_user_in_app($user_id, $userdata)
{
    settings_errors('meethour_messages');
    $meetHourApiService = new MHApiService();
    $user = get_userdata($user_id);
    $token = get_option('meethour_access_token', '');
    $email = $userdata['user_email'];
    $first_name = $userdata['first_name'];
    $last_name = $userdata['last_name'];
    $username = $userdata['user_login'];
    $role = $userdata['role'];
    $body = new AddContact($email, $first_name, $last_name, $username);
    $response = $meetHourApiService->AddContact($token, $body);
    if ($response->success == false) {
        set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
        return;
    } else {
        $data = $response->data;
        $meta_value = $data->id;
        $meta_key = 'meethour_user_id';
        add_user_meta($user_id, $meta_key, $meta_value, true);
        return;
    }
}

add_action('profile_update', 'mhvconf_my_profile_update', 10, 2);


function mhvconf_my_profile_update($user_id, $old_user_data)
{
    $meetHourApiService = new MHApiService();
    $meethour_id = get_user_meta($user_id, 'meethour_user_id', true);
    $access_token = get_option("meethour_access_token", "");
    $user_data = get_userdata($user_id);
    if (!empty($meethour_id)) {
        $contact_id = $meethour_id;
        $first_name = get_user_meta($user_id, "first_name", true);
        $email = $user_data->user_email;
        $last_name = get_user_meta($user_id, "last_name", true);
        $body = new EditContact($contact_id, $first_name, $email);
        $body->lastname = $last_name;
        $response = $meetHourApiService->editContact($access_token, $body);
        if ($response->success == false) {
            set_transient('meethour_error_message', $response->message, 30); // store the error message for 30 seconds
        }
    }
    return;
}

// This will show a Error Message if there is any error in api response
function mhvconf_meethour_display_error_message_guests()
{
    $error_message = get_transient('meethour_error_message');
    if ($error_message) {
        ?>
        <div class="notice notice-error test">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
<?php
        delete_transient('meethour_error_message'); // delete the transient
    }
}
add_action('admin_notices', 'mhvconf_meethour_display_error_message_guests');


//This is the code for Custom Colum in Users Page 
if (!empty(get_option('meethour_access_token', ''))) {
    add_filter('manage_users_columns', 'mhvconf_wpexplorer_add_new_users_columns');
};
function mhvconf_wpexplorer_add_new_users_columns($columns)
{
    $new_columns = [
        'meethour_id' => esc_html__('Meethour ID', 'meet-hour-video-conference'),
    ];
    return array_merge($columns, $new_columns);
}

function mhvconf_wpexplorer_populate_custom_users_columns($output, $column_name, $user_id)
{
    if ('meethour_id' === $column_name) {
        $meethour_user_id_column = get_user_meta($user_id, 'meethour_user_id', true);
        if ($meethour_user_id_column) {
            $output = $meethour_user_id_column;
        } else {
            $output = '-';
        }
    }
    return $output;
}
add_filter('manage_users_custom_column',  'mhvconf_wpexplorer_populate_custom_users_columns', 10, 3);
