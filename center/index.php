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
                            <h2>₹<?php echo number_format($wallet_balance, 2); ?></h2>
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
                        <a href="exams/schedule-exam.php" class="action-btn">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="fw-bold small">Schedule Exam</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="results/manage-results.php" class="action-btn">
                            <i class="fas fa-chart-line"></i>
                            <span class="fw-bold small">Results</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="profile.php" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span class="fw-bold small">Settings</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="#" class="action-btn">
                            <i class="fas fa-headset"></i>
                            <span class="fw-bold small">Support</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table Mockup -->
            <div class="card stat-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Student Admissions</h5>
                    <a href="students/manage-students.php" class="btn btn-sm btn-light text-primary fw-bold">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2" style="width:32px;height:32px;">JD</div> <strong>John Doe</strong></div></td>
                                <td>Web Development</td>
                                <td>12 Jan 2026</td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2" style="width:32px;height:32px;">AS</div> <strong>Alice Smith</strong></div></td>
                                <td>Graphic Design</td>
                                <td>11 Jan 2026</td>
                                <td><span class="badge bg-warning bg-opacity-10 text-warning">Pending</span></td>
                            </tr>
                             <tr>
                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2" style="width:32px;height:32px;">MK</div> <strong>Mike Kohl</strong></div></td>
                                <td>Digital Marketing</td>
                                <td>10 Jan 2026</td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
