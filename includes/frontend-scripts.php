<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function mhvconf_meethour_admin_enqueue_scripts($hook_suffix)
{
    // Only load on post editing screens.
    if ('post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix && 'meet-hour_page_meethour-instant' !== $hook_suffix && 'toplevel_page_meethour-settings' !== $hook_suffix) {
        return;
    }

    // We register a style handle without an external file.
    wp_register_style('meethour-admin-style', false);
    wp_enqueue_style('meethour-admin-style');

    // Inline CSS previously in the meta box output
    $css_custom = "
        .points {
            font-size: medium;
        }

        .content {
            background: white;
            padding: 10px 20px;
            margin: 10px 0px;
            border-radius: 4px;
            width: 96%;
        }

        ul {
            padding: 0px 20px;
            list-style-type: square;
            font-size: small;
        }

        p {
            font-size: medium;
        }


        /*start styles*/
        .accordion {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .accordion__item {
            border: 1px solid #e5f3fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .accordion__header {
            padding: 20px 25px;
            font-weight: bold;
            cursor: pointer;
            position: relative;
            font-size: medium;
        }

        .accordion__header::after {
            content: '';
            width: 20px;
            height: 20px;
            transition: .4s;
            display: inline-block;
            position: absolute;
            right: 20px;
            top: 20px;
            z-index: 1;
        }

        .accordion__header.active {
            background: #e5f3fa;
        }

        .accordion__header.active::after {
            transform: rotateX(180deg);
        }

        .accordion__item .accordion__content {
            padding: 0 0px;
            max-height: 0;
            transition: .5s;
            overflow: hidden;
        }
        #wpfooter {
            display: none;
        }
        .dcard {
            width: 100%;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-top: 20px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            display: flex;
            flex-direction: row;
            gap: 10%;
        }
        @media (min-width:480px) {
            .dcard {
                width: 100%;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                margin-top: 20px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
                display: flex;
                flex-direction: row;
                gap: 10%;
            }
        }
        @media (min-width: 769px) {
            #submitdiv {
                position: fixed;    
            }
        }
        .submit{
            padding: 0;
            margin: 5px 0;
            border-bottom-left-radius: 3px;
            border-bottom-right-radius: 3px;
            border: none;
        }
        .form-table {
            margin: 0;
            padding: 0;
        }
        .form-table tr td,
        .form-table tr th {
            margin: 0;
            padding: 5px;
        }
        .scard {
            width: 95%;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-top: 20px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            text-align: center;
            justify-content: center;
            align-items: center;
        }
        .generate-h1 {
            display: flex;
            justify-content: space-between;
        }

        .select {
            width: 100%;
        }

        .access-tkn {
            word-wrap: break-word;
            padding-bottom: 20px;
        }

        .access-tkn-btn {
            margin-bottom: 10px;
        }
    ";
    wp_add_inline_style('meethour-admin-style', $css_custom);

    // Register a script handle (no external file)
    wp_register_script('meethour-admin-script', false, array('jquery'), '1.0.0', true);
    wp_enqueue_script('meethour-admin-script');

    // Inline JavaScript previously output directly inside the meta box.
    $js_custom = "
        // Update publish button label based on URL ?action=edit
        var url = window.location.href;
        const urlParams = new URLSearchParams(url);
        const action = urlParams.get('action');
        console.log(action);
        if (action == 'edit') {
        document.getElementById('publish').value = 'Update Meeting';
        } else {
        document.getElementById('publish').value = 'Schedule Meeting';
        }

        // Validate meeting title before publishing
        jQuery('#publish').on('click', function() {
        if (jQuery('#title').val().length === 0) {
        jQuery('#title').css('border', '2px solid #a00');
        alert('Meeting Name Required');
        return false;
        }
        });

        // Calendar Invite: Always set to true unless explicitly marked as NotInvited
        var calendar_invite = document.getElementById('calendar_invite');
        if (calendar_invite) { // Check if element exists
        var calendar_invite_checked = calendar_invite.getAttribute('data-invite-status');
        console.log('Calendar Invite Data Attribute: ' + calendar_invite_checked);
        calendar_invite_checked = (calendar_invite_checked || '').trim();
        if (calendar_invite_checked === 'invite') {
        calendar_invite.checked = true;
        } else if (calendar_invite_checked === 'NotInvited') {
        calendar_invite.checked = false;
        } else {
        calendar_invite.checked = true;
        }
        }

        // Create moderators checkboxes based on selected attendes
        function selectedAttendesandHosts() {
        const attendes = document.getElementById('attendes-response');
        const moderators = document.getElementById('Moderator');
        if (!attendes || !moderators) {
        return;
        }
        var attendesJSON = JSON.parse(attendes.innerText);
        console.log('Selected Attendes: ', attendesJSON);
        attendesJSON.forEach(function(attendee, index) {
        var checkboxId = 'moderator-checkbox-' + index;
        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'hosts[]';
        checkbox.value = attendee.contact_id;
        checkbox.id = checkboxId;
        checkbox.checked = (attendee.is_host === 1);
        var label = document.createElement('label');
        label.htmlFor = checkboxId;
        label.textContent = attendee.email;
        var lineBreak = document.createElement('br');
        moderators.appendChild(checkbox);
        moderators.appendChild(label);
        moderators.appendChild(lineBreak);
        });
        }
        selectedAttendesandHosts();

        // Refresh moderators when attendes selection changes
        function getSelectedAttendes(sel) {
        var dnd = document.getElementById('attendes');
        var mod_element = document.getElementById('Moderator');
        if (!dnd || !mod_element) {
        return;
        }
        mod_element.innerHTML = '';
        Array.from(dnd.selectedOptions).forEach(function(option, index) {
        var checkboxId = 'moderator-checkbox-' + index;
        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'hosts[]';
        checkbox.value = option.value;
        checkbox.id = checkboxId;
        var label = document.createElement('label');
        label.htmlFor = checkboxId;
        label.textContent = option.text;
        var lineBreak = document.createElement('br');
        mod_element.appendChild(checkbox);
        mod_element.appendChild(label);
        mod_element.appendChild(lineBreak);
        });
        }

        // Fetch API options and update checkboxes if applicable
        function fetchAndUpdateCheckboxes() {
        var APIoptions = document.getElementById('api-options');
        if (!APIoptions) {
        return;
        }
        var options = JSON.parse(APIoptions.innerText);
        var keys = Object.keys(options);
        if (keys[0] == 0) { keys = Object.values(options); }
        console.log('Enabled Options Array', keys);
        updateCheckboxes(keys);
        }
        function updateCheckboxes(enabledOptions) {
        var checkboxes = document.querySelectorAll('input[name=\"options[]\"]');
        checkboxes.forEach(function(checkbox) {
        checkbox.checked = enabledOptions.includes(checkbox.value);
        });
        }
        fetchAndUpdateCheckboxes();
    ";
    wp_add_inline_script('meethour-admin-script', $js_custom);
}
add_action('admin_enqueue_scripts', 'mhvconf_meethour_admin_enqueue_scripts');
