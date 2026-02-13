<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../database/config.php';

$center_id = $_SESSION['center_id'];

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch Past Classes
$today = date('Y-m-d');
$stmtHistory = $pdo->prepare("SELECT l.*, c.course_name 
                              FROM live_classes l 
                              JOIN courses c ON l.course_id = c.id 
                              WHERE l.center_id = ? AND l.class_date < ? 
                              ORDER BY l.class_date DESC, l.class_time DESC
                              LIMIT $limit OFFSET $offset");
$stmtHistory->execute([$center_id, $today]);
$past_classes = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

// Total Count for Pagination
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM live_classes WHERE center_id = ? AND class_date < ?");
$stmtCount->execute([$center_id, $today]);
$total_rows = $stmtCount->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Class History - PACE Center</title>
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

    <?php include '../sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include '../header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold" style="color: #115E59;">Live Class History</h2>
                <a href="manage-live-class.php" class="btn btn-primary" style="background-color: #115E59; border-color: #115E59;">
                    <i class="fas fa-plus me-2"></i>Schedule New Class
                </a>
            </div>

            <div class="content-card">
                <?php if (empty($past_classes)): ?>
                    <div class="alert alert-secondary text-center">No past classes found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Course & Title</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($past_classes as $class): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo date('d M Y', strtotime($class['class_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($class['class_time'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary mb-1"><?php echo htmlspecialchars($class['course_name']); ?></span>
                                            <div class="fw-bold"><?php echo htmlspecialchars($class['title']); ?></div>
                                            <small class="text-muted"><a href="<?php echo htmlspecialchars($class['link']); ?>" target="_blank" class="text-decoration-none">Link</a></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success">Completed</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-end">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for($i=1; $i<=$total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
