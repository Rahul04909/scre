<?php
// Include database configuration
require_once '../database/config.php';

try {
    // Create the smtp_settings table
    $sql = "CREATE TABLE IF NOT EXISTS smtp_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        host VARCHAR(255) NOT NULL,
        port INT NOT NULL,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        encryption VARCHAR(50) DEFAULT 'tls',
        from_email VARCHAR(255) NOT NULL,
        from_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'smtp_settings' created successfully.<br>";

    // Insert a default empty row if not exists (so we can just UPDATE later)
    $checkSql = "SELECT COUNT(*) FROM smtp_settings";
    $stmt = $pdo->query($checkSql);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $insertSql = "INSERT INTO smtp_settings (host, port, username, password, encryption, from_email, from_name) VALUES ('', 587, '', '', 'tls', '', '')";
        $pdo->exec($insertSql);
        echo "Default configuration row inserted.<br>";
    } else {
        echo "Configuration row already exists.<br>";
    }

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>
