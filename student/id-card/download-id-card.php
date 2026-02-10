<?php
// student/id-card/download-id-card.php

// 1. Start session and check auth
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header("Location: ../../student/login.php");
    exit;
}

// 2. Include database and libraries
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// 3. Fetch Data
$student_id = $_SESSION['student_id'];
$student = [];

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection failed.");
    }
    $conn = $pdo;

    $sql = "SELECT s.*, c.name as country_name, st.name as state_name, ct.name as city_name, co.course_name, co.course_code,
                   ce.center_name, ce.id as center_code, cat.category_name as category_name, acs.session_name,
                   acs.end_month, acs.end_year, s.enrollment_date
            FROM students s 
            LEFT JOIN countries c ON s.country_id = c.id 
            LEFT JOIN states st ON s.state_id = st.id 
            LEFT JOIN cities ct ON s.city_id = ct.id 
            LEFT JOIN courses co ON s.course_id = co.id 
            LEFT JOIN centers ce ON s.center_id = ce.id 
            LEFT JOIN course_categories cat ON co.category_id = cat.id
            LEFT JOIN academic_sessions acs ON s.session_id = acs.id
            WHERE s.id = :student_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found.");
    }

    // Fix enrollment number
    if (empty($student['enrollment_no']) && !empty($student['enrollment_id'])) {
        $student['enrollment_no'] = $student['enrollment_id'];
    }
    // Final fallback
    if (empty($student['enrollment_no'])) {
        $student['enrollment_no'] = 'N/A';
    }

    // Fetch center signature
    $center_signature = null;
    if (isset($student['center_id'])) {
        $stmt_center = $conn->prepare("SELECT owner_sign FROM centers WHERE id = :center_id");
        $stmt_center->execute([':center_id' => $student['center_id']]);
        $center_row = $stmt_center->fetch(PDO::FETCH_ASSOC);
        if ($center_row) $center_signature = $center_row['owner_sign'];
    }

    // Admin signature removed as table does not exist
    $admin_signature = null;


} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// 4. Image Generation Setup
// Paths
$bg_image_path = __DIR__ . '/background/school-id-card.png';
// Use FreeSans for better compatibility
$font_path = __DIR__ . '/../../vendor/mpdf/mpdf/ttfonts/FreeSans.ttf'; 

if (!file_exists($bg_image_path)) {
    die("Background image not found.");
}
if (!file_exists($font_path)) {
    // If specific font not found, try another or fallback
    // Fallback to simpler font if needed
    // die("Font file not found at: " . $font_path);
}

// Create Image from Background
$image = imagecreatefrompng($bg_image_path);
if (!$image) {
    die("Failed to load background image.");
}

// Colors
$color_black = imagecolorallocate($image, 0, 0, 0);
$color_dark_blue = imagecolorallocate($image, 13, 71, 161); // #0d47a1
$color_white = imagecolorallocate($image, 255, 255, 255);

// 5. Helper function for text
function addText($image, $size, $angle, $x, $y, $color, $font, $text) {
    if (file_exists($font)) {
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
    } else {
        // Fallback to simpler text
        imagestring($image, 5, $x, $y - 15, $text, $color);
    }
}

// 6. Overlay Data
// Coordinates based on 1011x639 image. 
// These need to be adjusted based on the visual layout of 'school-id-card.png'.
// Assuming a standard layout based on sample.

// Left Side Details
$base_x = 55;
$base_y = 180; // Moved further up from 240
$line_height = 35;
$font_size_label = 16; 
$font_size_value = 16;
$label_width = 240; 

// Helper to draw Label: Value
function drawField($image, $font, $color, $x, $y, $label, $value, $label_width) {
    // Label
    addText($image, 14, 0, $x, $y, $color, $font, $label);
    // Value
    addText($image, 14, 0, $x + $label_width, $y, $color, $font, ": " . $value);
}

// Student Name (First)
$full_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
drawField($image, $font_path, $color_black, $base_x, $base_y, "Name", $full_name, $label_width);

// Enrollment No
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Enrolment Number", $student['enrollment_no'], $label_width);

