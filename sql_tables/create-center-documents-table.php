<?php
require_once '../database/config.php';

try {
    // Create 'center_documents' table
    $sql = "CREATE TABLE IF NOT EXISTS center_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        document_name VARCHAR(255) NOT NULL,
        document_number VARCHAR(255) DEFAULT NULL,
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'center_documents' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create tables. " . $e->getMessage());
}
?>
