<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$practical_id = intval($_GET['id']);
$message = '';
$messageType = '';

// 1. Fetch Practical Details
$stmt = $pdo->prepare("SELECT p.*, s.subject_name FROM practicals p JOIN subjects s ON p.subject_id = s.id WHERE p.id = ?");
$stmt->execute([$practical_id]);
$practical = $stmt->fetch();

if (!$practical) die("Practical not found");

// 2. Handle Marks Update
if (isset($_POST['update_marks'])) {
    $sub_id = intval($_POST['submission_id']);
    $marks = floatval($_POST['marks']);
    
    try {
        $stmtUpdate = $pdo->prepare("UPDATE practical_submissions SET marks_obtained = ?, status = 'Graded' WHERE id = ?");
        $stmtUpdate->execute([$marks, $sub_id]);
        $message = "Marks updated successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// 3. Fetch Submissions
$sql = "
    SELECT ps.*, s.first_name, s.last_name, s.enrollment_no, s.roll_no 
    FROM practical_submissions ps
    JOIN students s ON ps.student_id = s.id
    WHERE ps.practical_id = ?
    ORDER BY s.roll_no ASC, ps.submission_date DESC
";
$stmtSub = $pdo->prepare($sql);
$stmtSub->execute([$practical_id]);
$submissions = $stmtSub->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Submissions - Center Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; width: 100%; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
        .bg-light-success { background-color: #f0fdf4 !important; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="index.php" class="text-decoration-none text-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
                        <h4 class="fw-bold mt-2">Submissions: <?php echo htmlspecialchars($practical['title']); ?></h4>
                        <p class="text-muted mb-0 small"><?php echo htmlspecialchars($practical['subject_name']); ?> | Max Marks: <?php echo htmlspecialchars($practical['practical_marks'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Student Info</th>
                                        <th>File</th>
                                        <th>Submission Date</th>
                                        <th>Marks</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($submissions) > 0): ?>
                                        <?php foreach($submissions as $sub): ?>
                                            <tr class="<?php echo ($sub['status'] == 'Graded') ? 'bg-light-success' : ''; ?>">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?></div>
                                                    <div class="small text-muted">Roll: <?php echo htmlspecialchars($sub['roll_no']); ?></div>
                                                    <div class="small text-muted">Enroll: <?php echo htmlspecialchars($sub['enrollment_no']); ?></div>
                                                </td>
                                                <td>
                                                    <a href="../../<?php echo htmlspecialchars($sub['submission_file']); ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                                        <i class="fas fa-file-pdf me-1"></i> View PDF
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y h:i A', strtotime($sub['submission_date'])); ?>
                                                    <?php if($sub['submission_date'] > $practical['submission_last_date'] . ' 23:59:59'): ?>
                                                        <span class="badge bg-danger">Late</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-flex align-items-center" style="max-width: 150px;">
                                                        <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                                        <input type="number" step="0.5" name="marks" class="form-control form-control-sm me-2" 
                                                               value="<?php echo $sub['marks_obtained']; ?>" placeholder="0.0" required>
                                                </td>
                                                <td class="text-end pe-4">
                                                        <button type="submit" name="update_marks" class="btn btn-sm btn-primary">
                                                            Save
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-5 text-muted">No submissions yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
