<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $message = "Category deleted successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $status = $_POST['status'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Update
        $stmt = $pdo->prepare("UPDATE gallery_categories SET name = ?, slug = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $status, $id])) {
            $message = "Category updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating category.";
            $messageType = "danger";
        }
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, slug, status) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $slug, $status])) {
            $message = "Category added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding category.";
            $messageType = "danger";
        }
    }
}

// Fetch Categories
try {
    $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY created_at DESC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Gallery Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .form-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../../admin/sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold">Manage Gallery Categories</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h4 class="mb-3 border-bottom pb-2">Add/Edit Category</h4>
                            <form method="POST">
                                <input type="hidden" name="id" id="cat_id" value="0">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="cat_name" class="form-control" required placeholder="Ex: Campus Life">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" id="cat_status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <button type="submit" name="save_category" class="btn btn-primary w-100">Save Category</button>
                                <button type="button" class="btn btn-secondary w-100 mt-2 d-none" id="cancel_edit">Cancel</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="table-section">
                            <h4 class="mb-3 border-bottom pb-2">Categories List</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($categories) > 0): ?>
                                            <?php $sr = 1; foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo $sr++; ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $cat['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($cat['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info text-white me-1 edit-btn" 
                                                        data-id="<?php echo $cat['id']; ?>" 
                                                        data-name="<?php echo htmlspecialchars($cat['name']); ?>" 
                                                        data-status="<?php echo $cat['status']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="manage-categories.php?delete_id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete category?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../admin/assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const status = $(this).data('status');

                $('#cat_id').val(id);
                $('#cat_name').val(name);
                $('#cat_status').val(status);
                $('button[name="save_category"]').text('Update Category');
                $('#cancel_edit').removeClass('d-none');
            });

            $('#cancel_edit').click(function() {
                $('#cat_id').val(0);
                $('#cat_name').val('');
                $('#cat_status').val('active');
                $('button[name="save_category"]').text('Save Category');
                $(this).addClass('d-none');
            });
        });
    </script>
</body>
</html>
