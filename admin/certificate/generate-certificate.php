<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Admin Authentication Check
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied");
}

if (!isset($_GET['student_id'])) {
    die("Student ID required.");
}

$student_id = intval($_GET['student_id']);

// 2. Fetch Student Data
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

// 3. Fetch Results (Logic from previous file)
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

// 4. Serial Number Logic
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

// 5. Prepare Variables
$name = strtoupper(trim($student['first_name'] . ' ' . $student['last_name']));
$father_name = strtoupper(trim($student['father_name']));
$enrollment_no = $student['enrollment_no'];
$course_name = $student['course_name'];
$center_name = $student['center_name'] . ' (' . $student['center_code'] . ')';

// Duration
$duration = $student['duration_value'] . ' ' . $student['duration_type'];

// Session
$start_date = DateTime::createFromFormat('!m', $student['start_month']);
$end_date = DateTime::createFromFormat('!m', $student['end_month']);
$session_start = $start_date->format('F') . ' ' . $student['start_year'];
$session_end = $end_date->format('F') . ' ' . $student['end_year'];

// DOB
$dob = date('d-m-Y', strtotime($student['dob']));

// Exam Month (Assume end of session for now)
$exam_month = $session_end;

// Issue Date
$issue_date = date('d-m-Y');


// 6. QR Code (Using QuickChart API, same as student module)
$qrData = "Name: $name\nEnrollment: $enrollment_no\nCourse: $course_name\nGrade: $final_grade\nSerial: $certificate_serial\nVerify at: www.screduc.com";

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
         $qrCodeHtml = '<img src="' . $base64 . '" style="width: 80px;">';
    }
} catch (\Throwable $e) {
    // Ignore error
}

// 7. Signature & Stamp (Merged Image Logic - Same as Marksheet)
// Fetch Admin (ID 1 for now)
$stmtAdmin = $pdo->prepare("SELECT signature_path, stamp_path FROM admins WHERE id = 1");
$stmtAdmin->execute();
$adminAssets = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

$signPath = $adminAssets['signature_path'];
$stampPath = $adminAssets['stamp_path'];

// Absolute paths
$baseDir = "d:/wamp/www/pace-foundation/"; // Hardcoded for this environment
$signAbsPath = $baseDir . $signPath;
$stampAbsPath = $baseDir . $stampPath;

$mergedImageHtml = '';

if (!empty($signPath) && file_exists($signAbsPath) && !empty($stampPath) && file_exists($stampAbsPath)) {
    // GD Merge
    $sign = imagecreatefrompng($signAbsPath);
    $stamp = imagecreatefrompng($stampAbsPath);
    
    if ($sign && $stamp) {
        $sw = imagesx($stamp);
        $sh = imagesy($stamp);
        $siw = imagesx($sign);
        $sih = imagesy($sign);
        
        // Canvas Size (Same as Marksheet)
        $canvasW = max($sw, $siw, 500); 
        $canvasH = max($sh, $sih, 420); 
        
        $finalImg = imagecreatetruecolor($canvasW, $canvasH);
        
        // Transparency
        imagealphablending($finalImg, false);
        imagesavealpha($finalImg, true);
        $transparent = imagecolorallocatealpha($finalImg, 255, 255, 255, 127);
        imagefill($finalImg, 0, 0, $transparent);
        imagealphablending($finalImg, true);
        
        // Stamp Position (Centered, slightly higher)
        $targetStampW = 200;
        $targetStampH = ($sh / $sw) * $targetStampW;
        $stampX = ($canvasW - $targetStampW) / 2;
        $stampY = ($canvasH - $targetStampH) / 2 - 30; 
        
        imagecopyresampled($finalImg, $stamp, $stampX, $stampY, 0, 0, $targetStampW, $targetStampH, $sw, $sh);
        
        // Signature Position (Centered, slightly lower)
        $targetSignW = 300;
        $targetSignH = ($sih / $siw) * $targetSignW;
        $signX = ($canvasW - $targetSignW) / 2;
        $signY = ($canvasH - $targetSignH) / 2 - 35; 
        
        imagecopyresampled($finalImg, $sign, $signX, $signY, 0, 0, $targetSignW, $targetSignH, $siw, $sih);
        
        // Output
        ob_start();
        imagepng($finalImg);
        $imgData = ob_get_clean();
        $base64 = 'data:image/png;base64,' . base64_encode($imgData);
        $mergedImageHtml = '<img src="' . $base64 . '" style="width: 200px;">'; // Keep it reasonably sized for certificate
        
        imagedestroy($stamp);
        imagedestroy($sign);
        imagedestroy($finalImg);
    }
}

// 8. Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Load Background
$bgPath = '../../student/certificate/background/compressed-bg.png'; 
if (file_exists($bgPath)) {
    $bgData = base64_encode(file_get_contents($bgPath));
    $bgSrc = 'data:image/jpeg;base64,' . $bgData;
} else {
    die("Background image not found at: " . realpath($bgPath));
}

// CSS & HTML
$html = '
<html>
<head>
<style>
    @page { margin: 0; }
    body { 
        margin: 0; 
        padding: 0; 
        font-family: Arial, sans-serif;
        color: #000;
        width: 100%;
        height: 100%;
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
        width: 100%;
        height: 100%;
    }
    .field { position: absolute; }
    
    /* Coordinates from student version */
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
    
    .qr-code { top: 76%; left: 45%; }

    /* Merged Signature & Stamp */
    .merged-sign { top: 73%; left: 65%; z-index: 2; text-align: center; }
    .auth-sign-text { font-weight: bold; margin-top: 5px; }
    
    .reg-top { top: 8%; left: 40%; font-size: 14px; }
</style>
</head>
<body>
    <img src="' . $bgSrc . '" class="background">
    
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
        
        <div class="field merged-sign">
            ' . $mergedImageHtml . '
            <div class="auth-sign-text">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Certificate-" . $enrollment_no . ".pdf", ["Attachment" => 0]);
?>
