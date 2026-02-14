<?php
require_once 'database/config.php';
try {
    echo "Student Fees Cols: " . implode(", ", $pdo->query("DESCRIBE student_fees")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
