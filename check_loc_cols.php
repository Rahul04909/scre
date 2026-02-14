<?php
require_once 'database/config.php';
try {
    echo "States Cols: " . implode(", ", $pdo->query("DESCRIBE states")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
    echo "Cities Cols: " . implode(", ", $pdo->query("DESCRIBE cities")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
