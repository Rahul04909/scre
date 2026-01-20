<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create course_categories table
    $sql = "CREATE TABLE IF NOT EXISTS course_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(255) NOT NULL,
        category_image VARCHAR(255),
        short_description TEXT,
        
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'course_categories' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
