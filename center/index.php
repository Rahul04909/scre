<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

// Mock Stats (Replace with real DB queries later)
$total_students = 120; // Example
$active_courses = 8;
$pending_fees = "₹24,000";
$recent_admissions = 5;

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
    <!-- Custom Styles -->
    <link href="assets/css/sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/dashboard.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
    
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div id="page-content-wrapper">
            <!-- Header -->
            <?php include 'header.php'; ?>

            <div class="container-fluid px-4 py-4">
                <!-- Welcome Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold">Dashboard</h2>
                        <p class="text-muted mb-0">Welcome back, get an overview of your center's performance.</p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-5">
                    <!-- Stat 1: Total Students (Blue) -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-primary-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Total Students</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $total_students; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stat 2: Assigned Courses (Green) -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-success-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Assigned Courses</div>
                                        <div class="h3 mb-0 fw-bold"><?php echo $active_courses; ?></div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stat 3: Monthly Revenue (Info/Cyan) -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-info-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Monthly Revenue</div>
                                        <div class="h3 mb-0 fw-bold">₹0</div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stat 4: Upcoming Exams (Warning/Yellow) -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stats-card bg-gradient-warning-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase small fw-bold mb-1">Upcoming Exams</div>
                                        <div class="h3 mb-0 fw-bold">0</div>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-5">
                    <h5 class="fw-bold mb-3">Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="students/add-student.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-user-plus fs-4"></i>
                                <span class="small fw-bold">Add Student</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="live-class/manage-live-class.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-video fs-4"></i>
                                <span class="small fw-bold">Live Class</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="exams/schedule-exam.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-calendar-alt fs-4"></i>
                                <span class="small fw-bold">Schedule Exam</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="results/manage-results.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-chart-line fs-4"></i>
                                <span class="small fw-bold">Results</span>
                            </a>
                        </div>
                         <div class="col-6 col-md-3 col-lg-2">
                            <a href="profile.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-cog fs-4"></i>
                                <span class="small fw-bold">Settings</span>
                            </a>
                        </div>
                         <div class="col-6 col-md-3 col-lg-2">
                            <a href="#" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                <i class="fas fa-headset fs-4"></i>
                                <span class="small fw-bold">Support</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">Recent Student Admissions</h6>
                        <a href="students/manage-students.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Student Name</th>
                                        <th>Course</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 border" style="width:32px;height:32px;">JD</div> 
                                                <strong>John Doe</strong>
                                            </div>
                                        </td>
                                        <td>Web Development</td>
                                        <td>12 Jan 2026</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 border" style="width:32px;height:32px;">AS</div> 
                                                <strong>Alice Smith</strong>
                                            </div>
                                        </td>
                                        <td>Graphic Design</td>
                                        <td>11 Jan 2026</td>
                                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    </tr>
                                     <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 border" style="width:32px;height:32px;">MK</div> 
                                                <strong>Mike Kohl</strong>
                                            </div>
                                        </td>
                                        <td>Digital Marketing</td>
                                        <td>10 Jan 2026</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Use the sidebar interactive script from admin if compatible, or simple toggle here
        $("#sidebar-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>
</body>
</html>
