<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS typing_languages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        language_name VARCHAR(100) NOT NULL,
        language_code VARCHAR(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'typing_languages' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
