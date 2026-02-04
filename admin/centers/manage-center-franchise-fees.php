<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Helper
function clean($str) {
    return htmlspecialchars(trim($str ?? ''));
}

// 1. Handle Add Payment
if (isset($_POST['add_payment'])) {
    $center_id = intval($_POST['center_id']);
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    $payment_mode = clean($_POST['payment_mode']);
    $remarks = clean($_POST['remarks']);
    
    // Optional: Manual txn id or auto-generated
    $transaction_id = 'FR-' . strtoupper(uniqid());

    if ($center_id > 0 && $amount > 0 && $payment_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO center_franchise_payments (center_id, amount, payment_date, payment_mode, transaction_id, remarks) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$center_id, $amount, $payment_date, $payment_mode, $transaction_id, $remarks]);
            
            $message = "Payment of ₹$amount recorded successfully.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Invalid input details.";
        $messageType = "danger";
    }
}

// 2. Fetch Centers
try {
    $stmtCenters = $pdo->query("SELECT id, center_name, center_code, franchise_fee FROM centers ORDER BY center_name ASC");
    $centers = $stmtCenters->fetchAll();
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// 3. Fetch Selected Center Data
$selected_center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;
$center_details = null;
$payments = [];
$total_paid = 0;
$pending_amount = 0;

if ($selected_center_id > 0) {
    // Get Center Details
    $stmtDet = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
    $stmtDet->execute([$selected_center_id]);
    $center_details = $stmtDet->fetch();

    if ($center_details) {
        $franchise_fee = floatval($center_details['franchise_fee']);

        // Get Payments
        $stmtPay = $pdo->prepare("SELECT * FROM center_franchise_payments WHERE center_id = ? ORDER BY payment_date DESC, id DESC");
        $stmtPay->execute([$selected_center_id]);
        $payments = $stmtPay->fetchAll();

        // Calculate Totals
        foreach ($payments as $p) {
            $total_paid += floatval($p['amount']);
        }

        $pending_amount = $franchise_fee - $total_paid;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Franchise Fees - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .stat-card {
            background: #fff;
            border-left: 4px solid;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px;
            border-radius: 2px;
            height: 100%;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-title { color: #555; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: 400; color: #1e1e1e; }
        
        .border-blue { border-color: #2271b1; } /* WP Admin Blue */
        .border-green { border-color: #46b450; } /* WP Green */
        .border-red { border-color: #dc3232; } /* WP Red */
        
        .text-blue { color: #2271b1; }
        .text-green { color: #46b450; }
        .text-red { color: #dc3232; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                     <h2 class="fw-bold" style="color: #2271b1;">Manage Franchise Fees</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Select Center -->
                <div class="card shadow-sm border-0 mb-5 bg-white">
                    <div class="card-body p-4">
                        <form method="GET">
                            <label class="form-label fw-bold text-uppercase text-secondary small mb-2">
                                <i class="fas fa-search me-2"></i>Search Center
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                                <select name="center_id" class="form-select select2" required onchange="this.form.submit()">
                                    <option value="">Select a Center...</option>
                                    <?php foreach ($centers as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($selected_center_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo clean($c['center_name'] . ' (' . $c['center_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary px-4" type="submit">View Details</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($center_details): ?>
                    <!-- Financial Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card border-blue">
                                <div class="stat-title">Total Franchise Fee</div>
                                <div class="stat-value text-blue">₹<?php echo number_format($center_details['franchise_fee'], 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card border-green">
                                <div class="stat-title">Total Paid</div>
                                <div class="stat-value text-green">₹<?php echo number_format($total_paid, 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card border-red">
                                <div class="stat-title">Pending Balance</div>
                                <div class="stat-value text-red">₹<?php echo number_format($pending_amount, 2); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div class="d-flex justify-content-end mb-3">
                        <?php if ($pending_amount > 0): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-plus-circle me-2"></i> Receive Payment
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success disabled" disabled>
                                <i class="fas fa-check-circle me-2"></i> Payment Completed
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Payment History -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Payment History</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Txn ID</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Remarks</th>
                                        <th class="text-end">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($payments) > 0): ?>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
                                                <td><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                                                <td class="font-monospace text-primary"><?php echo clean($p['transaction_id']); ?></td>
                                                <td class="fw-bold text-success">₹<?php echo number_format($p['amount'], 2); ?></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo clean($p['payment_mode']); ?></span></td>
                                                <td><small class="text-muted"><?php echo clean($p['remarks']); ?></small></td>
                                                <td class="text-end">
                                                    <a href="receipt-franchise-fee.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-print me-1"></i> Print
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No payments recorded yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Modal -->
                    <div class="modal fade" id="paymentModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Record Fee Payment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="center_id" value="<?php echo $selected_center_id; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Payment Date</label>
                                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Amount (₹)</label>
                                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Payment Mode</label>
                                            <select name="payment_mode" class="form-select" required>
                                                <option value="Cash">Cash</option>
                                                <option value="UPI">UPI</option>
                                                <option value="Cheque">Cheque</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks / Txn Details</label>
                                            <textarea name="remarks" class="form-control" placeholder="Enter bank reference, cheque no. etc..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="add_payment" class="btn btn-primary">Save Payment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif(isset($_GET['center_id'])): ?>
                    <div class="alert alert-warning mt-4">Selected center not found.</div>
                <?php else: ?>
                    <div class="text-center py-5 mt-5">
                        <i class="fas fa-store-alt fa-4x text-muted opacity-25 mb-3"></i>
                        <h4 class="text-muted">Select a center to manage franchise fees</h4>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search for a center...',
                allowClear: true
            });
        });
    </script>
</body>
</html>
