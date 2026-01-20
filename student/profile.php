<?php
session_start();
require_once '../database/config.php';

// Auth Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';
$messageType = '';

// Handle Password Update
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $messageType = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and Confirm password do not match.";
        $messageType = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "danger";
    } else {
        // Fetch current password hash
        $stmtPw = $pdo->prepare("SELECT password FROM students WHERE id = ?");
        $stmtPw->execute([$student_id]);
        $stored_hash = $stmtPw->fetchColumn();

        if (password_verify($current_password, $stored_hash)) {
            // Update Password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmtUpd = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
            if ($stmtUpd->execute([$new_hash, $student_id])) {
                $message = "Password updated successfully!";
                $messageType = "success";
            } else {
                $message = "Failed to update password. Please try again.";
                $messageType = "danger";
            }
        } else {
            $message = "Incorrect current password.";
            $messageType = "danger";
        }
    }
}

// Fetch Student Data
$stmt = $pdo->prepare("
    SELECT s.*, c.course_name, c.course_code, ac.session_name,
           co.name as country_name, st.name as state_name, ci.name as city_name
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN academic_sessions ac ON s.session_id = ac.id
    LEFT JOIN countries co ON s.country_id = co.id
    LEFT JOIN states st ON s.state_id = st.id
    LEFT JOIN cities ci ON s.city_id = ci.id
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    
    <style>
        .profile-header-card {
            background: linear-gradient(135deg, #0F172A 0%, #1e293b 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
        }
        .profile-avatar-xl {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.2);
            object-fit: cover;
            background: #fff;
            margin-bottom: 15px;
        }
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #0F172A;
            color: white;
        }
        .info-label {
            font-size: 0.85rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-value {
            font-weight: 500;
            color: #0f172a;
            font-size: 1rem;
        }
        .info-group {
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Left: Profile Summary -->
                    <div class="col-lg-4">
                        <div class="profile-header-card mb-4">
                            <?php 
                            $img = !empty($student['student_image']) ? '../'.$student['student_image'] : 'https://ui-avatars.com/api/?name='.$student['first_name'].'+'.$student['last_name'].'&background=random';
                            ?>
                            <img src="<?php echo $img; ?>" class="profile-avatar-xl shadow-sm">
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                            <p class="mb-2 opacity-75"><?php echo htmlspecialchars($student['course_name']); ?></p>
                            <span class="badge bg-white bg-opacity-10 text-white border border-light border-opacity-20 fw-normal px-3 py-2">
                                <i class="fas fa-id-badge me-1"></i> <?php echo htmlspecialchars($student['enrollment_no']); ?>
                            </span>
                        </div>
                        
                        <div class="card border-0 shadow-sm rounded-4 p-3">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Academic Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Course Code</span>
                                <span class="fw-semibold small"><?php echo htmlspecialchars($student['course_code']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Session</span>
                                <span class="fw-semibold small"><?php echo htmlspecialchars($student['session_name']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Joining Date</span>
                                <span class="fw-semibold small"><?php echo date('d M Y', strtotime($student['enrollment_date'])); ?></span>
                            </div>
                             <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Admission Mode</span>
                                <span class="fw-semibold small"><?php echo htmlspecialchars($student['admission_mode']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Detailed Info tabs -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active" id="pills-personal-tab" data-bs-toggle="pill" data-bs-target="#pills-personal">
                                            <i class="far fa-user me-2"></i>Personal Details
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" id="pills-address-tab" data-bs-toggle="pill" data-bs-target="#pills-address">
                                            <i class="fas fa-map-marker-alt me-2"></i>Contact & Address
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security">
                                            <i class="fas fa-lock me-2"></i>Security
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="card-body p-4">
                                <div class="tab-content" id="pills-tabContent">
                                    
                                    <!-- Personal Details -->
                                    <div class="tab-pane fade show active" id="pills-personal">
                                        <h5 class="fw-bold mb-4">Basic Information</h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Full Name</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Date of Birth</div>
                                                    <div class="info-value"><?php echo date('d F Y', strtotime($student['dob'])); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Father's Name</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['father_name']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Mother's Name</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['mother_name']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Gender</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Category</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['category']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Address Details -->
                                    <div class="tab-pane fade" id="pills-address">
                                        <h5 class="fw-bold mb-4">Contact Information</h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Email Address</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-group">
                                                    <div class="info-label">Mobile Number</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['mobile']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 mt-4">
                                                <h6 class="fw-bold mb-3">Permanent Address</h6>
                                            </div>
                                            
                                            <div class="col-12">
                                                 <div class="info-group">
                                                    <div class="info-label">Address Line</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['address']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-group">
                                                    <div class="info-label">City</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['city_name'] ?? '-'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-group">
                                                    <div class="info-label">State</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['state_name'] ?? '-'); ?></div>
                                                </div>
                                            </div>
                                             <div class="col-md-4">
                                                <div class="info-group">
                                                    <div class="info-label">Country</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['country_name'] ?? '-'); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-group">
                                                    <div class="info-label">Pincode</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($student['pincode']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Security -->
                                    <div class="tab-pane fade" id="pills-security">
                                        <div class="row justify-content-center">
                                            <div class="col-lg-8">
                                                <div class="text-center mb-4">
                                                    <div class="avatar-lg bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                        <i class="fas fa-lock fa-lg text-primary"></i>
                                                    </div>
                                                    <h5 class="fw-bold">Change Password</h5>
                                                    <p class="text-muted small">Update your password to keep your account secure.</p>
                                                </div>
                                                
                                                <form method="POST">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Current Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="current_password" id="current_password" class="form-control bg-light" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', this)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="new_password" id="new_password" class="form-control bg-light" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', this)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label small fw-bold">Confirm New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control bg-light" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="d-grid">
                                                        <button type="submit" name="change_password" class="btn btn-primary fw-bold">Update Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
