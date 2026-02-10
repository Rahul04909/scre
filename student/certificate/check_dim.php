<?php
$file = 'd:/wamp/www/pace-foundation/student/certificate/background/background-img.png';
if (file_exists($file)) {
    $info = getimagesize($file);
    echo "Width: " . $info[0] . " Height: " . $info[1] . " Mime: " . $info['mime'];
} else {
    echo "File not found";
}
?>
