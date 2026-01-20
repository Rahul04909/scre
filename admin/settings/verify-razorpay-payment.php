<?php
require_once '../../database/config.php';
require '../../vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['razorpay_payment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Payment ID missing']);
    exit;
}

try {
    // 1. Fetch Key ID and Secret from DB
    $stmt = $pdo->query("SELECT * FROM razorpay_settings LIMIT 1");
    $settings = $stmt->fetch();

    if (!$settings || empty($settings['key_id']) || empty($settings['key_secret'])) {
        throw new Exception("Razorpay not configured in settings.");
    }

    $api = new Api($settings['key_id'], $settings['key_secret']);

    // 2. Fetch Payment Details from Razorpay API
    $payment = $api->payment->fetch($input['razorpay_payment_id']);

    // 3. Log into Database
    $sql = "INSERT INTO razorpay_payments (payment_id, order_id, amount, currency, status, method, email, contact) VALUES (:pid, :oid, :amt, :curr, :status, :method, :email, :contact)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':pid' => $payment->id,
        ':oid' => $payment->order_id, // Might be null for direct payments
        ':amt' => ($payment->amount / 100), // Convert paise to rupees
        ':curr' => $payment->currency,
        ':status' => $payment->status,
        ':method' => $payment->method,
        ':email' => $payment->email,
        ':contact' => $payment->contact
    ]);

    echo json_encode(['success' => true, 'message' => 'Payment verified and logged successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
