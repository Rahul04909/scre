<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student, Course, Center Info
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

// 2. Fetch Results for Grade Calculation
// We need to calculate the grand total and percentage to determine the grade.
$sqlResults = "
    SELECT er.*, 
           sub.subject_name, sub.total_marks as subject_total,
           er.score as obtained_total
    FROM exam_results er
    JOIN exam_schedules es ON er.exam_schedule_id = es.id
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE er.student_id = ?
";

$stmtRes = $pdo->prepare($sqlResults);
$stmtRes->execute([$student_id]);
$results = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    die("No completed exams found.");
}

// 3. Calculate Totals & Grade
$grand_total_max = 0;
$grand_total_obt = 0;
$is_fail = false;
$last_exam_date = '';

foreach ($results as $row) {
    $grand_total_max += $row['subject_total'];
    $grand_total_obt += $row['obtained_total'];
    if ($row['result_status'] !== 'Pass') {
        $is_fail = true;
    }
    // Track latest exam date? (Optional, if needed for 'Exam Month')
    // We don't have exam date in results easily without joining schedule.
    // Assuming 'Issue Date' is today.
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

// 4. Prepare Data for Display
$name = strtoupper($student['first_name'] . ' ' . $student['last_name']);
$father_name = strtoupper($student['father_name']);
$enrollment_no = $student['enrollment_no'];
$session = $student['session_name']; 
if (!empty($student['start_year']) && !empty($student['end_year'])) {
    $session = $student['start_month'] . ' ' . $student['start_year'] . ' - ' . $student['end_month'] . ' ' . $student['end_year'];
}
$dob = date('d-m-Y', strtotime($student['dob']));
$course_name = strtoupper($student['course_name']);
$center_name = strtoupper($student['center_name']);
$duration = $student['duration_value'] . ' ' . ucfirst($student['duration_type']);
$issue_date = date('d-m-Y');
$exam_month = date('M Y'); // Default to current month/year or static
$grade = $final_grade;


// 5. Generate PDF
try {
    // mPDF Configuration
    // A4 Landscape is common for certificates, but 'background.png' orientation dictates it.
    // Assuming Landscape based on typical certificates. If Portrait, change 'L' to 'P'.
    // Let's assume Landscape for now.
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => 'A4-L', // Landscape
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'default_font' => 'arial'
    ]);

    // Background Image
    // Using the specifically requested massive file.
    // WARNING: 70MB file might be slow.
    $bg_path = __DIR__ . '/background/background-img.png'; 
    
    if (file_exists($bg_path)) {
        $mpdf->SetDefaultBodyCSS('background', "url('{$bg_path}')");
        $mpdf->SetDefaultBodyCSS('background-image-resize', 6); // 6 = full page fit
    } else {
        die("Certificate background not found.");
    }

    // Styles & Content
    // Positions need to be calibrated. I'll use absolute positioning placeholders.
    // User needs to verify positions.
     
    $html = '
    <style>
        body { font-family: arial; color: #1a1a1a; font-weight: bold; font-size: 18px; }
        .data-overlay { position: absolute; }
        
        /* Adjusted coordinates based on background lines */
        /* Distance between lines approx 40px - 45px */
        
        /* Line 1: Certify that Mr./Miss/Mrs... */
        .student-name { top: 305px; left: 380px; font-size: 22px; color: #000; text-transform: uppercase; }
        
        /* Line 2: Son of/Daughter of... */
        .father-name { top: 348px; left: 380px; text-transform: uppercase; }
        
        /* Line 3: Registration No... */
        .enrollment { top: 390px; left: 380px; }
        
        /* Line 4: Session... to... */
        .session { top: 432px; left: 280px; }
        .session-start { top: 432px; left: 260px; } 
        .session-end { top: 432px; left: 700px; } 

        /* Line 5: Date of Birth... */
        .dob { top: 475px; left: 380px; }

        /* Line 6: In the course... (Subject/Course) */
        .course-name { top: 518px; left: 380px; }
        
        /* Line 7: Appeared from our ASC... */
        .center-name { top: 560px; left: 450px; width: 600px; }
        
        /* Line 8: Duration of... */
        .duration { top: 602px; left: 380px; }

        /* Line 9: final Examination held in... */
        .exam-month { top: 635px; left: 580px; }
        
        /* Line 10: Obtained marks... Out of... */
        .marks-obt { top: 670px; left: 420px; }
        .marks-max { top: 670px; left: 750px; }
        
        /* Line 11: with Grade... */
        .grade { top: 705px; left: 380px; }
        
        /* Line 12: Date of Issue... */
        .issue-date { top: 760px; left: 280px; }

        /* Scanned QR or extra reg no if needed */
        .top-serial { top: 150px; left: 850px; font-size: 14px; color: #666; }
        
    </style>
    
    <!-- Top Serial/Ref -->
    <div class="data-overlay top-serial">Reg: ' . $enrollment_no . '</div>

    <!-- Line 1 -->
    <div class="data-overlay student-name">' . $name . '</div>

    <!-- Line 2 -->
    <div class="data-overlay father-name">' . $father_name . '</div>
    
    <!-- Line 3 -->
    <div class="data-overlay enrollment">' . $enrollment_no . '</div>

    <!-- Line 4 - Session split logic -->';
    
    if (!empty($student['start_month']) && !empty($student['end_month'])) {
        $html .= '
        <div class="data-overlay session-start">' . $student['start_month'] . ' ' . $student['start_year'] . '</div>
        <div class="data-overlay session-end">' . $student['end_month'] . ' ' . $student['end_year'] . '</div>';
    } else {
        $html .= '<div class="data-overlay session">' . $session . '</div>';
    }
    
    $html .= '
    <!-- Line 5 -->
    <div class="data-overlay dob">' . $dob . '</div>

    <!-- Line 6 -->
    <div class="data-overlay course-name">' . $course_name . '</div>
    
     <!-- Line 7 -->
    <div class="data-overlay center-name">' . $center_name . '</div>

    <!-- Line 8 -->
    <div class="data-overlay duration">' . $duration . '</div>

    <!-- Line 9 -->
    <div class="data-overlay exam-month">' . $exam_month . '</div>
    
    <!-- Line 10 -->
    <div class="data-overlay marks-obt">' . $grand_total_obt . '</div>
    <div class="data-overlay marks-max">' . $grand_total_max . '</div>

    <!-- Line 11 -->
    <div class="data-overlay grade">' . $grade . '</div>
    
    <!-- Line 12 -->
    <div class="data-overlay issue-date">' . $issue_date . '</div>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('Certificate.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    die("PDF Generation Error: " . $e->getMessage());
}
?>
