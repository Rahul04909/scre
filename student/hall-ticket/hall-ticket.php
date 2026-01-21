<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student & Course Info
$stmtStudent = $pdo->prepare("
    SELECT s.*, 
           c.course_name, c.course_code, c.has_units, c.unit_type, c.unit_count, c.duration_value, c.duration_type,
           ac.session_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    WHERE s.id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found.");
}

// 2. Fetch All Scheduled Exams for this Student
// We need to know which units have exams to enable/disable cards.
$stmtExams = $pdo->prepare("
    SELECT es.id, sub.unit_no
    FROM exam_schedules es
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE es.course_id = ? AND es.session_id = ?
");
$stmtExams->execute([$student['course_id'], $student['session_id']]);
$all_exams = $stmtExams->fetchAll(PDO::FETCH_ASSOC);

// Group exams by unit to check availability
$exams_by_unit = [];
foreach ($all_exams as $exam) {
    $u = $exam['unit_no'] ?: 0; // 0 for non-unit or default
    if (!isset($exams_by_unit[$u])) {
        $exams_by_unit[$u] = [];
    }
    $exams_by_unit[$u][] = $exam;
}

// Helper for UI
$unit_label = ucfirst($student['unit_type'] ?? 'Semester');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hall Ticket - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .ticket-card {
            border: none;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            overflow: hidden;
        }
        .ticket-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .icon-box {
            width: 60px; height: 60px;
            background: #4F46E5; color: white; /* Indigo for Hall Ticket */
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <h4 class="fw-bold mb-4 text-dark">My Hall Tickets</h4>
                
                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Hall Tickets are generated based on the exam schedule for your course: <strong><?php echo htmlspecialchars($student['course_name']); ?></strong>
                </div>

                <div class="row g-4">
                    
                    <?php if ($student['has_units']): ?>
                        <!-- Unit Based Display -->
                        <?php for($i = 1; $i <= $student['unit_count']; $i++): ?>
                            <?php 
                                // Check if any exams exist for this unit
                                $has_exams = isset($exams_by_unit[$i]) && count($exams_by_unit[$i]) > 0;
                                $is_available = $has_exams; 
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="ticket-card p-4 h-100 <?php echo $is_available ? '' : 'bg-light'; ?>">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box <?php echo $is_available ? 'bg-indigo' : 'bg-secondary'; ?> bg-opacity-10 text-white" style="background-color: <?php echo $is_available ? '#4F46E5' : '#6c757d'; ?>">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="fw-bold mb-1"><?php echo $unit_label . ' ' . $i; ?></h5>
                                            <small class="text-muted">Admit Card</small>
                                        </div>
                                    </div>
                                    
                                    <hr class="opacity-25 my-3">
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?php echo $is_available ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border border-<?php echo $is_available ? 'success' : 'warning'; ?>-subtle">
                                            <?php echo $is_available ? 'Available' : 'Pending Schedule'; ?>
                                        </span>
                                        
                                        <?php if($is_available): ?>
                                            <a href="download-admit-card.php?unit=<?php echo $i; ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-download me-2"></i>Download
                                            </a>
                                        <?php else: ?>
                                             <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                                <i class="fas fa-clock me-2"></i>Pending
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                        
                    <?php else: ?>
                        <!-- Consolidated / Non-Unit Display -->
                         <?php 
                            // Check if ANY exams exist
                            $has_exams = count($all_exams) > 0;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="ticket-card p-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box" style="background-color: #4F46E5;">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="fw-bold mb-1">Final Admit Card</h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['course_name']); ?></small>
                                    </div>
                                </div>
                                <hr class="opacity-25 my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge <?php echo $has_exams ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border border-<?php echo $has_exams ? 'success' : 'warning'; ?>-subtle">
                                        <?php echo $has_exams ? 'Available' : 'No Schedule'; ?>
                                    </span>
                                    
                                    <?php if($has_exams): ?>
                                        <a href="download-admit-card.php" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                            <i class="fas fa-download me-2"></i>Download
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                            <i class="fas fa-ban me-2"></i>No Exams
                                        </button>
                                    <?php endif; ?>
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
