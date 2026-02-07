<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Get file path
        $stmt = $pdo->prepare("SELECT image_path FROM gallery_images WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $img = $stmt->fetch();

        if ($img && file_exists('../../' . $img['image_path'])) {
            unlink('../../' . $img['image_path']);
        }

        $delStmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = :id");
        $delStmt->execute([':id' => $id]);
        $message = "Image deleted successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Handle Add Image
if (isset($_POST['add_image'])) {
    $title = trim($_POST['title']);
    $category_id = intval($_POST['category_id']);
    
    // File Upload
    $uploadDir = '../../assets/uploads/gallery/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['image_file']['name']));
        $targetFile = $uploadDir . $fileName;
        $dbFilePath = 'assets/uploads/gallery/' . $fileName;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            try {
                $sql = "INSERT INTO gallery_images (category_id, title, image_path) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$category_id, $title, $dbFilePath]);
                $message = "Image uploaded successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
                $messageType = "danger";
            }
        } else {
            $message = "Error uploading file.";
            $messageType = "danger";
        }
    } else {
        $message = "Please select a valid image.";
        $messageType = "danger";
    }
}

// Fetch Categories for Dropdown
try {
    $stmt = $pdo->query("SELECT * FROM gallery_categories WHERE status='active' ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Fetch Images
try {
    $sql = "SELECT i.*, c.name as category_name 
            FROM gallery_images i 
            LEFT JOIN gallery_categories c ON i.category_id = c.id 
            ORDER BY i.created_at DESC";
    $stmt = $pdo->query($sql);
    $images = $stmt->fetchAll();
} catch (PDOException $e) { $images = []; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Images - Gallery Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .form-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .gallery-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../../admin/sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold">Manage Gallery Images</h2>
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
                            <h4 class="mb-3 border-bottom pb-2">Upload Image</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Optional title">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Image <span class="text-danger">*</span></label>
                                    <input type="file" name="image_file" class="form-control" required accept="image/*">
                                </div>
                                <button type="submit" name="add_image" class="btn btn-primary w-100"><i class="fas fa-upload me-1"></i> Upload</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="table-section">
                            <h4 class="mb-3 border-bottom pb-2">Gallery Images</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($images) > 0): ?>
                                            <?php foreach ($images as $img): ?>
                                            <tr>
                                                <td>
                                                    <img src="../../<?php echo htmlspecialchars($img['image_path']); ?>" class="gallery-thumb" alt="Thumb">
                                                </td>
                                                <td><?php echo htmlspecialchars($img['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($img['category_name']); ?></span>
                                                </td>
                                                <td class="small text-muted"><?php echo date('M d, Y', strtotime($img['created_at'])); ?></td>
                                                <td>
                                                    <a href="manage-images.php?delete_id=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete image?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No images uploaded yet.</td></tr>
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
</body>
</html>
