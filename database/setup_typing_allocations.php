<?php
require_once 'config.php';

try {
    // Note: 'courses' table uses MyISAM, so we cannot use Foreign Keys with InnoDB.
    // We will create the table without the Foreign Key constraint.
    $sql = "CREATE TABLE IF NOT EXISTS typing_course_allocations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'typing_course_allocations' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
