<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student Data
$sqlStudent = "
    SELECT s.*, 
           c.course_name, c.course_code, c.duration_value, c.duration_type,
           cen.center_name, cen.center_code, cen.address as center_address, 
           ac.session_name, ac.start_month, ac.start_year, ac.end_month, ac.end_year
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN centers cen ON s.center_id = cen.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    WHERE s.id = ?
";
$stmt = $pdo->prepare($sqlStudent);
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// 2. Fetch Results (Logic from previous file)
$sqlResults = "
    SELECT er.*, sub.subject_name, sub.total_marks as subject_total, er.score as obtained_total
    FROM exam_results er
    JOIN exam_schedules es ON er.exam_schedule_id = es.id
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE er.student_id = ?
";
$stmtRes = $pdo->prepare($sqlResults);
$stmtRes->execute([$student_id]);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

// Calculate Totals
$grand_total_max = 0;
$grand_total_obt = 0;
$is_fail = false;

if (!empty($results)) {
    foreach ($results as $row) {
        $grand_total_max += $row['subject_total'];
        $grand_total_obt += $row['obtained_total'];
        if ($row['result_status'] !== 'Pass') {
            $is_fail = true;
        }
    }
}

$percentage = ($grand_total_max > 0) ? ($grand_total_obt / $grand_total_max) * 100 : 0;
$final_grade = 'F';
if (!$is_fail) {
    if ($percentage >= 90) $final_grade = 'A+';
    elseif ($percentage >= 80) $final_grade = 'A';
    elseif ($percentage >= 70) $final_grade = 'B';
    elseif ($percentage >= 60) $final_grade = 'C';
    elseif ($percentage >= 50) $final_grade = 'D';
} else {
    $final_grade = 'Fail';
}

// 3. Serial Number Logic
$certificate_serial = $student['certificate_serial_no'];
if (empty($certificate_serial)) {
    // Generate new SR-XXXXXX
    // Ensure uniqueness
    $unique = false;
    $new_serial = '';
    while (!$unique) {
        $rand_num = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $new_serial = 'SR-' . $rand_num;
        
        // Check if exists
        $stmtCheck = $pdo->prepare("SELECT id FROM students WHERE certificate_serial_no = ?");
        $stmtCheck->execute([$new_serial]);
        if ($stmtCheck->rowCount() == 0) {
            $unique = true;
        }
    }
    
    // Save to DB
    $stmtUpdate = $pdo->prepare("UPDATE students SET certificate_serial_no = ? WHERE id = ?");
    $stmtUpdate->execute([$new_serial, $student_id]);
    $certificate_serial = $new_serial;
}

// 4. Prepare Variables
$name = strtoupper(trim($student['first_name'] . ' ' . $student['last_name']));
$father_name = strtoupper(trim($student['father_name']));
$enrollment_no = $student['enrollment_no'];
$dob = date('d-m-Y', strtotime($student['dob']));
$course_name = strtoupper($student['course_name']);
$center_name = strtoupper($student['center_name']);
$duration = $student['duration_value'] . ' ' . ucfirst($student['duration_type']);
$exam_month = date('M Y'); 
$issue_date = date('d-m-Y');

// Session Logic
$session_str = $student['session_name'];
$session_start = '';
$session_end = '';
if (!empty($student['start_month']) && !empty($student['end_month'])) {
    $session_start = $student['start_month'] . ' ' . $student['start_year'];
    $session_end = $student['end_month'] . ' ' . $student['end_year'];
}

// 5. Generate QR Code
$qrData = "Certificate: " . $course_name . "\n";
$qrData .= "Student: " . $name . "\n";
$qrData .= "Serial No: " . $certificate_serial . "\n";
$qrData .= "Enrollment: " . $enrollment_no . "\n";
$qrData .= "Grade: " . $final_grade . "\n";
$qrData .= "Verify at: www.screduc.com"; 

$qrCodeHtml = '';
$apiUrl = "https://quickchart.io/qr?text=" . urlencode($qrData) . "&size=150&margin=0";

