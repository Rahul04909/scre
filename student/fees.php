<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student Info (Course, Center, Fees)
$stmtStudent = $pdo->prepare("
    SELECT s.center_id, s.course_id, c.course_name, c.course_fees, c.admission_fees 
    FROM students s 
    JOIN courses c ON s.course_id = c.id 
    WHERE s.id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch();

if (!$student) {
    die("Student not found.");
}

// 2. Calculate Total & Paid
$total_fees = floatval($student['course_fees']) + floatval($student['admission_fees']);

$stmtPaid = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM student_fees WHERE student_id = ?");
$stmtPaid->execute([$student_id]);
$paid_amount = floatval($stmtPaid->fetchColumn());

$pending_amount = $total_fees - $paid_amount;

// 3. Fetch Transaction History
$stmtTxn = $pdo->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY payment_date DESC, id DESC");
$stmtTxn->execute([$student_id]);
$transactions = $stmtTxn->fetchAll();

// 4. Fetch Center Bank Details
$stmtCenter = $pdo->prepare("
    SELECT center_name, bank_name, account_no, ifsc_code, account_holder, branch_address, qr_code_1, qr_code_2 
    FROM centers WHERE id = ?
");
$stmtCenter->execute([$student['center_id']]);
$center = $stmtCenter->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Fees - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; }
        .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); color: white; }
        .bg-gradient-danger { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); color: white; }
        
        .qr-img {
            width: 100%;
            max-width: 180px;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 8px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .bank-details-card {
            background-color: #f8f9fc;
            border-left: 4px solid #4e73df;
            border-radius: 8px;
        }
        .card-header-clean {
            background: transparent;
            border-bottom: 1px solid #e3e6f0;
            padding: 1.5rem;
        }
        /* Fix for potential double scrollbar */
        html, body {
            overflow-x: hidden;
            height: 100%;
        }
        #wrapper {
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
                
                <h3 class="fw-bold text-dark mb-4"><i class="fas fa-wallet me-2 text-primary"></i>My Fee Status</h3>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-gradient-primary text-white h-100">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Total Course Fee</div>
                                    <div class="h2 mb-0 fw-bold">₹ <?php echo number_format($total_fees, 2); ?></div>
                                </div>
                                <div class="opacity-50">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-gradient-success text-white h-100">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Paid Amount</div>
                                    <div class="h2 mb-0 fw-bold">₹ <?php echo number_format($paid_amount, 2); ?></div>
                                </div>
                                <div class="opacity-50">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card <?php echo $pending_amount > 0 ? 'bg-gradient-danger' : 'bg-gradient-success'; ?> text-white h-100">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Pending Balance</div>
                                    <div class="h2 mb-0 fw-bold">₹ <?php echo number_format($pending_amount, 2); ?></div>
                                </div>
                                <div class="opacity-50">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Transaction History -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-secondary"></i>Transaction History</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-uppercase small text-muted">
                                            <tr>
                                                <th class="ps-4 py-3">Date</th>
                                                <th class="py-3">Amount</th>
                                                <th class="py-3">Mode</th>
                                                <th class="py-3">Reference ID</th>
                                                <th class="py-3 text-center">Receipt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($transactions) > 0): ?>
                                                <?php foreach ($transactions as $txn): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-medium text-dark">
                                                            <?php echo date('d M Y', strtotime($txn['payment_date'])); ?>
                                                        </td>
                                                        <td class="fw-bold text-success">₹ <?php echo number_format($txn['amount'], 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border fw-normal px-3 py-2">
                                                                <?php echo htmlspecialchars($txn['payment_mode']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="small text-muted font-monospace"><?php echo htmlspecialchars($txn['transaction_id'] ?: '-'); ?></td>
                                                        <td class="text-center">
                                                            <!-- Future: Link to download receipt -->
                                                            <button class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" title="Download Receipt">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-wallet-2-5666497-4726059.png" class="mb-3" style="width: 120px; opacity: 0.6;">
                                                        <p class="text-muted fw-medium">No transactions details available yet.</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-university me-2 text-secondary"></i>Bank Details</h5>
                            </div>
                            <div class="card-body px-4 pb-4 pt-2">
                                <p class="small text-muted mb-4">Please use the details below to complete your fee payment via Bank Transfer or UPI.</p>
                                
                                <div class="bank-details-card p-3 mb-4">
                                    <div class="mb-3 border-bottom pb-2">
                                        <small class="text-uppercase text-xs fw-bold text-primary">Beneficiary Name</small>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($center['account_holder'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="mb-3 border-bottom pb-2">
                                        <small class="text-uppercase text-xs fw-bold text-primary">Bank Name</small>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($center['bank_name'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="mb-3 border-bottom pb-2">
                                        <small class="text-uppercase text-xs fw-bold text-primary">Account Number</small>
                                        <div class="fw-bold text-dark fs-5 font-monospace"><?php echo htmlspecialchars($center['account_no'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-uppercase text-xs fw-bold text-primary">IFSC Code</small>
                                        <div class="fw-bold text-dark font-monospace"><?php echo htmlspecialchars($center['ifsc_code'] ?: 'N/A'); ?></div>
                                    </div>
                                </div>

                                <?php if ($center['qr_code_1'] || $center['qr_code_2']): ?>
                                    <div class="text-center pt-2">
                                        <h6 class="fw-bold mb-3 text-dark">Scan to Pay</h6>
                                        <div class="row g-2 justify-content-center">
                                            <?php if ($center['qr_code_1']): ?>
                                                <div class="col-6">
                                                    <img src="../<?php echo htmlspecialchars($center['qr_code_1']); ?>" class="qr-img mb-2">
                                                    <div class="small fw-bold text-muted">QR 1</div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($center['qr_code_2']): ?>
                                                <div class="col-6">
                                                    <img src="../<?php echo htmlspecialchars($center['qr_code_2']); ?>" class="qr-img mb-2">
                                                    <div class="small fw-bold text-muted">QR 2</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        
        if (toggleButton) {
            toggleButton.onclick = function () {
                el.classList.toggle("toggled");
            };
        }
    </script>
</body>
</html>
