<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jghfrodu_pace_foundation'); // Change this to your actual database name
define('DB_USER', 'root');            // Default WAMP username
define('DB_PASS', '');                // Default WAMP password is empty

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set character set to UTF-8
    $pdo->exec("set names utf8");
    
} catch(PDOException $e) {
    // In a production environment, you might want to log this instead of showing it
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>
