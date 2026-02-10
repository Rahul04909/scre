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
$base_y = 310;
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

// Enrollment No
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

// Student Name
$base_y += $line_height;
$full_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
drawField($image, $font_path, $color_black, $base_x, $base_y, "Name", $full_name, $label_width);

// Father's Name
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Father's Name", $student['father_name'] ?? '', $label_width);

// Address / DOB / Mobile
$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "DOB", $student['dob'] ?? '', $label_width);

$base_y += $line_height;
drawField($image, $font_path, $color_black, $base_x, $base_y, "Mobile", $student['mobile'] ?? '', $label_width);


// 7. Right Side Images

// Student Photo
// Positioning estimate: Right side, aligned with top details.
$photo_x = 760;
$photo_y = 230;
$photo_w = 210;
$photo_h = 240;

// Load Photo
$photo_path = '';
if (!empty($student['student_image'])) {
    $check_path = __DIR__ . '/../../' . $student['student_image'];
    if (file_exists($check_path)) {
        $photo_path = $check_path;
    }
}
// Default photo if needed?
if (empty($photo_path)) {
    $photo_path = __DIR__ . '/../../assets/uploads/students/default-user.png';
}

if (file_exists($photo_path)) {
    $photo_info = getimagesize($photo_path);
    $photo_mime = $photo_info['mime'];
    
    $src_photo = null;
    if ($photo_mime == 'image/jpeg') $src_photo = imagecreatefromjpeg($photo_path);
    elseif ($photo_mime == 'image/png') $src_photo = imagecreatefrompng($photo_path);
    
    if ($src_photo) {
        imagecopyresampled($image, $src_photo, $photo_x, $photo_y, 0, 0, $photo_w, $photo_h, imagesx($src_photo), imagesy($src_photo));
        // Draw border around photo
        imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_w, $photo_y + $photo_h, $color_dark_blue);
    }
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
// Position: Top Right area
$qr_x = 830; 
$qr_y = 40; 
$qr_size = 140;

if (class_exists('chillerlan\QRCode\QRCode') && class_exists('chillerlan\QRCode\QROptions')) {
    try {
        $qrData = "Valid: " . $student['enrollment_no'] . "\nName: " . $student['first_name'];
        $qrOptions = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_L,
            'scale'      => 4,
            'imageBase64' => true,
        ]);
        $qrcode = new QRCode($qrOptions);
        $qrBase64 = $qrcode->render($qrData);
        $qrBase64 = explode(',', $qrBase64)[1];
        $qrString = base64_decode($qrBase64);
        $qrGd = imagecreatefromstring($qrString);

        if ($qrGd) {
            imagecopyresampled($image, $qrGd, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qrGd), imagesy($qrGd));
        }
    } catch (Exception $e) {
        // QR Failed, skip
        addText($image, 10, 0, $qr_x, $qr_y + 20, $color_black, $font_path, "QR Error");
    }
} else {
    // Library missing
    addText($image, 10, 0, $qr_x, $qr_y + 20, $color_black, $font_path, "QR Lib Missing");
}

// Signatures
// Bottom Right: Authority
// Bottom Left/Center: Student
$sig_y = 560; // Near bottom
$sig_h = 50;  // approximate height

// Student Signature
if (!empty($student['student_signature'])) {
    $s_sig_path = __DIR__ . '/../../' . $student['student_signature'];
    if (file_exists($s_sig_path)) {
        // Load and place
        $s_sig_img = imagecreatefrompng($s_sig_path); // Assuming PNG
        if (!$s_sig_img) $s_sig_img = imagecreatefromjpeg($s_sig_path); // Try JPG
        
        if ($s_sig_img) {
            // Resize preserving aspect ratio
            $orig_w = imagesx($s_sig_img);
            $orig_h = imagesy($s_sig_img);
            $new_w = ($orig_w / $orig_h) * $sig_h;
            
            imagecopyresampled($image, $s_sig_img, 720, $sig_y - 40, 0, 0, $new_w, $sig_h, $orig_w, $orig_h);
        }
    }
}
// Label for student sign
addText($image, 10, 0, 720, $sig_y + 20, $color_black, $font_path, "Student Signature");


// Authority Signature (Center or Admin)
// Use Admin signature as default or Center director
$auth_sig_path = '';
if ($center_signature) {
    $c_path = __DIR__ . '/../../' . $center_signature;
    if (file_exists($c_path)) $auth_sig_path = $c_path;
}
if (!$auth_sig_path && $admin_signature) {
    $a_path = __DIR__ . '/../../assets/uploads/admin/' . $admin_signature;
    if (file_exists($a_path)) $auth_sig_path = $a_path;
}

if ($auth_sig_path) {
    $a_sig_img = imagecreatefrompng($auth_sig_path);
    if (!$a_sig_img) $a_sig_img = imagecreatefromjpeg($auth_sig_path);
    
    if ($a_sig_img) {
         $orig_w = imagesx($a_sig_img);
         $orig_h = imagesy($a_sig_img);
         $new_w = ($orig_w / $orig_h) * $sig_h;
         
         imagecopyresampled($image, $a_sig_img, 860, $sig_y - 40, 0, 0, $new_w, $sig_h, $orig_w, $orig_h);
    }
}
// Label
addText($image, 10, 0, 860, $sig_y + 20, $color_black, $font_path, "Authority Signature");


// 8. Output
ob_clean(); // Clean any previous output/buffers
header('Content-Type: image/png');

if (!isset($_GET['preview'])) {
    header('Content-Disposition: attachment; filename="ID_Card_'.$student['enrollment_no'].'.png"');
}

imagepng($image);
imagedestroy($image);

?>
