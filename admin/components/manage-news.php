<?php
require_once '../../database/config.php';

// Handle Add/Delete
$message = '';
$messageType = '';

// Delete News
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $delStmt = $pdo->prepare("DELETE FROM news_updates WHERE id = :id");
        $delStmt->execute([':id' => $id]);
        $message = "News update deleted successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Add News
if (isset($_POST['add_news'])) {
    $news_text = trim($_POST['news_text']);
    $link_url = trim($_POST['link_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($news_text)) {
        try {
            $sql = "INSERT INTO news_updates (message, link_url, is_active) VALUES (:msg, :link, :active)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':msg' => $news_text,
                ':link' => $link_url,
                ':active' => $is_active
            ]);
            $message = "News update added successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "News text is required.";
        $messageType = "danger";
    }
}

// Fetch News
try {
    $stmt = $pdo->query("SELECT * FROM news_updates ORDER BY created_at DESC");
    $news_list = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage News Ticker - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        /* Styles removed to use admin/sidebar.css values */
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include '../../admin/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid py-4 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 text-dark fw-bold">Manage News Ticker</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add News Form -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">Add New Update</div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">News Text <span class="text-danger">*</span></label>
                                        <textarea name="news_text" class="form-control" rows="3" required placeholder="Enter the news update text here..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Link URL (Optional)</label>
                                        <input type="text" name="link_url" class="form-control" placeholder="https://example.com/details">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" checked>
                                        <label class="form-check-label" for="activeCheck">Active immediately</label>
                                    </div>
                                    <button type="submit" name="add_news" class="btn btn-primary w-100">Add Update</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- News List -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">Recent Updates</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" class="ps-3">Message</th>
                                                <th scope="col">Link</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Date</th>
                                                <th scope="col" class="text-end pe-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($news_list) > 0): ?>
                                                <?php foreach ($news_list as $news): ?>
                                                <tr>
                                                    <td class="ps-3" style="max-width: 300px;">
                                                        <?php echo htmlspecialchars($news['message']); ?>
                                                    </td>
                                                    <td>
                                                        <?php if($news['link_url']): ?>
                                                            <a href="<?php echo htmlspecialchars($news['link_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-link"></i></a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($news['is_active']): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-muted small">
                                                        <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <a href="manage-news.php?delete_id=<?php echo $news['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this update?');">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">No news updates yet.</td>
                                                </tr>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../admin/assets/js/sidebar.js"></script>
</body>
</html>
