<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

$center_id = $_SESSION['center_id'];
$message = "";

// Handle Filters
$filter_date = $_GET['attendance_date'] ?? date('Y-m-d');
$filter_course = $_GET['course_id'] ?? '';
$filter_session = $_GET['session'] ?? '';

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $attendance_date = $_POST['date']; // Allow backdating from form
    $attendances = $_POST['status'] ?? []; // Array [student_id => status]

    try {
        $pdo->beginTransaction();
        
        $stmtCheck = $pdo->prepare("SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO student_attendance (student_id, attendance_date, status) VALUES (?, ?, ?)");
        $stmtUpdate = $pdo->prepare("UPDATE student_attendance SET status = ? WHERE id = ?");

        foreach ($attendances as $sid => $status) {
            // Check if record exists
            $stmtCheck->execute([$sid, $attendance_date]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                // Update
                $stmtUpdate->execute([$status, $existingId]);
            } else {
                // Insert
                $stmtInsert->execute([$sid, $attendance_date, $status]);
            }
        }

        $pdo->commit();
        $message = "<div class='alert alert-success'>Attendance saved successfully for $attendance_date!</div>";
        $filter_date = $attendance_date; // Keep the date selected
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Error saving attendance: " . $e->getMessage() . "</div>";
    }
}

// Fetch Courses for Filter
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch Students based on Filters
$students = [];
if ($filter_course) {
    try {
        // Build Query
        $sql = "SELECT s.id, s.first_name, s.last_name, s.enrollment_no, s.course_id, s.session, c.course_name 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE s.center_id = ?";
        $params = [$center_id];

        if ($filter_course) {
            $sql .= " AND s.course_id = ?";
            $params[] = $filter_course;
        }
        if ($filter_session) {
            $sql .= " AND s.session LIKE ?";
            $params[] = "%$filter_session%";
        }

        $sql .= " ORDER BY s.first_name ASC";
        
        $stmtStudents = $pdo->prepare($sql);
        $stmtStudents->execute($params);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        // Fetch existing attendance for these students on selected date
        if (!empty($students)) {
            $student_ids = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
            $sqlAtt = "SELECT student_id, status FROM student_attendance WHERE attendance_date = ? AND student_id IN ($placeholders)";
            $paramsAtt = array_merge([$filter_date], $student_ids);
            
            $stmtAtt = $pdo->prepare($sqlAtt);
            $stmtAtt->execute($paramsAtt);
            $existing_attendance = $stmtAtt->fetchAll(PDO::FETCH_KEY_PAIR); // [student_id => status]
        }

    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error fetching students.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Attendance - PACE Center</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        .content-card { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        .form-label { font-weight: 500; font-size: 0.9rem; color: #374151; }
        .table th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        .status-radio-group .btn-check:checked + .btn-outline-success { background-color: #10B981; color: white; border-color: #10B981; }
        .status-radio-group .btn-check:checked + .btn-outline-danger { background-color: #EF4444; color: white; border-color: #EF4444; }
        .status-radio-group .btn-check:checked + .btn-outline-warning { background-color: #F59E0B; color: white; border-color: #F59E0B; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include 'header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <h2 class="fw-bold mb-4" style="color: #115E59;">Manage Student Attendance</h2>
            <?php echo $message; ?>

            <!-- Filters -->
            <div class="content-card">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Attendance Date</label>
                        <input type="date" name="attendance_date" class="form-control" value="<?php echo $filter_date; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach($courses as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ($filter_course == $id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                         <label class="form-label">Session (Optional)</label>
                         <input type="text" name="session" class="form-control" placeholder="e.g. 2025-2026" value="<?php echo htmlspecialchars($filter_session); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #115E59; border-color: #115E59;">
                            <i class="fas fa-filter me-2"></i>Filter Students
                        </button>
                    </div>
                </form>
            </div>

            <!-- Student List -->
            <?php if ($filter_course && !empty($students)): ?>
                <div class="content-card">
                    <form method="POST">
                        <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Student List (<?php echo date('d M Y', strtotime($filter_date)); ?>)</h5>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success me-2" onclick="markAll('Present')">Mark All Present</button>
                                <button type="submit" name="save_attendance" class="btn btn-primary" style="background-color: #115E59; border-color: #115E59;">
                                    <i class="fas fa-save me-2"></i>Save Attendance
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Enrollment No</th>
                                        <th>Student Name</th>
                                        <th>Session</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <?php 
                                            // Default to Present if not set, or use existing status
                                            $current_status = $existing_attendance[$student['id']] ?? 'Present'; 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['enrollment_no']); ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($student['course_name']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['session']); ?></td>
                                            <td class="text-center" style="width: 300px;">
                                                <div class="btn-group status-radio-group" role="group">
                                                    
                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" id="p_<?php echo $student['id']; ?>" value="Present" <?php echo ($current_status == 'Present') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-success btn-sm" for="p_<?php echo $student['id']; ?>">Present</label>

                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" id="a_<?php echo $student['id']; ?>" value="Absent" <?php echo ($current_status == 'Absent') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-danger btn-sm" for="a_<?php echo $student['id']; ?>">Absent</label>

                                                    <input type="radio" class="btn-check" name="status[<?php echo $student['id']; ?>]" id="l_<?php echo $student['id']; ?>" value="Leave" <?php echo ($current_status == 'Leave') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-warning btn-sm" for="l_<?php echo $student['id']; ?>">Leave</label>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            <?php elseif ($filter_course): ?>
                <div class="alert alert-info text-center">No students found for the selected filter.</div>
            <?php else: ?>
                <div class="alert alert-secondary text-center">Please select a course to view students.</div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAll(status) {
            const radios = document.querySelectorAll(`input[value="${status}"]`);
            radios.forEach(radio => radio.checked = true);
        }
    </script>
</body>
</html>
