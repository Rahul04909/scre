<?php
require_once 'config.php';

try {
    echo "States:\n";
    $stmt = $pdo->query("DESCRIBE states");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    
    echo "\nCities:\n";
    $stmt = $pdo->query("DESCRIBE cities");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
