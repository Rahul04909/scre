<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch Current Balance
$stmt = $pdo->prepare("SELECT wallet_balance, royalty_percentage FROM centers WHERE id = ?");
$stmt->execute([$_SESSION['center_id']]);
$center = $stmt->fetch();
$balance = $center['wallet_balance'] ?? 0;
$royalty = $center['royalty_percentage'] ?? 0;

// Fetch Transactions
$txnStmt = $pdo->prepare("SELECT * FROM center_wallet_transactions WHERE center_id = ? ORDER BY created_at DESC");
$txnStmt->execute([$_SESSION['center_id']]);
$transactions = $txnStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Razorpay Scripts -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        /* Balance Card */
        .balance-card {
            background: linear-gradient(135deg, #115E59 0%, #0f766e 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        .balance-card::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 150px; height: 150px; background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        /* Top-up Form */
        .topup-card {
            border: none; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .txn-table th { font-weight: 600; color: #4b5563; background-color: #f9fafb; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .txn-table td { font-size: 0.95rem; vertical-align: middle; }
        
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <?php include '../sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
        <?php include '../header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <h2 class="fw-bold mb-4" style="color: #115E59;">My Wallet</h2>

            <div class="row g-4 mb-5">
                <!-- Balance Section -->
                <div class="col-lg-5">
                    <div class="balance-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <p class="text-white-50 mb-1 fw-medium">Available Balance</p>
                            <h1 class="fw-bold display-4 mb-0">₹<?php echo number_format($balance, 2); ?></h1>
                        </div>
                        <div class="mt-4 pt-3 border-top border-white-50 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="d-block text-white-50">Current Royalty Rate</small>
                                <span class="fw-bold text-warning fs-5"><?php echo $royalty; ?>%</span>
                            </div>
                            <i class="fas fa-wallet fa-3x text-white-50 opacity-25"></i>
                        </div>
                    </div>
                </div>

                <!-- Top-up Form -->
                <div class="col-lg-7">
                    <div class="card topup-card h-100">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Top-up Wallet</h5>
                        </div>
                        <div class="card-body">
                           <form id="topupForm">
                                <div class="mb-4">
                                    <label class="form-label text-muted fw-bold small text-uppercase">Amount to Add (Credit)</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0">₹</span>
                                        <input type="number" id="amount" class="form-control border-start-0 ps-0 fw-bold" placeholder="E.g. 1000" min="1" required>
                                    </div>
                                    <div class="form-text mt-2" id="calculationText">
                                         Enter amount to calculate payable.
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 rounded-3 d-flex align-items-center" role="alert">
                                    <i class="fas fa-info-circle fs-4 me-3"></i>
                                    <div>
                                        You pay only <strong><?php echo $royalty; ?>%</strong> Royalty. 
                                        Rest is covered by Sir Chhotu Ram Education Pvt. Ltd.
                                    </div>
                                </div>

                                <button type="submit" id="payBtn" class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                                    Proceed to Pay <span id="btnAmount"></span>
                                </button>
                           </form>
                           <div id="msg" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-secondary">Transaction History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 txn-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Transaction ID</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end pe-4">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transactions) > 0): ?>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td class="ps-4 text-secondary"><?php echo date('M d, Y h:i A', strtotime($txn['created_at'])); ?></td>
                                        <td class="font-monospace text-primary"><?php echo htmlspecialchars($txn['transaction_id']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($txn['description'] ?? 'Wallet Top-up'); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo ($txn['type'] == 'debit') ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?> text-uppercase" style="font-size: 0.75rem;">
                                                <?php echo $txn['type']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold <?php echo ($txn['type'] == 'debit') ? 'text-danger' : 'text-success'; ?>">
                                            <?php 
                                            // Show amount based on type
                                            if ($txn['type'] == 'debit') {
                                                echo '-₹' . number_format($txn['amount_debit'], 2);
                                            } else {
                                                echo '+₹' . number_format($txn['amount_credit'], 2);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($txn['status'] == 'success'): ?>
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Success</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><?php echo ucfirst($txn['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-wallet-2-5407077-4516480.png" width="100" class="mb-3 opacity-50" alt="Empty">
                                        <p>No transactions yet.</p>
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

<script>
    const royaltyPercent = <?php echo floatval($royalty); ?>;
    const amountInput = document.getElementById('amount');
    const calcText = document.getElementById('calculationText');
    const payBtn = document.getElementById('payBtn');
    const msgDiv = document.getElementById('msg');

    // Real-time Calculation
    amountInput.addEventListener('input', function() {
        const amt = parseFloat(this.value);
        if (amt && amt > 0) {
            const payable = (amt * royaltyPercent) / 100;
            calcText.innerHTML = `You will receive <strong class='text-success'>₹${amt}</strong> credit by paying <strong class='text-primary'>₹${payable.toFixed(2)}</strong>.`;
            document.getElementById('btnAmount').innerText = `₹${payable.toFixed(2)}`;
        } else {
            calcText.innerText = "Enter amount to calculate payable.";
            document.getElementById('btnAmount').innerText = "";
        }
    });

    document.getElementById('topupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const amt = parseFloat(amountInput.value);
        
        if (!amt || amt <= 0) {
            alert("Please enter a valid amount");
            return;
        }

        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        // 1. Create Order
        fetch('create-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: amt })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 2. Open Razorpay
                const options = {
                    "key": data.key_id,
                    "amount": data.payable_amount * 100, // paise
                    "currency": data.currency,
                    "name": data.center_name,
                    "description": "Wallet Top-up",
                    "order_id": data.razorpay_order_id,
                    "handler": function (response){
                        verifyPayment(response, amt);
                    },
                    "prefill": {
                        "name": data.center_name,
                        "email": data.email,
                        "contact": data.mobile
                    },
                    "theme": { "color": "#115E59" }
                };
                const rzp = new Razorpay(options);
                rzp.open();
                
                rzp.on('payment.failed', function (response){
                    msgDiv.innerHTML = `<div class="alert alert-danger">Payment Failed: ${response.error.description}</div>`;
                    payBtn.disabled = false;
                    payBtn.innerText = 'Proceed to Pay';
                });

            } else {
                msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                payBtn.disabled = false;
                payBtn.innerText = 'Proceed to Pay';
            }
        })
        .catch(err => {
            console.error(err);
            msgDiv.innerHTML = `<div class="alert alert-danger">An error occurred. Try again.</div>`;
            payBtn.disabled = false;
            payBtn.innerText = 'Proceed to Pay';
        });
    });

    function verifyPayment(response, requestedAmount) {
        msgDiv.innerHTML = `<div class="alert alert-info">Verifying Payment...</div>`;
        
        fetch('verify-payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
                requested_amount: requestedAmount // Pass original credit amount
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msgDiv.innerHTML = `<div class="alert alert-success">Top-up Successful! Refreshing...</div>`;
                setTimeout(() => location.reload(), 2000);
            } else {
                msgDiv.innerHTML = `<div class="alert alert-danger">Verification Failed: ${data.message}</div>`;
                payBtn.disabled = false;
                payBtn.innerText = 'Proceed to Pay';
            }
        })
        .catch(err => {
            msgDiv.innerHTML = `<div class="alert alert-danger">Verification Error. Check transactions.</div>`;
        });
    }
</script>

</body>
</html>
