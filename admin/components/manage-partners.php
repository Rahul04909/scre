<?php
require_once '../../database/config.php';

// Handle Logo Upload & Add Partner
$message = '';
$messageType = '';

// Delete Partner
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // Get logo path to delete file
        $stmt = $pdo->prepare("SELECT logo_path FROM partners WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $partner = $stmt->fetch();
        
        if ($partner) {
            if (file_exists('../../' . $partner['logo_path'])) {
                unlink('../../' . $partner['logo_path']);
            }
            $delStmt = $pdo->prepare("DELETE FROM partners WHERE id = :id");
            $delStmt->execute([':id' => $id]);
            $message = "Partner deleted successfully.";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

if (isset($_POST['add_partner'])) {
    $name = trim($_POST['name']);
    $order = intval($_POST['display_order']);

    // Image Upload
    $uploadDir = '../../assets/uploads/partners/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = '';
    if (isset($_FILES['partner_logo']) && $_FILES['partner_logo']['error'] == 0) {
        $ext = pathinfo($_FILES['partner_logo']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        // Validate Image
        $check = getimagesize($_FILES['partner_logo']['tmp_name']);
        if($check !== false) {
             if (move_uploaded_file($_FILES['partner_logo']['tmp_name'], $targetFile)) {
                $imagePath = 'assets/uploads/partners/' . $fileName;
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $messageType = "danger";
            }
        } else {
            $message = "File is not an image.";
            $messageType = "danger";
        }
    } else {
         $message = "Please select a logo image.";
         $messageType = "danger";
    }

    if ($imagePath && empty($message)) {
        try {
            $sql = "INSERT INTO partners (name, logo_path, display_order) VALUES (:name, :logo, :order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':logo' => $imagePath,
                ':order' => $order
            ]);
            $message = "Partner added successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch Partners
try {
    $stmt = $pdo->query("SELECT * FROM partners ORDER BY display_order ASC, created_at DESC");
    $partners = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Partners - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
        .logo-preview {
            width: 120px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            background: #fff;
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
                    <h2 class="mb-0 text-dark fw-bold">Manage Partners</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Partner Form -->
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h4 class="mb-3 border-bottom pb-2">Add New Partner</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Partner Logo <span class="text-danger">*</span></label>
                                    <input type="file" name="partner_logo" class="form-control" accept="image/*" required>
                                    <div class="form-text text-muted">Transparent PNG recomended.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Partner Name (Alt Text)</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Google">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="0">
                                </div>
                                <button type="submit" name="add_partner" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-1"></i> Add Partner</button>
                            </form>
                        </div>
                    </div>

                    <!-- Partners List -->
                    <div class="col-lg-8">
                        <div class="table-section">
                            <h4 class="mb-3 border-bottom pb-2">Existing Partners</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Logo</th>
                                            <th>Name</th>
                                            <th>Order</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($partners) > 0): ?>
                                            <?php foreach ($partners as $partner): ?>
                                            <tr>
                                                <td>
                                                    <img src="../../<?php echo htmlspecialchars($partner['logo_path']); ?>" alt="Logo" class="logo-preview">
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($partner['name']); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary rounded-pill"><?php echo $partner['display_order']; ?></span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('M d, Y', strtotime($partner['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <a href="manage-partners.php?delete_id=<?php echo $partner['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this partner?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No partners found. Add one to get started!</td>
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
