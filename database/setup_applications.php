<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        course_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        qualification VARCHAR(100),
        country_id INT,
        state_id INT,
        city_id INT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (center_id),
        INDEX (course_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "Table 'applications' created successfully.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
