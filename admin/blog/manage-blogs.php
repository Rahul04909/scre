<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Get image path to delete
        $stmt = $pdo->prepare("SELECT image_path FROM blogs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $blog = $stmt->fetch();

        if ($blog && !empty($blog['image_path']) && file_exists('../../' . $blog['image_path'])) {
            unlink('../../' . $blog['image_path']);
        }

        $delStmt = $pdo->prepare("DELETE FROM blogs WHERE id = :id");
        $delStmt->execute([':id' => $id]);
        $message = "Blog post deleted successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Blogs
try {
    $sql = "SELECT b.*, c.name as category_name 
            FROM blogs b 
            LEFT JOIN blog_categories c ON b.category_id = c.id 
            ORDER BY b.created_at DESC";
    $stmt = $pdo->query($sql);
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Blogs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .table-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .blog-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../../admin/sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold">Manage Blogs</h2>
                    <a href="add-blog.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Create New Blog</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($blogs) > 0): ?>
                                        <?php foreach ($blogs as $blog): ?>
                                        <tr>
                                            <td>
                                                <?php if($blog['image_path']): ?>
                                                    <img src="../../<?php echo htmlspecialchars($blog['image_path']); ?>" class="blog-thumb">
                                                <?php else: ?>
                                                    <span class="text-muted small">No Img</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($blog['title']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($blog['category_name']); ?></span></td>
                                            <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $blog['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($blog['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $blog['views']; ?></td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                                            <td>
                                                <a href="edit-blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-sm btn-info text-white me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="manage-blogs.php?delete_id=<?php echo $blog['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this blog post?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="text-center py-4 text-muted">No blog posts found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../admin/assets/js/sidebar.js"></script>
</body>
</html>
