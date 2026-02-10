<?php
// student/id-card.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../database/config.php';

// Prepare data for header (optional, if header uses student name etc)
// Header usually fetches its own data or relies on session.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My ID Card - Student Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    
    <style>
        .id-card-preview-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        .id-card-image {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <div class="row">
                    <div class="col-12">
                        <h2 class="fw-bold mb-4">Student ID Card</h2>
                        
                        <div class="id-card-preview-container">
                            <div class="mb-4">
                                <h5 class="text-muted mb-3">Live Preview</h5>
                                <!-- The ID Card Image -->
                                <img src="id-card/download-id-card.php?preview=1" alt="Student ID Card" class="id-card-image">
                            </div>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-print me-2"></i> Print Preview
                                </button>
                                <a href="id-card/download-id-card.php" class="btn btn-success rounded-pill px-4">
                                    <i class="fas fa-download me-2"></i> Download Image
                                </a>
                            </div>
                            
                            <div class="mt-3 text-muted small">
                                <i class="fas fa-info-circle me-1"></i> 
                                Use the "Download Image" button to get the high-quality PNG file.
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sidebar.js"></script>
</body>
</html>
