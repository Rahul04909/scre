<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$message = '';
$messageType = '';

// Handle Password Update
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "New Password and Confirm Password do not match.";
        $messageType = "danger";
    } else {
        // Verify Old Password
        $stmt = $pdo->prepare("SELECT password FROM centers WHERE id = ?");
        $stmt->execute([$center_id]);
        $center_auth = $stmt->fetch();

        if ($center_auth && password_verify($current_password, $center_auth['password'])) {
            // Update Password
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmtUpdate = $pdo->prepare("UPDATE centers SET password = ? WHERE id = ?");
            if ($stmtUpdate->execute([$new_hash, $center_id])) {
                $message = "Password updated successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to update password.";
                $messageType = "danger";
            }
        } else {
            $message = "Incorrect Current Password.";
            $messageType = "danger";
        }
    }
}

// Fetch Center Details
$stmt = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
$stmt->execute([$center_id]);
$center = $stmt->fetch();

if (!$center) {
    die("Center not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - PACE Center</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; }
        .profile-header {
            background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);
            padding: 2rem 2rem 5rem; /* Increased bottom padding */
            color: white;
            border-radius: 0 0 20px 20px;
            margin-bottom: -4rem; /* Deeper overlap */
            position: relative;
            z-index: 1;
        }
        .profile-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            background: white;
            overflow: visible; /* FIXED: Allow image to overflow */
            position: relative;
            z-index: 2;
        }
        .profile-img-lg {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border: 5px solid white;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            background-color: white; /* Fallback */
        }
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        .info-value {
            font-weight: 600;
            color: #1f2937;
            font-size: 1.05rem;
        }
        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #115E59; /* Brand color */
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
        }
        .section-title i {
            background-color: #F0FDFA;
            padding: 8px;
            border-radius: 8px;
            margin-right: 12px;
        }
        
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%;">
            <?php include 'header.php'; ?>
            
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($center['center_name']); ?></h2>
                        <div class="opacity-75"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($center['city'] . ', ' . $center['state']); ?> | Code: <strong><?php echo htmlspecialchars($center['center_code']); ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-4 pb-5">
                <div class="row g-4">
                    <!-- Left Column: Profile Info -->
                    <div class="col-lg-8">
                        <div class="profile-card p-4 pt-5 position-relative mt-4">
                            <div class="position-absolute" style="top: -65px; left: 40px;">
                                <?php $img = !empty($center['owner_image']) ? $center['owner_image'] : 'https://ui-avatars.com/api/?name='.urlencode($center['owner_name']); ?>
                                <img src="<?php echo $img; ?>" class="profile-img-lg">
                            </div>
                            
                            <div class="d-flex justify-content-end mb-4">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Active Center
                                </span>
                            </div>

                            <h4 class="section-title mt-2"><i class="fas fa-user-tie me-2 text-primary"></i>Owner & Contact Details</h4>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Owner Name</div>
                                        <div class="info-value fs-5"><?php echo htmlspecialchars($center['owner_name']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($center['email']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Mobile Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($center['mobile']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Full Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($center['address'] . ', ' . $center['city'] . ' - ' . $center['pincode']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="section-title"><i class="fas fa-building me-2 text-primary"></i>Infrastructure & Legal</h4>
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded text-center h-100">
                                        <h3 class="fw-bold text-primary mb-0"><?php echo $center['num_computers']; ?></h3>
                                        <small class="text-muted">Computers</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded text-center h-100">
                                        <h3 class="fw-bold text-primary mb-0"><?php echo $center['num_classrooms']; ?></h3>
                                        <small class="text-muted">Classrooms</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded text-center h-100">
                                        <h3 class="fw-bold text-primary mb-0"><?php echo $center['num_staff']; ?></h3>
                                        <small class="text-muted">Staff</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded text-center h-100">
                                        <small class="d-block text-muted">Lab Type</small>
                                        <div class="fw-bold"><?php echo htmlspecialchars($center['lab_type']); ?></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column: Security & Bank -->
                    <div class="col-lg-4 mt-3">
                        
                        <!-- Change Password -->
                        <div class="profile-card p-4 mb-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-lock me-2 text-warning"></i>Security</h5>
                            
                            <?php if ($message): ?>
                                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show p-2 small">
                                    <?php echo $message; ?>
                                    <button type="button" class="btn-close small p-2" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary w-100">Update Password</button>
                            </form>
                        </div>

                        <!-- Bank Details -->
                        <div class="profile-card p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-university me-2 text-info"></i>Bank Details</h5>
                            <div class="mb-3 border-bottom pb-2">
                                <small class="d-block text-muted text-uppercase" style="font-size: 0.7rem;">Bank Name</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($center['bank_name'] ?: 'N/A'); ?></div>
                            </div>
                            <div class="mb-3 border-bottom pb-2">
                                <small class="d-block text-muted text-uppercase" style="font-size: 0.7rem;">Account Number</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($center['account_no'] ?: 'N/A'); ?></div>
                            </div>
                            <div class="mb-3 border-bottom pb-2">
                                <small class="d-block text-muted text-uppercase" style="font-size: 0.7rem;">IFSC Code</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($center['ifsc_code'] ?: 'N/A'); ?></div>
                            </div>
                            <div class="mb-0">
                                <small class="d-block text-muted text-uppercase" style="font-size: 0.7rem;">Account Holder</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($center['account_holder'] ?: 'N/A'); ?></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
