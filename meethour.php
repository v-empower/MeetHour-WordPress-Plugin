<?php
/*
 * Plugin Name: Meet Hour Video Conference
 * Description: Discover the power of video conferencing with Meet Hour. Learn what video conferencing is, explore its diverse applications across industries, and find out why Meet Hour stands out as your preferred choice. Explore key features, reliability, and seamless integration options for your technology stacks. Join the future of remote collaboration with Meet Hour.
 * Plugin URI: https://meethour.io
 * Version: 1.0
 * Author: Meet Hour LLC 
 * Author URI: https://meethour.io/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (! defined('ABSPATH')) exit; // Exit if accessed directly


define('MEETHOUR_PLUGIN_FILE',  plugin_dir_path(__FILE__));

include("includes/frontend-scripts.php");
include("includes/meethour-recording.php");
include("includes/meethour-meeting.php");
include("includes/meethour-token.php");
include("includes/meethour-guests.php");
include("includes/meethour-scheduler.php");
include("includes/readme-meethour-page.php");
include("includes/meethour-instant.php");


// Create the admin menu items
function mhvconf_meethour_admin_menu()
{
    // Add main menu item
    add_menu_page(
        'Meet Hour',                  // Page title
        'Meet Hour',                  // Menu title
        'manage_options',            // Required capability
        'meethour-settings',         // Menu slug
        'meethour_readme_page',       // Function to display the page
        'dashicons-video-alt2',       // Icon
        2
    );
    $access_token = get_option('meethour_access_token', '');
    if (!empty($access_token)) {
        add_submenu_page(
            'meethour-settings',         // Parent slug
            'Quick Meeting',           // Page title
            'Quick Meeting',           // Menu title
            'manage_options',            // Required capability
            'meethour-instant',          // Menu slug
            'mhvconf_meethour_instant_page'      // Function to display the page
        );
        add_submenu_page(
            'meethour-settings', // Parent slug
            'Schedule Meeting', // Page title
            'Schedule Meeting', // Menu title
            'manage_options', // Capability
            'post-new.php?post_type=mhvconf_meetings' // Menu slug
        );
        add_submenu_page(
            'meethour-settings', // Parent slug
            'Meetings', // Page title
            'Meetings', // Menu title
            'manage_options', // Capability
            'edit.php?post_type=mhvconf_meetings' // Menu slug
        );
        add_submenu_page(
            'meethour-settings', // Parent slug
            'Recordings', // Page title
            'Recordings', // Menu title
            'manage_options', // Capability
            'edit.php?post_type=mhvconf_recordings' // Menu slug
        );
    }
}
// adds the menus and sub menus
add_action('admin_menu', 'mhvconf_meethour_admin_menu');

// adding Meet Hour Role to the users of wordpress
add_role('meethour', 'Meet Hour', array(
    'read' => true,
    'create_posts' => true,
    'edit_posts' => true,
    'edit_others_posts' => true,
    'publish_posts' => true,
    'manage_categories' => true,
));

// removing the trash, edit, view from the mhvconf_recordings page
add_filter('post_row_actions', 'mhvconf_clear_row_actions', 10, 1);
function mhvconf_clear_row_actions($actions)
{
    if (get_post_type() === 'mhvconf_recordings') {
        unset($actions['edit']);
        // unset($actions['view']);
        // unset($actions['trash']);
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}

// here we are creating shortcodes for meetings and recordings 
add_shortcode('meethour', 'mhvconf_meethour_shortcode');

function mhvconf_meethour_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'recording_id' => '',
        'meeting_id'   => null,
        'style'        => ''
    ), $atts, 'meethour');

    $meeting_id   = $atts['meeting_id'];
    $recording_id = $atts['recording_id'];
    $mstyle        = $atts['mstyle'];
    $rstyle        = $atts['rstyle'];

    // Default style if no custom style is provided
    $default_mstyle = 'width: 100vh; display: flex; height: 100vh; border: 0px; justify-content: center; align-items: center;';
    $default_rstyle = 'width:100%';
    $final_mstyle = empty($mstyle) ? $default_mstyle : $mstyle;
    $final_rstyle = empty($rstyle) ? $default_rstyle : $rstyle;

    if ($meeting_id) {
        return '<iframe allow="camera; microphone; display-capture; autoplay; clipboard-write" src="https://meethour.io/' . esc_attr($meeting_id) . '#interfaceConfig.applyMeetingSettings=true&interfaceConfig.disablePrejoinHeader=true&interfaceConfig.ENABLE_DESKTOP_DEEPLINK=false&interfaceConfig.disablePrejoinFooter=true&interfaceConfig.SHOW_MEET_HOUR_WATERMARK=false&interfaceConfig.HIDE_DEEP_LINKING_LOGO=true&interfaceConfig.MOBILE_APP_PROMO=false&interfaceConfig.ENABLE_MOBILE_BROWSER=true&appData.localStorageContent=null" name="mhConferenceFrame0" id="mhConferenceFrame0" allowfullscreen="true" style="' . esc_attr($final_mstyle) . '"></iframe>';
    }

    if ($recording_id) {
        $video_url = get_post_meta($recording_id, 'recording_path', true);
        return '<video controls style="' . esc_attr($final_rstyle) . '"><source src="' . esc_url($video_url) . '" type="video/mp4">Your browser does not support the video tag.</video>';
    }
}



register_uninstall_hook(__FILE__, 'mhvconf_meethour_deactivate');

// Function to delete custom post types and their data on uninstall
function mhvconf_meethour_deactivate()
{
    global $wpdb;

    // Delete all posts of custom post type 'mhvconf_meetings'
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'mhvconf_meetings'");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");

    // Delete all posts of custom post type 'mhvconf_recordings'
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'mhvconf_recordings'");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");

    $args = array(
        'role'    => 'meethour',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $users = get_users($args);
    foreach ($users as $user) {
        wp_update_user(array(
            'ID' => $user->ID,
            'role' => 'subscriber',
        ));
    }

    $meethourusers = get_users(array(
        'meta_key' => 'meethour_user_id'
    ));
    foreach ($meethourusers as $meethouruser) {
        delete_user_meta(
            $meethouruser->ID,
            'meethour_user_id'
        );
    };
    remove_role('meethour');
    delete_option('meethour_access_token');
    delete_option('meethour_client_id');
    delete_option('meethour_client_secret');
    delete_option('meethour_username');
    delete_option('meethour_password');
    delete_option('meethour_api_key');
    delete_option('mhvconf_meetings_total_pages');
    delete_option('mhvconf_meetings_current_page');
    delete_option('mhvconf_recordings_total_pages');
    delete_option('mhvconf_recordings_current_page');
    delete_option('meethour_main_user');
    delete_option('mhvconf_recordings_post_limit');
    delete_option('mhvconf_meetings_post_limit');
}
add_action('wp_ajax_mhvconf_meethour_deactivate', 'mhvconf_meethour_deactivate'); // For logged-in users 

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mhvconf_action_links');

// this functions add the settings, option in my plugin page settings | deactivate
function mhvconf_action_links($links)
{
    $links[] = '<a href="' . esc_url(get_admin_url(null, '?page=meethour-settings')) . '">Settings</a>';
    return $links;
}

// Activation Hook
function mhvconf_meethour_activate_plugin()
{
    // Register custom post type
    meethour_register_meeting_post_type();

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mhvconf_meethour_activate_plugin');

// Deactivation Hook
function mhvconf_meethour_deactivate_plugin()
{
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mhvconf_meethour_deactivate_plugin');


function mhvconf_meethour_format_utc_time($timestamp, $format = 'Y-m-d h:i:s a')
{
    $timezone_string = get_option('meethour_timezone_value');
    if (empty($timezone_string)) {
        $timezone_string = 'Asia/Kolkata';
    }
    try {
        $user_tz = new DateTimeZone($timezone_string);
    } catch (Exception $e) {
        $user_tz = new DateTimeZone('Asia/Kolkata');
    }
    $timestamp_int = intval($timestamp);
    $date = new DateTime('', new DateTimeZone('UTC'));
    $date->setTimestamp($timestamp_int);

    $date->setTimezone($user_tz);
    return $date->format($format);
}
