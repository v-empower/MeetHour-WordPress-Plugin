<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

function meethour_readme_page()
{
?>
    <div id="tabs">
        <h2 class="nav-tab-wrapper">
            <a target="_blank" href="#tab-1" class="nav-tab">Settings</a>
            <a target="_blank" href="#tab-2" class="nav-tab">Documentation</a>
        </h2>
        <div id="tab-1" class="tab-content">
            <?php
            meethour_token_page()
            ?>
        </div>
        <div id="tab-2" class="tab-content">
            <article class="svelte-17yrd8o">
                <div class="preview svelte-prhgxu">
                    <div class="tabs">
                        <div class="grid svelte-prhgxu">
                            <div class="content">
                                <h2 class="svelte-prhgxu">Description</h2>
                                <div class="section plugin-description svelte-xiu0ih">
                                    <p class="points">Discover the power of video conferencing with Meet Hour. Learn what video conferencing is, explore its diverse applications across industries, and find out why Meet Hour stands out as your preferred choice. Explore key features, reliability, and seamless integration options for your technology stacks. Join the future of remote collaboration with Meet Hour, For More Details Visit <a target="_blank" href="https://meethour.io">Meet Hour</a></p>

                                    <!-- Markdown Code -->
                                    <hr>
                                    <h2 id="getting-started">Pre-Requistics </h2>
                                    <ul>
                                        <li>
                                            <p class="points">Must subscribed to Meet Hour Developer Plan</p>
                                        </li>
                                        <li>
                                            <p class="points"> Must be ready with <a target="_blank" href="http://repo.meethour.io/landing/wordpress-plugin/meethour-video-conference.zip">Meet Hour-Video-Conference.zip</a> or able to install from WordPress Plugin directory.</p>
                                        </li>
                                    </ul>
                                    <hr>
                                    <h2 id="getting-started">Getting Started</h2>
                                    <h3 id="1-activate-the-plugin">1. Activate the Plugin</h3>
                                    <p class="points">After activating the plugin, head over to the <a target="_blank" href="https://portal.meethour.io/customer/developers">Meet Hour Developer Portal</a>. You&#39;ll need a developer account to access this page.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Find Your Credentials</strong>: In Meet Hour : Login Meet Hour and go to <strong>Developer</strong> Page, locate your <strong>Client ID</strong>, <strong>Client Secret</strong>, and <strong>API Key</strong>.</p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Meet Hour Wordpress Configure Plugin Settings</strong>: Go back to your WordPress dashboard and navigate to the plugin settings page of Meet Hour.
                                            <ul>
                                                <li>
                                                    <p class="points">Insert the <strong>Client ID</strong>, <strong>Client Secret</strong>, and <strong>API Key</strong>.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Click on <strong>Generate Access Token</strong>.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Unlock Features</strong>: Once you&#39;ve generated the access token, all the plugin&#39;s features will be available to you.</p>
                                        </li>
                                        <img src="<?php echo esc_url(plugins_url('../assets/screenshot-15.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Settings Screenshot">
                                    </ul>
                                    <hr>
                                    <h2 id="user-management">User Management</h2>
                                    <h3 id="2-sync-and-manage-users">2. Sync and Manage Users</h3>
                                    <p class="points">Navigate to the <strong>Users</strong> section in WordPress. With the plugin activated, you&#39;ll notice new options like <strong>Fetch Meet Hour Users</strong>.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Fetch Meet Hour Users</strong>: Click this to import all your Meet Hour users into your WordPress database.</p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Two-Way Synchronization</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Actions you perform on users with the <strong>Meet Hour</strong> role in WordPress can also affect those users in the Meet Hour portal.</p>
                                                </li>
                                                <li>
                                                    <p class="points">You&#39;ll have the option to decide whether changes in WordPress should reflect on the Meet Hour portal.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Creating Users</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">When you create a new user in WordPress, assign them the <strong>Meet Hour</strong> role.</p>
                                                </li>
                                                <li>
                                                    <p class="points">This will automatically create a corresponding user in the Meet Hour portal.</p>
                                                </li>
                                                <li>
                                                    <p class="points"><em>Note</em>: Synchronization won&#39;t work if the user isn&#39;t assigned the <strong>Meet Hour</strong> role.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Deleting Users</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">When deleting a user with the <strong>Meet Hour</strong> role, you&#39;ll be prompted to choose whether to remove them from the Meet Hour portal as well.</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-3.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Delete User Page Screenshot">
                                        </li>
                                    </ul>
                                    <hr>
                                    <h2 id="meetings">Meetings</h2>
                                    <h3 id="3-instant-meeting">3. Quick Meeting</h3>
                                    <p class="points">Need a quick meeting without the fuss? The <strong>Quick Meeting</strong> feature is your go-to.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Creating a Quick Meeting</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Provide a <strong>Meeting Name</strong> and <strong>Passcode</strong>.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Click <strong>Create</strong>, and you&#39;re all set!</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-10.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Quick Meeting Page Screenshot">
                                        </li>
                                        <li>
                                            <p class="points"><strong>Shortcode</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">After creating the meeting, click <strong>Copy Shortcode</strong>.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Paste this shortcode into any post or page to embed the meeting directly on your site.</p>
                                                </li>
                                            </ul>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-16.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Quick Meeting Page Screenshot">
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Link</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Copy the meeting link to share with participants.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Paste it into your browser to join the meeting instantly.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                    </ul>
                                    <h3 id="4-schedule-meeting">4. Schedule Meeting</h3>
                                    <p class="points">For more detailed setups, use the <strong>Schedule Meeting</strong> option.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Setting Up a Meeting</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Fill in the meeting details—date, time, agenda, and any other preferences.</p>
                                                </li>
                                                <li>
                                                    <p class="points">You&#39;ll have more customization options here compared to a Quick meeting.</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-14.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Schedule Meeting Screenshot">
                                        </li>
                                        <li>
                                            <p class="points"><strong>Publishing the Meeting</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Click <strong>Publish</strong> to create the meeting.</p>
                                                </li>
                                                <li>
                                                    <p class="points">You&#39;ll receive a <strong>Permalink</strong> to the meeting post.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Editing the Meeting</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">After publishing, you&#39;ll be redirected to the <strong>Edit Meeting</strong> page.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Any updates you make here will sync with the Meet Hour portal.</p>
                                                </li>
                                                <li>
                                                    <p class="points">The meeting link remains the same even after updates.</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-4.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Edit Meeting Page Screenshot">
                                        </li>
                                    </ul>
                                    <h3 id="5-manage-meetings">5. Manage Meetings</h3>
                                    <p class="points">In the <strong>Meetings</strong> section, you can view all meetings created in WordPress and fetch meetings from the Meet Hour portal.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Fetching Meetings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Click on <strong>Fetch Meet Hour Meetings</strong>.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Each click fetches 20 meetings from the portal.</p>
                                                </li>
                                                <li>
                                                    <p class="points">If you have more meetings (e.g., 100), click the button multiple times until all meetings are imported.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Meeting Details</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">View <strong>Meeting ID</strong>, <strong>Duration</strong>, <strong>Agenda</strong>, <strong>Meeting Link</strong>, and <strong>External Meeting Link</strong>.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Joining Meetings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points"><strong>Meeting Link</strong>: Opens the meeting within your WordPress site. Invited users will be automatically signed in.</p>
                                                </li>
                                                <li>
                                                    <p class="points"><strong>External Link</strong>: Opens the meeting in a new browser tab.</p>
                                                </li>
                                                <li>
                                                    <!-- <p style="background-color: indianred; padding: 2px 5px; width: fit-content; color: white;" class="points">If View Meeting/Join Meeting is Showing Page Not Found. <strong> Schedule a New Meeting and the Error will be Fixed</strong></p> -->
                                                    <p class="points">If View Meeting/Join Meeting is Showing Page Not Found. <strong> Schedule a New Meeting and the Error will be Fixed</strong></p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Shortcodes</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Copy the shortcode to embed the meeting in any post or page.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Managing Meetings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points"><strong>Edit</strong>, <strong>Move to Trash</strong>, or <strong>Delete</strong> meetings.</p>
                                                </li>
                                                <li>
                                                    <p class="points">When deleting, you&#39;ll be asked if you want to remove the meeting from the Meet Hour portal as well.
                                                    <ul>
                                                        <li>
                                                            <p class="points"><strong>Agree</strong>: The meeting is archived and deleted from both WordPress and Meet Hour.</p>
                                                        </li>
                                                        <li>
                                                            <p class="points"><strong>Disagree</strong>: The meeting is removed from WordPress but remains on Meet Hour.</p>
                                                        </li>
                                                    </ul>
                                                    </p>
                                                    <img src="<?php echo esc_url(plugins_url('../assets/screenshot-2.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Delete Confirm Box Screenshot">
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Sync Upcoming Meetings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">The <strong>Sync Upcoming Meetings from Meet Hour</strong> button fetches only upcoming meetings.</p>
                                                </li>
                                                <li>
                                                    <p class="points"><em>Important</em>: Only future meetings are synced with this option.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                    </ul>
                                    <hr>
                                    <h2 id="recordings">Recordings</h2>
                                    <h3 id="6-manage-recordings">6. Manage Recordings</h3>
                                    <p class="points">Access all your recordings in the <strong>Recordings</strong> section, and fetch new ones from the Meet Hour portal.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>Fetching Recordings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Click <strong>Fetch Meet Hour Recordings</strong>.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Each click imports 20 recordings.</p>
                                                </li>
                                                <li>
                                                    <p class="points">For more recordings, click multiple times as needed.</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-11.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Recordings Page Screenshot">
                                        </li>
                                        <li>
                                            <p class="points"><strong>Recording Details</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">View <strong>Recording Name</strong>, <strong>Duration</strong>, <strong>Size</strong>, <strong>Recording Link</strong>, and <strong>Recording Date</strong>.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Viewing Recordings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points"><strong>Recording Link</strong>: Opens the recording within your WordPress site.</p>
                                                </li>
                                                <li>
                                                    <p class="points">You can integrate recordings with other plugins or embed them in posts.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Shortcodes</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Copy the shortcode to embed the recording in any post or page.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>Deleting Recordings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Choose whether to delete recordings from the Meet Hour portal when removing them from WordPress.</p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-2.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Delete Confirm Box Screenshot">
                                        </li>
                                        <li>
                                            <p class="points"><strong>Refresh Shortcodes</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">If a shortcode expires, use the <strong>Refresh Shortcode</strong> option to update it automatically.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                    </ul>
                                    <hr>
                                    <h2 id="shortcodes">Shortcodes</h2>
                                    <h3 id="7-using-shortcodes">7. Using Shortcodes</h3>
                                    <p class="points">Leverage shortcodes to integrate Meet Hour functionalities throughout your WordPress site.</p>
                                    <ul>
                                        <li>
                                            <p class="points"><strong>For Meetings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points"><code>[meethour meeting_id=&quot;your_meeting_id&quot;]</code></p>
                                                </li>
                                            </ul>
                                            </p>
                                            <img src="<?php echo esc_url(plugins_url('../assets/screenshot-16.png', __FILE__)); ?>" width="80%" alt="Meet Hour Wordpress Shortcode Adding Screenshot">
                                        </li>
                                        <li>
                                            <p class="points"><strong>For Recordings</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points"><code>[meethour recording_id=&quot;your_recording_id&quot;]</code></p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                        <li>
                                            <p class="points"><strong>How to Use</strong>:
                                            <ul>
                                                <li>
                                                    <p class="points">Copy the relevant shortcode.</p>
                                                </li>
                                                <li>
                                                    <p class="points">Paste it into any post or page where you want the meeting or recording to appear.</p>
                                                </li>
                                            </ul>
                                            </p>
                                        </li>
                                    </ul>
                                    <hr>

                                    <h3>Features</h3>
                                    <ul>
                                        <li>
                                            <p class="points">Unlimited Meeting Duration in Free Plan<br>
                                                Enjoy endless meetings with no time restrictions in Meet Hour’s free plan, ensuring uninterrupted collaboration.</p>
                                        </li>
                                        <li>
                                            <p class="points">Schedule a Meeting<br>
                                                Meeting organizer can invite participants in the meeting through email or by sharing the link with the participants via WhatsApp, Slack or Teams as well.</p>
                                        </li>
                                        <li>
                                            <p class="points">Sync Meetings to Calendar<br>
                                                When the user schedules a meeting, it’s gets automatically attach to the Calendar of user account helping to organize &amp; manage meetings in one place.</p>
                                        </li>
                                        <li>
                                            <p class="points">Meeting Prefix<br>
                                                The Meeting Prefix refers to a set of unique characters at the beginning of a meeting ID to uniquely promote your organizational initials.</p>
                                        </li>
                                        <li>
                                            <p class="points">Branded Conference<br>
                                                Branded Conference is a unique feature of Meet Hour where a company or individual can have a branded conference of his own. You can attach your domain or use sub domain of Meet Hour for the conference call.</p>
                                        </li>
                                        <li>
                                            <p class="points">Recordings<br>
                                                You can access all your recordings from the dashboard, you can play, download and share.</p>
                                        </li>
                                        <li>
                                            <p class="points">Live Streaming<br>
                                                You can do live streaming from Meet Hour on a platform or multiple platforms. We provide parallel live streaming for up to 3 platforms. You can live stream on YouTube, Facebook, Instagram, LinkedIn, Twitch, Custom RTMP &amp; RTMPS and many more…</p>
                                        </li>
                                        <li>
                                            <p class="points">Whiteboard<br>
                                                A whiteboard is a feature for the teams to collaborate and it is a learning space where both teacher and student can write and interact with.</p>
                                        </li>
                                        <li>
                                            <p class="points">Screen Sharing<br>
                                                Screen sharing is a technology that allows one user to share their computer screen with others in real time.</p>
                                        </li>
                                        <li>
                                            <p class="points">Join from Any Device<br>
                                                In Meet Hour the users can join from anywhere web browser, desktop app, mobile app android and ios</p>
                                        </li>
                                        <li>
                                            <p class="points">Lobby Mode<br>
                                                Enabling Lobby mode makes the meeting more secure. It gives moderator the right to allow or reject the participants.</p>
                                        </li>
                                        <li>
                                            <p class="points">End-to-End Encrypted<br>
                                                All the meetings in the Meet Hour are end-to-end encrypted. It provides a high level of security and privacy</p>
                                        </li>
                                        <li>
                                            <p class="points">Chat with Participants<br>
                                                Meet Hour also has built-in chat provision where the participants can chat with each other, also the participants can send private messages to other participants</p>
                                        </li>
                                        <li>
                                            <p class="points">Virtual Background<br>
                                                The virtual background feature in Meet Hour allows users to replace their actual background with a digitally generated image.</p>
                                        </li>
                                        <li>
                                            <p class="points">Live pad<br>
                                                Live pad is a powerful collaborative editing tool that enables real-time document editing in Meet Hour. It facilitates seamless collaboration on the document remotely over the video conference call.</p>
                                        </li>
                                        <li>
                                            <p class="points">Multiple Donate Option<br>
                                                Meet Hour has integrated Donor box, Click &amp; Pledge as the donation’s options within the conference. Fundraise via video call they can do it with the help of Meet Hour.</p>
                                        </li>
                                        <li>
                                            <p class="points">Share YouTube Video<br>
                                                Meet Hour allows the users to share a YouTube video without sharing their screen</p>
                                        </li>
                                        <li>
                                            <p class="points">Embed Meeting<br>
                                                Embed the meetings with just a line of code. This is available in the Developer plan and Enterprise plan</p>
                                        </li>
                                        <li>
                                            <p class="points">Contacts<br>
                                                Access detailed information about any contact you add in the Meet Hour platform</p>
                                        </li>
                                        <li>
                                            <p class="points">Meeting Analytics<br>
                                                Analyze your meeting data with our powerful reports built into the dashboard. Get detailed insights of meetings scheduled by you Get the data metrics like</p>
                                        </li>
                                        <li>
                                            <p class="points">Webinar Mode<br>
                                                Webinar Mode offers experience for hosting large-scale events with audience participation. With built-in registration system attendees can sign up before the webinar, ensuring an organized attendee list.</p>
                                        </li>
                                        <li>
                                            <p class="points">Voice Command<br>
                                                Use Meet Hour best in class voice command to perform specific actions within the meeting.</p>
                                        </li>
                                        <li>
                                            <p class="points">Manage Video Quality<br>
                                                Manually manage the video quality from low definition to high definition.</p>
                                        </li>
                                        <li>
                                            <p class="points">Speaker Stats<br>
                                                See the stats of the participants who have spoken for most of the time in the meeting. Check the stat live during the meeting.</p>
                                        </li>
                                        <li>
                                            <p class="points">Keyboard Shortcuts<br>
                                                Users can perform specific meeting actions via shortcuts of the meeting.</p>
                                        </li>
                                        <li>
                                            <p class="points">Raise Hand<br>
                                                A user can raise hand during the meeting if he/she wants to ask/say something while other participants are having a conversation.</p>
                                        </li>
                                        <li>
                                            <p class="points">Picture In Picture Mode (Pip)<br>
                                                Meet Hour allows picture in picture mode when users are sharing the screen.</p>
                                        </li>
                                    </ul>
                                    <hr>

                                    <h3>Use Cases</h3>
                                    <ul>
                                        <li>
                                            <p class="points">Video conferencing<br>
                                                Discover the power of video conferencing and learn what video conferencing is, explore its diverse applications across industries.</p>
                                        </li>
                                        <li>
                                            <p class="points">Live Streaming<br>
                                                Meet Hour allows you to broadcast your conferences directly to popular channels like YouTube, LinkedIn, Instagram, and Facebook, all at once.</p>
                                        </li>
                                        <li>
                                            <p class="points">Virtual Classrooms<br>
                                                Unlock a new dimension of education with Meet Hour. Whether you’re a school, university, corporate training center, or any organization.</p>
                                        </li>
                                        <li>
                                            <p class="points">Virtual Events<br>
                                                In today’s digital age, virtual events have become an integral part of businesses, educational institutions, and organizations.</p>
                                        </li>
                                        <li>
                                            <p class="points">Video KYC<br>
                                                Embrace the Future of Identity Verification with Video e-KYC. In a world driven by digital transformation, the need for seamless and secure verification.</p>
                                        </li>
                                        <li>
                                            <p class="points">Webinars<br>
                                                Meet Hour Webinars offer a simple, secure, and reliable platform for your virtual gatherings. Let’s dive into the key features that make Meet Hour Webinars.</p>
                                        </li>
                                        <li>
                                            <p class="points">Fundraising<br>
                                                At Meet Hour, we understand the importance of fundraising in today’s dynamic world. That’s why we’ve introduced seamless integration with – Donor box and Click pledge.</p>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
        </div>
        </article>
    </div>

    </div>


<?php
    wp_register_script(
        'mhvconf-readme-date-toggle',
        false,
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf-readme-date-toggle',);
    $inline_script = "
        document.addEventListener('DOMContentLoaded', () => {
            const togglers = document.querySelectorAll('[data-toggle]');

            togglers.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const selector = e.currentTarget.dataset.toggle
                    const block = document.querySelector(selector);
                    if (e.currentTarget.classList.contains('active')) {
                        block.style.maxHeight = '';
                    } else {
                        block.style.maxHeight = block.scrollHeight + 'px';
                    }

                    e.currentTarget.classList.toggle('active')
                })
            })
        })
        ";
    wp_add_inline_script('mhvconf-readme-date-toggle', $inline_script, 'after');


    wp_register_script(
        'mhvconf-readme-tab-content',
        false,
        array('jquery'),
        '1.0.0',
        true
    );
    wp_enqueue_script('mhvconf-readme-tab-content',);
    $inline_tab_script = "
            jQuery(function($) {
            $('#tabs .tab-content').hide();
            $('#tabs .tab-content:first').show();
            $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
            $('.nav-tab-wrapper').on('click', '.nav-tab', function(e) {
                e.preventDefault();
                $('.nav-tab-wrapper a:first').removeClass('nav-tab-active');
                $('.tab-content').hide();
                $(this).addClass('nav-tab-active');
                $($(this).attr('href')).show();
            });
        })
    ";
    wp_add_inline_script('mhvconf-readme-tab-content', $inline_tab_script, 'after');
}
