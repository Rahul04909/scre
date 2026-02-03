<?php
require_once 'config.php';

try {
    // Drop if exists to ensure we start fresh (optional, but good for debugging)
    // $pdo->exec("DROP TABLE IF EXISTS applications");

    $sql = "CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        center_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        qualification VARCHAR(100),
        country_id INT,
        state_id INT,
        city_id INT,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "Table 'applications' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
