<?php
require_once 'includes/auth.php';
require_once '../database/config.php';

// Fetch Basic Stats if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Typing Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .content-wrapper { margin-left: 260px; min-height: calc(100vh - 70px); padding: 2rem; }
        
        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .bg-teal-soft { background-color: #ccfbf1; color: #0f766e; }
        .bg-blue-soft { background-color: #dbeafe; color: #1e40af; }
        .bg-purple-soft { background-color: #f3e8ff; color: #7e22ce; }
        
        .welcome-banner {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 200px; height: 100%;
            background: url('https://img.freepik.com/free-vector/typing-concept-illustration_114360-3944.jpg?w=740'); 
            background-size: cover;
            opacity: 0.2;
            mask-image: linear-gradient(to left, black, transparent);
        }
    </style>
</head>
<body>
    
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <div class="content-wrapper">
        <div class="welcome-banner mb-4 shadow">
            <h2 class="fw-bold">Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['student_name'])[0]); ?>! 👋</h2>
            <p class="mb-0 opacity-75">Ready to improve your typing speed today?</p>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-teal-soft me-3">
                            <i class="fas fa-keyboard"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small fw-bold">WPM (Last)</p>
                            <h4 class="fw-bold mb-0">0 WPM</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-blue-soft me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small fw-bold">Accuracy</p>
                            <h4 class="fw-bold mb-0">0%</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-purple-soft me-3">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small fw-bold">Tests Taken</p>
                            <h4 class="fw-bold mb-0">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card stat-card h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" width="150" class="mb-3 opacity-50">
                        <p class="text-muted">No practice history found yet. Start a lesson!</p>
                        <a href="lessons.php" class="btn btn-primary">Start Practice</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="card stat-card h-100 bg-dark text-white" style="background: #1e293b !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> Quick Tip</h5>
                        <p class="opacity-75" style="line-height: 1.8;">"Touch typing is all about muscle memory. Try not to look at the keyboard, even if you make mistakes. Speed comes with accuracy!"</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
