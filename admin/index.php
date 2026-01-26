<?php
require_once '../database/config.php';
$sidebarPrefix = '../';

// Initialize stats
$stats = [
    'centers' => 0,
    'courses' => 0,
    'categories' => 0,
    'subjects' => 0
];

try {
    // Centers Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM centers");
    $stats['centers'] = $stmt->fetchColumn();

    // Courses Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $stats['courses'] = $stmt->fetchColumn();

    // Categories Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM course_categories");
    $stats['categories'] = $stmt->fetchColumn();
    
    // Subjects Count (checking if table exists, handle gracefully if not)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM subjects"); // Assuming 'subjects' table based on sidebar
        $stats['subjects'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $stats['subjects'] = 0; // Table might be named differently
    }

    // Recent Centers
    $stmt = $pdo->query("SELECT * FROM centers ORDER BY created_at DESC LIMIT 5");
    $recentCenters = $stmt->fetchAll();

    // Recent Transactions (Wallet) - Pagination Logic
    $limit = 10;
    $log_page = isset($_GET['log_page']) ? (int)$_GET['log_page'] : 1;
    $offset = ($log_page - 1) * $limit;

    // Get Total Count
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM center_wallet_transactions");
    $total_logs = $stmtCount->fetchColumn();
    $total_pages = ceil($total_logs / $limit);

    // Fetch Paginated Logs
    $stmtTxn = $pdo->prepare("
        SELECT t.*, c.center_name, c.center_code 
        FROM center_wallet_transactions t 
        JOIN centers c ON t.center_id = c.id 
        ORDER BY t.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmtTxn->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmtTxn->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtTxn->execute();
    $recentTransactions = $stmtTxn->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <style>
        .stats-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .bg-gradient-primary-custom { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
        .bg-gradient-success-custom { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
        .bg-gradient-info-custom { background: linear-gradient(45deg, #36b9cc, #258391); color: white; }
        .bg-gradient-warning-custom { background: linear-gradient(45deg, #f6c23e, #dda20a); color: white; }
        
        .card-header-custom {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            font-weight: 700;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 380px;">
            <div class="container-fluid py-4 px-4" style="max-width: 100%;">
                
                <!-- Welcome Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark">Dashboard</h2>
                        <p class="text-muted mb-0">Welcome back to your admin panel.</p>
                    </div>
                    <div>
                        <button class="btn btn-primary shadow-sm"><i class="fas fa-download me-2"></i> Generate Report</button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-5">
                    <!-- Centers Card -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-primary-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Total Centers</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $stats['centers']; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Card -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-success-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Total Courses</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $stats['courses']; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Card -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-info-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Categories</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $stats['categories']; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-list"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Card -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-warning-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Subjects</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $stats['subjects']; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities / Tables -->
                <div class="row">
                    <!-- Recent Centers -->
                    <!-- Recent Centers -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Recent Centers</h6>
                                <a href="centers/manage-centers.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Code</th>
                                                <th>Name</th>
                                                <th>City</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recentCenters) > 0): ?>
                                                <?php foreach ($recentCenters as $center): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($center['center_code']); ?></td>
                                                        <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($center['city']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $center['is_active'] ? 'success' : 'secondary'; ?>">
                                                                <?php echo $center['is_active'] ? 'Active' : 'Inactive'; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center py-3 text-muted">No centers found.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Transaction Logs Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Transaction Logs</h6>
                                <a href="centers/manage-center-wallet.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Date & Time</th>
                                                <th>Center</th>
                                                <th>Amount Credit</th>
                                                <th class="text-end pe-4">Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recentTransactions) > 0): ?>
                                                <?php foreach ($recentTransactions as $txn): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <span class="fw-bold d-block"><?php echo date('d M Y', strtotime($txn['created_at'])); ?></span>
                                                            <small class="text-muted"><?php echo date('h:i A', strtotime($txn['created_at'])); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($txn['center_name']); ?></td>
                                                        <td>
                                                            <span class="text-success fw-bold">+₹<?php echo number_format($txn['amount_credit'], 2); ?></span>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <span class="badge bg-light text-dark border"><?php echo $txn['center_code']; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center py-3 text-muted">No recent transactions.</td></tr>
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
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sidebar.js"></script>

    <script>
        // Ensure sidebar toggle works if not handled in sidebar.js for this specific path level
        // (Assuming sidebar.js handles it generally, otherwise might need adjustment)
    </script>
</body>
</html>
