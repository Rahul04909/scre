<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$student_id = $_SESSION['student_id'];
$unit_no = isset($_GET['unit']) ? intval($_GET['unit']) : null;

// 1. Fetch Student, Course, Center Info
$sqlStudent = "
    SELECT s.*, 
           c.course_name, c.course_code, c.duration_value, c.duration_type, c.has_units, c.unit_type,
           cen.center_name, cen.center_code, cen.address as center_address, 
           ac.session_name
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

// 2. Fetch Results
// We need subject name, max marks (theory/practical), obtained marks (theory/practical)
// Note: Currently assuming exam_results['score'] is total obtained. 
// If your system splits theory/practical in input, we need that column. 
// For now, assuming 'score' is the obtained marks for the subject.
$sqlResults = "
    SELECT er.*, 
           sub.subject_name, sub.theory_marks, sub.practical_marks, sub.total_marks as subject_total,
           er.score as obtained_total
    FROM exam_results er
    JOIN exam_schedules es ON er.exam_schedule_id = es.id
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE er.student_id = ?
";

$params = [$student_id];
if ($unit_no && $student['has_units']) {
    $sqlResults .= " AND er.unit_no = ?";
    $params[] = $unit_no;
}

$stmtRes = $pdo->prepare($sqlResults);
$stmtRes->execute($params);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    die("No results found for this marksheet.");
}

// 3. Calculate Totals
$grand_total_max = 0;
$grand_total_obt = 0;
$is_fail = false;

foreach ($results as $row) {
    $grand_total_max += $row['subject_total'];
    $grand_total_obt += $row['obtained_total'];
    if ($row['result_status'] !== 'Pass') {
        $is_fail = true;
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
    $final_grade = 'Fail'; // Or just F
}
$overall_status = $is_fail ? 'FAIL' : 'PASS';


// 4. Formatting Dates
$dob = date('d M Y', strtotime($student['dob']));
$issue_date = date('d-m-Y');

// 5. Generate PDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => 'A4',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'orientation' => 'P',
        'default_font' => 'freeserif'
    ]);

    // Background
    // Assuming background image is at student/marksheet/background/marksheet-background.png
    // Need absolute path for mPDF usually, or relative to script.
    $bg_path = __DIR__ . '/background/marksheet.png';
    if (file_exists($bg_path)) {
        $mpdf->SetDefaultBodyCSS('background', "url('{$bg_path}')");
        $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
    }

    // Profile Image
    $profile_img = '';
    if (!empty($student['student_image'])) {
        $path = '../../' . $student['student_image'];
        if (file_exists($path)) {
            $profile_img = $path;
        }
    }

    // Generate QR Code
    $qrData = "Student Name: " . $student['first_name'] . " " . $student['last_name'] . "\n";
    $qrData .= "Enrollment No: " . $student['enrollment_no'] . "\n";
    $qrData .= "Course: " . $student['course_name'] . "\n";
    $qrData .= "Total Marks: " . $grand_total_obt . "/" . $grand_total_max . "\n";
    $qrData .= "Result: " . $overall_status;

    $qrCodeHtml = '';
    
    // Robust Strategy: Use Public API to avoid server dependency issues
    // We fetch the image and convert to Base64 to ensure mPDF can render it without allow_url_fopen issues
    
    $apiUrl = "https://quickchart.io/qr?text=" . urlencode($qrData) . "&size=150&margin=0";
    
    try {
        // Try to fetch image data
        $imageData = false;
        
        if (ini_get('allow_url_fopen')) {
            $imageData = @file_get_contents($apiUrl);
        }
        
        // Fallback to cURL if file_get_contents fails or is disabled
        if ($imageData === false && function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Best effort
            $imageData = curl_exec($ch);
            curl_close($ch);
        }
        
        if ($imageData !== false && !empty($imageData)) {
             $base64 = 'data:image/png;base64,' . base64_encode($imageData);
             $qrCodeHtml = '<img src="' . $base64 . '" alt="QR Code" style="width: 100px; height: 100px; margin-top: 15px;">';
        } else {
             // If connection fails, show a simple text fallback instead of fatal error
             $qrCodeHtml = '<div style="font-size: 8px; border: 1px solid #ccc; padding: 2px;">QR Unavailable</div>';
        }
    } catch (\Throwable $e) {
        $qrCodeHtml = '';
    }

    // 6. Load Stamp and Signature
    $stamp_path = __DIR__ . '/../assets/scre-stamp.png';
    $sign_path = __DIR__ . '/../assets/scre-sign.png';

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
        $sign_html = '<img src="' . $base64 . '" style="width: 220px;">';
    }

    // Styles
    $html = '
    <style>
        body { font-family: freeserif; color: #000; }
        /* ... existing styles ... */
        /* Update signature box for mPDF */
        .signature-container {
             width: 250px; 
             text-align: center; 
             float: right; 
             margin-right: 20px;
        }
    </style>
    
    <!-- ... HTML content ... -->

                        <div class="signature-container">
                            <!-- Attempting simple layering for mPDF -->
                            <div style="height: 100px; position: relative;">
                                <!-- Stamp -->
                                <div style="position: absolute; top: 10px; left: 70px; z-index: 0;">
                                    '.$stamp_html.'
                                </div>
                                <!-- Signature -->
                                <div style="position: absolute; top: 0px; left: 10px; z-index: 1;">
                                    '.$sign_html.'
                                </div>
                            </div>
                            <div style="font-weight: bold; margin-top: 5px;">Authorized Signatory</div>
                         </div>

        
        <div style="position: absolute; bottom: 10px; width: 100%; text-align: center; font-size: 10px; color: #666; margin-top: 20px;">
            This Certificate/Diploma is issued by PACE FOUNDATION. Result may be verified on www.pacefoundation.com
        </div>

    </div>
    ';

    $mpdf->WriteHTML($html);
    
    // Add QR Code
    // <barcode code="Your URL" type="QR" class="barcode" size="1.0" error="M" />
    
    $mpdf->Output('Marksheet.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    die("PDF Generation Error: " . $e->getMessage());
}

?>
