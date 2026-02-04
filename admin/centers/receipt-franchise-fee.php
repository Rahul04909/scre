<?php
require_once '../../database/config.php';

$txn_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($txn_id <= 0) {
    die("Invalid Transaction ID");
}

try {
    // Fetch Transaction & Center Details
    $sql = "SELECT p.*, c.center_name, c.center_code, c.address, 
                   cities.name as city_name, states.name as state_name, 
                   c.mobile, c.email
            FROM center_franchise_payments p
            JOIN centers c ON p.center_id = c.id
            LEFT JOIN cities ON c.city = cities.id
            LEFT JOIN states ON c.state = states.id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$txn_id]);
    $txn = $stmt->fetch();

    if (!$txn) {
        die("Transaction not found.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo htmlspecialchars($txn['transaction_id']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .receipt-container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px; }
        .receipt-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .brand-color { color: #115E59; }
        .table-borderless td { padding: 8px 0; }
        @media print {
            body { background: white; }
            .receipt-container { box-shadow: none; margin: 0; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center receipt-header">
            <div>
                <h2 class="fw-bold brand-color mb-0">PACE FOUNDATION</h2>
                <p class="text-muted small mb-0">Software Center for Research & Education</p>
            </div>
            <div class="text-end">
                <h4 class="mb-1 text-uppercase text-secondary">Fee Receipt</h4>
                <div class="fw-bold fs-5 text-dark">#<?php echo htmlspecialchars($txn['transaction_id']); ?></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row mb-5">
            <div class="col-6">
                <h6 class="text-uppercase text-secondary small fw-bold mb-3">Received From:</h6>
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($txn['center_name']); ?></h5>
                <p class="mb-0 text-muted">Did: <?php echo htmlspecialchars($txn['center_code']); ?></p>
                <p class="mb-0 text-muted"><?php echo htmlspecialchars($txn['city_name'] . ', ' . $txn['state_name']); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($txn['address']); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($txn['mobile']); ?></p>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-uppercase text-secondary small fw-bold mb-3">Payment Details:</h6>
                <table class="table table-borderless mb-0 w-auto ms-auto">
                    <tr>
                        <td class="text-muted pe-3">Date:</td>
                        <td class="fw-bold text-end"><?php echo date('d M, Y', strtotime($txn['payment_date'])); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted pe-3">Mode:</td>
                        <td class="fw-bold text-end"><?php echo htmlspecialchars($txn['payment_mode']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Amount -->
        <div class="card bg-light border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5 text-muted">Total Amount Received</span>
                    <span class="fs-2 fw-bold brand-color">₹<?php echo number_format($txn['amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <?php if (!empty($txn['remarks'])): ?>
            <div class="mb-5">
                <h6 class="fw-bold text-secondary text-uppercase small">Remarks / Description</h6>
                <p class="text-muted"><?php echo htmlspecialchars($txn['remarks']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center">
            <p class="text-muted small mb-1">This is a potential computer generated receipt.</p>
            <p class="fw-bold brand-color">Thank you for your business!</p>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print me-2"></i> Print Receipt</button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">Close</button>
        </div>
    </div>

    <!-- FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
