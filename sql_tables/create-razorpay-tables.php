<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create razorpay_settings table
    $sqlSettings = "CREATE TABLE IF NOT EXISTS razorpay_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        key_id VARCHAR(255) NOT NULL,
        key_secret VARCHAR(255) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlSettings);
    echo "Table 'razorpay_settings' created successfully.<br>";

    // Create razorpay_payments table for logging test/live payments
    $sqlPayments = "CREATE TABLE IF NOT EXISTS razorpay_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_id VARCHAR(255) NOT NULL,
        order_id VARCHAR(255),
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        status VARCHAR(50),
        method VARCHAR(50),
        email VARCHAR(255),
        contact VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlPayments);
    echo "Table 'razorpay_payments' created successfully.<br>";

    // Insert default empty row for settings if not exists
    $checkSql = "SELECT COUNT(*) FROM razorpay_settings";
    $stmt = $pdo->query($checkSql);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $insertSql = "INSERT INTO razorpay_settings (key_id, key_secret, currency) VALUES ('', '', 'INR')";
        $pdo->exec($insertSql);
        echo "Default configuration row inserted.<br>";
    } else {
        echo "Configuration row already exists.<br>";
    }

} catch (PDOException $e) {
    die("ERROR: Could not create tables. " . $e->getMessage());
}
?>
