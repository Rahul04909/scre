<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch Student Stats (Fees)
// Calculate Total Fee (Course + Admission)
$stmtFee = $pdo->prepare("
    SELECT 
        (c.course_fees + c.admission_fees) as total_fee,
        c.course_name,
        s.first_name, s.last_name, s.enrollment_no, s.student_image
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.id = ?
");
$stmtFee->execute([$student_id]);
$student = $stmtFee->fetch();

// Calculate Paid
$stmtPaid = $pdo->prepare("SELECT SUM(amount) FROM student_fees WHERE student_id = ?");
$stmtPaid->execute([$student_id]);
$paid = $stmtPaid->fetchColumn() ?: 0;

$pending = $student['total_fee'] - $paid;

// Recent Transactions
$stmtTxn = $pdo->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY payment_date DESC LIMIT 5");
$stmtTxn->execute([$student_id]);
$recent_txns = $stmtTxn->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <!-- Welcome Section -->
                <div class="welcome-card p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="welcome-heading mb-1">Hello, <?php echo htmlspecialchars($student['first_name']); ?> 👋</h2>
                        <p class="welcome-subtext mb-0">Welcome to your student portal. Here's your quick overview.</p>
                    </div>
                    <div>
                        <span class="student-badge">
                            <i class="fas fa-id-badge me-2"></i><?php echo htmlspecialchars($student['enrollment_no']); ?>
                        </span>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <!-- Fees Paid -->
                    <div class="col-md-4">
                        <div class="stat-card stat-green p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Fees Paid</div>
                                    <h3 class="stat-value">₹<?php echo number_format($paid); ?></h3>
                                </div>
                                <div class="stat-icon-circle">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fees Pending -->
                    <div class="col-md-4">
                        <div class="stat-card stat-amber p-4">
                             <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Fees Pending</div>
                                    <h3 class="stat-value">₹<?php echo number_format($pending); ?></h3>
                                </div>
                                <div class="stat-icon-circle">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Info -->
                    <div class="col-md-4">
                        <div class="stat-card stat-indigo p-4">
                             <div class="d-flex justify-content-between align-items-start">
                                <div style="overflow: hidden;">
                                    <div class="stat-label">Enrolled Course</div>
                                    <h4 class="fw-bold mb-0 text-truncate text-dark" title="<?php echo htmlspecialchars($student['course_name']); ?>">
                                        <?php echo htmlspecialchars($student['course_name']); ?>
                                    </h4>
                                </div>
                                <div class="stat-icon-circle flex-shrink-0 ms-2">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row g-4">
                    
                    <!-- Recent Payments -->
                    <div class="col-lg-8">
                        <div class="content-card h-100">
                            <div class="card-header-clean">
                                <h5 class="card-title-clean">
                                    <i class="fas fa-history text-muted"></i> Recent Payments
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-clean mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Mode</th>
                                            <th>Transaction ID</th>
                                            <th class="text-end pe-4">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($recent_txns) > 0): ?>
                                            <?php foreach($recent_txns as $txn): ?>
                                            <tr>
                                                <td class="ps-4 text-secondary fw-500"><?php echo date('d M Y', strtotime($txn['payment_date'])); ?></td>
                                                <td><span class="badge bg-light text-dark border fw-normal"><?php echo htmlspecialchars($txn['payment_mode']); ?></span></td>
                                                <td class="small font-monospace text-muted"><?php echo htmlspecialchars($txn['transaction_id'] ?? '-'); ?></td>
                                                <td class="text-end pe-4 fw-bold text-success">+₹<?php echo number_format($txn['amount']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-5 text-muted">No recent payments found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="col-lg-4">
                         <div class="content-card h-100">
                            <div class="card-header-clean">
                                <h5 class="card-title-clean">
                                    <i class="far fa-bell text-muted"></i> Notifications
                                </h5>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="#" class="notification-item">
                                    <div class="notif-icon">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <div>
                                        <div class="notif-title">Welcome to PACE!</div>
                                        <div class="notif-desc">Start your learning journey today. Check your course details.</div>
                                    </div>
                                </a>
                                <!-- Example Notification 2 -->
                                <a href="#" class="notification-item">
                                    <div class="notif-icon" style="background-color: #DCFCE7; color: #16A34A;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <div class="notif-title">Admission Confirmed</div>
                                        <div class="notif-desc">Your admission process is complete. Explore your dashboard.</div>
                                    </div>
                                </a>
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
