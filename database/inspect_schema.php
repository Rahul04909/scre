<?php
require_once 'config.php';

function describeTable($pdo, $table) {
    echo "<h3>Table: $table</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        $stmt2 = $pdo->query("SHOW CREATE TABLE $table");
        $create = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($create);
        echo "</pre>";
    } catch (PDOException $e) {
        echo "Error describing $table: " . $e->getMessage();
    }
    echo "<hr>";
}

describeTable($pdo, 'courses');
describeTable($pdo, 'centers');
?>
