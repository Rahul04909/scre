<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

if (!isset($_GET['id'])) {
    header("Location: manage-languages.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch Language
try {
    $stmt = $pdo->prepare("SELECT * FROM typing_languages WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $language = $stmt->fetch();
    
    if (!$language) {
        die("Language not found.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (isset($_POST['update_language'])) {
    $language_name = trim($_POST['language_name']);
    $language_code = trim($_POST['language_code']);

    try {
        // Check if language code already exists for OTHER languages
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM typing_languages WHERE language_code = :code AND id != :id");
        $stmt->execute([':code' => $language_code, ':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $message = "Language code already exists!";
            $messageType = "danger";
        } else {
            $sql = "UPDATE typing_languages SET language_name = :name, language_code = :code WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $language_name, ':code' => $language_code, ':id' => $id]);

            $message = "Language updated successfully!";
            $messageType = "success";
            
            // Refresh data
            $language['language_name'] = $language_name;
            $language['language_code'] = $language_code;
        }
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
    <title>Edit Language - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .section-header { border-left: 4px solid #0d6efd; padding-left: 10px; margin-bottom: 20px; color: #333; font-weight: 600; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Edit Language</h2>
                    <a href="manage-languages.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST">
                            <h5 class="section-header">Language Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Language Name <span class="text-danger">*</span></label>
                                    <input type="text" name="language_name" class="form-control" value="<?php echo htmlspecialchars($language['language_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Language Code <span class="text-danger">*</span></label>
                                    <input type="text" name="language_code" class="form-control" value="<?php echo htmlspecialchars($language['language_code']); ?>" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="update_language" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Update Language</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
