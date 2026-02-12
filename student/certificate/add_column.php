<?php
require_once '../../database/config.php';
try {
    $sql = "ALTER TABLE students ADD COLUMN certificate_serial_no VARCHAR(20) DEFAULT NULL AFTER enrollment_no";
    $pdo->exec($sql);
    echo "Column certificate_serial_no added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
