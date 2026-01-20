<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $sql = "DELETE FROM courses WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        header("Location: manage-courses.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "DataBase Error: " . $e->getMessage();
    }
}

// Fetch Courses with Categories
try {
    $sql = "SELECT c.*, cat.category_name 
            FROM courses c 
            LEFT JOIN course_categories cat ON c.category_id = cat.id 
            ORDER BY c.created_at DESC";
    $stmt = $pdo->query($sql);
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses - Admin</title>
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
                    <h2 class="mb-0">Manage Courses</h2>
                    <a href="add-course.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Course</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Course deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Course added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Image</th>
                                        <th class="py-3">Course Info</th>
                                        <th class="py-3">Type</th>
                                        <th class="py-3">Category</th>
                                        <th class="py-3">Fees</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($courses) > 0): ?>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <?php if($course['course_image']): ?>
                                                        <img src="../../<?php echo htmlspecialchars($course['course_image']); ?>" width="60" height="40" class="rounded object-fit-cover">
                                                    <?php else: ?>
                                                        <span class="text-muted small">No Img</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($course['course_code']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark text-capitalize"><?php echo str_replace('_', ' ', $course['course_type']); ?></span>
                                                    <div class="small text-muted mt-1"><?php echo $course['duration_value'] . ' ' . ucfirst($course['duration_type']); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['category_name']); ?></td>
                                                <td>₹<?php echo number_format($course['course_fees'], 2); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-courses.php?delete_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this course?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No courses found.</td></tr>
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
