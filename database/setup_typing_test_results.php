<?php
require_once 'config.php';

try {
    // Remove FKs due to MyISAM engine in students table
    $sql = "CREATE TABLE IF NOT EXISTS typing_test_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        test_id INT DEFAULT NULL,
        wpm DECIMAL(5,2) DEFAULT 0.00,
        accuracy DECIMAL(5,2) DEFAULT 0.00,
        errors INT DEFAULT 0,
        total_words INT DEFAULT 0,
        result_json JSON DEFAULT NULL,
        student_input LONGTEXT DEFAULT NULL,
        duration_seconds INT NOT NULL,
        test_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (test_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'typing_test_results' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
