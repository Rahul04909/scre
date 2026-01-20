<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

if (!isset($_SESSION['center_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['razorpay_payment_id'], $input['razorpay_order_id'], $input['razorpay_signature'], $input['requested_amount'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
    exit;
}

try {
    // 1. Fetch Razorpay Keys
    $settingsStmt = $pdo->query("SELECT * FROM razorpay_settings LIMIT 1");
    $settings = $settingsStmt->fetch();

    $api = new Api($settings['key_id'], $settings['key_secret']);

    // 2. Verify Signature
    $attributes = [
        'razorpay_order_id' => $input['razorpay_order_id'],
        'razorpay_payment_id' => $input['razorpay_payment_id'],
        'razorpay_signature' => $input['razorpay_signature']
    ];
    $api->utility->verifyPaymentSignature($attributes);

    // 3. Get Payment Details to confirm amount paid
    $payment = $api->payment->fetch($input['razorpay_payment_id']);
    $amount_paid = $payment->amount / 100; // in Rupees
    $requested_amount = floatval($input['requested_amount']);

    // 4. Fetch Center Royalty for Record Keeping
    $stmtCenter = $pdo->prepare("SELECT royalty_percentage FROM centers WHERE id = ?");
    $stmtCenter->execute([$_SESSION['center_id']]);
    $royalty = $stmtCenter->fetchColumn();

    // 5. Database Update Transaction
    $pdo->beginTransaction();

    // A. Insert Transaction
    $stmtTxn = $pdo->prepare("INSERT INTO center_wallet_transactions 
        (center_id, transaction_id, razorpay_order_id, amount_credit, amount_paid, royalty_percentage, status) 
        VALUES (:cid, :txid, :oid, :cred, :paid, :roy, 'success')");
    
    $stmtTxn->execute([
        ':cid' => $_SESSION['center_id'],
        ':txid' => $input['razorpay_payment_id'],
        ':oid' => $input['razorpay_order_id'],
        ':cred' => $requested_amount,
        ':paid' => $amount_paid,
        ':roy' => $royalty
    ]);

    // B. Update Center Balance
    // Using atomic update
    $stmtBal = $pdo->prepare("UPDATE centers SET wallet_balance = wallet_balance + :amt WHERE id = :cid");
    $stmtBal->execute([
        ':amt' => $requested_amount,
        ':cid' => $_SESSION['center_id']
    ]);

    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (SignatureVerificationError $e) {
    echo json_encode(['success' => false, 'message' => 'Signature Verification Failed']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}
?>
