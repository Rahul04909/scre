<?php
require_once '../../database/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage-categories.php");
    exit;
}

$id = intval($_GET['id']);
$message = '';
$messageType = '';

// Fetch User Data
try {
    $stmt = $pdo->prepare("SELECT * FROM course_categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        die("Category not found.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Handle Update
if (isset($_POST['update_category'])) {
    $category_name = trim($_POST['category_name']);
    $short_description = trim($_POST['short_description']);
    // SEO
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $schema_markup = trim($_POST['schema_markup']);
    $og_title = trim($_POST['og_title']);
    $og_description = trim($_POST['og_description']);

    // Image Upload Logic (Update only if new file exists)
    $uploadDir = '../../assets/uploads/categories/';
    
    // Main Image
    $category_image = $category['category_image']; // Default to old
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['category_image']['name']);
        if (move_uploaded_file($_FILES['category_image']['tmp_name'], $uploadDir . $fileName)) {
            $category_image = 'assets/uploads/categories/' . $fileName;
        }
    }

    // OG Image
    $og_image = $category['og_image']; // Default to old
    if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] == 0) {
        $fileName = time() . '_og_' . basename($_FILES['og_image']['name']);
        if (move_uploaded_file($_FILES['og_image']['tmp_name'], $uploadDir . $fileName)) {
            $og_image = 'assets/uploads/categories/' . $fileName;
        }
    }

    try {
        $sql = "UPDATE course_categories SET 
                category_name = :name, 
                category_image = :image, 
                short_description = :desc, 
                meta_title = :m_title, 
                meta_description = :m_desc, 
                meta_keywords = :m_keys, 
                schema_markup = :schema, 
                og_title = :og_title, 
                og_description = :og_desc, 
                og_image = :og_img 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $category_name,
            ':image' => $category_image,
            ':desc' => $short_description,
            ':m_title' => $meta_title,
            ':m_desc' => $meta_description,
            ':m_keys' => $meta_keywords,
            ':schema' => $schema_markup,
            ':og_title' => $og_title,
            ':og_desc' => $og_description,
            ':og_img' => $og_image,
            ':id' => $id
        ]);

        $message = "Category updated successfully!";
        $messageType = "success";
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM course_categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch();

    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course Category - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .image-preview-container {
            width: 100%;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8f9fa;
            cursor: pointer;
            position: relative;
        }
        .image-preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .image-preview-container:hover {
            border-color: #aaa;
        }
        .preview-text {
            color: #888;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Edit Category: <span class="text-primary"><?php echo htmlspecialchars($category['category_name']); ?></span></h2>
                    <a href="manage-categories.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Left Column: Basic Info -->
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0 text-primary fw-bold">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Category Name</label>
                                        <input type="text" name="category_name" class="form-control" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Short Description</label>
                                        <textarea name="short_description" class="form-control" rows="4"><?php echo htmlspecialchars($category['short_description']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SEO Section -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0 text-success fw-bold">SEO Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($category['meta_title']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Meta Description</label>
                                        <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($category['meta_description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Meta Keywords (Comma Seprated)</label>
                                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($category['meta_keywords']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Schema Markup</label>
                                        <textarea name="schema_markup" class="form-control" rows="5"><?php echo htmlspecialchars($category['schema_markup']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- OG Section -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0 text-info fw-bold">Open Graph (Social Sharing)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">OG Title</label>
                                        <input type="text" name="og_title" class="form-control" value="<?php echo htmlspecialchars($category['og_title']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">OG Description</label>
                                        <textarea name="og_description" class="form-control" rows="3"><?php echo htmlspecialchars($category['og_description']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Images -->
                        <div class="col-md-4">
                            <!-- Category Image -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0 text-dark fw-bold">Category Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    <!-- Use existing image if available -->
                                    <label for="cat_img_input" class="image-preview-container" id="cat_img_preview_box">
                                        <?php if($category['category_image']): ?>
                                            <img src="../../<?php echo htmlspecialchars($category['category_image']); ?>" id="cat_preview_img">
                                            <span class="preview-text" style="display:none;">Click to Change</span>
                                        <?php else: ?>
                                            <span class="preview-text">Click to Upload Image</span>
                                            <img src="" style="display:none;" id="cat_preview_img">
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="category_image" id="cat_img_input" class="d-none" accept="image/*" onchange="previewImage(this, 'cat_preview_img', 'cat_img_preview_box')">
                                </div>
                            </div>

                            <!-- OG Image -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0 text-dark fw-bold">Featured SEO Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    <label for="og_img_input" class="image-preview-container" id="og_img_preview_box">
                                        <?php if($category['og_image']): ?>
                                            <img src="../../<?php echo htmlspecialchars($category['og_image']); ?>" id="og_preview_img">
                                            <span class="preview-text" style="display:none;">Click to Change</span>
                                        <?php else: ?>
                                            <span class="preview-text">Click to Upload OG Image</span>
                                            <img src="" style="display:none;" id="og_preview_img">
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="og_image" id="og_img_input" class="d-none" accept="image/*" onchange="previewImage(this, 'og_preview_img', 'og_img_preview_box')">
                                </div>
                            </div>

                            <button type="submit" name="update_category" class="btn btn-primary btn-lg w-100"><i class="fas fa-save me-2"></i> Update Category</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        function previewImage(input, imgId, boxId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById(imgId);
                    var box = document.getElementById(boxId);
                    img.src = e.target.result;
                    img.style.display = 'block';
                    // Hide text
                    if(box.querySelector('.preview-text')) {
                        box.querySelector('.preview-text').style.display = 'none';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
