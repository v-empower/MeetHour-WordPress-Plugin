
Description
-----------

Discover the power of video conferencing with Meet Hour. Learn what video conferencing is, explore its diverse applications across industries, and find out why Meet Hour stands out as your preferred choice. Explore key features, reliability, and seamless integration options for your technology stacks. Join the future of remote collaboration with Meet Hour.

Pre-Requistics:
---------------

1. Must have Meet Hour Developer Plan
2. Must be ready with [MeetHour-Video-Conference.zip](http://repo.meethour.io/landing/wordpress-plugin/meethour-video-conference.zip) or able to install from WordPress Plugin directory.


Getting Started
---------------

### 1\. Activate the Plugin

After activating the plugin, head over to the [Meethour Developer Portal](https://portal.meethour.io/customer/developers). You'll need a developer account to access this page.

*   **Find Your Credentials in Meet Hour**: In the **Developer** section, locate your **Client ID**, **Client Secret**, and **API Key**.
    
*   **Configure Plugin Settings**: Go back to your WordPress dashboard and navigate to the Meet Hour plugin settings page.
    
    *   Insert the **Client ID**, **Client Secret**, and **API Key**.
        
    *   Click on **Generate Access Token**.
        
    
*   **Unlock Features**: Once you've generated the access token, all the plugin's features will be available to you.
    
![image](/assets/meethour_settings_screenshot.png)


* * *

User Management
---------------

### 2\. Sync and Manage Users

Navigate to the **Users** section in WordPress. With the plugin activated, you'll notice new options like **Fetch Meethour Users**.

*   **Fetch Meethour Users**: Click this to import all your Meethour users into your WordPress database.
    
*   **Two-Way Synchronization**:
    
    *   Actions you perform on users with the **Meethour** role in WordPress can also affect those users in the Meethour portal.
        
    *   You'll have the option to decide whether changes in WordPress should reflect on the Meethour portal.
        
    ![image](/assets/meethour_users_page.png)    
    
*   **Creating Users**:
    
    *   When you create a new user in WordPress, assign them the **Meethour** role.
        
    *   This will automatically create a corresponding user in the Meethour portal.
        
    *   _Note_: Synchronization won't work if the user isn't assigned the **Meethour** role.
        
    
*   **Deleting Users**:
    
    *   When deleting a user with the **Meethour** role, you'll be prompted to choose whether to remove them from the Meethour portal as well.
        
     ![image](/assets/meethour_delete_user_page.png)

* * *

Meetings
--------

### 3\. Quick Meeting

Need a quick meeting without the fuss? The **Quick Meeting** feature is your go-to.

*   **Creating an Quick Meeting**:
    
    *   Provide a **Meeting Name** and **Passcode**.
        
    *   Click **Create**, and you're all set!

     ![image](/assets/meethour_instant_meeting_screenshot.png)
        
    
*   **Shortcode**:
    
    *   After creating the meeting, click **Copy Shortcode**.
        
    *   Paste this shortcode into any post or page to embed the meeting directly on your site.
        
    
*   **Link**:
    
    *   Copy the meeting link to share with participants.
        
    *   Paste it into your browser to join the meeting instantly.
        
    

### 4\. Schedule Meeting

For more detailed setups, use the **Schedule Meeting** option.

*   **Setting Up a Meeting**:
    
    *   Fill in the meeting details—date, time, agenda, and any other preferences.
        
    *   You'll have more customization options here compared to an Quick meeting.
        
     ![image](/assets/meethour_schedule_meeting_screenshot.png)
    
*   **Publishing the Meeting**:
    
    *   Click **Publish** to create the meeting.
        
    *   You'll receive a **Permalink** to the meeting post.
        
    
*   **Editing the Meeting**:
    
    *   After publishing, you'll be redirected to the **Edit Meeting** page.
        
    *   Any updates you make here will sync with the Meethour portal.
        
    *   The meeting link remains the same even after updates.
        
     ![image](/assets/meethour_edit_meeting_page.png)
    

### 5\. Manage Meetings

In the **Meetings** section, you can view all meetings created in WordPress and fetch meetings from the Meethour portal.

*   **Fetching Meetings**:
    
    *   Click on **Fetch Meethour Meetings**.
        
    *   Each click fetches 20 meetings from the portal.
        
    *   If you have more meetings (e.g., 100), click the button multiple times until all meetings are imported.
        
    
*   **Meeting Details**:
    
    *   View **Meeting ID**, **Duration**, **Agenda**, **Meeting Link**, and **External Meeting Link**.
        
     ![image](/assets/meethour_upcomming_meetings_screenshot.png)
    
*   **Joining Meetings**:
    
    *   **Meeting Link**: Opens the meeting within your WordPress site. Invited users will be automatically signed in.
        
     ![image](/assets/meethour_join_meeting_page.png) 

    *   **External Link**: Opens the meeting in a new browser tab.
        
    
*   **Shortcodes**:
    
    *   Copy the shortcode to embed the meeting in any post or page.
        
    
*   **Managing Meetings**:
    
    *   **Edit**, **Move to Trash**, or **Delete** meetings.
        
    *   When deleting, you'll be asked if you want to remove the meeting from the Meethour portal as well.
        
        *   **Agree**: The meeting is archived and deleted from both WordPress and Meethour.
            
        *   **Disagree**: The meeting is removed from WordPress but remains on Meethour.
            
        
    
*   **Sync Upcoming Meetings**:
    
    *   The **Sync Upcoming Meetings from Meethour** button fetches only upcoming meetings.
        
    *   _Important_: Only future meetings are synced with this option.
        
    

* * *

Recordings
----------

### 6\. Manage Recordings

Access all your recordings in the **Recordings** section, and fetch new ones from the Meethour portal.

*   **Fetching Recordings**:
    
    *   Click **Fetch Meethour Recordings**.
        
    *   Each click imports 20 recordings.
        
    *   For more recordings, click multiple times as needed.
        
    
*   **Recording Details**:
    
    *   View **Recording Name**, **Duration**, **Size**, **Recording Link**, and **Recording Date**.
        
    
*   **Viewing Recordings**:
    
    *   **Recording Link**: Opens the recording within your WordPress site.
        
    *   You can integrate recordings with other plugins or embed them in posts.
        
    
*   **Shortcodes**:
    
    *   Copy the shortcode to embed the recording in any post or page.
        
    
*   **Deleting Recordings**:
    
    *   Choose whether to delete recordings from the Meethour portal when removing them from WordPress.
        
    
*   **Refresh Shortcodes**:
    
    *   If a shortcode expires, use the **Refresh Shortcode** option to update it automatically.
        
    

* * *

Shortcodes
----------

### 7\. Using Shortcodes

Leverage shortcodes to integrate Meethour functionalities throughout your WordPress site.

*   **For Meetings**:
    
    *   `[meethour meeting_id="your_meeting_id"]`
        
    
*   **For Recordings**:
    
    *   `[meethour recording_id="your_recording_id"]`
        
    
*   **How to Use**:
    
    *   Copy the relevant shortcode.
        
    *   Paste it into any post or page where you want the meeting or recording to appear.
        
    

* * *

### Features

*   Unlimited Meeting Duration in Free Plan  
    Enjoy endless meetings with no time restrictions in Meet Hour’s free plan, ensuring uninterrupted collaboration.
    
*   Schedule a Meeting  
    Meeting organizer can invite participants in the meeting through email or by sharing the link with the participants via WhatsApp, Slack or Teams as well.
    
*   Sync Meetings to Calendar  
    When the user schedules a meeting, it’s gets automatically attach to the Calendar of user account helping to organize & manage meetings in one place.
    
*   Meeting Prefix  
    The Meeting Prefix refers to a set of unique characters at the beginning of a meeting ID to uniquely promote your organizational initials.
    
*   Branded Conference  
    Branded Conference is a unique feature of Meet Hour where a company or individual can have a branded conference of his own. You can attach your domain or use sub domain of Meet Hour for the conference call.
    
*   Recordings  
    You can access all your recordings from the dashboard, you can play, download and share.
    
*   Live Streaming  
    You can do live streaming from Meet Hour on a platform or multiple platforms. We provide parallel live streaming for up to 3 platforms. You can live stream on YouTube, Facebook, Instagram, LinkedIn, Twitch, Custom RTMP & RTMPS and many more…
    
*   Whiteboard  
    A whiteboard is a feature for the teams to collaborate and it is a learning space where both teacher and student can write and interact with.
    
*   Screen Sharing  
    Screen sharing is a technology that allows one user to share their computer screen with others in real time.
    
*   Join from Any Device  
    In Meet Hour the users can join from anywhere web browser, desktop app, mobile app android and ios
    
*   Lobby Mode  
    Enabling Lobby mode makes the meeting more secure. It gives moderator the right to allow or reject the participants.
    
*   End-to-End Encrypted  
    All the meetings in the Meet Hour are end-to-end encrypted. It provides a high level of security and privacy
    
*   Chat with Participants  
    Meet Hour also has built-in chat provision where the participants can chat with each other, also the participants can send private messages to other participants
    
*   Virtual Background  
    The virtual background feature in Meet Hour allows users to replace their actual background with a digitally generated image.
    
*   Live pad  
    Live pad is a powerful collaborative editing tool that enables real-time document editing in Meet Hour. It facilitates seamless collaboration on the document remotely over the video conference call.
    
*   Multiple Donate Option  
    Meet Hour has integrated Donor box, Click & Pledge as the donation’s options within the conference. Fundraise via video call they can do it with the help of Meet Hour.
    
*   Share YouTube Video  
    Meet Hour allows the users to share a YouTube video without sharing their screen
    
*   Embed Meeting  
    Embed the meetings with just a line of code. This is available in the Developer plan and Enterprise plan
    
*   Contacts  
    Access detailed information about any contact you add in the Meet Hour platform
    
*   Meeting Analytics  
    Analyze your meeting data with our powerful reports built into the dashboard. Get detailed insights of meetings scheduled by you Get the data metrics like
    
*   Webinar Mode  
    Webinar Mode offers experience for hosting large-scale events with audience participation. With built-in registration system attendees can sign up before the webinar, ensuring an organized attendee list.
    
*   Voice Command  
    Use Meet Hour best in class voice command to perform specific actions within the meeting.
    
*   Manage Video Quality  
    Manually manage the video quality from low definition to high definition.
    
*   Speaker Stats  
    See the stats of the participants who have spoken for most of the time in the meeting. Check the stat live during the meeting.
    
*   Keyboard Shortcuts  
    Users can perform specific meeting actions via shortcuts of the meeting.
    
*   Raise Hand  
    A user can raise hand during the meeting if he/she wants to ask/say something while other participants are having a conversation.
    
*   Picture In Picture Mode (Pip)  
    Meet Hour allows picture in picture mode when users are sharing the screen.
    

* * *

### Use Cases

*   Video conferencing  
    Discover the power of video conferencing and learn what video conferencing is, explore its diverse applications across industries.
    
*   Live Streaming  
    Meet Hour allows you to broadcast your conferences directly to popular channels like YouTube, LinkedIn, Instagram, and Facebook, all at once.
    
*   Virtual Classrooms  
    Unlock a new dimension of education with Meet Hour. Whether you’re a school, university, corporate training center, or any organization.
    
*   Virtual Events  
    In today’s digital age, virtual events have become an integral part of businesses, educational institutions, and organizations.
    
*   Video KYC  
    Embrace the Future of Identity Verification with Video e-KYC. In a world driven by digital transformation, the need for seamless and secure verification.
    
*   Webinars  
    Meet Hour Webinars offer a simple, secure, and reliable platform for your virtual gatherings. Let’s dive into the key features that make Meet Hour Webinars.
    
*   Fundraising  
    At Meet Hour, we understand the importance of fundraising in today’s dynamic world. That’s why we’ve introduced seamless integration with – Donor box and Click pledge.

