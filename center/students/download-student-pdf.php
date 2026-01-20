<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['center_id'])) {
    die("Unauthorized Access");
}

$center_id = $_SESSION['center_id'];
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id == 0) {
    die("Invalid Student ID");
}

// Fetch Student Data
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               c.course_name, 
               c.course_code,
               ses.session_name,
               cnt.name as country_name,
               st.name as state_name,
               ct.name as city_name
        FROM students s
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN academic_sessions ses ON s.session_id = ses.id
        LEFT JOIN countries cnt ON s.country_id = cnt.id
        LEFT JOIN states st ON s.state_id = st.id
        LEFT JOIN cities ct ON s.city_id = ct.id
        WHERE s.id = ? AND s.center_id = ?
    ");
    $stmt->execute([$student_id, $center_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found.");
    }

    // Fetch Qualifications
    $stmtQ = $pdo->prepare("SELECT * FROM student_qualifications WHERE student_id = ?");
    $stmtQ->execute([$student_id]);
    $qualifications = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Helpers
function safe($str) {
    return htmlspecialchars($str ?? '-');
}

function get_image_base64($path) {
    if (empty($path)) return '';
    $fullPath = realpath('../../' . $path);
    if ($fullPath && file_exists($fullPath)) {
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

// Setup DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Prepare Data for View
$fullName = trim($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']);
$mobile = $student['mobile'];
$email = $student['email'];
$address = $student['address'] . ', ' . $student['city_name'] . ', ' . $student['state_name'] . ', ' . $student['country_name'] . ' - ' . $student['pincode'];

$photoData = get_image_base64($student['student_image']);
$signData = get_image_base64($student['student_signature']);

// HTML Content
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Helvetica", sans-serif; color: #333; line-height: 1.4; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #115E59; padding-bottom: 15px; margin-bottom: 20px; }
        .institute-name { font-size: 20px; font-weight: bold; color: #115E59; text-transform: uppercase; margin: 0; }
        .sub-header { font-size: 12px; color: #666; margin-top: 3px; }
        
        .profile-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .profile-table td { vertical-align: top; }
        .photo-cell { width: 100px; text-align: center; }
        .photo-img { width: 90px; height: 110px; border: 1px solid #ddd; object-fit: cover; border-radius: 4px; padding: 2px; }
        .info-cell { padding-left: 20px; }
        
        .student-name { font-size: 16px; font-weight: bold; color: #222; margin: 0 0 5px 0; }
        .student-id { font-size: 12px; color: #555; font-weight: bold; }
        
        .section-title { 
            background-color: #f3f4f6; 
            color: #115E59; 
            font-size: 12px; 
            font-weight: bold; 
            padding: 6px 10px; 
            border-left: 4px solid #FCD34D; 
            margin-top: 15px; 
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        table.details { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.details th { text-align: left; width: 30%; color: #555; padding: 5px; border-bottom: 1px solid #eee; font-weight: 600; }
        table.details td { color: #222; padding: 5px; border-bottom: 1px solid #eee; }
        
        .sign-box { text-align: right; margin-top: 30px; }
        .sign-img { height: 35px; }
        
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="institute-name">PACE FOUNDATION</h1>
        <div class="sub-header">Student Profile Report</div>
    </div>

    <table class="profile-table">
        <tr>
            <td class="photo-cell">
                ';
                if($photoData) {
                    $html .= '<img src="'.$photoData.'" class="photo-img">';
                } else {
                    $html .= '<div style="width:90px; height:110px; border:1px solid #ddd; background:#f9f9f9; text-align:center; padding-top:40px; font-size:10px;">No Photo</div>';
                }
$html .= '
            </td>
            <td class="info-cell">
                <h2 class="student-name">'.safe($fullName).'</h2>
                <div class="student-id">Enrollment No: '.safe($student['enrollment_no']).'</div>
                <div style="margin-top: 5px;">
                    Course: <strong>'.safe($student['course_name']).'</strong> ('.safe($student['course_code']).')
                </div>
                <div>
                    Session: '.safe($student['session_name']).' | Mode: '.safe($student['admission_mode']).'
                </div>
                <div style="margin-top: 5px;">
                    Join Date: '.safe(date('d M, Y', strtotime($student['enrollment_date']))).'
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Personal Details</div>
    <table class="details">
        <tr>
            <th>Father\'s Name</th>
            <td>'.safe($student['father_name']).'</td>
            <th>Mother\'s Name</th>
            <td>'.safe($student['mother_name']).'</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>'.safe($student['dob']).'</td>
            <th>Gender</th>
            <td>'.safe($student['gender']).'</td>
        </tr>
        <tr>
            <th>Category</th>
            <td>'.safe($student['category']).'</td>
            <th>Highest Qualification</th>
            <td>'.safe($student['qualification']).'</td>
        </tr>
    </table>

    <div class="section-title">Contact Information</div>
    <table class="details">
        <tr>
            <th>Email Address</th>
            <td>'.safe($email).'</td>
        </tr>
        <tr>
            <th>Mobile Number</th>
            <td>'.safe($mobile).'</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>'.safe($address).'</td>
        </tr>
    </table>
    
    <div class="section-title">Emergency Contact</div>
    <table class="details">
        <tr>
            <th>Contact Person</th>
            <td>'.safe($student['emergency_name']).'</td>
            <th>Relation</th>
            <td>'.safe($student['emergency_relation']).'</td>
        </tr>
        <tr>
            <th>Emergency Mobile</th>
            <td colspan="3">'.safe($student['emergency_mobile']).'</td>
        </tr>
    </table>

    <div class="section-title">Identification & Documents</div>
    <table class="details">
        <tr>
            <th>National ID Type</th>
            <td>'.safe($student['national_id_type']).'</td>
            <th>ID Number</th>
            <td>'.safe($student['national_id_no']).'</td>
        </tr>
    </table>
    ';

    if(!empty($qualifications)) {
        $html .= '<div class="section-title">Submitted Documents</div>
        <table class="details">
            <thead>
                <tr style="background:#f9f9f9;">
                    <th>Document Name</th>
                    <th>Document Number</th>
                </tr>
            </thead>
            <tbody>';
        foreach($qualifications as $q) {
            $html .= '<tr>
                <td>'.safe($q['doc_name']).'</td>
                <td>'.safe($q['doc_number']).'</td>
            </tr>';
        }
        $html .= '</tbody></table>';
    }

    $html .= '
    <div class="sign-box">
        <p>Student Signature:</p>
        ';
        if($signData) {
             $html .= '<img src="'.$signData.'" class="sign-img">';
        } else {
             $html .= '<span>(Not Uploaded)</span>';
        }
    $html .= '
    </div>

    <div class="footer">
        Generated on '.date('d-M-Y h:i A').' | PACE Foundation Center Portal
    </div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('Student_Profile_'.$student['enrollment_no'].'.pdf', ["Attachment" => true]);
?>