// Center Code / RC Code - Using Center Name as per sample
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "ASC Name", $student['center_name'] ?? '', $label_width);

// Course Name
$base_y += $line_height;
// Truncate course name if too long
$course_name = $student['course_name'] ?? '';
if (strlen($course_name) > 40) {
    $course_name = substr($course_name, 0, 37) . '...';
}
drawField($image, $font_path, $color_black, $base_x, $base_y, "Course Name", $course_name, $label_width);

// Session (New)
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Session", $student['session_name'] ?? '', $label_width);

// Father's Name
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Father's Name", $student['father_name'] ?? '', $label_width);

// Address / DOB / Mobile
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "DOB", $student['dob'] ?? '', $label_width);

$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Mobile", $student['mobile'] ?? '', $label_width);


// 7. Right Side Images

// 7. Right Side Images

// Student Photo
// Positioning estimate: Right side, aligned with top details.
// Reduced size as requested (was 210x240)
$photo_w = 170;
$photo_h = 200;
$photo_x = 780; // Centered roughly in the right area (was 760)
$photo_y = 160; 

// Load Photo
$photo_path = '';
if (!empty($student['student_image'])) {
    // Try multiple path variations to be safe
    $paths_to_try = [
        __DIR__ . '/../../' . $student['student_image'], // Standard relative path
        $_SERVER['DOCUMENT_ROOT'] . '/scre/' . $student['student_image'], // Absolute path guess
        $_SERVER['DOCUMENT_ROOT'] . '/' . $student['student_image']
    ];

    foreach ($paths_to_try as $p) {
        if (file_exists($p) && !is_dir($p)) {
            $photo_path = $p;
            break;
        }
    }
}

// Default photo if needed
if (empty($photo_path)) {
    $photo_path = __DIR__ . '/../../assets/uploads/students/default-user.png';
    if (!file_exists($photo_path)) {
        // Make sure we have SOME default or just skip
        $photo_path = ''; 
    }
}

if (!empty($photo_path) && file_exists($photo_path)) {
    $photo_info = @getimagesize($photo_path); // Start with @ to suppress errors
    if ($photo_info) {
        $photo_mime = $photo_info['mime'];
        
        $src_photo = null;
        if ($photo_mime == 'image/jpeg') $src_photo = imagecreatefromjpeg($photo_path);
        elseif ($photo_mime == 'image/png') $src_photo = imagecreatefrompng($photo_path);
        elseif ($photo_mime == 'image/webp') $src_photo = imagecreatefromwebp($photo_path);
        
        if ($src_photo) {
            imagecopyresampled($image, $src_photo, $photo_x, $photo_y, 0, 0, $photo_w, $photo_h, imagesx($src_photo), imagesy($src_photo));
            // Draw border around photo
            imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_w, $photo_y + $photo_h, $color_dark_blue);
        }
    }
} else {
    // Draw placeholder box
    imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_w, $photo_y + $photo_h, $color_dark_blue);
    addText($image, 10, 0, $photo_x + 20, $photo_y + 100, $color_black, $font_path, "No Photo");
}

// Enrollment under photo
$enroll_text = $student['enrollment_no'];
if (file_exists($font_path)) {
    $bbox = imagettfbbox(18, 0, $font_path, $enroll_text);
    $text_w = $bbox[2] - $bbox[0];
    $enroll_x = $photo_x + ($photo_w - $text_w) / 2;
    addText($image, 18, 0, $enroll_x, $photo_y + $photo_h + 25, $color_black, $font_path, $enroll_text);
} else {
    imagestring($image, 5, $photo_x, $photo_y + $photo_h + 10, $enroll_text, $color_black);
}


// QR Code
// Position: Moved to Bottom Right (Below Photo and Enrollment)
$qr_size = 100;
// Center under photo
$qr_x = $photo_x + ($photo_w - $qr_size)/2; 
$qr_y = $photo_y + $photo_h + 40; // Below enrollment text (which is +25)

// Use QuickChart API (like marksheet) to avoid dependency issues
$qrData = "Valid: " . $student['enrollment_no'] . "\nName: " . $student['first_name'];
$apiUrl = "https://quickchart.io/qr?text=" . urlencode($qrData) . "&size=300&margin=0";

