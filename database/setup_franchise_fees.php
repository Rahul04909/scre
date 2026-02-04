<?php
require_once 'config.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS center_franchise_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE NOT NULL,
        payment_mode VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100) DEFAULT NULL,
        remarks TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'center_franchise_payments' created successfully or already exists.<br>";
    
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
