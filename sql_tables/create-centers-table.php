<?php
require_once '../database/config.php';

try {
    // 1. Create 'centers' table
    $sqlCenters = "CREATE TABLE IF NOT EXISTS centers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Basic Details
        center_code VARCHAR(50) NOT NULL UNIQUE,
        center_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        mobile VARCHAR(20),
        owner_name VARCHAR(100),
        
        -- Images (Paths)
        owner_image VARCHAR(255),
        owner_sign VARCHAR(255),
        center_stamp VARCHAR(255),
        auth_letter VARCHAR(255), -- Uploaded Authorization Letter
        
        -- Location Details
        pincode VARCHAR(10),
        country VARCHAR(50),
        state VARCHAR(50),
        city VARCHAR(50),
        address TEXT,
        map_url TEXT, -- Embedded Map URL
        
        -- Infrastructure
        num_computers INT DEFAULT 0,
        num_classrooms INT DEFAULT 0,
        num_staff INT DEFAULT 0,
        internet_avail ENUM('Yes', 'No') DEFAULT 'No',
        power_backup ENUM('Yes', 'No') DEFAULT 'No',
        lab_type VARCHAR(50),
        
        -- Legal & Documentation
        owner_id_type VARCHAR(50),
        owner_id_no VARCHAR(50),
        owner_id_file VARCHAR(255),
        other_doc_name VARCHAR(50),
        other_doc_no VARCHAR(50),
        other_doc_file VARCHAR(255),
        
        -- Fees & Royalty
        franchise_fee DECIMAL(10, 2) DEFAULT 0.00,
        royalty_percentage DECIMAL(5, 2) DEFAULT 0.00,
        
        -- Media
        banner_image VARCHAR(255),
        logo_image VARCHAR(255),
        gallery_images JSON, -- Store array of image paths
        
        -- Working Details
        weekdays VARCHAR(255), -- e.g. Mon-Fri
        weekend_off VARCHAR(255), -- e.g. Sunday
        opening_time TIME,
        closing_time TIME,
        
        -- Bank Details
        bank_name VARCHAR(100),
        account_no VARCHAR(50),
        ifsc_code VARCHAR(20),
        account_holder VARCHAR(100),
        branch_address TEXT,
        
        -- APIs & Payment
        razorpay_key VARCHAR(255),
        razorpay_secret VARCHAR(255),
        qr_code_1 VARCHAR(255),
        qr_code_2 VARCHAR(255),
        
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlCenters);
    echo "Table 'centers' created successfully.<br>";

    // 2. Create 'center_course_allotment' table
    $sqlAllotment = "CREATE TABLE IF NOT EXISTS center_course_allotment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        course_id INT NOT NULL,
        FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(center_id, course_id) -- Prevent duplicate entries
    )";
    $pdo->exec($sqlAllotment);
    echo "Table 'center_course_allotment' created successfully.<br>";

} catch (PDOException $e) {
    die("ERROR: Could not create tables. " . $e->getMessage());
}
?>
