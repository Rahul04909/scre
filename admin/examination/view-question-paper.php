<?php
require_once '../../database/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage-question-paper.php");
    exit;
}

$exam_id = intval($_GET['id']);

// Fetch Exam Details
$sql = "SELECT e.*, c.course_name, s.subject_name 
        FROM exams e
        JOIN courses c ON e.course_id = c.id
        JOIN subjects s ON e.subject_id = s.id
        WHERE e.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Exam not found.");
}

// Fetch Questions
$stmtQ = $pdo->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY id ASC");
$stmtQ->execute([$exam_id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Question Paper - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .highlight-box { background: #f8f9fa; border-radius: 8px; padding: 20px; }
        .question-item { border-bottom: 1px solid #eee; padding: 20px 0; }
        .question-item:last-child { border-bottom: none; }
        .correct-opt { background-color: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; font-weight: bold; }
        .opt-box { padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="margin-left: 280px; flex-grow: 1;">
            <div class="container-fluid py-5 px-lg-5">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary fw-bold">View Question Paper</h2>
                    <a href="manage-question-paper.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
                </div>

                <!-- Exam Metadata -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body highlight-box">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="text-muted small">Course</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($exam['course_name']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Subject</label>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($exam['subject_name']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Serial No</label>
                                <div class="fw-bold"><?php echo htmlspecialchars($exam['exam_serial_no']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Marks</label>
                                <div class="fw-bold"><?php echo $exam['total_marks']; ?> <small class="text-muted">(<?php echo $exam['total_questions']; ?> Qs x <?php echo $exam['marks_per_question']; ?>)</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h5 class="mb-0 fw-bold">Questions List</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                    </div>
                    <div class="card-body">
                        <?php foreach ($questions as $index => $q): ?>
                            <div class="question-item">
                                <h6 class="fw-bold mb-3">Q<?php echo $index + 1; ?>: <?php echo nl2br(htmlspecialchars($q['question_text'])); ?></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="opt-box <?php echo ($q['correct_option'] == 'A') ? 'correct-opt' : ''; ?>">
                                            <span class="fw-bold me-2">A.</span> <?php echo htmlspecialchars($q['option_a']); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="opt-box <?php echo ($q['correct_option'] == 'B') ? 'correct-opt' : ''; ?>">
                                            <span class="fw-bold me-2">B.</span> <?php echo htmlspecialchars($q['option_b']); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="opt-box <?php echo ($q['correct_option'] == 'C') ? 'correct-opt' : ''; ?>">
                                            <span class="fw-bold me-2">C.</span> <?php echo htmlspecialchars($q['option_c']); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="opt-box <?php echo ($q['correct_option'] == 'D') ? 'correct-opt' : ''; ?>">
                                            <span class="fw-bold me-2">D.</span> <?php echo htmlspecialchars($q['option_d']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <style>
        @media print {
            #sidebar-wrapper, .btn { display: none !important; }
            #page-content-wrapper { margin-left: 0 !important; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
