<?php
require_once '../database/config.php';

try {
    // Create 'student_fees' table
    $sql = "CREATE TABLE IF NOT EXISTS student_fees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        center_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_mode VARCHAR(50) NOT NULL COMMENT 'Cash, UPI, Bank Transfer',
        transaction_id VARCHAR(100) DEFAULT NULL,
        payment_date DATE NOT NULL,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Table 'student_fees' created successfully.";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
