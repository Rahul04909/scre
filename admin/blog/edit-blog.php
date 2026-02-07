<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Check ID
if (!isset($_GET['id'])) {
    header("Location: manage-blogs.php");
    exit;
}
$blog_id = intval($_GET['id']);

// Fetch Categories
try {
    $stmt = $pdo->query("SELECT * FROM blog_categories WHERE status='active' ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Handle Update
if (isset($_POST['update_blog'])) {
    $title = trim($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $category_id = intval($_POST['category_id']);
    $content = $_POST['content'];
    $author = trim($_POST['author']);
    $status = $_POST['status'];

    $image_path = $_POST['current_image'];
    $uploadDir = '../../assets/uploads/blog/';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['image']['name']));
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Delete old image
            if (!empty($image_path) && file_exists('../../' . $image_path)) {
                unlink('../../' . $image_path);
            }
            $image_path = 'assets/uploads/blog/' . $fileName;
        }
    }

    try {
        $sql = "UPDATE blogs SET category_id=?, title=?, slug=?, content=?, image_path=?, author=?, status=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id, $title, $slug, $content, $image_path, $author, $status, $blog_id]);
        $message = "Blog post updated successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Blog Data
try {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$blog_id]);
    $blog = $stmt->fetch();
    if (!$blog) {
        header("Location: manage-blogs.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../../admin/sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold">Edit Blog Post</h2>
                    <a href="manage-blogs.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($blog['image_path']); ?>">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($blog['title']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                                        <textarea id="summernote" name="content" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $blog['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Author</label>
                                        <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($blog['author']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="published" <?php echo $blog['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="draft" <?php echo $blog['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Featured Image</label>
                                        <?php if($blog['image_path']): ?>
                                            <div class="mb-2">
                                                <img src="../../<?php echo htmlspecialchars($blog['image_path']); ?>" class="img-thumbnail" style="max-height: 150px;">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <div class="form-text">Leave empty to keep current image.</div>
                                    </div>
                                    <hr>
                                    <button type="submit" name="update_blog" class="btn btn-warning w-100 btn-lg text-white">Update Blog</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="../../admin/assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Write your blog content here...',
                tabsize: 2,
                height: 400,
                toolbar: [
                  ['style', ['style']],
                  ['font', ['bold', 'underline', 'clear']],
                  ['color', ['color']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['table', ['table']],
                  ['insert', ['link', 'picture', 'video']],
                  ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>
