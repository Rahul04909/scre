<?php
session_start();
require_once '../database/config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

if (!isset($_SESSION['student_id'])) {
    die("Access Denied");
}

if (!isset($_GET['txn_id'])) {
    die("Invalid Request");
}

$student_id = $_SESSION['student_id'];
$txn_id = intval($_GET['txn_id']);

// 1. Fetch Transaction & Student Details
$stmt = $pdo->prepare("
    SELECT sf.*, s.first_name, s.last_name, s.enrollment_no, s.father_name, 
           c.course_name, cen.center_name, cen.address as center_address
    FROM student_fees sf
    JOIN students s ON sf.student_id = s.id
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN centers cen ON s.center_id = cen.id
    WHERE sf.id = ? AND sf.student_id = ?
");
$stmt->execute([$txn_id, $student_id]);
$txn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$txn) {
    die("Receipt not found or access denied.");
}

// 2. Prepare Signatory Image (Removed as per request)
$mergedImageHtml = '';


// 3. HTML Content
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00008B; padding-bottom: 10px; }
        .company-name { font-size: 22px; font-weight: bold; color: #00008B; text-transform: uppercase; margin-bottom: 5px; }
        .receipt-title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; text-decoration: underline; background: #eee; padding: 5px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .label { font-weight: bold; width: 150px; color: #555; }
        
        .amount-table { width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #000; }
        .amount-table th { background-color: #f2f2f2; border: 1px solid #000; padding: 10px; text-align: left; }
        .amount-table td { border: 1px solid #000; padding: 10px; }
        
        .footer { margin-top: 50px; text-align: right; }
        .sig-block { display: inline-block; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">SIR CHHOTU RAM EDUCATION PVT. LTD.</div>
        <div>' . htmlspecialchars($txn['center_name']) . '</div>
        <div style="font-size: 11px;">' . htmlspecialchars($txn['center_address']) . '</div>
    </div>

    <div class="receipt-title">FEE RECEIPT</div>

    <table class="info-table">
        <tr>
            <td class="label">Receipt No:</td>
            <td>#' . str_pad($txn['id'], 6, '0', STR_PAD_LEFT) . '</td>
            <td class="label">Date:</td>
            <td>' . date('d-M-Y', strtotime($txn['payment_date'])) . '</td>
        </tr>
        <tr>
            <td class="label">Student Name:</td>
            <td>' . htmlspecialchars($txn['first_name'] . ' ' . $txn['last_name']) . '</td>
            <td class="label">Enrollment No:</td>
            <td>' . htmlspecialchars($txn['enrollment_no']) . '</td>
        </tr>
        <tr>
            <td class="label">Father Name:</td>
            <td>' . htmlspecialchars($txn['father_name']) . '</td>
            <td class="label">Course:</td>
            <td>' . htmlspecialchars($txn['course_name']) . '</td>
        </tr>
    </table>

    <table class="amount-table">
        <thead>
            <tr>
                <th>Description</th>
                <th width="150" style="text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Course Fee Payment</strong><br>
                    <small>via ' . htmlspecialchars($txn['payment_mode']) . '</small>
                    ' . ($txn['transaction_id'] ? '<br><small>Txn ID: ' . htmlspecialchars($txn['transaction_id']) . '</small>' : '') . '
                    ' . ($txn['remarks'] ? '<br><small>Remarks: ' . htmlspecialchars($txn['remarks']) . '</small>' : '') . '
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <strong>' . number_format($txn['amount'], 2) . '</strong>
                </td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="text-align: right;"><strong>Total Paid:</strong></td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;">
                    ₹ ' . number_format($txn['amount'], 2) . '
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-block">
            <!-- Authorized Signatory Removed -->
        </div>
    </div>
    
    <div style="position: absolute; bottom: 20px; width: 100%; text-align: center; font-size: 10px; color: #999;">
        This is a computer-generated receipt.
    </div>
</body>
</html>';

try {
    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A5', 'orientation' => 'L']); // Landscape A5 for receipts usually
    $mpdf->WriteHTML($html);
    $mpdf->Output('Receipt_' . $txn['id'] . '.pdf', 'I');
} catch (\Mpdf\MpdfException $e) {
    die($e->getMessage());
}
?>
