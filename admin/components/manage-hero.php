<?php
require_once '../../database/config.php';

// Handle Image Upload & Add Slide
$message = '';
$messageType = '';

// Delete Slide
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Get image path to delete file
        $stmt = $pdo->prepare("SELECT image_path FROM hero_slides WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $slide = $stmt->fetch();
        
        if ($slide) {
            if (file_exists('../../' . $slide['image_path'])) {
                unlink('../../' . $slide['image_path']);
            }
            $delStmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = :id");
            $delStmt->execute([':id' => $id]);
            $message = "Slide deleted successfully.";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

if (isset($_POST['add_slide'])) {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $btn_text = trim($_POST['button_text']);
    $btn_link = trim($_POST['button_link']);
    $order = intval($_POST['display_order']);

    // Image Upload
    $uploadDir = '../../assets/uploads/hero/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = '';
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0) {
        $ext = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        // Validate Image
        $check = getimagesize($_FILES['hero_image']['tmp_name']);
        if($check !== false) {
             if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
                $imagePath = 'assets/uploads/hero/' . $fileName;
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $messageType = "danger";
            }
        } else {
            $message = "File is not an image.";
            $messageType = "danger";
        }
    } else {
         $message = "Please select an image.";
         $messageType = "danger";
    }

    if ($imagePath && empty($message)) {
        try {
            $sql = "INSERT INTO hero_slides (image_path, title, subtitle, button_text, button_link, display_order) VALUES (:img, :title, :sub, :btn_txt, :btn_lnk, :order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':img' => $imagePath,
                ':title' => $title,
                ':sub' => $subtitle,
                ':btn_txt' => $btn_text,
                ':btn_lnk' => $btn_link,
                ':order' => $order
            ]);
            $message = "Slide added successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch Slides
try {
    $stmt = $pdo->query("SELECT * FROM hero_slides ORDER BY display_order ASC, created_at DESC");
    $slides = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hero Slides - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .slide-preview {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
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
                    <h2 class="mb-0 text-dark fw-bold">Manage Hero Section</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Slide Form -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">Add New Slide</div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Slide Image <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="file" name="hero_image" class="form-control" accept="image/*" required>
                                        </div>
                                        <div class="form-text text-muted">Recommended Size: 1920x600px</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="e.g. Empowering Future">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subtitle</label>
                                        <textarea name="subtitle" class="form-control" rows="2" placeholder="e.g. Quality education that transforms lives..."></textarea>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Button Text</label>
                                            <input type="text" name="button_text" class="form-control" placeholder="e.g. Join Now">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Button Link</label>
                                            <input type="text" name="button_link" class="form-control" placeholder="e.g. courses.php">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Display Order</label>
                                        <input type="number" name="display_order" class="form-control" value="0">
                                    </div>
                                    <button type="submit" name="add_slide" class="btn btn-primary w-100">Add Slide</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Slides List -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">Existing Slides</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" class="ps-3">Image</th>
                                                <th scope="col">Content</th>
                                                <th scope="col">Order</th>
                                                <th scope="col" class="text-end pe-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($slides) > 0): ?>
                                                <?php foreach ($slides as $slide): ?>
                                                <tr>
                                                    <td class="ps-3">
                                                        <img src="../../<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Slide" class="slide-preview">
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($slide['title'] ?? 'No Title'); ?></div>
                                                        <div class="text-muted small"><?php echo htmlspecialchars(substr($slide['subtitle'] ?? '', 0, 50)) . '...'; ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $slide['display_order']; ?></span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <a href="manage-hero.php?delete_id=<?php echo $slide['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this slide?');">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted">No slides found. Add one to get started!</td>
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
