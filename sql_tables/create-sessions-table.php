<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create academic_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS academic_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        
        session_name VARCHAR(100) NOT NULL, -- e.g. 'Jan 2024 - Dec 2024'
        
        start_month VARCHAR(20) NOT NULL,
        start_year INT NOT NULL,
        
        end_month VARCHAR(20) NOT NULL,
        end_year INT NOT NULL,
        
        is_active BOOLEAN DEFAULT TRUE,
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'academic_sessions' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
