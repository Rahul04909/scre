<?php
require_once '../database/config.php';

try {
    // Check if columns exist, if not add them
    $columns = [
        "ADD COLUMN amount_debit DECIMAL(10, 2) DEFAULT 0.00 AFTER amount_credit",
        "ADD COLUMN type ENUM('credit', 'debit') DEFAULT 'credit' AFTER transaction_id",
        "ADD COLUMN description VARCHAR(255) AFTER type"
    ];
    
    // We'll try to add them one by one. If they exist, it might fail, so we can check or just use ignore logic if possible, 
    // but in standard MySQL ADD COLUMN IF NOT EXISTS is only in very new versions.
    // Instead, we will wrap in try-catch blocks individually or checking information_schema is better but complex for a simple script.
    // simpler approach: output message if failure.
    
    foreach ($columns as $col) {
        try {
            $sql = "ALTER TABLE center_wallet_transactions $col";
            $pdo->exec($sql);
            echo "Executed: $sql <br>";
        } catch (PDOException $e) {
            // Ignore Duplicate column name error (1060)
            if ($e->getCode() != '42S21' && !strpos($e->getMessage(), "Duplicate column name")) {
               echo "Error executing $col: " . $e->getMessage() . "<br>";
            } else {
               echo "Column likely already exists for: $col <br>";
            }
        }
    }
    
    echo "Table 'center_wallet_transactions' updated successfully.";

} catch (PDOException $e) {
    die("ERROR: Could not update table. " . $e->getMessage());
}
?>
