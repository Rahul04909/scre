<?php
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Center ID");
}

$center_id = intval($_GET['id']);

// Fetch Center Data (Reusing similar logic as view-center.php for consistency)
try {
    $stmt = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
    $stmt->execute([$center_id]);
    $center = $stmt->fetch();
    if (!$center) die("Center not found.");
    
    // Helper locations
    $country_name = ''; $state_name = ''; $city_name = '';
    if($center['country']) {
        $stmt = $pdo->prepare("SELECT name FROM countries WHERE id = ?");
        $stmt->execute([$center['country']]);
        $country_name = $stmt->fetchColumn();
    }
    if($center['state']) {
        $stmt = $pdo->prepare("SELECT name FROM states WHERE id = ?");
        $stmt->execute([$center['state']]);
        $state_name = $stmt->fetchColumn();
    }
    if($center['city']) {
        $stmt = $pdo->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->execute([$center['city']]);
        $city_name = $stmt->fetchColumn();
    }
    
} catch (PDOException $e) { die("DB Error"); }

// Safe Output Helper
function safe_output($value) {
    if ($value === null) return '-';
    $valStr = (string)$value;
    if (strpos($valStr, 'xdebug-error') !== false || stripos($valStr, '<br />') === 0) return '-';
    return htmlspecialchars($valStr) ?: '-';
}

// Image Helper (Convert relative path to absolute for DOMPDF)
function get_image_path($path) {
    if (empty($path)) return '';
    $fullPath = realpath('../../' . $path);
    if ($fullPath && file_exists($fullPath)) {
        return $fullPath;
    }
    return '';
}

// Initialize DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Allow remote images if needed, but we use local paths
$dompdf = new Dompdf($options);

// HTML Construction
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Helvetica", sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { color: #0056b3; margin-top: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .section { margin-bottom: 15px; }
        .section-title { background-color: #f0f8ff; padding: 5px; font-weight: bold; border-left: 5px solid #0056b3; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f9f9f9; width: 30%; font-weight: bold; }
        .img-box { text-align: center; margin: 10px 0; }
        .img-box img { max-height: 100px; border: 1px solid #999; }
        .row:after { content: ""; display: table; clear: both; }
        .col { float: left; width: 50%; }
    </style>
</head>
<body>

    <div class="header">
        <h1>' . safe_output($center['center_name']) . '</h1>
        <p>Center Code: ' . safe_output($center['center_code']) . ' | Owner: ' . safe_output($center['owner_name']) . '</p>
    </div>

    <div class="section">
        <div class="section-title">Basic Details</div>
        <table>
            <tr>
                <th>Email</th><td>' . safe_output($center['email']) . '</td>
                <th>Mobile</th><td>' . safe_output($center['mobile']) . '</td>
            </tr>
            <tr>
                <th>Address</th><td colspan="3">
                    ' . safe_output($center['address']) . '<br>
                    ' . safe_output($city_name) . ', ' . safe_output($state_name) . ', ' . safe_output($country_name) . ' - ' . safe_output($center['pincode']) . '
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Infrastructure & Operations</div>
        <table>
            <tr>
                <th>Computers</th><td>' . safe_output($center['num_computers']) . '</td>
                <th>Lab Type</th><td>' . safe_output($center['lab_type']) . '</td>
            </tr>
            <tr>
                <th>Classrooms</th><td>' . safe_output($center['num_classrooms']) . '</td>
                <th>Staff</th><td>' . safe_output($center['num_staff']) . '</td>
            </tr>
            <tr>
                <th>Internet</th><td>' . safe_output($center['internet_avail']) . '</td>
                <th>Power Backup</th><td>' . safe_output($center['power_backup']) . '</td>
            </tr>
            <tr>
                <th>Working Days</th><td colspan="3">' . safe_output($center['weekdays']) . ' (Closed: ' . safe_output($center['weekend_off']) . ')</td>
            </tr>
            <tr>
                <th>Timings</th><td colspan="3">' . safe_output($center['opening_time']) . ' - ' . safe_output($center['closing_time']) . '</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Financials & Bank Details</div>
        <table>
            <tr>
                <th>Franchise Fee</th><td>' . safe_output($center['franchise_fee']) . '</td>
                <th>Royalty %</th><td>' . safe_output($center['royalty_percentage']) . '</td>
            </tr>
            <tr>
                <th>Bank Name</th><td>' . safe_output($center['bank_name']) . '</td>
                <th>Account No</th><td>' . safe_output($center['account_no']) . '</td>
            </tr>
            <tr>
                <th>IFSC Code</th><td>' . safe_output($center['ifsc_code']) . '</td>
                <th>Holder Name</th><td>' . safe_output($center['account_holder']) . '</td>
            </tr>
            <tr>
                <th>Branch</th><td colspan="3">' . safe_output($center['branch_address']) . '</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Official Documents & Images</div>
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; text-align: center;">
                    <Strong>Owner Image</Strong><br>';
                    $oBtn = get_image_path($center['owner_image']);
                    if($oBtn) $html .= '<img src="'.$oBtn.'" style="max-height:80px;">';
                    else $html .= '-';
$html .= '      </td>
                <td style="border: none; text-align: center;">
                    <Strong>Owner Sign</Strong><br>';
                    $oSig = get_image_path($center['owner_sign']);
                    if($oSig) $html .= '<img src="'.$oSig.'" style="max-height:50px;">';
                    else $html .= '-';
$html .= '      </td>
                <td style="border: none; text-align: center;">
                    <Strong>Center Stamp</Strong><br>';
                    $cStp = get_image_path($center['center_stamp']);
                    if($cStp) $html .= '<img src="'.$cStp.'" style="max-height:80px;">';
                    else $html .= '-';
$html .= '      </td>
                <td style="border: none; text-align: center;">
                    <Strong>Logo</Strong><br>';
                    $cLogo = get_image_path($center['logo_image']);
                    if($cLogo) $html .= '<img src="'.$cLogo.'" style="max-height:80px;">';
                    else $html .= '-';
$html .= '      </td>
            </tr>
        </table>
    </div>
    
    <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #777;">
        <p>Generated on ' . date('d-M-Y H:i A') . ' | PACE Foundation Admin Portal</p>
    </div>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output
$dompdf->stream('Center_Details_' . $center_id . '.pdf', ["Attachment" => true]);
