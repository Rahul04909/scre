<?php
require_once 'config.php';

try {
    // Add signature column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM admins LIKE 'signature'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN signature VARCHAR(255) DEFAULT NULL AFTER image");
        echo "Column 'signature' added successfully.<br>";
    } else {
        echo "Column 'signature' already exists.<br>";
    }

    // Add stamp column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM admins LIKE 'stamp'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN stamp VARCHAR(255) DEFAULT NULL AFTER signature");
        echo "Column 'stamp' added successfully.<br>";
    } else {
        echo "Column 'stamp' already exists.<br>";
    }

} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
