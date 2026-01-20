<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id == 0) {
    header("Location: manage-fees.php");
    exit;
}

// Fetch Student Details & Fee Structure
$sqlStudent = "SELECT s.id, s.enrollment_no, s.first_name, s.last_name, s.father_name, s.mobile, s.student_image,
               c.course_name, c.course_fees, c.admission_fees
               FROM students s 
               LEFT JOIN courses c ON s.course_id = c.id 
               WHERE s.id = ? AND s.center_id = ?";
$stmt = $pdo->prepare($sqlStudent);
$stmt->execute([$student_id, $center_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found or access denied.");
}

// Calculate Totals
$total_fee = floatval($student['course_fees']) + floatval($student['admission_fees']);

// Fetch Fee Transactions
$sqlFees = "SELECT * FROM student_fees WHERE student_id = ? ORDER BY payment_date DESC, id DESC";
$stmtFees = $pdo->prepare($sqlFees);
$stmtFees->execute([$student_id]);
$transactions = $stmtFees->fetchAll();

$total_paid = 0;
foreach($transactions as $t) {
    $total_paid += floatval($t['amount']);
}
$pending = $total_fee - $total_paid;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee History - <?php echo htmlspecialchars($student['first_name']); ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .student-header { background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .student-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card { background: white; border-radius: 8px; padding: 15px; text-align: center; border: 1px solid #dee2e6; height: 100%; }
        .stat-label { font-size: 0.85rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .stat-value { font-size: 1.5rem; font-weight: bold; margin-top: 5px; }
        .text-fee { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%; margin-left: 280px;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Fee Transaction History</h2>
                    <a href="manage-fees.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <!-- Student Summary -->
                <div class="student-header">
                    <div class="row align-items-center">
                        <div class="col-md-6 d-flex align-items-center">
                            <?php $img = !empty($student['student_image']) ? '../../'.$student['student_image'] : 'https://ui-avatars.com/api/?name='.$student['first_name']; ?>
                            <img src="<?php echo $img; ?>" class="student-img me-3">
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                                <p class="mb-0 text-muted">Enrollment: <span class="fw-bold text-dark"><?php echo htmlspecialchars($student['enrollment_no']); ?></span></p>
                                <small class="text-primary"><?php echo htmlspecialchars($student['course_name']); ?></small>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-label">Total Fee</div>
                                        <div class="stat-value text-dark text-fee">₹<?php echo number_format($total_fee, 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-label">Paid</div>
                                        <div class="stat-value text-success text-fee">₹<?php echo number_format($total_paid, 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-label">Pending</div>
                                        <div class="stat-value <?php echo $pending > 0 ? 'text-danger' : 'text-success'; ?> text-fee">₹<?php echo number_format($pending, 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction List -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary"><i class="fas fa-list-alt me-2"></i> Payment Records</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Transaction ID</th>
                                        <th>Mode</th>
                                        <th>Remarks</th>
                                        <th class="text-end pe-4">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($transactions) > 0): ?>
                                        <?php foreach ($transactions as $t): ?>
                                            <tr>
                                                <td class="ps-4 text-nowrap"><?php echo date('d M, Y', strtotime($t['payment_date'])); ?></td>
                                                <td>
                                                    <?php if(!empty($t['transaction_id'])): ?>
                                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($t['transaction_id']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $badgeClass = 'bg-secondary';
                                                        if($t['payment_mode'] == 'Cash') $badgeClass = 'bg-success';
                                                        if($t['payment_mode'] == 'UPI') $badgeClass = 'bg-warning text-dark';
                                                        if($t['payment_mode'] == 'Bank Transfer') $badgeClass = 'bg-info text-dark';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($t['payment_mode']); ?></span>
                                                </td>
                                                <td class="small text-muted"><?php echo htmlspecialchars($t['remarks'] ?? '-'); ?></td>
                                                <td class="text-end pe-4 fw-bold text-success text-fee">+₹<?php echo number_format($t['amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-receipt fs-1 mb-3 opacity-25"></i>
                                                <p>No payment records found for this student.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
