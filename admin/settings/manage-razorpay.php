<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Save
if (isset($_POST['save_settings'])) {
    $key_id = trim($_POST['key_id']);
    $key_secret = trim($_POST['key_secret']);
    $currency = trim($_POST['currency']);

    try {
        $sql = "UPDATE razorpay_settings SET key_id = :key_id, key_secret = :key_secret, currency = :currency LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':key_id' => $key_id,
            ':key_secret' => $key_secret,
            ':currency' => $currency
        ]);
        $message = "Razorpay Settings saved successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Settings
try {
    $stmt = $pdo->query("SELECT * FROM razorpay_settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Fetch Recent Transaction Logs for display
try {
    $logStmt = $pdo->query("SELECT * FROM razorpay_payments ORDER BY created_at DESC LIMIT 5");
    $logs = $logStmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Razorpay - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Razorpay Checkout JS -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <h2 class="mb-4">Razorpay Configuration</h2>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-key me-2"></i> API Keys</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Key ID</label>
                                        <input type="text" name="key_id" class="form-control" value="<?php echo htmlspecialchars($settings['key_id'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Key Secret</label>
                                        <input type="password" name="key_secret" class="form-control" value="<?php echo htmlspecialchars($settings['key_secret'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Currency</label>
                                        <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? 'INR'); ?>" required>
                                    </div>
                                    <button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Keys</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Live Test Section -->
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-success fw-bold"><i class="fas fa-vial me-2"></i> Live Test Payment</h5>
                            </div>
                            <div class="card-body text-center">
                                <p class="text-muted">Initiate a mock transaction of ₹1 to verify integration.</p>
                                <button id="pay-btn" class="btn btn-success btn-lg"><i class="fas fa-credit-card me-2"></i> Pay ₹1 Now</button>
                                <div id="payment-status" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-dark fw-bold">Recent Test Transactions</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Email</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($logs) > 0): ?>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($log['payment_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['currency'] . ' ' . $log['amount']); ?></td>
                                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($log['status']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center text-muted">No transactions recorded yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logic for Razorpay Payment -->
    <script>
    document.getElementById('pay-btn').onclick = function(e){
        e.preventDefault();
        
        var keyId = "<?php echo $settings['key_id'] ?? ''; ?>";
        if(!keyId) {
            alert("Please save Key ID first.");
            return;
        }

        var options = {
            "key": keyId, 
            "amount": "100", // 100 paise = INR 1
            "currency": "INR",
            "name": "Ace Admin",
            "description": "Test Transaction",
            "image": "https://example.com/logo.png",
            "handler": function (response){
                // On success, verify with backend
                document.getElementById('payment-status').innerHTML = '<span class="text-info">Verifying payment...</span>';
                
                fetch('verify-razorpay-payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        document.getElementById('payment-status').innerHTML = '<span class="text-success fw-bold">Payment Captured: ' + response.razorpay_payment_id + '</span>';
                        setTimeout(() => location.reload(), 2000); // Reload to show in table
                    } else {
                        document.getElementById('payment-status').innerHTML = '<span class="text-danger">Verification Failed: ' + data.message + '</span>';
                    }
                })
                .catch(err => {
                    document.getElementById('payment-status').innerHTML = '<span class="text-danger">Error verifying payment.</span>';
                });
            },
            "prefill": {
                "name": "Admin Tester",
                "email": "test@example.com",
                "contact": "9999999999"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
        rzp1.on('payment.failed', function (response){
                alert("Payment Failed: " + response.error.description);
        });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
