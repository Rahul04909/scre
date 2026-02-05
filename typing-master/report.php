<?php
require_once 'includes/auth.php';
require_once '../database/config.php';

$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = null;

if ($result_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM typing_test_results WHERE id = :id AND student_id = :sid");
        $stmt->execute([':id' => $result_id, ':sid' => $_SESSION['student_id']]);
        $result = $stmt->fetch();
    } catch (PDOException $e) { }
}

if (!$result) {
    die("Result not found or access denied.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Result - Typing Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0fdfa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .result-card {
            background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 600px; width: 100%; overflow: hidden;
        }
        .result-header {
            background: #0f766e; color: white; padding: 2rem; text-align: center;
        }
        .stat-circle {
            width: 120px; height: 120px; border-radius: 50%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            margin: 0 auto 1rem; border: 5px solid rgba(255,255,255,0.3);
        }
        .stat-item { text-align: center; padding: 1rem; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0f766e; }
        .stat-label { font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <div class="result-card">
        <div class="result-header">
            <h4 class="fw-bold mb-0"><?php echo !empty($result['lesson_id']) ? 'Lesson Completed!' : 'Test Completed!'; ?></h4>
            <p class="mb-0 opacity-75">Here is your performance report</p>
        </div>
        <div class="p-4">
            <div class="text-center mb-4">
                <div class="stat-circle bg-white text-primary">
                    <span class="h1 fw-bold mb-0"><?php echo $result['wpm']; ?></span>
                    <span class="small fw-bold">WPM</span>
                </div>
                <h5 class="fw-bold text-dark">Net Speed</h5>
            </div>
            
            <div class="row g-0 border-top border-bottom">
                <div class="col-4 border-end">
                    <div class="stat-item">
                        <div class="stat-value text-success"><?php echo $result['accuracy']; ?>%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
                <div class="col-4 border-end">
                    <div class="stat-item">
                        <div class="stat-value text-danger"><?php echo $result['errors']; ?></div>
                        <div class="stat-label">Errors</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-item">
                        <div class="stat-value text-primary"><?php echo gmdate("i:s", $result['duration_seconds']); ?></div>
                        <div class="stat-label">Time</div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="dashboard.php" class="btn btn-outline-secondary flex-grow-1">Back to Home</a>
                <a href="practice-tests.php" class="btn btn-primary flex-grow-1">Take Another Test</a>
            </div>
        </div>
    </div>

</body>
</html>
