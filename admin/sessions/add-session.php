<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Fetch Courses
try {
    $stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Generate Years (Current - 2 to Current + 5)
$currentYear = date('Y');
$years = range($currentYear - 2, $currentYear + 5);

$months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

if (isset($_POST['add_session'])) {
    $course_id = intval($_POST['course_id']);
    
    $start_month = $_POST['start_month'];
    $start_year = intval($_POST['start_year']);
    
    $end_month = $_POST['end_month'];
    $end_year = intval($_POST['end_year']);
    
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Auto-generate Session Name
    $session_name = "$start_month $start_year - $end_month $end_year";

    try {
        $sql = "INSERT INTO academic_sessions (course_id, session_name, start_month, start_year, end_month, end_year, is_active) 
                VALUES (:cid, :sname, :sm, :sy, :em, :ey, :active)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cid' => $course_id, ':sname' => $session_name,
            ':sm' => $start_month, ':sy' => $start_year,
            ':em' => $end_month, ':ey' => $end_year,
            ':active' => $is_active
        ]);
        header("Location: manage-sessions.php?msg=added");
        exit;
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Session - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add New Session</h2>
                    <a href="manage-sessions.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>
                <?php if ($message): ?><div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Select Course</label>
                                    <select name="course_id" class="form-select text-uppercase" required>
                                        <option value="">-- Choose Course --</option>
                                        <?php foreach ($courses as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4 d-flex align-items-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                        <label class="form-check-label fw-bold" for="isActive">Set as Active Session?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="text-success fw-bold mb-3">Session Start</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Month</label>
                                                    <select name="start_month" id="start_month" class="form-select" required>
                                                        <?php foreach($months as $m): ?>
                                                            <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Year</label>
                                                    <select name="start_year" id="start_year" class="form-select" required>
                                                        <?php foreach($years as $y): ?>
                                                            <option value="<?php echo $y; ?>" <?php if($y == $currentYear) echo 'selected'; ?>><?php echo $y; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="text-danger fw-bold mb-3">Session End</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Month</label>
                                                    <select name="end_month" id="end_month" class="form-select" required>
                                                        <?php foreach($months as $m): ?>
                                                            <option value="<?php echo $m; ?>" <?php if($m == 'December') echo 'selected'; ?>><?php echo $m; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Year</label>
                                                    <select name="end_year" id="end_year" class="form-select" required>
                                                        <?php foreach($years as $y): ?>
                                                            <option value="<?php echo $y; ?>" <?php if($y == $currentYear) echo 'selected'; ?>><?php echo $y; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info text-center mt-3">
                                <span class="fw-bold">Preview Session Name:</span> <span id="previewName">January <?php echo $currentYear; ?> - December <?php echo $currentYear; ?></span>
                            </div>

                            <button type="submit" name="add_session" class="btn btn-primary w-100 btn-lg mt-2"><i class="fas fa-save me-2"></i> Create Session</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        // Real-time Preview
        const inputs = ['start_month', 'start_year', 'end_month', 'end_year'];
        inputs.forEach(id => {
            document.getElementById(id).addEventListener('change', updatePreview);
        });

        function updatePreview() {
            const sm = document.getElementById('start_month').value;
            const sy = document.getElementById('start_year').value;
            const em = document.getElementById('end_month').value;
            const ey = document.getElementById('end_year').value;
            document.getElementById('previewName').innerText = `${sm} ${sy} - ${em} ${ey}`;
        }
    </script>
</body>
</html>
