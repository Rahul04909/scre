<?php
session_start();
require_once '../../database/config.php';
require_once '../../vendor/autoload.php'; // For Razorpay SDK

use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['center_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $requested_amount = floatval($data['amount']); // The amount to add to wallet

    if ($requested_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }

    try {
        // 1. Fetch Razorpay Keys & Center Royalty
        $settingsStmt = $pdo->query("SELECT * FROM razorpay_settings LIMIT 1");
        $settings = $settingsStmt->fetch();

        $centerStmt = $pdo->prepare("SELECT royalty_percentage, email, mobile, center_name FROM centers WHERE id = ?");
        $centerStmt->execute([$_SESSION['center_id']]);
        $center = $centerStmt->fetch();

        if (!$settings || !$center) {
            throw new Exception("Configuration missing.");
        }

        // 2. Calculate Payable Amount (Based on Royalty)
        $royalty_percent = floatval($center['royalty_percentage']);
        if ($royalty_percent <= 0) {
            // Safety: If 0 royalty, decide logic? For now, assume min 1% or free?
            // Let's assume they must pay full amount or prevent 0 royalty topups if not intended.
            // But per request: "pay as per royalty amount". 
            // If royalty is 0, payable is 0? That implies free wallet credit.
            // Let's enforce a minimum payable or handle gracefully.
            $payable_amount = 0; 
        } else {
            $payable_amount = ($requested_amount * $royalty_percent) / 100;
        }
        
        // Minimum transaction amount for Razorpay is usually ₹1 (100 paise)
        if ($payable_amount < 1) {
             echo json_encode(['success' => false, 'message' => 'Calculated royalty amount is too low for online payment.']);
             exit;
        }

        // 3. Create Razorpay Order
        $api = new Api($settings['key_id'], $settings['key_secret']);
        $orderData = [
            'receipt'         => 'rcptid_' . time() . '_' . $_SESSION['center_id'],
            'amount'          => $payable_amount * 100, // paise
            'currency'        => $settings['currency'],
            'payment_capture' => 1 // Auto capture
        ];
        
        $razorpayOrder = $api->order->create($orderData);
        $razorpayOrderId = $razorpayOrder['id'];

        // 4. Store Preliminary Transaction Info (Using a separate table or session if needed, 
        // strictly we save after success, but keeping track helps. 
        // For simplicity, we create the record on 'verify' but we send needed data back)

        echo json_encode([
            'success' => true,
            'razorpay_order_id' => $razorpayOrderId,
            'payable_amount' => $payable_amount,
            'key_id' => $settings['key_id'],
            'currency' => $settings['currency'],
            'center_name' => $center['center_name'],
            'email' => $center['email'],
            'mobile' => $center['mobile'],
            'royalty_percent' => $royalty_percent // Send back for UI display if needed
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
