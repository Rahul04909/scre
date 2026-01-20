<?php
require_once '../database/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function logMsg($msg, $type='success') {
    $color = ($type == 'error') ? 'red' : 'green';
    echo "<div style='color:$color; margin-bottom:5px;'>$msg</div>";
}

try {
    // 1. Ensure 'courses' table exists (Required for allotment)
    // We assume it exists from previous tasks, but let's be safe.
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB");

    // 2. Create 'centers' table if it doesn't exist
    $sqlCenters = "CREATE TABLE IF NOT EXISTS centers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_code VARCHAR(50) NOT NULL UNIQUE,
        center_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        mobile VARCHAR(20),
        owner_name VARCHAR(100),
        owner_image VARCHAR(255),
        owner_sign VARCHAR(255),
        center_stamp VARCHAR(255),
        auth_letter VARCHAR(255),
        pincode VARCHAR(10),
        country VARCHAR(50),
        state VARCHAR(50),
        city VARCHAR(50),
        address TEXT,
        map_url TEXT,
        num_computers INT DEFAULT 0,
        num_classrooms INT DEFAULT 0,
        num_staff INT DEFAULT 0,
        internet_avail ENUM('Yes', 'No') DEFAULT 'No',
        power_backup ENUM('Yes', 'No') DEFAULT 'No',
        lab_type VARCHAR(50),
        franchise_fee DECIMAL(10, 2) DEFAULT 0.00,
        royalty_percentage DECIMAL(5, 2) DEFAULT 0.00,
        banner_image VARCHAR(255),
        logo_image VARCHAR(255),
        gallery_images JSON,
        weekdays VARCHAR(255),
        weekend_off VARCHAR(255),
        opening_time TIME,
        closing_time TIME,
        bank_name VARCHAR(100),
        account_no VARCHAR(50),
        ifsc_code VARCHAR(20),
        account_holder VARCHAR(100),
        branch_address TEXT,
        razorpay_key VARCHAR(255),
        razorpay_secret VARCHAR(255),
        qr_code_1 VARCHAR(255),
        qr_code_2 VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlCenters);
    
    // FORCE InnoDB to prevent FK errors if it was MyISAM
    $pdo->exec("ALTER TABLE centers ENGINE=InnoDB");
    logMsg("1. Table 'centers' checked and Engine set to InnoDB.");

    // 3. Create 'center_course_allotment' table
    $sqlAllotment = "CREATE TABLE IF NOT EXISTS center_course_allotment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        course_id INT NOT NULL,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(center_id, course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlAllotment);
    logMsg("2. Table 'center_course_allotment' checked.");

    // 4. Create 'center_documents' table (The missing one)
    $sqlDocs = "CREATE TABLE IF NOT EXISTS center_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        document_name VARCHAR(255) NOT NULL,
        document_number VARCHAR(255) DEFAULT NULL,
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sqlDocs);
    logMsg("3. Table 'center_documents' checked/created.");

    // 5. Cleanup Legacy Columns
    $columnsToDrop = [
        'owner_id_type', 
        'owner_id_no', 
        'owner_id_file', 
        'other_doc_name', 
        'other_doc_no', 
        'other_doc_file'
    ];

    foreach ($columnsToDrop as $col) {
        try {
            $pdo->exec("ALTER TABLE centers DROP COLUMN $col");
        } catch (PDOException $e) {
            // Check if error is because column doesn't exist
            if($e->getCode() != '42000' && $e->getCode() != '1091') {
                 // logMsg("Info: Column $col already gone or error: " . $e->getMessage(), 'error');
            }
        }
    }
    logMsg("4. Schema cleanup checked.");

    echo "<h3>Success! Relational Integrity Verified.</h3>";
    echo "<p><a href='../admin/centers/manage-centers.php'>Go Back to Manage Centers</a></p>";

} catch (PDOException $e) {
    echo "<h3>Critical Database Error</h3>";
    echo "<p>Error Message: " . $e->getMessage() . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    
    // Suggestion based on error
    if(strpos($e->getMessage(), 'referenced table') !== false) {
        echo "<p style='color:red;'><b>Hint:</b> Your 'centers' or 'courses' table might be missing or using a different storage engine (MyISAM vs InnoDB). This script tried to fix it.</p>";
    }
}
?>
