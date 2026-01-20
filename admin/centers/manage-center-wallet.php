<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Helper to sanitize
function clean($str) {
    return htmlspecialchars(trim($str ?? ''));
}

// Handle Add Funds
if (isset($_POST['admin_topup'])) {
    $center_id = intval($_POST['center_id']);
    $amount = floatval($_POST['amount']);
    $txn_date = $_POST['txn_date']; // YYYY-MM-DD
    $txn_time = $_POST['txn_time']; // HH:MM
    $remarks = trim($_POST['remarks']);

    if ($center_id > 0 && $amount > 0 && $txn_date && $txn_time) {
        $created_at = $txn_date . ' ' . $txn_time . ':00';
        $txn_id = 'ADMIN_' . strtoupper(uniqid());

        try {
            // Fetch Center Royalty first
            $stmtR = $pdo->prepare("SELECT royalty_percentage FROM centers WHERE id = ?");
            $stmtR->execute([$center_id]);
            $royalty_percent = floatval($stmtR->fetchColumn());
            
            // Calculate Amount Paid (Royalty Amount)
            $amount_paid = ($amount * $royalty_percent) / 100;

            $pdo->beginTransaction();

            // 1. Insert Transaction
            $sqlTxn = "INSERT INTO center_wallet_transactions (center_id, transaction_id, amount_credit, amount_paid, royalty_percentage, status, created_at, razorpay_order_id) 
                       VALUES (:cid, :txid, :amt, :paid, :roy, 'success', :dt, :desc)";
            $stmtTxn = $pdo->prepare($sqlTxn);
            $stmtTxn->execute([
                ':cid' => $center_id,
                ':txid' => $txn_id,
                ':amt' => $amount,
                ':paid' => $amount_paid,
                ':roy' => $royalty_percent,
                ':dt' => $created_at,
                ':desc' => 'Admin Top-up: ' . $remarks
            ]);

            // 2. Update Balance
            $stmtBal = $pdo->prepare("UPDATE centers SET wallet_balance = wallet_balance + :amt WHERE id = :cid");
            $stmtBal->execute([':amt' => $amount, ':cid' => $center_id]);

            $pdo->commit();
            $message = "Successfully added ₹$amount to center wallet.";
            $messageType = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Invalid Input Data.";
        $messageType = "danger";
    }
}

// Handle Edit Transaction Date
if (isset($_POST['update_date'])) {
    $txn_id_db = intval($_POST['txn_db_id']);
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];

    if ($txn_id_db > 0 && $new_date && $new_time) {
        $new_datetime = $new_date . ' ' . $new_time . ':00';
        try {
            $stmtUpd = $pdo->prepare("UPDATE center_wallet_transactions SET created_at = ? WHERE id = ?");
            $stmtUpd->execute([$new_datetime, $txn_id_db]);
            $message = "Transaction date updated successfully.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error updating date: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch All Centers for Dropdown
try {
    $stmtCenters = $pdo->query("SELECT id, center_name, center_code FROM centers ORDER BY center_name ASC");
    $centers = $stmtCenters->fetchAll();
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Fetch Selected Center Data
$selected_center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;
$center_details = null;
$transactions = [];

if ($selected_center_id > 0) {
    // 1. Get Details
    $stmtDet = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
    $stmtDet->execute([$selected_center_id]);
    $center_details = $stmtDet->fetch();

    // 2. Get Transactions
    $stmtTxns = $pdo->prepare("SELECT * FROM center_wallet_transactions WHERE center_id = ? ORDER BY created_at DESC");
    $stmtTxns->execute([$selected_center_id]);
    $transactions = $stmtTxns->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Center Wallet - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <style>
        .balance-card { background: linear-gradient(45deg, #2563EB, #1d4ed8); color: white; border-radius: 12px; padding: 20px; }
        .table-responsive { max-height: 600px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <h2 class="mb-4">Manage Center Wallet</h2>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- 1. Select Center -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Select Center</label>
                                <select name="center_id" class="form-select select2" required onchange="this.form.submit()">
                                    <option value="">-- Search by Name or Code --</option>
                                    <?php foreach ($centers as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($selected_center_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo clean($c['center_name'] . ' (' . $c['center_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <!-- Maybe a reset button or just visual alignment -->
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($center_details): ?>
                    <div class="row mb-4">
                        <!-- Balance Card -->
                        <div class="col-md-4">
                            <div class="balance-card shadow h-100">
                                <h6 class="text-white-50 text-uppercase small ls-1">Current Balance</h6>
                                <h1 class="fw-bold mb-3">₹<?php echo number_format($center_details['wallet_balance'], 2); ?></h1>
                                <div class="d-flex justify-content-between border-top border-white-50 pt-2">
                                    <span>Royalty: <?php echo $center_details['royalty_percentage']; ?>%</span>
                                    <span>ID: <?php echo $center_details['center_code']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 fw-bold">Admin Actions</h6>
                                </div>
                                <div class="card-body d-flex align-items-center">
                                    <button class="btn btn-success btn-lg me-3" data-bs-toggle="modal" data-bs-target="#topupModal">
                                        <i class="fas fa-plus-circle me-2"></i> Add Funds Manually
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Transaction History</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Transaction ID</th>
                                        <th>Credit</th>
                                        <th>Paid</th>
                                        <th>Description / Info</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($transactions) > 0): ?>
                                        <?php foreach ($transactions as $txn): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold d-block"><?php echo date('d M Y', strtotime($txn['created_at'])); ?></span>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($txn['created_at'])); ?></small>
                                                </td>
                                                <td class="font-monospace text-primary"><?php echo clean($txn['transaction_id']); ?></td>
                                                <td class="text-success fw-bold">+₹<?php echo number_format($txn['amount_credit'], 2); ?></td>
                                                <td>₹<?php echo number_format($txn['amount_paid'], 2); ?></td>
                                                <td>
                                                    <?php 
                                                        // Show Order ID or simple note
                                                        if (strpos($txn['transaction_id'], 'ADMIN') === 0) {
                                                            echo '<span class="badge bg-secondary">Admin Entry</span>';
                                                            echo '<br><small class="text-muted">' . clean($txn['razorpay_order_id']) . '</small>'; 
                                                        } else {
                                                            echo '<small class="text-muted">Razorpay Order: ' . clean($txn['razorpay_order_id']) . '</small>';
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary edit-date-btn" 
                                                        data-id="<?php echo $txn['id']; ?>" 
                                                        data-date="<?php echo date('Y-m-d', strtotime($txn['created_at'])); ?>"
                                                        data-time="<?php echo date('H:i', strtotime($txn['created_at'])); ?>">
                                                        <i class="fas fa-edit"></i> Edit Date
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No transactions found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top-up Modal -->
                    <div class="modal fade" id="topupModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Manual Wallet Top-up</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="center_id" value="<?php echo $selected_center_id; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Amount (Credit to Add)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" name="amount" class="form-control" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="form-label">Date</label>
                                                <input type="date" name="txn_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Time</label>
                                                <input type="time" name="txn_time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Remarks / Description</label>
                                            <textarea name="remarks" class="form-control" required placeholder="Reason for manual top-up..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="admin_topup" class="btn btn-success">Add Funds</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Date Modal -->
                    <div class="modal fade" id="editDateModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Transaction Date</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="center_id" value="<?php echo $selected_center_id; ?>">
                                        <input type="hidden" name="txn_db_id" id="edit_txn_id">
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="form-label">New Date</label>
                                                <input type="date" name="new_date" id="edit_date" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">New Time</label>
                                                <input type="time" name="new_time" id="edit_time" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_date" class="btn btn-primary">Update Date</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/search-not-found-6275834-5210416.png" width="200" alt="Select Center" class="opacity-50">
                        <p class="mt-3 text-muted">Please select a center to manage wallet.</p>
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
                width: '100%'
            });

            // Handle Edit Click
            $('.edit-date-btn').click(function() {
                var id = $(this).data('id');
                var date = $(this).data('date');
                var time = $(this).data('time');

                $('#edit_txn_id').val(id);
                $('#edit_date').val(date);
                $('#edit_time').val(time);

                var modal = new bootstrap.Modal(document.getElementById('editDateModal'));
                modal.show();
            });
        });
    </script>
</body>
</html>
