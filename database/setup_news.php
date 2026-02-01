<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS news_updates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        link_url VARCHAR(255) NULL,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'news_updates' created successfully (or already exists).<br>";
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
