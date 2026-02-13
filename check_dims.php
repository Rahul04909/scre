<?php
$path = 'd:/wamp/www/pace-foundation/student/id-card/background/background.png';
if (file_exists($path)) {
    $info = getimagesize($path);
    echo "Width: " . $info[0] . "\n";
    echo "Height: " . $info[1] . "\n";
} else {
    echo "File not found\n";
}
?>
