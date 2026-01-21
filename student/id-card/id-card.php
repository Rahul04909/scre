<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch Student Info with Course & Center
$stmtStudent = $pdo->prepare("
    SELECT s.*, 
           c.course_name, c.course_code,
           cen.center_name, cen.center_code,
           ac.session_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    LEFT JOIN centers cen ON s.center_id = cen.id
    WHERE s.id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .id-card-preview-container {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }
        .id-card-placeholder {
            max-width: 400px;
            margin: 0 auto;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 40px;
            color: #6c757d;
            background: white;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <h4 class="fw-bold mb-4 text-dark">My ID Card</h4>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title fw-bold text-primary mb-0">Student Identity Card</h5>
                                    <a href="download-id-card.php" target="_blank" class="btn btn-primary rounded-pill px-4">
                                        <i class="fas fa-download me-2"></i>Download PDF
                                    </a>
                                </div>
                                
                                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please ensure your profile details and photo are correct. If you find any discrepancy, contact your center administrator immediately.
                                </div>

                                <div class="id-card-preview-container">
                                    <div class="id-card-placeholder">
                                        <i class="fas fa-id-card fa-3x mb-3 text-secondary"></i>
                                        <h6 class="fw-bold text-dark">ID Card Preview</h6>
                                        <p class="small text-muted mb-0">
                                            Click the download button to generate and view your official ID Card.
                                        </p>
                                    </div>
                                    <div class="mt-3 text-muted small">
                                        <strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> <br>
                                        <strong>Enrollment No:</strong> <?php echo htmlspecialchars($student['enrollment_no']); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">Photo & Signature</h6>
                                <div class="text-center mb-3">
                                    <?php if(!empty($student['student_image'])): ?>
                                        <img src="../../<?php echo $student['student_image']; ?>" class="rounded border p-1" style="width: 100px; height: 120px; object-fit: cover;" alt="Student Photo">
                                    <?php else: ?>
                                        <div class="bg-light d-inline-flex align-items-center justify-content-center rounded border p-1" style="width: 100px; height: 120px;">
                                            <span class="text-muted small">No Photo</span>
                                        </div>
                                    <?php endif; ?>
                                    <p class="small text-muted mt-1 mb-3">Profile Photo</p>
                                    
                                    <?php if(!empty($student['student_signature'])): ?>
                                        <img src="../../<?php echo $student['student_signature']; ?>" class="border p-1" style="width: 150px; height: 40px; object-fit: contain;" alt="Signature">
                                    <?php else: ?>
                                        <div class="bg-light d-inline-block rounded border p-1" style="width: 150px; height: 40px;">
                                            <span class="text-muted small">No Signature</span>
                                        </div>
                                    <?php endif; ?>
                                    <p class="small text-muted mt-1 mb-0">Signature</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
