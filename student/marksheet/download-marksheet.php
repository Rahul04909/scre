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
    try {
        // Build QR Code using Endroid
        $result = \Endroid\QrCode\Builder\Builder::create()
            ->writer(new \Endroid\QrCode\Writer\PngWriter())
            ->writerOptions([])
            ->data($qrData)
            ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->errorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High)
            ->size(100)
            ->margin(0)
            ->roundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin)
            ->build();
            
        $qrCodeHtml = '<img src="' . $result->getDataUri() . '" alt="QR Code" style="width: 80px; height: 80px;">';
    } catch (Exception $e) {
        $qrCodeHtml = ''; 
    }

    // Styles
    $html = '
    <style>
        body { font-family: freeserif; color: #000; }
        .container { padding: 40px 80px; padding-top: 220px; } /* Increased sidebar padding to decrease width */
        
        .header-overlay {
            position: absolute;
            top: 78px;
            left: 80px; 
            right: 80px; 
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .section-box {
            background-color: #3b5998; color: white;
            padding: 5px; text-align: center;
            font-weight: bold; font-size: 14px;
            margin-bottom: 8px; border-radius: 4px;
        }
        
        .student-details-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
        .student-details-table td { padding: 4px; font-weight: bold; color: #333; }
        .label { color: #0d6efd; width: 140px; }
        
        .marks-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 11px; }
        .marks-table th { 
            background-color: #3b5998; color: white; 
            padding: 6px; border: 1px solid #999; 
        }
        .marks-table td { 
            padding: 6px; border: 1px solid #999; 
            text-align: center; font-weight: bold; 
        }
        
        .summary-table { width: 40%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        .summary-table td { border: 1px solid #999; padding: 4px; font-weight: bold; }
        .summary-header { background-color: #3b5998; color: white; text-align: center; }
        
        .footer { position: absolute; bottom: 80px; left: 50px; right: 50px; }
        .qr-box { width: 100px; height: 100px; border: 1px solid #ddd; }
        .signature-box { text-align: center; width: 150px; float: right; }
        
        /* Specific adjustments to match sample */
        .blue-bar { background-color: #2c64b6; color: white; padding: 5px; text-align: center; font-weight: bold; margin: 10px 0; }
        
        .meta-table td { white-space: nowrap; padding: 0 20px; font-weight: bold; font-size: 14px; }
    </style>
    
    <!-- Absolute Header Meta -->
    <div class="header-overlay">
        <table width="100%" class="meta-table">
            <tr>
                <td align="center">National ID: <span style="font-weight: bold;">'.($student['national_id'] ?? 'N/A').'</span></td>
                <td align="center">Serial No: <span style="font-weight: bold;">SC-'.str_pad($student['id'], 6, '0', STR_PAD_LEFT).'</span></td>
                <td align="center">Enrollment No: <span style="font-weight: bold;">'.$student['enrollment_no'].'</span></td>
            </tr>
        </table>
    </div>
    
    <div class="container">
        
        <!-- Student Details -->
        <div class="blue-bar">STUDENT DETAILS</div>
        
        <table width="100%">
            <tr>
                <td width="75%" valign="top">
                    <!-- Details Left -->
                    <table class="student-details-table">
                        <tr>
                            <td class="label">विद्यार्थी का नाम<br>Student Name:</td><td>'.htmlspecialchars($student['first_name'] . ' ' . $student['last_name']).'</td>
                            <td class="label">पिता का नाम<br>Father\'s Name:</td><td>'.htmlspecialchars($student['father_name']).'</td>
                        </tr>
                        <tr>
                            <td class="label">माँ का नाम<br>Mother\'s Name:</td><td>'.htmlspecialchars($student['mother_name']).'</td>
                            <td class="label">जन्म तिथि<br>Date of Birth:</td><td>'.$dob.'</td>
                        </tr>
                        <tr>
                            <td class="label">पैटर्न<br>Pattern:</td><td>'.ucfirst($student['unit_type'] ?? 'Annual').'</td>
                            <td class="label">लिंग<br>Gender:</td><td>'.ucfirst($student['gender']).'</td>
                        </tr>
                    </table>
                </td>
                <td width="25%" align="right" valign="top">
                    <!-- Photo -->
                    '.($profile_img ? '<img src="'.$profile_img.'" style="width: 100px; height: 120px; border: 1px solid #000; padding: 2px;">' : '').'
                </td>
            </tr>
        </table>
        
        <!-- Course Details -->
        <div class="blue-bar">COURSE DETAILS</div>
        <table class="student-details-table">
            <tr>
                <td class="label">प्रवेश मोड<br>Admission Mode:</td><td>Regular</td>
                <td class="label">सत्र<br>Session:</td><td>'.$student['session_name'].'</td>
            </tr>
            <tr>
                <td class="label">कोर्स का नाम<br>Course Name:</td><td>'.htmlspecialchars($student['course_name']).'</td>
                <td class="label">अवधि<br>Duration:</td><td>'.$student['duration_value'] . ' ' . ucfirst($student['duration_type']).'</td>
            </tr>
            <tr>
                <td class="label">ASC नाम<br>ASC Name:</td><td>'.htmlspecialchars($student['center_name']).'</td>
                <td class="label">ASC कोड<br>ASC Code:</td><td>'.$student['center_code'].'</td>
            </tr>
            <tr>
                <td class="label">ASC पता<br>ASC Address:</td><td colspan="3">'.htmlspecialchars($student['center_address']).'</td>
            </tr>
        </table>
        
        <!-- Marks Table -->
        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" width="6%">Sr. No.</th>
                    <th rowspan="2" width="28%">SUBJECT NAME</th>
                    <th colspan="2">TOTAL MARKS</th>
                    <th colspan="2">OBTAINED MARKS</th>
                    <th rowspan="2" width="12%">TOTAL OBTAINED</th>
                    <th rowspan="2" width="10%">STATUS</th>
                </tr>
                <tr style="font-size: 7px;">
                    <th style="white-space: nowrap;">THEORY</th>
                    <th style="white-space: nowrap;">PRACTICAL</th>
                    <th style="white-space: nowrap;">THEORY</th>
                    <th style="white-space: nowrap;">PRACTICAL</th>
                </tr>
            </thead>
            <tbody>';
            
            $sr_no = 1;
            foreach ($results as $row) {
                // If practical marks not defined in DB, assume 0
                $th_max = $row['theory_marks'];
                $pr_max = $row['practical_marks'] ?? 0;
                
                // Assuming "score" is Theory Obtained if Practical is separate?
                // Or "score" is Total Obtained. 
                // Let's assume for this layout: Score is Theory + Practical. 
                // Since we don't have split in `exam_results`.
                // We will display Score in Total Obtained column, and 0/0 for split for now effectively?
                // OR: Display Score in Theory Obtained and 0 in Practical.
                
                $th_obt = $row['obtained_total'];
                $pr_obt = 0; // Placeholder
                
                $row_total = $th_obt + $pr_obt;
                $status = ($row['result_status'] == 'Pass') ? 'Pass' : 'Fail';
                
                $html .= '
                <tr>
                    <td>'.$sr_no++.'</td>
                    <td align="left" style="text-align:left; padding-left:10px;">'.htmlspecialchars($row['subject_name']).'</td>
                    <td>'.$th_max.'</td>
                    <td>'.$pr_max.'</td>
                    <td>'.$th_obt.'</td>
                    <td>'.$pr_obt.'</td>
                    <td>'.$row_total.'</td>
                    <td>'.strtoupper($status).'</td>
                </tr>';
            }
            
            // Grand Total Row
            $html .= '
                <tr style="background-color: #2c64b6; color: white;">
                    <td colspan="2" align="right" style="padding-right: 10px;">GRAND TOTAL MARKS:</td>
                    <td colspan="2">'.$grand_total_max.'</td>
                    <td colspan="2">GRAND OBTAINED MARKS:</td>
                    <td>'.$grand_total_obt.'</td>
                    <td>'.$overall_status.'</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Footer: Summary & Signatures -->
        <div style="margin-top: 10px;">
            <table width="100%">
                <tr>
                    <td width="40%" valign="top">
                        <table class="summary-table" width="100%">
                            <tr><td class="summary-header" colspan="2">SUMMARY</td></tr>
                            <tr><td>Exam Date</td><td>'.$issue_date.'</td></tr> <!-- Using Issue Date as placeholder -->
                            <tr><td>Result Date</td><td>'.$issue_date.'</td></tr>
                            <tr><td>Date of Issue</td><td>'.$issue_date.'</td></tr>
                            <tr><td>Percentage:</td><td>'.number_format($percentage, 2).'%</td></tr>
                            <tr><td>Grade:</td><td>'.$final_grade.'</td></tr>
                            <tr><td>Overall Status:</td><td>'.$overall_status.'</td></tr>
                        </table>
                    </td>
                    <td width="20%" align="center" valign="middle">
                         '.$qrCodeHtml.'
                    </td>
                    <td width="40%" valign="bottom" align="right">
                         <!-- QR Code Placeholder -->
                         <!-- Implementation of real QR requires a library or API. -->
                         <!-- Mpdf has basic QR support. -->
                         <br><br>
                         <div style="text-align: center;">
                            <br>
                            <b>Authorize<br>Signature</b>
                         </div>
                    </td>
                </tr>
            </table>
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
