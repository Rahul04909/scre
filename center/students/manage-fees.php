<?php
ob_start();
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

// Handle Fee Collection
if (isset($_POST['collect_fee'])) {
    $student_id = intval($_POST['student_id']);
    $amount = floatval($_POST['amount']);
    $payment_mode = $_POST['payment_mode'];
    $transaction_id = $_POST['transaction_id']; // Optional
    $payment_date = $_POST['payment_date'];
    $remarks = $_POST['remarks'];

    if ($amount > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Fetch Center Wallet Info & Student Info
            $stmtCenter = $pdo->prepare("SELECT wallet_balance FROM centers WHERE id = ? FOR UPDATE");
            $stmtCenter->execute([$center_id]);
            $centerInfo = $stmtCenter->fetch();
            
            // Fetch Student Enrollment for Description
            $stmtStudent = $pdo->prepare("SELECT enrollment_no FROM students WHERE id = ?");
            $stmtStudent->execute([$student_id]);
            $studentInfo = $stmtStudent->fetch();
            $enrollment_no = $studentInfo ? $studentInfo['enrollment_no'] : 'Unknown';
            
            $balance = floatval($centerInfo['wallet_balance']);
            
            // 2. Deduction Amount = Full Fee Amount
            $deduction = $amount; 
            
            // 3. Check Balance
            if ($balance < $deduction) {
                // Insufficient Balance
                $pdo->rollBack();
                $error = "Insufficient Wallet Balance! You need at least ₹" . number_format($deduction, 2) . " to collect this fee.";
            } else {
                // 4. Insert Student Fee
                $stmtInsert = $pdo->prepare("INSERT INTO student_fees (student_id, center_id, amount, payment_mode, transaction_id, payment_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$student_id, $center_id, $amount, $payment_mode, $transaction_id, $payment_date, $remarks]);

                // 5. Deduct from Wallet
                $new_balance = $balance - $deduction;
                $stmtUpdate = $pdo->prepare("UPDATE centers SET wallet_balance = ? WHERE id = ?");
                $stmtUpdate->execute([$new_balance, $center_id]);

                // 6. Record Wallet Transaction
                $desc = "Fees Collection ($enrollment_no)";
                // Generate a pseudo-txn ID for internal record
                $wallet_txn_id = "FEE-" . time() . "-" . rand(100,999);
                
                $stmtWallet = $pdo->prepare("INSERT INTO center_wallet_transactions (center_id, transaction_id, type, amount_debit, description, status, created_at) VALUES (?, ?, 'debit', ?, ?, 'success', NOW())");
                $stmtWallet->execute([$center_id, $wallet_txn_id, $deduction, $desc]);
                
                $pdo->commit();
                header("Location: manage-fees.php?msg=collected");
                exit;
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Students with Fee Details
// We need: Student Basic Info, Course Fee Info, Total Paid
$sql = "SELECT s.id, s.enrollment_no, s.first_name, s.last_name, s.father_name, s.email, s.mobile, s.student_image,
               c.course_name, c.course_fees, c.admission_fees,
               (SELECT COALESCE(SUM(amount), 0) FROM student_fees sf WHERE sf.student_id = s.id) as total_paid
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.center_id = ? 
        ORDER BY s.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$center_id]);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees - PACE Center</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .student-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd; }
        .fee-font { font-family: 'Courier New', monospace; font-weight: bold; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%; margin-left: 280px;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Student Fees Management</h2>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'collected'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Fees collected successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="feesTable" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Enrollment</th>
                                        <th>Student</th>
                                        <th>Father Name</th>
                                        <th>Mobile</th>
                                        <th>Total Fee</th>
                                        <th>Paid</th>
                                        <th>Pending</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sr = 1;
                                    foreach ($students as $s): 
                                        $total_fee = floatval($s['course_fees']) + floatval($s['admission_fees']);
                                        $paid = floatval($s['total_paid']);
                                        $pending = $total_fee - $paid;
                                        // Pending shouldn't be negative logically, but if extra paid, it shows negative (refund?)
                                    ?>
                                        <tr>
                                            <td><?php echo $sr++; ?></td>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($s['enrollment_no']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php $img = !empty($s['student_image']) ? '../../'.$s['student_image'] : 'https://ui-avatars.com/api/?name='.$s['first_name']; ?>
                                                    <img src="<?php echo $img; ?>" class="student-img me-2">
                                                    <div>
                                                        <span class="d-block fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></span>
                                                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($s['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($s['father_name']); ?></td>
                                            <td><?php echo htmlspecialchars($s['mobile']); ?></td>
                                            <td class="fee-font text-dark"><?php echo number_format($total_fee, 2); ?></td>
                                            <td class="fee-font text-success"><?php echo number_format($paid, 2); ?></td>
                                            <td class="fee-font <?php echo $pending > 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo number_format($pending, 2); ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="fee-history.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info text-white" title="View History" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0;">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-primary d-flex align-items-center" onclick="openCollectModal(<?php echo $s['id']; ?>, '<?php echo addslashes($s['first_name'] . ' ' . $s['last_name']); ?>', <?php echo $pending; ?>)" title="Collect Fee" style="height: 32px;">
                                                        <i class="fas fa-hand-holding-usd me-2"></i> Collect
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collect Fee Modal -->
    <div class="modal fade" id="collectFeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Collect Fees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="modal_student_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="modal_student_name" readonly disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Amount (INR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="Enter Amount">
                            <small class="text-danger" id="modal_pending_hint"></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction ID / Cheque No (Optional)</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UPI Ref No">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="collect_fee" class="btn btn-success">Collect Fees</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#feesTable').DataTable({
                "order": [[ 0, "asc" ]]
            });
        });

        function openCollectModal(id, name, pending) {
            $('#modal_student_id').val(id);
            $('#modal_student_name').val(name);
            $('#modal_pending_hint').text('Pending Amount: ₹' + pending.toFixed(2));
            
            var modal = new bootstrap.Modal(document.getElementById('collectFeeModal'));
            modal.show();
        }
    </script>
</body>
</html>
