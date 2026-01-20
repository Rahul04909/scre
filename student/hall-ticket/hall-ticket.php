<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch Student Course & Session Info
$stmtStudent = $pdo->prepare("
    SELECT s.course_id, s.session_id, c.course_name, ac.session_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    WHERE s.id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch();

if (!$student) {
    die("Student not found or session invalid.");
}

// Fetch Exam Schedule
$stmtExams = $pdo->prepare("
    SELECT es.*, sub.subject_name, sub.exam_duration, sub.theory_marks, sub.practical_marks
    FROM exam_schedules es
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE es.course_id = ? AND es.session_id = ?
    ORDER BY es.exam_date ASC, es.start_time ASC
");
$stmtExams->execute([$student['course_id'], $student['session_id']]);
$exams = $stmtExams->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hall Ticket - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-primary"><i class="fas fa-id-card me-2"></i>Hall Ticket / Admit Card</h2>
                    <?php if (count($exams) > 0): ?>
                        <a href="download-admit-card.php" target="_blank" class="btn btn-primary shadow-sm hover-scale">
                            <i class="fas fa-download me-2"></i>Download Admit Card
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Showing Exam Schedule for <strong><?php echo htmlspecialchars($student['course_name']); ?></strong> (<?php echo htmlspecialchars($student['session_name']); ?>)
                        </div>

                        <?php if (count($exams) > 0): ?>
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light text-secondary">
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Marks (Th/Pr)</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exams as $exam): ?>
                                            <tr>
                                                <td class="fw-bold text-dark">
                                                    <?php echo date('d M Y', strtotime($exam['exam_date'])); ?>
                                                    <div class="small text-muted fw-normal"><?php echo date('l', strtotime($exam['exam_date'])); ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($exam['subject_name']); ?></div>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $th = floatval($exam['theory_marks']);
                                                        $pr = floatval($exam['practical_marks']);
                                                        echo $th . ' / ' . $pr;
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo date('h:i A', strtotime($exam['start_time'])); ?> - <?php echo date('h:i A', strtotime($exam['end_time'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $exam['exam_duration']; ?> Mins</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" style="width: 200px; opacity: 0.5;">
                                <h4 class="text-muted mt-3">No Exams Scheduled Yet</h4>
                                <p class="text-secondary">Your exam schedule has not been released. Please contact the center.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 pt-4 border-top">
                            <h5 class="fw-bold mb-3">Important Instructions</h5>
                            <ul class="text-muted small">
                                <li>Please bring a printed copy of your Hall Ticket to the examination center.</li>
                                <li>Carry a valid photo ID proof along with the Hall Ticket.</li>
                                <li>Report to the exam hall at least 30 minutes before the scheduled time.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
