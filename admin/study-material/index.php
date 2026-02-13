<?php
session_start();
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../login.php");
//     exit;
// }
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Get file path to unlink
    $stmtPath = $pdo->prepare("SELECT file_path FROM study_materials WHERE id = ?");
    $stmtPath->execute([$id]);
    $file = $stmtPath->fetchColumn();
    
    if ($file && file_exists("../../" . $file)) {
        unlink("../../" . $file);
    }
    
    $stmtDel = $pdo->prepare("DELETE FROM study_materials WHERE id = ?");
    $stmtDel->execute([$id]);
    header("Location: index.php?msg=deleted");
    exit;
}

// Fetch Materials
$sql = "SELECT sm.*, c.course_name, s.subject_name 
        FROM study_materials sm 
        JOIN courses c ON sm.course_id = c.id 
        LEFT JOIN subjects s ON sm.subject_id = s.id 
        ORDER BY sm.created_at DESC";
$materials = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Material - Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        .content-card { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper">
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Study Material</h2>
                    <a href="add-study-material.php" class="btn btn-primary" style="background-color: #115E59; border-color: #115E59;">
                        <i class="fas fa-plus me-2"></i>Add Material
                    </a>
                </div>

                <?php if(isset($_GET['msg']) && $_GET['msg']=='added'): ?>
                    <div class="alert alert-success">Study material added successfully.</div>
                <?php endif; ?>
                <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
                    <div class="alert alert-success">Material deleted successfully.</div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Unit / Subject</th>
                                    <th>File</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($materials)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No study materials found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($materials as $mat): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($mat['title']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($mat['course_name']); ?></span></td>
                                            <td>
                                                <?php if($mat['unit_no']): ?>
                                                    <span class="badge bg-info text-dark me-1">Unit <?php echo $mat['unit_no']; ?></span>
                                                <?php endif; ?>
                                                <?php echo $mat['subject_name'] ? htmlspecialchars($mat['subject_name']) : '<em class="text-muted">General</em>'; ?>
                                            </td>
                                            <td>
                                                <a href="../../<?php echo htmlspecialchars($mat['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i> View
                                                </a>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d M Y', strtotime($mat['created_at'])); ?></td>
                                            <td>
                                                <a href="?delete_id=<?php echo $mat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this material?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
