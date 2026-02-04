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
    
    <style>
        :root {
            --bg-dark: #0f172a;       /* Slate 900 */
            --bg-card: #1e293b;       /* Slate 800 */
            --bg-hover: #334155;      /* Slate 700 */
            --border-color: #334155;  /* Slate 700 */
            --text-main: #f1f5f9;     /* Slate 100 */
            --text-muted: #94a3b8;    /* Slate 400 */
            --accent-color: #06b6d4;  /* Cyan 500 */
            --accent-hover: #0891b2;  /* Cyan 600 */
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-dark); 
            color: var(--text-main);
        }

        h1, h2, h3, h4, h5, h6 { color: var(--text-main) !important; font-weight: 600; }
        .text-muted { color: var(--text-muted) !important; }

        /* Dashboard Header */
        .dashboard-header h2 { color: var(--text-main) !important; border-left: 4px solid var(--accent-color); padding-left: 15px; }

        /* Stat Cards */
        .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            position: relative;
            overflow: hidden;
            min-height: 130px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent-color);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }
        .stat-card .card-body { position: relative; z-index: 2; padding: 1.5rem; }
        .stat-card h2 { font-size: 2.2rem; font-weight: 700; margin-bottom: 5px; color: var(--text-main); }
        .stat-card p { font-size: 0.95rem; margin-bottom: 0; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .stat-card .icon-overlay {
            position: absolute;
            right: 20px;
            bottom: 15px;
            font-size: 4rem;
            opacity: 0.05;
            color: var(--text-main);
            z-index: 1;
            transform: rotate(0deg);
        }

        /* Accent Borders for Cards */
        .card-accent-blue { border-top: 3px solid #3b82f6; }
        .card-accent-green { border-top: 3px solid #10b981; }
        .card-accent-teal { border-top: 3px solid #14b8a6; }
        .card-accent-yellow { border-top: 3px solid #f59e0b; }

        /* Quick Actions */
        .action-btn {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 25px 15px;
            background: var(--bg-card);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: all 0.2s;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-muted);
        }
        .action-btn:hover {
            background-color: var(--bg-hover);
            color: var(--text-main);
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }
        .action-btn i { font-size: 1.8rem; margin-bottom: 15px; color: var(--accent-color); transition: all 0.2s; }
        .action-btn:hover i { transform: scale(1.1); filter: brightness(1.2); }
        .action-btn span { font-weight: 500; font-size: 0.9rem; }

        /* Recent Activity Table */
        .recent-activity-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .table { color: var(--text-main); margin-bottom: 0; }
        .table thead th { 
            background-color: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-muted);
            font-weight: 600;
            padding: 15px;
            letter-spacing: 0.5px;
        }
        .table td { 
            border-color: var(--border-color); 
            padding: 15px;
            vertical-align: middle;
            color: var(--text-main);
        }
        .table tr:hover td { background-color: rgba(255,255,255,0.02); }
        
        .badge-soft-success { background-color: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-soft-warning { background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        
        .avatar-circle {
            width: 35px;
            height: 35px;
            background-color: var(--bg-hover);
            color: var(--accent-color);
            border-radius: 50%;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-view-all {
            color: var(--accent-color);
            border: 1px solid var(--border-color);
            background: transparent;
            font-size: 0.85rem;
            padding: 6px 15px;
        }
        .btn-view-all:hover {
            background: var(--bg-hover);
            color: var(--text-main);
            border-color: var(--accent-color);
        }

        /* Page Wrapper */
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; background-color: var(--bg-dark); min-height: 100vh; }
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
            <div class="mb-5 dashboard-header">
                <h2 class="fw-bold mb-2">Dashboard</h2>
                <p class="text-muted ps-3">Welcome back, get an overview of your center's performance.</p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <!-- Stat 1: Total Students -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card-accent-blue h-100">
                        <div class="card-body">
                            <h2><?php echo $total_students; ?></h2>
                            <p>Total Students</p>
                        </div>
                        <i class="fas fa-users icon-overlay"></i>
                    </div>
                </div>
                
                <!-- Stat 2: Assigned Courses -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card-accent-green h-100">
                        <div class="card-body">
                            <h2><?php echo $active_courses; ?></h2>
                            <p>Assigned Courses</p>
                        </div>
                        <i class="fas fa-book icon-overlay"></i>
                    </div>
                </div>

                <!-- Stat 3: Monthly Revenue -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card-accent-teal h-100">
                        <div class="card-body">
                            <h2>0</h2> <!-- Placeholder for Revenue -->
                            <p>Monthly Revenue</p>
                        </div>
                        <span class="icon-overlay fw-bold" style="font-family: sans-serif; bottom: 15px; font-size: 3rem;">Rs</span>
                    </div>
                </div>

                <!-- Stat 4: Upcoming Exams -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card-accent-yellow h-100">
                         <div class="card-body">
                            <h2>0</h2> <!-- Placeholder for Exams -->
                            <p>Upcoming Exams</p>
                        </div>
                        <i class="fas fa-calendar-alt icon-overlay"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-5">
                <h4 class="fw-bold mb-4 ps-2 border-start border-3 border-info">Quick Actions</h4>
                <div class="row g-3">
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="students/add-student.php" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Student</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="live-class/manage-live-class.php" class="action-btn">
                            <i class="fas fa-video"></i>
                            <span>Live Class</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="exams/schedule-exam.php" class="action-btn">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Schedule Exam</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="results/manage-results.php" class="action-btn">
                            <i class="fas fa-chart-line"></i>
                            <span>Results</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="profile.php" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                     <div class="col-6 col-md-3 col-lg-2">
                        <a href="#" class="action-btn">
                            <i class="fas fa-headset"></i>
                            <span>Support</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table Mockup -->
            <div class="recent-activity-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-white">Recent Student Admissions</h5>
                    <a href="students/manage-students.php" class="btn btn-view-all">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><div class="d-flex align-items-center"><div class="avatar-circle me-3">JD</div> <strong>John Doe</strong></div></td>
                                <td>Web Development</td>
                                <td>12 Jan 2026</td>
                                <td><span class="badge badge-soft-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="d-flex align-items-center"><div class="avatar-circle me-3">AS</div> <strong>Alice Smith</strong></div></td>
                                <td>Graphic Design</td>
                                <td>11 Jan 2026</td>
                                <td><span class="badge badge-soft-warning">Pending</span></td>
                            </tr>
                             <tr>
                                <td><div class="d-flex align-items-center"><div class="avatar-circle me-3">MK</div> <strong>Mike Kohl</strong></div></td>
                                <td>Digital Marketing</td>
                                <td>10 Jan 2026</td>
                                <td><span class="badge badge-soft-success">Active</span></td>
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
