<?php
require_once 'config.php';

echo "<h2>Starting Wallet Database Setup...</h2>";

try {
    // 1. Add wallet_balance column if not exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM centers LIKE 'wallet_balance'");
    if ($colCheck->rowCount() == 0) {
        $pdo->exec("ALTER TABLE centers ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00 AFTER royalty_percentage");
        echo "✅ Added 'wallet_balance' column to 'centers' table.<br>";
    } else {
        echo "ℹ️ 'wallet_balance' column already exists.<br>";
    }

    // 2. Create center_wallet_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS center_wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        transaction_id VARCHAR(100) NOT NULL COMMENT 'Razorpay Payment ID or Custom',
        razorpay_order_id VARCHAR(100) DEFAULT NULL,
        amount_credit DECIMAL(10,2) NOT NULL COMMENT 'The amount added to wallet',
        amount_paid DECIMAL(10,2) NOT NULL COMMENT 'The amount actually paid by center',
        royalty_percentage DECIMAL(5,2) NOT NULL COMMENT 'Royalty at time of tx',
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Checked/Created 'center_wallet_transactions' table.<br>";

    echo "<h3 style='color:green'>Database Setup Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
