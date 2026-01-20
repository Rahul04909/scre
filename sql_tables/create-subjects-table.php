<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create subjects table
    $sql = "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        
        -- Unit Information (if applicable)
        unit_no INT DEFAULT 0, -- Represents Semester No or Year No (e.g. 1, 2, 3...)
        
        subject_name VARCHAR(255) NOT NULL,
        
        -- Marks
        theory_marks DECIMAL(5,2) DEFAULT 0.00,
        
        has_practical BOOLEAN DEFAULT FALSE,
        practical_marks DECIMAL(5,2) DEFAULT 0.00,
        
        exam_duration INT, -- In minutes
        
        passing_marks DECIMAL(5,2) DEFAULT 0.00,
        total_marks DECIMAL(5,2) DEFAULT 0.00, -- Calculated (Theory + Practical)
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'subjects' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
