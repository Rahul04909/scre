<?php
require_once __DIR__ . '/../../database/config.php';

try {
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM centers LIKE 'validity_date'");
    $stmt->execute();
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add column if not exists
        $sql = "ALTER TABLE centers ADD COLUMN validity_date DATE DEFAULT NULL AFTER created_at";
        $pdo->exec($sql);
        echo "Column 'validity_date' added successfully.";
    } else {
        echo "Column 'validity_date' already exists.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
