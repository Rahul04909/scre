<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Server Environment Check</h1>";

// 1. Check GD
if (extension_loaded('gd')) {
    echo "<p style='color:green'>[OK] GD Extension is loaded.</p>";
    $gd_info = gd_info();
    echo "<pre>"; print_r($gd_info); echo "</pre>";
} else {
    echo "<p style='color:red'>[ERROR] GD Extension is NOT loaded.</p>";
}

// 2. Check Autoload
$autoload_path = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "<p style='color:green'>[OK] vendor/autoload.php found.</p>";
    require_once $autoload_path;
    if (class_exists('chillerlan\QRCode\QRCode')) {
        echo "<p style='color:green'>[OK] chillerlan\QRCode\QRCode class found.</p>";
    } else {
        echo "<p style='color:red'>[ERROR] chillerlan\QRCode\QRCode class NOT found.</p>";
    }
} else {
    echo "<p style='color:red'>[ERROR] vendor/autoload.php NOT found at $autoload_path</p>";
}

// 3. Check Assets
$bg_path = __DIR__ . '/background/school-id-card.png';
if (file_exists($bg_path)) {
    echo "<p style='color:green'>[OK] Background image found.</p>";
} else {
    echo "<p style='color:red'>[ERROR] Background image NOT found at $bg_path</p>";
}

$font_path = __DIR__ . '/../../assets/fonts/NotoSansDevanagari-Regular.ttf';
if (file_exists($font_path)) {
    echo "<p style='color:green'>[OK] Font file found.</p>";
} else {
    echo "<p style='color:red'>[ERROR] Font file NOT found at $font_path</p>";
}

echo "<p>Done.</p>";
?>
