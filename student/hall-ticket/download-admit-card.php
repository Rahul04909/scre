<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php'; // Adjust path if vendor is elsewhere

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$student_id = $_SESSION['student_id'];

// --- 1. Fetch Data ---
$stmtStudent = $pdo->prepare("
    SELECT s.*, 
           c.course_name, 
           ac.session_name,
           cen.center_name,
           co.name as country_name, st.name as state_name, ci.name as city_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    LEFT JOIN centers cen ON s.center_id = cen.id
    LEFT JOIN countries co ON s.country_id = co.id
    LEFT JOIN states st ON s.state_id = st.id
    LEFT JOIN cities ci ON s.city_id = ci.id
    WHERE s.id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) die("Student not found");

// Exams
$sqlExams = "
    SELECT es.*, sub.subject_name, sub.theory_marks, sub.practical_marks, sub.exam_duration
    FROM exam_schedules es
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE es.course_id = ? AND es.session_id = ?
";
$params = [$student['course_id'], $student['session_id']];

if (isset($_GET['unit'])) {
    $unit = intval($_GET['unit']);
    $sqlExams .= " AND sub.unit_no = ?";
    $params[] = $unit;
}

$sqlExams .= " ORDER BY es.exam_date ASC, es.start_time ASC";

$stmtExams = $pdo->prepare($sqlExams);
$stmtExams->execute($params);
$exams = $stmtExams->fetchAll(PDO::FETCH_ASSOC);

// --- 2. Image Handling ---
// Helper to get base64
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return ''; // Handle missing image gracefully or return placeholder
}

// Prepare Images
$bgImage = getBase64Image(__DIR__ . '/background/admit-card-background.jpg'); // Adjust if needed
$studentPhoto = getBase64Image('../../' . $student['student_image']);
$studentSign = getBase64Image('../../' . $student['student_signature']);

