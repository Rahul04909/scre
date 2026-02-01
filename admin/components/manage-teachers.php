<?php
require_once '../../database/config.php';

// Handle Image Upload & Add Teacher
$message = '';
$messageType = '';

// Delete Teacher
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Get image path to delete file
        $stmt = $pdo->prepare("SELECT image_path FROM teachers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            if (file_exists('../../' . $teacher['image_path'])) {
                unlink('../../' . $teacher['image_path']);
            }
            $delStmt = $pdo->prepare("DELETE FROM teachers WHERE id = :id");
            $delStmt->execute([':id' => $id]);
            $message = "Teacher deleted successfully.";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

if (isset($_POST['add_teacher'])) {
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $college = trim($_POST['college']);
    $experience = trim($_POST['experience_years']);
    $order = intval($_POST['display_order']);

    // Image Upload
    $uploadDir = '../../assets/uploads/teachers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = '';
    if (isset($_FILES['teacher_image']) && $_FILES['teacher_image']['error'] == 0) {
        $ext = pathinfo($_FILES['teacher_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        // Validate Image
        $check = getimagesize($_FILES['teacher_image']['tmp_name']);
        if($check !== false) {
             if (move_uploaded_file($_FILES['teacher_image']['tmp_name'], $targetFile)) {
                $imagePath = 'assets/uploads/teachers/' . $fileName;
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $messageType = "danger";
            }
        } else {
            $message = "File is not an image.";
            $messageType = "danger";
        }
    } else {
         // Default placeholder if needed, or enforce upload
         $message = "Please select an image.";
         $messageType = "danger";
    }

    if ($imagePath && empty($message)) {
        try {
            $sql = "INSERT INTO teachers (name, designation, college, experience_years, image_path, display_order) VALUES (:name, :desig, :coll, :exp, :img, :order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':desig' => $designation,
                ':coll' => $college,
                ':exp' => $experience,
                ':img' => $imagePath,
                ':order' => $order
            ]);
            $message = "Teacher added successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch Teachers
try {
    $stmt = $pdo->query("SELECT * FROM teachers ORDER BY display_order ASC, created_at DESC");
    $teachers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .teacher-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #ddd;
        }
        .form-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include '../../admin/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 text-dark fw-bold">Manage Teachers</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Teacher Form -->
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h4 class="mb-3 border-bottom pb-2">Add New Teacher</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Teacher Image <span class="text-danger">*</span></label>
                                    <input type="file" name="teacher_image" class="form-control" accept="image/*" required>
                                    <div class="form-text text-muted">Use Transparent PNG for best look.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Math Master Teacher">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">College/University</label>
                                    <input type="text" name="college" class="form-control" placeholder="e.g. IIT Bombay">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Experience</label>
                                    <input type="text" name="experience_years" class="form-control" placeholder="e.g. 10+ years">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="0">
                                </div>
                                <button type="submit" name="add_teacher" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-1"></i> Add Teacher</button>
                            </form>
                        </div>
                    </div>

                    <!-- Teachers List -->
                    <div class="col-lg-8">
                        <div class="table-section">
                            <h4 class="mb-3 border-bottom pb-2">Existing Teachers</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Details</th>
                                            <th>Experience</th>
                                            <th>Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($teachers) > 0): ?>
                                            <?php foreach ($teachers as $teacher): ?>
                                            <tr>
                                                <td>
                                                    <img src="../../<?php echo htmlspecialchars($teacher['image_path']); ?>" alt="Teacher" class="teacher-preview">
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($teacher['designation']); ?></div>
                                                    <div class="text-info small"><?php echo htmlspecialchars($teacher['college']); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($teacher['experience_years']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary rounded-pill"><?php echo $teacher['display_order']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="manage-teachers.php?delete_id=<?php echo $teacher['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No teachers found. Add one to get started!</td>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../admin/assets/js/sidebar.js"></script>
</body>
</html>
