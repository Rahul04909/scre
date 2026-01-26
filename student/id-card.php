<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

// Include database configuration
require_once '../database/config.php';
require_once '../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Initialize student variable
$student = [];
$error = null;

try {
    // Check if $pdo is available from config.php
    if (!isset($pdo)) {
        throw new Exception("Database connection failed. Config not loaded properly.");
    }
    $conn = $pdo;
    
    $student_id = $_SESSION['student_id'];
    
    // Fetch complete student details
    $sql = "SELECT s.*, c.name as country_name, st.name as state_name, ct.name as city_name, co.course_name, co.course_code,
                   ce.center_name, ce.id as center_code, cat.category_name as category_name, acs.session_name,
                   acs.end_month, acs.end_year, s.enrollment_date
            FROM students s 
            LEFT JOIN countries c ON s.country_id = c.id 
            LEFT JOIN states st ON s.state_id = st.id 
            LEFT JOIN cities ct ON s.city_id = ct.id 
            LEFT JOIN courses co ON s.course_id = co.id 
            LEFT JOIN centers ce ON s.center_id = ce.id 
            LEFT JOIN course_categories cat ON co.category_id = cat.id
            LEFT JOIN academic_sessions acs ON s.session_id = acs.id
            WHERE s.id = :student_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        throw new Exception('Student details not found');
    }
    
    // Build address
    $address_parts = [];
    if (!empty($student['city_name'])) $address_parts[] = $student['city_name'];
    if (!empty($student['state_name'])) $address_parts[] = $student['state_name'];
    if (!empty($student['country_name'])) $address_parts[] = $student['country_name'];
    $student['address'] = !empty($address_parts) ? implode(', ', $address_parts) : 'Not Available';
    
    // Set defaults
    $student['mother_name'] = $student['mother_name'] ?? 'Not Available';
    if (!isset($student['enrollment_number']) && isset($student['enrollment_id'])) {
        $student['enrollment_number'] = $student['enrollment_id'];
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Function to generate QR code
function generateQRCode($data) {
    $options = new QROptions([
        'version'    => 10,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_L,
        'scale'      => 3,
        'imageBase64' => true,
    ]);
    
    $qrcode = new QRCode($options);
    return $qrcode->render($data);
}

// Handle download request setup
$is_download = isset($_GET['download']) && $_GET['download'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Card - <?php echo htmlspecialchars($student['first_name'] ?? 'Student'); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d47a1;
            --secondary-color: #1976d2;
            --accent-color: #ff9800;
            --border-color: #e0e0e0;
            --shadow-color: rgba(0,0,0,0.1);
        }
        
        /* ID Card Specific Styles */
        .id-card-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px var(--shadow-color);
            overflow: hidden;
            border: 1px solid var(--border-color);
            padding-bottom: 40px;
        }
        
        .id-card-header-banner {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
            border-bottom: 5px solid var(--accent-color);
        }
        
        .id-card-header-banner h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .id-card-body {
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        /* The Card Itself */
        .id-card {
            width: 280px;
            height: 220px; 
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            position: relative;
            margin: 60px auto; /* Margin for scale */
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            overflow: hidden;
            transform: scale(2); /* Zoom effect */
            transform-origin: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            font-family: 'Inter', sans-serif;
        }
        
        .id-card::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('../assets/logo/logo.jpeg'); /* Corrected path */
            background-position: center;
            background-repeat: no-repeat;
            background-size: 115px;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
        
        .id-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 8px;
            background: var(--primary-color);
        }
        
        .id-card-content {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            padding: 10px;
            color: #000;
            font-size: 6px;
            line-height: 1.4;
            display: flex;
            flex-direction: column;
            z-index: 1;
        }
        
        .university-header {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 3px;
        }
        
        .university-logo, .iso-logo {
            width: 20px; height: 20px;
            margin-right: 3px;
            display: flex; align-items: center; justify-content: center;
        }
        .university-logo img, .iso-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        .university-text { flex: 1; text-align: center; }
        .university-name-english { font-size: 6px; font-weight: bold; color: var(--secondary-color); }
        .university-address { font-size: 4.5px; color: #666; }
        
        .card-title {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            text-align: center; padding: 2px;
            font-size: 5.5px; font-weight: bold; color: white;
            margin-bottom: 4px; border-radius: 3px;
            text-transform: uppercase;
        }
        
        .student-info { display: flex; gap: 4px; flex: 1; }
        
        .student-details {
            flex: 1; font-size: 5px;
            background: rgba(240, 247, 255, 0.5);
            padding: 3px; border-radius: 3px;
            border-left: 2px solid var(--secondary-color);
        }
        
        .detail-row { display: flex; margin-bottom: 2px; }
        .detail-label { font-weight: bold; min-width: 40px; color: var(--primary-color); }
        .detail-value { flex: 1; font-weight: 500; }
        
        .right-column { display: flex; flex-direction: column; align-items: center; gap: 4px; width: 60px;}
        
        .student-photo {
            width: 25px; height: 30px;
            border: 1px solid var(--secondary-color);
            background: white;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .student-photo img { width: 100%; height: 100%; object-fit: cover; }
        
        .qr-section { display: flex; flex-direction: column; align-items: center; }
        .qr-code { width: 23px; height: 23px; border: 1px solid #ddd; background: white; margin-bottom: 2px; }
        .qr-code img { width: 100%; height: 100%; }
        
        .enrollment-number-large {
            font-size: 4.5px; font-weight: bold; color: var(--primary-color);
            background: rgba(25, 118, 210, 0.1); padding: 1px 3px; border-radius: 2px;
        }
        
        .valid-date, .issue-date { font-size: 4px; text-align: center; color: #666; }
        
        .signatures { display: flex; justify-content: space-between; font-size: 3px; margin-top: 3px; padding-top: 3px; }
        .signature { text-align: center; width: 30px; position: relative; }
        .signature-line { border-top: 0.5px solid #000; margin: 1px 0; }
        .signature-name { font-weight: bold; color: var(--primary-color); }
        .signature-title { color: #666; font-size: 2.5px; }

        /* Print Override */
        @media print {
            body { background: white; margin: 0; }
            #wrapper { display: block; }
            #sidebar-wrapper, #main-header, .control-buttons, .id-card-header-banner { display: none !important; }
            #page-content-wrapper { margin: 0; padding: 0; }
            .id-card-container { box-shadow: none; border: none; padding: 0; margin: 0; }
            .id-card { transform: scale(1.5); margin: 20px auto; page-break-inside: avoid; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <?php if ($is_download): ?>
        <div class="id-card-body">
             <?php renderIDCard($student, $conn); ?>
        </div>
        <script>window.print();</script>
    <?php else: ?>
    
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
             <?php include 'header.php'; ?>
             
             <div class="container-fluid px-4 py-4">
                 <div class="id-card-container">
                    <div class="id-card-header-banner">
                        <h2>Student ID Card</h2>
                    </div>
                    
                    <div class="id-card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php elseif ($student): ?>
                            
                            <!-- Wrapper for visual centering and scale -->
                            <div style="height: 350px; display: flex; align-items: center; justify-content: center;">
                                <?php renderIDCard($student, $conn); ?>
                            </div>
                            
                            <div class="text-center mt-4 mb-3 d-print-none">
                                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-print me-2"></i> Print ID Card
                                </button>
                            </div>
                            
                        <?php endif; ?>
                    </div>
                 </div>
             </div>
        </div>
    </div>
    
    <?php endif; ?>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function renderIDCard($student, $conn) {
    // Fetch signatures logic
    $center_data = null;
    if (isset($student['center_id'])) {
        try {
            $stmt_center = $conn->prepare("SELECT signature FROM centers WHERE id = :center_id");
            $stmt_center->bindParam(':center_id', $student['center_id']);
            $stmt_center->execute();
            $center_data = $stmt_center->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {}
    }

    $admin_data = null;
    try {
        $stmt_admin = $conn->prepare("SELECT signature, stamp FROM admin WHERE id = 1");
        $stmt_admin->execute();
        $admin_data = $stmt_admin->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
    ?>
    <div class="id-card">
        <div class="id-card-content">
            <!-- Header -->
            <div class="university-header">
                <div class="university-logo"><img src="../assets/logo/logo.jpeg" alt="Logo"></div> <!-- Corrected -->
                <div class="university-text">
                     <div class="university-name-english">SIR CHHOTU RAM EDUCATION PVT. LTD.</div>
                     <div class="university-name-hindi" style="font-size: 5px; color: #0d47a1;">Regd. Under Ministry of Corporate Affairs, Govt of India</div>
                     <div class="university-address">AN ISO 9001-2015 CERTIFIED ORGANIZATION</div>
                </div>
                <div class="iso-logo"><img src="../assets/logo/iso.webp" alt="ISO"></div> <!-- Corrected -->
            </div>
            
            <div class="card-title">SCRE - Student Identity Card</div>
            
            <div class="student-info">
                <div class="student-details">
                    <div class="detail-row"><span class="detail-label">Enrolment No:</span><span class="detail-value"><?php echo htmlspecialchars($student['enrollment_number'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="detail-label">ASC Name:</span><span class="detail-value"><?php echo htmlspecialchars($student['center_name'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Course:</span><span class="detail-value"><?php echo htmlspecialchars($student['course_code'] ?? ''); ?></span></div>
                     <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Father's Name:</span><span class="detail-value"><?php echo htmlspecialchars($student['father_name'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="detail-label">DOB:</span><span class="detail-value"><?php echo htmlspecialchars($student['dob'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="detail-label">Mobile:</span><span class="detail-value"><?php echo htmlspecialchars($student['mobile'] ?? ''); ?></span></div>
                </div>
                
                <div class="right-column">
                    <div class="qr-section">
                        <div class="qr-code">
                             <?php
                                $qrData = "Valid: " . $student['enrollment_number'] . "\nName: " . $student['first_name'];
                                echo '<img src="'.generateQRCode($qrData).'" alt="QR">';
                             ?>
                        </div>
                        <div class="enrollment-number-large"><?php echo htmlspecialchars($student['enrollment_number']); ?></div>
                    </div>
                    <div class="student-photo">
                        <?php 
                        // Default
                        $photo = '../assets/uploads/students/default-user.png';
                        
                        // Check if student_image is valid
                        if (!empty($student['student_image'])) {
                            // Logic: The DB stores "assets/uploads/students/FILENAME"
                            // We are in "student/", so we need "../" + DB_PATH
                            $checkPath = '../' . $student['student_image'];
                            if (file_exists($checkPath)) {
                                $photo = $checkPath;
                            }
                        }
                        ?>
                        <img src="<?php echo $photo; ?>" alt="Photo">
                    </div>
                    <div class="valid-date" style="font-size: 3px;">
                        Valid Till: <?php 
                            if (!empty($student['end_month']) && !empty($student['end_year'])) {
                                echo date('t-m-Y', strtotime("1 " . $student['end_month'] . " " . $student['end_year']));
                            } else {
                                echo 'N/A';
                            }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="signatures">
                <div class="signature">
                    <?php 
                    if (!empty($student['student_signature'])) {
                        $signPath = '../' . $student['student_signature'];
                        if (file_exists($signPath)) {
                            echo '<img src="'.$signPath.'" style="height: 12px;">';
                        }
                    } 
                    ?>
                    <div class="signature-line"></div>
                    <div class="signature-name">Student Signal</div>
                </div>
                <div class="signature">
                     <?php if (!empty($center_data['signature']) && file_exists('../'.$center_data['signature'])): ?>
                        <img src="../<?php echo htmlspecialchars($center_data['signature']); ?>" style="height: 12px;">
                    <?php endif; ?>
                    <div class="signature-line"></div>
                    <div class="signature-name">Center Director</div>
                </div>
                <div class="signature">
                    <?php 
                    if (!empty($admin_data['signature'])) {
                         $adminSignPath = '../assets/uploads/admin/' . $admin_data['signature'];
                         if (file_exists($adminSignPath)) {
                             echo '<img src="'.$adminSignPath.'" style="height: 12px;">';
                         }
                    }
                    ?>
                    <div class="signature-line"></div>
                    <div class="signature-name">Controller Exam</div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>