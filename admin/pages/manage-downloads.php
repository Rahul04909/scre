<?php
require_once '../../database/config.php';

// Handle Add/Delete
$message = '';
$messageType = '';

// Delete Download
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        // First get file path to delete from server
        $stmt = $pdo->prepare("SELECT file_path FROM downloads WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch();
        
        if ($doc && file_exists('../../' . $doc['file_path'])) {
            unlink('../../' . $doc['file_path']);
        }

        $delStmt = $pdo->prepare("DELETE FROM downloads WHERE id = :id");
        $delStmt->execute([':id' => $id]);
        $message = "Download deleted successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Add Download
if (isset($_POST['add_download'])) {
    $title = trim($_POST['title']);
    
    // File Upload
    $uploadDir = '../../assets/uploads/downloads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] == 0) {
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['doc_file']['name']));
        $targetFile = $uploadDir . $fileName;
        $dbFilePath = 'assets/uploads/downloads/' . $fileName; // Path stored in DB

        if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $targetFile)) {
            try {
                $sql = "INSERT INTO downloads (title, file_path) VALUES (:title, :fpath)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $title,
                    ':fpath' => $dbFilePath
                ]);
                $message = "Download added successfully!";
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
        $message = "Please select a valid file.";
        $messageType = "danger";
    }
}

// Fetch Downloads
try {
    $stmt = $pdo->query("SELECT * FROM downloads ORDER BY created_at DESC");
    $downloads = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Downloads - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
    <style>
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
                    <h2 class="mb-0 text-dark fw-bold">Manage Download Files</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Download Form -->
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h4 class="mb-3 border-bottom pb-2">Upload File</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required placeholder="Ex: Course Syllabus">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                                    <input type="file" name="doc_file" class="form-control" required>
                                </div>
                                <button type="submit" name="add_download" class="btn btn-primary w-100"><i class="fas fa-upload me-1"></i> Upload</button>
                            </form>
                        </div>
                    </div>

                    <!-- Downloads List -->
                    <div class="col-lg-8">
                        <div class="table-section">
                            <h4 class="mb-3 border-bottom pb-2">Uploaded Files</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Title</th>
                                            <th>File</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($downloads) > 0): ?>
                                            <?php 
                                            $sr = 1;
                                            foreach ($downloads as $doc): 
                                            ?>
                                            <tr>
                                                <td><?php echo $sr++; ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($doc['title']); ?></td>
                                                <td>
                                                    <a href="../../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="small text-decoration-none"><i class="fas fa-eye"></i> View File</a>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('M d, Y', strtotime($doc['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <a href="manage-downloads.php?delete_id=<?php echo $doc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this file?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No files uploaded yet.</td>
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
