<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

// Real Stats Queries
$total_students = 0;
$active_courses = 0;
$total_enquiries = 0;
$wallet_balance = 0.00;

try {
    $center_id = $_SESSION['center_id'];

    // 1. Total Students
    $stmtStd = $pdo->prepare("SELECT COUNT(*) FROM students WHERE center_id = ?");
    $stmtStd->execute([$center_id]);
    $total_students = $stmtStd->fetchColumn();

    // 2. Assigned Courses
    $stmtCrs = $pdo->prepare("SELECT COUNT(*) FROM center_course_allotment WHERE center_id = ?");
    $stmtCrs->execute([$center_id]);
    $active_courses = $stmtCrs->fetchColumn();

    // 3. Total Enquiries (from applications table)
    $stmtEnq = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE center_id = ?");
    $stmtEnq->execute([$center_id]);
    $total_enquiries = $stmtEnq->fetchColumn();

    // 4. Wallet Balance
    $stmtWal = $pdo->prepare("SELECT wallet_balance FROM centers WHERE id = ?");
    $stmtWal->execute([$center_id]);
    $wallet_balance = $stmtWal->fetchColumn();

    // 5. Recent Enrollments (Limit 5)
    $stmtRecent = $pdo->prepare("
        SELECT s.first_name, s.last_name,c.course_name, s.created_at, s.status 
        FROM students s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.center_id = ? 
        ORDER BY s.created_at DESC 
        LIMIT 5
    ");
    $stmtRecent->execute([$center_id]);
    $recent_enrollments = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    // Handle error silently or log
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PACE Panel</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f3f4f6; 
            color: #1f2937;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 120px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .card-body {
            position: relative;
            z-index: 2;
        }
        .stat-card .icon-overlay {
            position: absolute;
            right: 15px;
            bottom: 10px;
            font-size: 5rem;
            opacity: 0.2;
            z-index: 1;
            transform: rotate(-10deg);
        }
        .stat-card h2 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0; }
        .stat-card p { font-size: 1rem; font-style: italic; margin-bottom: 0; opacity: 0.9; }
        
        /* Colors matching theme and reference */
        .bg-card-blue { background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%); } /* Matches Badge Blue */
        .bg-card-green { background: linear-gradient(135deg, #10B981 0%, #059669 100%); } /* Matches Status Dot */
        .bg-card-teal { background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%); } /* Complementary to Sidebar Teal */
        .bg-card-yellow { background: linear-gradient(135deg, #F59E0B 0%, #d97706 100%); } /* Matches Sidebar Active */
        
        .action-btn {
            border: none;
            border-radius: 12px;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #374151;
        }
        .action-btn:hover {
            background-color: #115E59; /* Sidebar Teal */
            color: white;
            transform: scale(1.02);
        }
        .action-btn i { font-size: 2rem; margin-bottom: 10px; color: #115E59; transition: color 0.2s; }
        .action-btn:hover i { color: white; }

        /* Page Wrapper */
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
    </style>
</head>
<body>
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div id="page-content-wrapper">
        <!-- Header -->
        <?php include 'header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <!-- Welcome Section -->
            <div class="mb-5">
                <h2 class="fw-bold mb-1" style="color: #115E59;">Dashboard</h2>
                <p class="text-muted">Welcome back, get an overview of your center's performance.</p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <!-- Stat 1: Total Students (Blue) -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card bg-card-blue p-3 h-100">
                        <div class="card-body">
                            <h2><?php echo $total_students; ?></h2>
                            <p>Total Students</p>
                        </div>
                        <i class="fas fa-users icon-overlay"></i>
                    </div>
                </div>
                
                <!-- Stat 2: Assigned Courses (Green) -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card bg-card-green p-3 h-100">
                        <div class="card-body">
                            <h2><?php echo $active_courses; ?></h2>
                            <p>Assigned Courses</p>
                        </div>
                        <i class="fas fa-book icon-overlay"></i>
                    </div>
                </div>

                <!-- Stat 3: Monthly Revenue (Teal) -->
                <!-- Stat 3: Total Enquiries (Teal) -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card bg-card-teal p-3 h-100">
                        <div class="card-body">
                            <h2><?php echo $total_enquiries; ?></h2>
                            <p>Total Enquiries</p>
                        </div>
                        <i class="fas fa-envelope-open-text icon-overlay"></i>
                    </div>
                </div>

                <!-- Stat 4: Wallet Balance (Yellow) -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card bg-card-yellow p-3 h-100">
                        <div class="card-body">
                            <h2>₹<?php echo number_format($wallet_balance, 0); ?></h2>
                            <p>Wallet Balance</p>
                        </div>
                        <i class="fas fa-wallet icon-overlay"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-5">
                <h4 class="fw-bold mb-3">Quick Actions</h4>
                <div class="row g-3">
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="students/add-student.php" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span class="fw-bold small">Add Student</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="live-class/manage-live-class.php" class="action-btn">
                            <i class="fas fa-video"></i>
                            <span class="fw-bold small">Live Class</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="attendance.php" class="action-btn">
                            <i class="fas fa-calendar-check"></i>
                            <span class="fw-bold small">Attendance</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="wallet/wallet.php" class="action-btn">
                            <i class="fas fa-wallet"></i>
                            <span class="fw-bold small">Wallet</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="enquiry/index.php" class="action-btn">
                            <i class="fas fa-envelope-open-text"></i>
                            <span class="fw-bold small">Enquiries</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="profile.php" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span class="fw-bold small">Settings</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Enrollments Table -->
            <div class="card stat-card bg-white text-dark p-4 shadow-sm" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Enrollments</h5>
                    <a href="students/index.php" class="btn btn-sm btn-light text-primary fw-bold">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th style="border-bottom: 1px solid #eee;">Student Name</th>
                                <th style="border-bottom: 1px solid #eee;">Course</th>
                                <th style="border-bottom: 1px solid #eee;">Date</th>
                                <th style="border-bottom: 1px solid #eee;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_enrollments) > 0): ?>
                                <?php foreach ($recent_enrollments as $student): ?>
                                    <?php 
                                        $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                        $fullName = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                                        // Status Logic (Assuming status column exists, else default to Active)
                                        $status = $student['status'] ?? 'Active'; 
                                        $statusClass = (strtolower($status) == 'active') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                                    ?>
                                    <tr>
                                        <td class="py-3" style="border-bottom: 1px solid #f8f9fa;">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 40px; height: 40px; color: #555; font-weight: 600;">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <span class="fw-bold"><?php echo $fullName; ?></span>
                                            </div>
                                        </td>
                                        <td style="border-bottom: 1px solid #f8f9fa; color: #555;">
                                            <?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?>
                                        </td>
                                        <td style="border-bottom: 1px solid #f8f9fa; color: #555;">
                                            <?php echo date('d M Y', strtotime($student['created_at'])); ?>
                                        </td>
                                        <td style="border-bottom: 1px solid #f8f9fa;">
                                            <span class="badge <?php echo $statusClass; ?> px-3 py-2 rounded-pill" style="font-weight: 500;">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent enrollments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
