<?php
require_once 'includes/auth.php';
require_once '../database/config.php';

// Fetch Languages with Lesson Counts
try {
    $sql = "SELECT l.id, l.language_name, l.language_code, COUNT(tl.id) as lesson_count 
            FROM typing_languages l
            LEFT JOIN typing_lessons tl ON l.id = tl.language_id
            GROUP BY l.id
            ORDER BY l.language_name ASC";
    $stmt = $pdo->query($sql);
    $languages = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Fetch Lessons if language is selected
$selected_lang = isset($_GET['lang_id']) ? intval($_GET['lang_id']) : ($languages[0]['id'] ?? 0);
$lessons = [];

if ($selected_lang) {
    try {
        $lStmt = $pdo->prepare("SELECT * FROM typing_lessons WHERE language_id = :lid ORDER BY id ASC");
        $lStmt->execute([':lid' => $selected_lang]);
        $lessons = $lStmt->fetchAll();
    } catch (PDOException $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Typing Lessons - Typing Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .content-wrapper { margin-left: 260px; min-height: calc(100vh - 70px); padding: 2rem; }
        .lesson-card {
            border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s;
            cursor: pointer; text-decoration: none; color: inherit; display: block;
        }
        .lesson-card:hover { transform: translateY(-3px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-color: #0f766e; }
        .lang-badge { cursor: pointer; }
    </style>
</head>
<body>
    
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <div class="content-wrapper">
        <h3 class="fw-bold mb-4">Typing Lessons</h3>
        
        <!-- Language Tabs -->
        <div class="d-flex overflow-auto mb-4 pb-2">
            <?php foreach ($languages as $lang): ?>
                <a href="?lang_id=<?php echo $lang['id']; ?>" class="badge rounded-pill px-3 py-2 me-2 text-decoration-none border <?php echo ($selected_lang == $lang['id']) ? 'bg-primary text-white border-primary' : 'bg-white text-dark border-secondary'; ?>">
                    <?php echo htmlspecialchars($lang['language_name']); ?>
                    <span class="badge bg-white text-dark ms-1 rounded-circle"><?php echo $lang['lesson_count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <?php if (count($lessons) > 0): ?>
                <?php foreach ($lessons as $lesson): ?>
                    <div class="col-md-4 col-lg-3">
                        <a href="take-lesson.php?id=<?php echo $lesson['id']; ?>" class="lesson-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-primary border">Time: <?php echo $lesson['duration_minutes']; ?> min</span>
                                <i class="far fa-play-circle text-primary fs-5"></i>
                            </div>
                            <h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($lesson['lesson_title']); ?></h6>
                            <small class="text-muted">Master this lesson</small>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486831.png" width="100" class="opacity-50 mb-3">
                    <p class="text-muted">No lessons found for this language.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
