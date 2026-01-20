<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student & Course Info
$stmt = $pdo->prepare("
    SELECT s.*, c.course_name, c.course_code, c.has_units, c.unit_type, c.unit_count, c.duration_value, c.duration_type
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found.");
}

// 2. Fetch Completed Exams/Results to determine availability
// We check if student has ANY result for a unit to enable download? 
// Or ideally, all exams for that unit. For now, let's enable if at least one result exists.
$stmtResults = $pdo->prepare("SELECT DISTINCT unit_no FROM exam_results WHERE student_id = ?");
$stmtResults->execute([$student_id]);
$completed_units = $stmtResults->fetchAll(PDO::FETCH_COLUMN);

// Helper for UI
$unit_label = ucfirst($student['unit_type'] ?? 'Semester');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Marksheets - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .marksheet-card {
            border: none;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            overflow: hidden;
        }
        .marksheet-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .icon-box {
            width: 60px; height: 60px;
            background: #115E59; color: white;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .locked-overlay {
            background: #f3f4f6;
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <h4 class="fw-bold mb-4 text-dark">My Marksheets</h4>
                
                <div class="row g-4">
                    
                    <?php if ($student['has_units']): ?>
                        <!-- Unit Based Display -->
                        <?php for($i = 1; $i <= $student['unit_count']; $i++): ?>
                            <?php 
                                $is_available = in_array($i, $completed_units); 
                                // For demo, assuming available if at least one result.
                                // In real app, check if ALL exams completed.
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="marksheet-card p-4 h-100 <?php echo $is_available ? '' : 'bg-light'; ?>">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box <?php echo $is_available ? 'bg-success' : 'bg-secondary'; ?> bg-opacity-10 text-<?php echo $is_available ? 'success' : 'secondary'; ?>">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="fw-bold mb-1"><?php echo $unit_label . ' ' . $i; ?></h5>
                                            <small class="text-muted"><?php echo htmlspecialchars($student['course_name']); ?></small>
                                        </div>
                                    </div>
                                    
                                    <hr class="opacity-25 my-3">
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?php echo $is_available ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border border-<?php echo $is_available ? 'success' : 'warning'; ?>-subtle">
                                            <?php echo $is_available ? 'Available' : 'Pending'; ?>
                                        </span>
                                        
                                        <?php if($is_available): ?>
                                            <a href="marksheet/download-marksheet.php?unit=<?php echo $i; ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-download me-2"></i>Download
                                            </a>
                                        <?php else: ?>
                                             <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                                <i class="fas fa-lock me-2"></i>Locked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                        
                    <?php else: ?>
                        <!-- Consolidated / Non-Unit Display -->
                         <?php 
                            // For non-unit, check if ANY result exists (unit_no would be 1 or 0)
                            $is_available = !empty($completed_units);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="marksheet-card p-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-1">Final Marksheet</h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['course_name']); ?></small>
                                    </div>
                                </div>
                                <hr class="opacity-25 my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>
                                    <a href="marksheet/download-marksheet.php" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="fas fa-download me-2"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