try {
    $imageData = false;
    if (ini_get('allow_url_fopen')) {
        $imageData = @file_get_contents($apiUrl);
    }
    if ($imageData === false && function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $imageData = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($imageData !== false && !empty($imageData)) {
         $base64 = 'data:image/png;base64,' . base64_encode($imageData);
         // QR Image HTML
         $qrCodeHtml = '<img src="' . $base64 . '" style="width: 100px; height: 100px;">';
    }
} catch (\Throwable $e) {
    // Ignore error
}

// 6. Load Stamp and Signature
$stamp_path = __DIR__ . '/../assets/scre-stamp.png';
$sign_path = __DIR__ . '/../assets/scre-sign.png'; // Corrected path with slash

$stamp_html = '';
if (file_exists($stamp_path)) {
    $type = pathinfo($stamp_path, PATHINFO_EXTENSION);
    $data = file_get_contents($stamp_path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    $stamp_html = '<img src="' . $base64 . '" style="width: 100px; opacity: 0.8;">';
}

$sign_html = '';
    if (file_exists($sign_path)) {
        $type = pathinfo($sign_path, PATHINFO_EXTENSION);
        $data = file_get_contents($sign_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $sign_html = '<img src="' . $base64 . '" style="width: 150px;">';
    }

// 7. DomPDF Setup
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Allow loading images
$dompdf = new Dompdf($options);

// Load Background Image
$bg_path = __DIR__ . '/background/compressed-bg.png';
$bg_data = '';
if (file_exists($bg_path)) {
    $type = pathinfo($bg_path, PATHINFO_EXTENSION);
    $data = file_get_contents($bg_path);
    $bg_data = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// HTML Structure
$html = '
<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 0px; }
    body { 
        margin: 0px; 
        font-family: Arial, sans-serif; 
        font-size: 16px; 
        font-weight: bold;
        color: #000;
    }
    .background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .field { position: absolute; }
    
    /* Coordinate Estimates for Portrait A4 (approx 794px width x 1123px height at 96dpi) */
    /* Adjust these based on visual check */
    
    .name { top: 26.7%; left: 46%; }
    .father { top: 31%; left: 46%; }
    .enroll { top: 35%; left: 46%; }
    
    .session-start { top: 39%; left: 28%; }
    .session-end { top: 39%; left: 59%; }
    
    .dob { top: 43%; left: 35%; }
    .course { top: 47.3%; left: 35%; }
    .center { top: 51.6%; left: 43%; width: 50%; }
    .duration { top: 55.6%; left: 35%; }
    .exam-month { top: 59.7%; left: 55%; }
    
    .marks-obt { top: 63.8%; left: 40%; }
    .marks-max { top: 63.8%; left: 70%; }
    
    .grade { top: 68%; left: 29%; }
    .issue-date { top: 72.6%; left: 29%; }
    
    /* QR Code Position: Below Date of Issue */
    .qr-code { top: 76%; left: 40%; }

    /* Stamp & Signature: Right side of QR */
    .stamp { top: 74%; left: 70%; z-index: 1; }
    .signature { top: 76.5%; left: 69%; z-index: 2; } 
    
    .auth-sign { top: 82%; left: 68%; width: 150px; text-align: center; font-size: 14px; font-weight: bold; }

    .reg-top { top: 8%; left: 40%; font-size: 14px; }
    
</style>
</head>
<body>
    <img src="' . $bg_data . '" class="background">
    
    <div class="content">
        <div class="field reg-top">Serial Number: ' . $certificate_serial . '</div>
        
        <div class="field name">' . $name . '</div>
        <div class="field father">' . $father_name . '</div>
        <div class="field enroll">' . $enrollment_no . '</div>
        
        <div class="field session-start">' . $session_start . '</div>
        <div class="field session-end">' . $session_end . '</div>
        
        <div class="field dob">' . $dob . '</div>
        <div class="field course">' . $course_name . '</div>
        <div class="field center">' . $center_name . '</div>
        <div class="field duration">' . $duration . '</div>
        <div class="field exam-month">' . $exam_month . '</div>
        
        <div class="field marks-obt">' . $grand_total_obt . '</div>
        <div class="field marks-max">' . $grand_total_max . '</div>
        
        <div class="field grade">' . $final_grade . '</div>
        <div class="field issue-date">' . $issue_date . '</div>
        
        <div class="field qr-code">' . $qrCodeHtml . '</div>
        <div class="field stamp">' . $stamp_html . '</div>
        <div class="field signature">' . $sign_html . '</div>
        <div class="field auth-sign">Authorized Signatory</div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Detected Portrait from dimensions
$dompdf->render();

// Output
$dompdf->stream("Certificate-" . $enrollment_no . ".pdf", ["Attachment" => 0]);
?>
