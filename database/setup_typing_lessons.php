<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS typing_lessons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        language_id INT NOT NULL,
        lesson_title VARCHAR(255) NOT NULL,
        duration_minutes INT NOT NULL,
        lesson_content LONGTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (language_id) REFERENCES typing_languages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'typing_lessons' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
