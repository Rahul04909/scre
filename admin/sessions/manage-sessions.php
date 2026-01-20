<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM academic_sessions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-sessions.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "DataBase Error: " . $e->getMessage();
    }
}

// Fetch Sessions
try {
    $sql = "SELECT s.*, c.course_name 
            FROM academic_sessions s 
            JOIN courses c ON s.course_id = c.id 
            ORDER BY c.course_name ASC, s.start_year DESC";
    $stmt = $pdo->query($sql);
    $sessions = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sessions - Admin</title>
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
                    <h2 class="mb-0">Manage Sessions</h2>
                    <a href="add-session.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Session</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">Session deleted successfully.<button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success alert-dismissible fade show">Session added successfully!<button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Course</th>
                                        <th class="py-3">Session Name</th>
                                        <th class="py-3">Period</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($sessions) > 0): ?>
                                        <?php foreach ($sessions as $s): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($s['course_name']); ?></td>
                                                <td><span class="badge bg-light text-primary border"><?php echo htmlspecialchars($s['session_name']); ?></span></td>
                                                <td class="small text-muted">
                                                    <?php echo $s['start_month'] . ' ' . $s['start_year']; ?> 
                                                    <i class="fas fa-arrow-right mx-1 fa-xs"></i> 
                                                    <?php echo $s['end_month'] . ' ' . $s['end_year']; ?>
                                                </td>
                                                <td>
                                                    <?php if($s['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-session.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-sessions.php?delete_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this session?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No sessions found.</td></tr>
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
