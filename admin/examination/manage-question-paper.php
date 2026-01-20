<?php
require_once '../../database/config.php';

// Pagination Settings
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter Settings
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $pdo->beginTransaction();
        // Constraints usually cascade, but good to be explicit or rely on FK ON DELETE CASCADE
        $stmtD = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        $stmtD->execute([$delete_id]);
        $pdo->commit();
        header("Location: manage-question-paper.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Build Query
$sql = "SELECT e.*, c.course_name, s.subject_name 
        FROM exams e
        JOIN courses c ON e.course_id = c.id
        JOIN subjects s ON e.subject_id = s.id
        WHERE 1=1";

$params = [];

if ($course_id > 0) {
    $sql .= " AND e.course_id = ?";
    $params[] = $course_id;
}

// Clone for Count
$countSql = str_replace("SELECT e.*, c.course_name, s.subject_name", "SELECT COUNT(*)", $sql);
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = $stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Finalize Data Query
$sql .= " ORDER BY e.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$exams = $stmt->fetchAll();

// Fetch Courses for Filter
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Question Papers - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
                    <h2 class="text-primary fw-bold"><i class="fas fa-file-signature me-2"></i>Manage Question Papers</h2>
                    <a href="create-exam.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create New</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Question Paper deleted successfully.
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Filter by Course</label>
                                <select name="course_id" class="form-select select2" onchange="this.form.submit()">
                                    <option value="">-- All Courses --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($course_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <?php if ($course_id > 0): ?>
                                    <a href="manage-question-paper.php" class="btn btn-outline-secondary w-100"><i class="fas fa-undo me-1"></i> Reset</a>
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
                                        <th>Serial No</th>
                                        <th>Course</th>
                                        <th>Subject</th>
                                        <th>Questions</th>
                                        <th>Total Marks</th>
                                        <th>Created Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($exams) > 0): ?>
                                        <?php foreach ($exams as $ex): ?>
                                            <tr>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($ex['exam_serial_no']); ?></span></td>
                                                <td><?php echo htmlspecialchars($ex['course_name']); ?></td>
                                                <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($ex['subject_name']); ?></span></td>
                                                <td><?php echo $ex['total_questions']; ?></td>
                                                <td><?php echo $ex['total_marks']; ?></td>
                                                <td><?php echo date('d M Y', strtotime($ex['created_at'])); ?></td>
                                                <td class="text-end text-nowrap">
                                                    <a href="view-question-paper.php?id=<?php echo $ex['id']; ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                    <a href="edit-question-paper.php?id=<?php echo $ex['id']; ?>" class="btn btn-sm btn-outline-warning mx-1" title="Edit"><i class="fas fa-pen"></i></a>
                                                    <a href="manage-question-paper.php?delete_id=<?php echo $ex['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this question paper? This action cannot be undone.');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">No question papers found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?>&course_id=<?php echo $course_id; ?>">Previous</a>
                                    </li>
                                    
                                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&course_id=<?php echo $course_id; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?>&course_id=<?php echo $course_id; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>

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
