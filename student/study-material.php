<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

$student_id = $_SESSION['student_id'];

// Fetch Student Course
$stmtStudent = $pdo->prepare("SELECT course_id FROM students WHERE id = ?");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}
$course_id = $student['course_id'];

// Fetch Materials
// Link with Subjects and Course to get names
$sql = "SELECT sm.*, s.subject_name 
        FROM study_materials sm 
        LEFT JOIN subjects s ON sm.subject_id = s.id 
        WHERE sm.course_id = ? 
        ORDER BY sm.unit_no ASC, sm.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$course_id]);
$all_materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by Unit
$grouped_materials = [];
$general_materials = [];

foreach ($all_materials as $mat) {
    if (!empty($mat['unit_no'])) {
        $grouped_materials[$mat['unit_no']][] = $mat;
    } else {
        $general_materials[] = $mat;
    }
}
ksort($grouped_materials); // Sort units e.g. 1, 2, 3

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Material - PACE Student</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <style>
        .material-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .material-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-color: #115E59;
        }
        .file-icon { font-size: 24px; color: #dc3545; margin-right: 15px; }
        .unit-header { background: #f8f9fa; padding: 10px 15px; border-radius: 8px; font-weight: 600; margin: 20px 0 10px; color: #333; }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>

            <div class="container-fluid px-4 py-5">
                <h2 class="fw-bold mb-4" style="color: #115E59;">Study Material</h2>

                <?php if (empty($all_materials)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No study material uploaded for your course yet.
                    </div>
                <?php else: ?>

                    <!-- General Materials (No Unit) -->
                    <?php if (!empty($general_materials)): ?>
                        <h5 class="unit-header">General Materials</h5>
                        <div class="row">
                            <?php foreach($general_materials as $mat): ?>
                                <div class="col-md-6">
                                    <div class="material-card">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-pdf file-icon"></i>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($mat['title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $mat['subject_name'] ? htmlspecialchars($mat['subject_name']) : 'General'; ?>
                                                    &bull; <?php echo date('d M Y', strtotime($mat['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <a href="../<?php echo htmlspecialchars($mat['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Grouped by Unit -->
                    <?php foreach($grouped_materials as $unit => $materials): ?>
                        <h5 class="unit-header">Unit <?php echo $unit; ?></h5>
                        <div class="row">
                            <?php foreach($materials as $mat): ?>
                                <div class="col-md-6">
                                    <div class="material-card">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-pdf file-icon"></i>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($mat['title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $mat['subject_name'] ? htmlspecialchars($mat['subject_name']) : 'Subject: N/A'; ?>
                                                    &bull; <?php echo date('d M Y', strtotime($mat['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <a href="../<?php echo htmlspecialchars($mat['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
