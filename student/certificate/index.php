<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../../database/config.php';

$student_id = $_SESSION['student_id'];

// Fetch Student Course Info and Result Status
$sql = "
    SELECT s.*, c.course_name, c.has_units, c.unit_type
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student data not found.");
}

// Check if student has passed all required exams to generally qualify for certificate
// For simplicity, we check if they have a 'Pass' status in their consolidated view or specific logic.
// Logic: Fetch all exam results. If any failed or not attempted? 
// Let's assume for now keeping it simple: If they have data, let them try to download. 
// Or better: Check if they passed.
// Reusing logic:
$sqlResults = "SELECT result_status FROM exam_results WHERE student_id = ?";
$stmtRes = $pdo->prepare($sqlResults);
$stmtRes->execute([$student_id]);
$results = $stmtRes->fetchAll(PDO::FETCH_COLUMN);

$has_results = count($results) > 0;
$is_pass = true;
if (!$has_results) {
    $is_pass = false; // No exams yet
} else {
    foreach ($results as $status) {
        if ($status !== 'Pass') {
            $is_pass = false; // Has a fail
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Certificate - SCRE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../student/assets/css/sidebar.css" rel="stylesheet">
    <link href="../../student/assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../../student/sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include '../../student/header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <h2 class="fs-2 mb-4 text-dark border-bottom pb-2">My Certificate</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-certificate text-warning fa-4x"></i>
                                </div>
                                <h4 class="mb-3"><?= htmlspecialchars($student['course_name']) ?></h4>
                                
                                <?php if ($is_pass): ?>
                                    <div class="alert alert-success d-inline-block mb-3 px-4">
                                        <i class="fas fa-check-circle me-2"></i> Eligible
                                    </div>
                                    <br>
                                    <a href="download-certificate.php" target="_blank" class="btn btn-primary btn-lg rounded-pill px-5">
                                        <i class="fas fa-download me-2"></i> Download Certificate
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i> 
                                        You are not eligible for the certificate yet. <br>
                                        <small>(Complete all exams with PASS status)</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../student/assets/js/sidebar.js"></script>
</body>
</html>
