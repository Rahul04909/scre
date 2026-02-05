<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Delete (Remove Allocation)
if (isset($_GET['remove_id'])) {
    $id = intval($_GET['remove_id']);
    try {
        $sql = "DELETE FROM typing_course_allocations WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        header("Location: allot-typing-master.php?msg=removed");
        exit;
    } catch (PDOException $e) {
        $error = "DataBase Error: " . $e->getMessage();
    }
}

// Handle Add Allocation
if (isset($_POST['allot_course'])) {
    $course_id = intval($_POST['course_id']);
    
    if ($course_id > 0) {
        try {
            $sql = "INSERT INTO typing_course_allocations (course_id) VALUES (:course_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':course_id' => $course_id]);
            $message = "Typing Master allotted successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "This course is already allotted!";
                $messageType = "warning";
            } else {
                $message = "Database Error: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    } else {
        $message = "Please select a valid course.";
        $messageType = "danger";
    }
}

// Fetch Allotted Courses
try {
    $sql = "SELECT tca.*, c.course_name, c.course_code 
            FROM typing_course_allocations tca
            JOIN courses c ON tca.course_id = c.id
            ORDER BY tca.created_at DESC";
    $stmt = $pdo->query($sql);
    $allotted_courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Fetch Available Courses (Not yet allotted)
try {
    $sql = "SELECT id, course_name, course_code FROM courses 
            WHERE id NOT IN (SELECT course_id FROM typing_course_allocations)
            ORDER BY course_name ASC";
    $stmt = $pdo->query($sql);
    $available_courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Allot Typing Master - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .section-header { border-left: 4px solid #0d6efd; padding-left: 10px; margin-bottom: 20px; color: #333; font-weight: 600; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Allot Typing Master</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'removed'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Allocation removed successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Allocation Form -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-header">Allot New Course</h5>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Select Course</label>
                                        <select name="course_id" class="form-select" required>
                                            <option value="">Choose Course...</option>
                                            <?php foreach ($available_courses as $course): ?>
                                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?> (<?php echo htmlspecialchars($course['course_code']); ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="allot_course" class="btn btn-primary w-100"><i class="fas fa-check-circle me-2"></i> Allot Permission</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Allotted List -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-0">
                                <h5 class="section-header ms-3 mt-3">Allotted Courses</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3 ps-4">Course Name</th>
                                                <th class="py-3">Code</th>
                                                <th class="py-3">Allotted On</th>
                                                <th class="py-3 text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($allotted_courses) > 0): ?>
                                                <?php foreach ($allotted_courses as $alloc): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($alloc['course_name']); ?></td>
                                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($alloc['course_code']); ?></span></td>
                                                        <td><?php echo date('d M Y', strtotime($alloc['created_at'])); ?></td>
                                                        <td class="text-end pe-4">
                                                            <a href="allot-typing-master.php?remove_id=<?php echo $alloc['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove Typing Master access for this course?');"><i class="fas fa-trash me-1"></i> Remove</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center py-4 text-muted">No courses allotted yet.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
