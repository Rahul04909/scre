<?php
require_once 'database/config.php';
$output = "";
try {
    $output .= "Tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $output .= $table . "\n";
    }
    
    if (in_array('centers', $tables)) {
        $output .= "\nCenters Columns:\n";
        $stmt = $pdo->query("DESCRIBE centers");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $output .= $col['Field'] . " - " . $col['Type'] . "\n"; 
        }
    }
    
    // Check if 'users' table exists (common for admin)
    if (in_array('users', $tables)) {
         $output .= "\nUsers Columns:\n";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $output .= $col['Field'] . " - " . $col['Type'] . "\n"; 
        }
    }
    
    // Check if 'admins' table exists
    if (in_array('admins', $tables)) {
         $output .= "\nAdmins Columns:\n";
        $stmt = $pdo->query("DESCRIBE admins");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $output .= $col['Field'] . " - " . $col['Type'] . "\n"; 
        }
    }

} catch (PDOException $e) {
    $output .= "Error: " . $e->getMessage();
}
file_put_contents('db_info.txt', $output);
?>
