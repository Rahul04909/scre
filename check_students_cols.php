<?php
require_once 'database/config.php';
try {
    $stmt = $pdo->query("DESCRIBE students");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
