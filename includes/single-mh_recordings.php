<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require(MEETHOUR_PLUGIN_FILE  . 'vendor/autoload.php');
require(MEETHOUR_PLUGIN_FILE  . 'vendor/meethour/php-sdk/src/autoload.php');


$video_url = get_post_meta(get_the_ID(), 'recording_path', true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Recording</title>
</head>

<body style="padding: 0; margin: 0;">
    <div class="">
        <video controls style="border-radius: 10px;">
            <source src="<?php echo esc_html($video_url) ?>" type="video/mp4">
        </video>

</body>

</html>