-- Student Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    center_id INT NOT NULL,
    enrollment_no VARCHAR(50) UNIQUE NOT NULL,
    roll_no VARCHAR(50) DEFAULT NULL,
    course_id INT NOT NULL,
    session_id INT NOT NULL,
    admission_mode ENUM('Virtual', 'Online', 'Offline', 'Regular') DEFAULT 'Regular',
    enrollment_date DATE NOT NULL,
    
    -- Basic Info
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    father_name VARCHAR(150) DEFAULT NULL,
    mother_name VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    dob DATE NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    is_indian TINYINT(1) DEFAULT 1,
    
    -- Location
    country_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    city_id INT DEFAULT NULL,
    pincode VARCHAR(10) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    
    -- Emergency Contact
    emergency_name VARCHAR(100) DEFAULT NULL,
    emergency_relation VARCHAR(50) DEFAULT NULL,
    emergency_mobile VARCHAR(20) DEFAULT NULL,
    
    -- Academic & Docs
    qualification VARCHAR(100) DEFAULT NULL,
    national_id_type VARCHAR(50) DEFAULT 'Aadhar Card',
    national_id_no VARCHAR(50) DEFAULT NULL,
    national_id_file VARCHAR(255) DEFAULT NULL,
    student_image VARCHAR(255) DEFAULT NULL,
    student_signature VARCHAR(255) DEFAULT NULL,
    
    -- Account
    password VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive', 'Dropped') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Student Qualifications (Multiple Docs)
CREATE TABLE IF NOT EXISTS student_qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    doc_name VARCHAR(100) NOT NULL,
    doc_number VARCHAR(100) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
