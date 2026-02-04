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
    $bg_path = __DIR__ . '/background/background.png'; 
    
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
        body { font-family: arial; color: #000; font-weight: bold; font-size: 16px; }
        .data-overlay { position: absolute; }
        
        /* ADJUST THESE TOP/LEFT VALUES BASED ON THE BACKGROUND IMAGE LAYOUT */
        /* These are guesstimates for a standard landscape certificate */
        
        .enrollment { top: 120px; left: 850px; } /* Top Right */
        
        .student-name { top: 280px; left: 400px; font-size: 24px; color: #2c3e50; }
        
        .father-name { top: 330px; left: 400px; }
        
        .dob { top: 330px; left: 800px; }
        
        .course-name { top: 380px; left: 400px; }
        
        .duration { top: 380px; left: 850px; }
        
        .center-name { top: 430px; left: 400px; width: 600px; }
        
        .session { top: 480px; left: 400px; }
        
        .grade { top: 530px; left: 400px; font-size: 18px; }
        
        .exam-month { top: 530px; left: 600px; }
        
        .issue-date { top: 650px; left: 200px; } /* Bottom Left */
        
    </style>
    
    <!-- Enrollment No -->
    <div class="data-overlay enrollment">'.$enrollment_no.'</div>

    <!-- Name -->
    <div class="data-overlay student-name">'.$name.'</div>

    <!-- Father Name -->
    <div class="data-overlay father-name">'.$father_name.'</div>
    
    <!-- DOB -->
    <div class="data-overlay dob">'.$dob.'</div>

    <!-- Course -->
    <div class="data-overlay course-name">'.$course_name.'</div>
    
    <!-- Duration -->
    <div class="data-overlay duration">'.$duration.'</div>

    <!-- Center -->
    <div class="data-overlay center-name">'.$center_name.'</div>

    <!-- Session -->
    <div class="data-overlay session">'.$session.'</div>
    
    <!-- Grade -->
    <div class="data-overlay grade">'.$grade.'</div>
    
    <!-- Exam Month -->
    <div class="data-overlay exam-month">'.$exam_month.'</div>

    <!-- Date of Issue -->
    <div class="data-overlay issue-date">'.$issue_date.'</div>
    ';

    $mpdf->WriteHTML($html);
    $mpdf->Output('Certificate.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    die("PDF Generation Error: " . $e->getMessage());
}
?>
