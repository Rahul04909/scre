<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

$student_id = $_SESSION['student_id'];

// 1. Fetch Data
$sql = "
    SELECT s.*, 
           c.course_name, c.duration_value, c.duration_type,
           cen.center_name, cen.center_code,
           ac.session_name, ac.start_month, ac.start_year, ac.end_month, ac.end_year
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN centers cen ON s.center_id = cen.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    WHERE s.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Result Data
$sqlResults = "
    SELECT er.*, sub.total_marks as subject_total, er.score as obtained_total
    FROM exam_results er
    JOIN exam_schedules es ON er.exam_schedule_id = es.id
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE er.student_id = ?
";
$stmtRes = $pdo->prepare($sqlResults);
$stmtRes->execute([$student_id]);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

$grand_total_max = 0;
$grand_total_obt = 0;
foreach ($results as $row) {
    $grand_total_max += $row['subject_total'];
    $grand_total_obt += $row['obtained_total'];
}

$percentage = ($grand_total_max > 0) ? ($grand_total_obt / $grand_total_max) * 100 : 0;
$grade = 'F';
if ($percentage >= 80) $grade = 'A';
elseif ($percentage >= 60) $grade = 'B';
elseif ($percentage >= 40) $grade = 'C';
else $grade = 'Fail'; // Below 40

// Formatting
$student_name = strtoupper($student['first_name'] . ' ' . $student['last_name']);
$father_name = strtoupper($student['father_name']);
$enrollment = $student['enrollment_no'];
$dob = date('d-m-Y', strtotime($student['dob']));
$course_name = strtoupper($student['course_name']);
$center_text = "(" . $student['center_code'] . ") " . strtoupper($student['center_name']);
$duration = $student['duration_value'] . " " . ucfirst($student['duration_type']); // e.g., 12 Months

// Session Dates
$session_start = "01-" . substr($student['start_month'], 0, 3) . "-" . $student['start_year']; // approx
$session_end = "30-" . substr($student['end_month'], 0, 3) . "-" . $student['end_year']; // approx

// Exam Date (Use consolidated date or issue date)
$exam_date = $session_end; // Placeholder
$issue_date = date('d-M-Y');

// Profile Image
$profile_img = '';
if (!empty($student['student_image'])) {
    $path = '../../' . $student['student_image'];
    if (file_exists($path)) {
        $profile_img = $path;
    }
}

// QR Code
$qrData = "Cert: $student_name\nEnroll: $enrollment\nCourse: $course_name\nGrade: $grade";
$qrCodeHtml = '';
$apiUrl = "https://quickchart.io/qr?text=" . urlencode($qrData) . "&size=100&margin=0";
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
         $qrCodeHtml = '<img src="' . $base64 . '" style="width: 80px; height: 80px;">';
    }
} catch (\Throwable $e) {}


// Generate PDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => 'A4-L', // Landscape
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'default_font' => 'freeserif'
    ]);

    // Background
    $bg_path = 'background/certificate-bg.png';
    if (file_exists($bg_path)) {
        $mpdf->SetDefaultBodyCSS('background', "url('{$bg_path}')");
        $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
    }

    $html = '
    <style>
    <style>
        body { font-family: freeserif; color: #000; }
        .cert-container { 
            padding-top: 220px; /* Increased to clear the Background Header */
            padding-bottom: 20px;
            padding-left: 80px; 
            padding-right: 80px; 
            position: relative; 
        }
        
        /* Photo on LEFT Layout - Overlay Position */
        .header-photo {
            position: absolute;
            top: 260px;
            left: 50px;
            width: 100px; height: 120px; border: 1px solid #000;
            margin-top: 55px;
        }
        
        .content-text {
            font-size: 16px; line-height: 1.6; margin-top: -140px; 
            font-weight: bold;
            text-align: center; 
            width: 100%;
        }
        
        .fill-blank {
            border-bottom: 1px dotted #000;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            color: blue;
            padding: 0 5px;
        }
        
        .footer-table { width: 100%; margin-top: 20px; }
        .footer-table td { text-align: center; vertical-align: bottom; font-weight: bold; }
        
        .grade-legend { font-size: 10px; color: red; font-weight: bold; margin-top: 5px; }
        .director-sign { font-size: 14px; border-top: 2px solid #000; display: inline-block; width: 150px; margin-top: 30px; }
        .coordinator-sign { font-size: 14px; border-top: 2px solid #000; display: inline-block; width: 150px; margin-top: 30px; }
    </style>
    
    <div class="cert-container">
        
        <!-- Profile Photo -->
         '.($profile_img ? '<img src="'.$profile_img.'" class="header-photo">' : '<div class="header-photo"></div>').'

        <div class="content-text">
            This is to Certify that Mr./Miss/Mrs. <span class="fill-blank" style="min-width: 300px;">'.$student_name.'</span> 
            Son of/Daughter of Sh. <span class="fill-blank" style="min-width: 300px;">'.$father_name.'</span><br>
            
            Registration No. <span class="fill-blank" style="min-width: 150px;">'.$enrollment.'</span> 
            Session <span class="fill-blank" style="min-width: 150px;">'.$session_start.' to '.$session_end.'</span><br>

            Date of Birth <span class="fill-blank" style="min-width: 120px;">'.$dob.'</span> 
            In the course <span class="fill-blank" style="min-width: 400px; max-width: 500px;">'.$course_name.'</span><br>

            Appeared from our ASC* <span class="fill-blank" style="min-width: 500px;">'.$center_text.'</span><br>

            Duration of <span class="fill-blank" style="min-width: 100px;">'.$duration.'</span> 
            has successfully used by his/her final Examination held in <span class="fill-blank" style="min-width: 150px;">'.$exam_date.'</span><br>

            Obtained marks <span class="fill-blank" style="min-width: 60px;">'.(0 + $grand_total_obt).'</span> 
            Out of <span class="fill-blank" style="min-width: 60px;">'.(0 + $grand_total_max).'</span> 
            with Grade <span class="fill-blank" style="min-width: 40px;">'.$grade.'</span> 
            and hereby awarded CERTIFICATE/DIPLOMA.
            <br><br>
            
            Date of Issue : <span class="fill-blank" style="min-width: 150px;">'.$issue_date.'</span>
        </div>

        <table class="footer-table">
            <tr>
                <td width="30%">
                    <div class="coordinator-sign">Co-Ordinator</div>
                </td>
                <td width="40%">
                    '.$qrCodeHtml.'
                </td>
                <td width="30%">
                    <div class="director-sign">Director</div>
                </td>
            </tr>
        </table>
        
        <!-- Legend and Footer Text centered at bottom -->
        <div style="text-align: center; margin-top: 5px;">
             <div class="grade-legend">
                Grade A 80% & Above, Grade B 60-79, Grade C 40-59, Grade Below 40%
            </div>
            <div style="font-size: 10px; color: #333; margin-top: 5px;">
                This Certificate/Diploma is issued by IAGCSM Education Pvt. Ltd.<br>
                Result may be verified on www.screduc.com
            </div>
        </div>

    </div>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('Certificate.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    die("PDF Error: " . $e->getMessage());
}
?>