// --- 3. HTML Layout ---
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #000;
        }
        @page {
            margin: 0px; 
            size: A4;
        }
        .bg-watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
            opacity: 1; /* Adjust opacity in image itself if possible, or here */
        }
        .container {
            padding: 40px;
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #00008B; /* Dark Blue */
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .subtext {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }
        .govt-text {
            font-size: 11px;
            font-weight: bold;
            color: #d32f2f; /* Red-ish */
            margin-top: 5px;
        }
        
        .admit-card-title {
            text-align: center;
            background-color: #00008B;
            color: #fff;
            padding: 5px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }

        /* Student Details Section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .details-table td, .details-table th {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .details-label {
            width: 130px;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .photo-cell {
            text-align: center;
            vertical-align: middle;
            width: 25%;
        }

        /* Exam Table */
        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 11px;
        }
        .exam-table th, .exam-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .exam-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        /* Footer */
        .footer-sig {
            text-align: right;
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .instructions {
            border: 1px solid #000;
            padding: 15px;
            font-size: 10px;
            line-height: 1.5;
            background: #fff;
        }
        .instructions h4 {
            margin: 0 0 5px 0;
            text-decoration: underline;
        }
        ul { margin: 0; padding-left: 20px; }
    </style>
</head>
<body>
    ' . ($bgImage ? '<img src="' . $bgImage . '" class="bg-watermark">' : '') . '
    
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">SIR CHHOTU RAM EDUCATION PVT. LTD.</div>
            <div class="subtext">AN ISO 9001-2015 Certified Organization</div>
            <div class="subtext">Registred Under The Company ACT 2013 By The</div>
            <div class="subtext">Ministry Of Corporate Affairs,</div>
            <div class="govt-text">Ministry of Micro, Small & Medium Enterprises, Goverment Of India</div>
        </div>

        <div class="admit-card-title">Hall Ticket / Admit Card</div>

        <!-- Student Details Table (Bordered) -->
        <table class="details-table">
            <tr>
                <td class="details-label">पंजीक्रम/Enrollment No</td>
                <td style="font-weight: bold;">' . $student['enrollment_no'] . '</td>
                <td class="photo-cell" rowspan="6">
                    ' . ($studentPhoto ? '<img src="' . $studentPhoto . '" style="width: 100px; height: 120px; object-fit: cover; border: 1px solid #000; padding: 2px;">' : 'No Photo') . '
                </td>
            </tr>
            <tr>
                <td class="details-label">उम्मीदवारका नाम/Candidate Name</td>
                <td style="text-transform: uppercase;">' . $student['first_name'] . ' ' . $student['last_name'] . '</td>
            </tr>
            <tr>
                <td class="details-label">पिताका नाम/Father Name</td>
                <td style="text-transform: uppercase;">' . $student['father_name'] . '</td>
            </tr>
            <tr>
                <td class="details-label">आमाका नाम/Mother Name</td>
                <td style="text-transform: uppercase;">' . $student['mother_name'] . '</td>
            </tr>
            <tr>
                <td class="details-label">कोर्स नाम/Course Name</td>
                <td>' . $student['course_name'] . '</td>
            </tr>
            <tr>
                <td class="details-label">सत्र/Session</td>
                <td>' . $student['session_name'] . '</td>
            </tr>
            <tr>
                <td class="details-label">सेंटर नाम/Center Name</td>
                <td>' . $student['center_name'] . '</td>
                <td class="photo-cell">
                    ' . ($studentSign ? '<img src="' . $studentSign . '" style="width: 100px; height: 40px; object-fit: contain;">' : 'Signature') . '
                </td>
            </tr>
            <tr>
                <td class="details-label">मोबाइल नंबर/Mobile No</td>
                <td colspan="2">' . $student['mobile'] . '</td>
            </tr>
        </table>

        <!-- Exam Schedule -->
        <table class="exam-table">
            <thead>
                <tr>
                    <th width="5%">Sr.</th>
                    <th width="30%">Subject Name</th>
                    <th>Theory</th>
                    <th>Practical</th>
                    <th>Total</th>
                    <th>Exam Date</th>
                    <th>Exam Time</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>';
                if (count($exams) > 0) {
                    $i = 1;
                    foreach ($exams as $ex) {
                        $total = floatval($ex['theory_marks']) + floatval($ex['practical_marks']);
                        $html .= '<tr>
                            <td>' . $i++ . '</td>
                            <td style="text-align: left;">' . $ex['subject_name'] . '</td>
                            <td>' . floatval($ex['theory_marks']) . '</td>
                            <td>' . floatval($ex['practical_marks']) . '</td>
                            <td>' . $total . '</td>
                            <td>' . date('d-m-Y', strtotime($ex['exam_date'])) . '</td>
                            <td>' . date('h:i A', strtotime($ex['start_time'])) . '</td>
                            <td>' . $ex['exam_duration'] . ' Min</td>
                        </tr>';
                    }
                } else {
                    $html .= '<tr><td colspan="8">No Exam Schedule Found</td></tr>';
                }
$html .= '  </tbody>
        </table>

        <!-- Signatory -->
        <div class="footer-sig">
            <p>Authorized Signatory</p>
            <br>
            <p style="font-size: 10px; font-weight: normal;">(Controller of Examination)</p>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h4>Important Instructions for Online/Offline Examination:</h4>
            <ul>
                <li>The Hall Ticket is a mandatory document. No candidate will be allowed to enter the exam hall without it.</li>
                <li>Please verify all your details printed on this Admit Card. In case of any discrepancy, contact your center immediately.</li>
                <li>Reach the examination center at least 30 minutes before the scheduled time.</li>
                <li>Electronic gadgets like mobile phones, smartwatches, etc., are strictly prohibited inside the exam hall.</li>
                <li>Maintain silence and discipline during the examination.</li>
                <li>Use of unfair means will lead to disqualification.</li>
            </ul>
        </div>
    </div>
</body>
</html>';

// --- 4. Render PDF ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Vital for images

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("Admit_Card_" . $student['enrollment_no'] . ".pdf", ["Attachment" => 0]); // 0 = PReview, 1 = Download
?>
