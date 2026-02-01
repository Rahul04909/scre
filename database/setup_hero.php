<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image_path VARCHAR(255) NOT NULL,
        title VARCHAR(255) NULL,
        subtitle VARCHAR(255) NULL,
        button_text VARCHAR(50) NULL,
        button_link VARCHAR(255) NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'hero_slides' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
