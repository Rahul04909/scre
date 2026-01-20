<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Optional: Delete image files logic could go here
        $sql = "DELETE FROM course_categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        header("Location: manage-categories.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating database: " . $e->getMessage();
    }
}

// Fetch All Categories
try {
    $stmt = $pdo->query("SELECT * FROM course_categories ORDER BY created_at DESC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Course Categories - Admin</title>
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
                    <h2 class="mb-0">Manage Categories</h2>
                    <a href="add-category.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Category</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Category deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Category added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Image</th>
                                        <th class="py-3">Name</th>
                                        <th class="py-3">Short Desc</th>
                                        <th class="py-3">SEO Title</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($categories) > 0): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <?php if($cat['category_image']): ?>
                                                        <img src="../../<?php echo htmlspecialchars($cat['category_image']); ?>" alt="img" width="50" height="50" class="rounded object-fit-cover">
                                                    <?php else: ?>
                                                        <span class="text-muted small">No Img</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($cat['category_name']); ?></h6></td>
                                                <td><small class="text-muted"><?php echo substr(htmlspecialchars($cat['short_description']), 0, 50) . '...'; ?></small></td>
                                                <td><?php echo htmlspecialchars($cat['meta_title']); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-categories.php?delete_id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No categories found.</td></tr>
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
