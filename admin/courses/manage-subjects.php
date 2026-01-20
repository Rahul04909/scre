<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-subjects.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "DataBase Error: " . $e->getMessage();
    }
}

// Fetch Subjects
try {
    $sql = "SELECT s.*, c.course_name, c.unit_type, c.has_units 
            FROM subjects s 
            JOIN courses c ON s.course_id = c.id 
            ORDER BY c.course_name ASC, s.unit_no ASC";
    $stmt = $pdo->query($sql);
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects - Admin</title>
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
                    <h2 class="mb-0">Manage Subjects</h2>
                    <a href="add-subject.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Subject</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">Subject deleted successfully.<button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Course</th>
                                        <th class="py-3">Details</th>
                                        <th class="py-3">Subject Name</th>
                                        <th class="py-3">Marks (Th+Pr)</th>
                                        <th class="py-3">Duration</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($subjects) > 0): ?>
                                        <?php foreach ($subjects as $sub): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($sub['course_name']); ?></td>
                                                <td>
                                                    <?php if($sub['has_units']): ?>
                                                        <span class="badge bg-info text-dark"><?php echo ucfirst($sub['unit_type']) . ' ' . $sub['unit_no']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Direct</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                                                <td>
                                                    <strong><?php echo $sub['total_marks']; ?></strong> 
                                                    <small class="text-muted">(<?php echo $sub['theory_marks']; ?> + <?php echo $sub['practical_marks']; ?>)</small>
                                                </td>
                                                <td><?php echo $sub['exam_duration']; ?> mins</td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-subject.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-subjects.php?delete_id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this subject?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No subjects found.</td></tr>
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
