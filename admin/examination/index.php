<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM exam_schedules WHERE id = ?");
        $stmtDel->execute([$del_id]);
        $message = "Schedule deleted successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error deleting: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Courses for Filter
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

// Initialize Filters
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

// Handle Success Messages
if (isset($_GET['msg']) && $_GET['msg'] == 'created') {
    $message = "Exam Schedule Created Successfully!";
    $messageType = "success";
}

// Fetch Sessions if Course Selected (for dropdown)
$sessions = [];
if ($course_id > 0) {
    try {
        $stmtSessions = $pdo->prepare("SELECT id, session_name FROM academic_sessions WHERE course_id = ? ORDER BY id DESC");
        $stmtSessions->execute([$course_id]);
        $sessions = $stmtSessions->fetchAll();
    } catch (PDOException $e) {
        // Handle error or ignore
    }
}

// Build List Query
$sql = "SELECT es.*, c.course_name, s.subject_name, ac.session_name, e.exam_serial_no 
        FROM exam_schedules es
        JOIN courses c ON es.course_id = c.id
        JOIN subjects s ON es.subject_id = s.id
        JOIN academic_sessions ac ON es.session_id = ac.id
        LEFT JOIN exams e ON es.exam_id = e.id
        WHERE 1=1";

$params = [];

if ($course_id > 0) {
    $sql .= " AND es.course_id = ?";
    $params[] = $course_id;
}

if ($session_id > 0) {
    $sql .= " AND es.session_id = ?";
    $params[] = $session_id;
}

$sql .= " ORDER BY es.exam_date DESC, es.start_time ASC";

try {
    $stmtList = $pdo->prepare($sql);
    $stmtList->execute($params);
    $schedules = $stmtList->fetchAll();
} catch (PDOException $e) {
    $schedules = [];
    $message = "Error loading schedules: " . $e->getMessage();
    $messageType = "danger";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Schedules - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 10px; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="margin-left: 280px; flex-grow: 1;">
            <div class="container-fluid py-5 px-lg-5">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary fw-bold"><i class="fas fa-list-alt me-2"></i>Exam Schedule List</h2>
                    <a href="exam-schedule.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create New Schedule</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Filter by Course</label>
                                <select name="course_id" id="course_id" class="form-select select2" onchange="this.form.submit()">
                                    <option value="">-- All Courses --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($course_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Filter by Session</label>
                                <select name="session_id" id="session_id" class="form-select select2" onchange="this.form.submit()">
                                    <option value="">-- All Sessions --</option>
                                    <?php if ($course_id > 0): ?>
                                        <?php foreach ($sessions as $ses): ?>
                                            <option value="<?php echo $ses['id']; ?>" <?php echo ($session_id == $ses['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ses['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Select a course first</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <?php if ($course_id > 0 || $session_id > 0): ?>
                                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i> Reset Filters</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Course</th>
                                        <th>Subject</th>
                                        <th>Question Paper</th>
                                        <th>Session</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($schedules) > 0): ?>
                                        <?php foreach ($schedules as $s): ?>
                                            <tr>
                                                <td><?php echo date('d M Y', strtotime($s['exam_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo date('h:i A', strtotime($s['start_time'])); ?> - <?php echo date('h:i A', strtotime($s['end_time'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($s['course_name']); ?></td>
                                                <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($s['subject_name']); ?></span></td>
                                                <td>
                                                    <?php if($s['exam_serial_no']): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><?php echo htmlspecialchars($s['exam_serial_no']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info"><?php echo htmlspecialchars($s['session_name']); ?></span></td>
                                                <td class="text-end">
                                                    <a href="?delete_id=<?php echo $s['id']; ?>&course_id=<?php echo $course_id; ?>&session_id=<?php echo $session_id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this schedule?');"><i class="fas fa-trash-alt"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">No exam schedules found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
</body>
</html>