// Try to fetch image data
$qrContent = false;
if (ini_get('allow_url_fopen')) {
    $qrContent = @file_get_contents($apiUrl);
}
// Fallback to cURL
if ($qrContent === false && function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $qrContent = curl_exec($ch);
    curl_close($ch);
}

if ($qrContent) {
    $qrGd = imagecreatefromstring($qrContent);
    if ($qrGd) {
        imagecopyresampled($image, $qrGd, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qrGd), imagesy($qrGd));
    }
} else {
    // If API fails, show text
    addText($image, 10, 0, $qr_x, $qr_y + 20, $color_black, $font_path, "QR Service Unavailable");
}

// Signatures
// Bottom Layout: Left (Center), Middle (Auth), Right (Student) or similar
$sig_y = 575; // Moved down from 560
$sig_h = 45; 

// 1. Center Signature (Left)
$center_sig_x = 80; // Adjusted Left
if ($center_signature) {
    $c_path = __DIR__ . '/../../' . $center_signature;
    if (file_exists($c_path)) {
       $c_sig_img = imagecreatefrompng($c_path);
       if (!$c_sig_img) $c_sig_img = imagecreatefromjpeg($c_path);
       if ($c_sig_img) {
            $orig_w = imagesx($c_sig_img);
            $orig_h = imagesy($c_sig_img);
            $new_w = ($orig_w / $orig_h) * $sig_h;
            // Center image relative to text roughly
            $img_x = $center_sig_x + (100 - $new_w)/2; 
            imagecopyresampled($image, $c_sig_img, $img_x, $sig_y - 40, 0, 0, $new_w, $sig_h, $orig_w, $orig_h);
       }
    }
}
addText($image, 10, 0, $center_sig_x, $sig_y + 20, $color_black, $font_path, "Center Director");

// 2. Authorize Signatory (Middle)
$auth_sig_x = 440; // Adjusted Middle
// If a static file exists, load it:
$static_auth_path = __DIR__ . '/../../assets/images/auth_sign.png'; 
if (file_exists($static_auth_path)) {
    $a_sig_img = imagecreatefrompng($static_auth_path);
    if ($a_sig_img) {
        $orig_w = imagesx($a_sig_img);
        $orig_h = imagesy($a_sig_img);
        $new_w = ($orig_w / $orig_h) * $sig_h;
        $img_x = $auth_sig_x + (120 - $new_w)/2; // Center relative to text
        imagecopyresampled($image, $a_sig_img, $img_x, $sig_y - 40, 0, 0, $new_w, $sig_h, $orig_w, $orig_h);
    }
}
addText($image, 10, 0, $auth_sig_x, $sig_y + 20, $color_black, $font_path, "Authorized Signatory");

// 3. Student Signature (Right)
$student_sig_x = 800; // Adjusted Right
if (!empty($student['student_signature'])) {
    $s_sig_path = __DIR__ . '/../../' . $student['student_signature'];
    if (file_exists($s_sig_path)) {
        $s_sig_img = imagecreatefrompng($s_sig_path); 
        if (!$s_sig_img) $s_sig_img = imagecreatefromjpeg($s_sig_path);
        if ($s_sig_img) {
            $orig_w = imagesx($s_sig_img);
            $orig_h = imagesy($s_sig_img);
            $new_w = ($orig_w / $orig_h) * $sig_h;
            $img_x = $student_sig_x + (100 - $new_w)/2;
            imagecopyresampled($image, $s_sig_img, $img_x, $sig_y - 40, 0, 0, $new_w, $sig_h, $orig_w, $orig_h);
        }
    }
}
addText($image, 10, 0, $student_sig_x, $sig_y + 20, $color_black, $font_path, "Student Signature");


// 8. Output
ob_clean(); // Clean any previous output/buffers
header('Content-Type: image/png');

if (!isset($_GET['preview'])) {
    header('Content-Disposition: attachment; filename="ID_Card_'.$student['enrollment_no'].'.png"');
}

imagepng($image);
imagedestroy($image);

?>
