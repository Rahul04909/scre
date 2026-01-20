<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create courses table
    $sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        course_code VARCHAR(50),
        course_image VARCHAR(255),
        description LONGTEXT,
        
        -- Course Type and Duration
        course_type VARCHAR(50), -- degree, diploma, crash_course, certification
        duration_type VARCHAR(20), -- hours, days, months, years
        duration_value INT,
        
        -- Fees Information
        course_fees DECIMAL(10,2) DEFAULT 0.00,
        admission_fees DECIMAL(10,2) DEFAULT 0.00,
        
        exam_fees_enabled BOOLEAN DEFAULT FALSE,
        exam_fees DECIMAL(10,2) DEFAULT 0.00,
        
        backlog_fees_enabled BOOLEAN DEFAULT FALSE,
        backlog_fees DECIMAL(10,2) DEFAULT 0.00,
        
        -- Unit Information
        has_units BOOLEAN DEFAULT FALSE,
        unit_type VARCHAR(20), -- semester, year
        unit_count INT DEFAULT 0,
        
        -- SEO Information
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords TEXT,
        schema_markup TEXT,
        
        -- Open Graph Information
        og_title VARCHAR(255),
        og_description TEXT,
        og_image VARCHAR(255),
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (category_id) REFERENCES course_categories(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'courses' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
