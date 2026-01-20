<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE courses");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    $stmt2 = $pdo->query("SHOW CREATE TABLE subjects");
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($row2);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
