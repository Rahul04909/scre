<?php

// Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Mpdf\Mpdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow; // Updated for v5/v6 if needed, checking standard
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

// Ensure correct ErrorCorrectionLevel class usage based on version.
// v4/v5 uses ErrorCorrectionLevelHigh::class usually.
// Let's assume v4+ standard.

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

$student_id = $_SESSION['student_id'];

// --- 1. Fetch Data ---
$stmtStudent = $pdo->prepare("
    SELECT s.*, 
           c.course_name, c.course_code, c.duration_value, c.duration_type,
           ac.session_name,
           cen.center_name, cen.center_code, cen.address as center_address,
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

// --- 2. Image Helpers ---
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return ''; 
}

// Prepare Images
$bgPath = __DIR__ . '/background/school-id-card.png';
$studentPhotoPath = '../../' . $student['student_image'];
$studentSignPath  = '../../' . $student['student_signature'];

$bgImage = getBase64Image($bgPath);
$profileImg = getBase64Image($studentPhotoPath);
$signImg = getBase64Image($studentSignPath);

// --- 3. QR Code Generation ---
// Using Endroid QR Code
// Content: URL to verify or JSON data
$qrContent = "Student: " . $student['first_name'] . "\nEnrollment: " . $student['enrollment_no']; 
// Or a verification link: https://pacefoundation.com/verify?id=...

try {
    $result = Builder::create()
        ->writer(new PngWriter())
        ->writerOptions([])
        ->data($qrContent)
        ->encoding(new Encoding('UTF-8'))
        ->size(150)
        ->margin(0)
        ->build();
    $qrImage = $result->getDataUri();
} catch (Exception $e) {
    $qrImage = '';
}

// --- 4. HTML Layout ---
// ID Card Standard Size: 86mm x 54mm (Landscape)
// We will use pixels or mm for positioning in mPDF.
// mPDF @page size will be set to [86, 54].
// Margins 0.

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: freeserif;
            font-size: 8pt;
            color: #000;
        }
        /* Background set via body or mPDF method */
        
        .field-label { 
            font-weight: bold; 
            width: 110px; 
            display: inline-block;
            font-size: 8pt;
        }
        .field-value {
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }
        
        /* Positioning based on IGNOU Sample roughly */
        .content-area {
            position: absolute;
            top: 50px; /* Adjust based on header height in background image */
            left: 5px;
            width: 70%; /* Left side content */
        }
        
        .photo-area {
            position: absolute;
            top: 55px; /* Adjust */
            right: 5px;
            width: 25%;
            text-align: center;
        }
        
        .qr-area {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 18mm; 
            height: 18mm;
        }
        
        .row-item { margin-bottom: 2px; }
        
        .enroll-no {
            position: absolute;
            top: 104px;
            right: 8px;
            font-weight: bold;
            font-size: 10pt;
            background: white; /* overlay photo slightly if needed or below */
            z-index: 10;
        }
        
        .signature-area {
            position: absolute;
            bottom: 25px; /* Adjust */
            right: 5px;
            width: 25%;
            text-align: center;
        }

        .auth-sig {
             position: absolute;
             bottom: 5px;
             right: 5px;
             font-size: 6pt;
             text-align: center;
             width: 50px;
        }
        
        /* Specific tweaks for "IGNOU" style layout */
        table { border-collapse: collapse; width: 100%; }
        td { padding: 1px 2px; vertical-align: top; }
    </style>
</head>
<body>

    <!-- QR Code Top Right -->
    <div class="qr-area">
        <img src="' . $qrImage . '" style="width: 100%; height: auto;">
    </div>

    <!-- Main Content Table (Left Side) -->
    <div class="content-area">
        <table>
            <tr>
                <td width="90" class="field-label">Enrolment Number :</td>
                <td class="field-value">' . $student['enrollment_no'] . '</td>
            </tr>
            <tr>
                <td class="field-label">RC Code :</td>
                <td class="field-value">' . $student['center_code'] . ': ' . $student['center_name'] . '</td>
            </tr>
            <tr>
                <td class="field-label">Name of the Programme :</td>
                <td class="field-value">' . $student['course_code'] . ' : ' . $student['course_name'] . '</td>
            </tr>
            <tr>
                <td class="field-label">Name :</td>
                <td class="field-value">' . $student['first_name'] . ' ' . $student['last_name'] . '</td>
            </tr>
            <tr>
                <td class="field-label">Guardian\'s Name :</td>
                <td class="field-value">' . $student['father_name'] . '</td>
            </tr>
            <tr>
                <td class="field-label">Address :</td>
                <td class="field-value" style="font-size: 7pt;">' . 
                    $student['address'] . ' ' . $student['city_name'] . ', ' . $student['state_name'] . 
                '</td>
            </tr>
            <tr>
                <td class="field-label">Pin Code :</td>
                <td class="field-value">' . $student['pincode'] . '</td>
            </tr>
        </table>
    </div>

    <!-- Student Photo -->
    <div class="photo-area">
        ' . ($profileImg ? '<img src="' . $profileImg . '" style="width: 22mm; height: 26mm; border: 1px solid #000;">' : '') . '
    </div>
    
    <!-- Enrollment Overlay under photo (IGNOU style) -->
    <div class="enroll-no">
        ' . $student['enrollment_no'] . '
    </div>

    <!-- Signature -->
    <div class="signature-area">
       ' . ($signImg ? '<img src="' . $signImg . '" style="width: 30mm; height: 10mm; object-fit: contain;">' : '') . '
    </div>
    
</body>
</html>
';

// --- 5. Generate PDF ---
try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => [86, 54], // ID Card Size
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'default_font' => 'freeserif'
    ]);

    // Set Background
    if ($bgImage) {
        $mpdf->SetDefaultBodyCSS('background', "url('" . $bgImage . "')");
        // No resize needed if image is exactly sized, otherwise 'cover' or '100% 100%'
        $mpdf->SetDefaultBodyCSS('background-image-resize', 6); 
    }

    $mpdf->WriteHTML($html);
    $mpdf->Output('ID_Card_' . $student['enrollment_no'] . '.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    die("PDF Error: " . $e->getMessage());
}
?>
