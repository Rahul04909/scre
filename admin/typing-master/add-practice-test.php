<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Fetch Languages
try {
    $stmt = $pdo->query("SELECT * FROM typing_languages ORDER BY language_name ASC");
    $languages = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (isset($_POST['add_test'])) {
    $language_id = intval($_POST['language_id']);
    $test_title = trim($_POST['test_title']);
    $duration_minutes = intval($_POST['duration_minutes']);
    $test_content = $_POST['test_content'];

    try {
        $sql = "INSERT INTO typing_practice_tests (language_id, test_title, duration_minutes, test_content) 
                VALUES (:lang_id, :title, :duration, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':lang_id' => $language_id,
            ':title' => $test_title,
            ':duration' => $duration_minutes,
            ':content' => $test_content
        ]);

        header("Location: manage-practice-tests.php?msg=added");
        exit;
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
    <title>Add Practice Test - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
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
                    <h2 class="mb-0">Add Practice Test</h2>
                    <a href="manage-practice-tests.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST">
                            <h5 class="section-header">Test Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Language <span class="text-danger">*</span></label>
                                    <select name="language_id" class="form-select" required>
                                        <option value="">Select Language</option>
                                        <?php foreach ($languages as $lang): ?>
                                            <option value="<?php echo $lang['id']; ?>"><?php echo htmlspecialchars($lang['language_name']); ?> (<?php echo htmlspecialchars($lang['language_code']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Test Title <span class="text-danger">*</span></label>
                                    <input type="text" name="test_title" class="form-control" placeholder="e.g. Advanced Paragraphs" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_minutes" class="form-control" placeholder="e.g. 10" min="1" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Test Content <span class="text-danger">*</span></label>
                                <textarea id="summernote" name="test_content" required></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" name="add_test" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Save Practice Test</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Enter test content here...',
                tabsize: 2,
                height: 300,
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
