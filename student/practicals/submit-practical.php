<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';
$messageType = '';

if (!isset($_GET['id'])) {
    die("Invalid Practical ID");
}

$practical_id = intval($_GET['id']);

// 1. Fetch Practical Details
$stmt = $pdo->prepare("
    SELECT p.*, s.subject_name 
    FROM practicals p 
    JOIN subjects s ON p.subject_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$practical_id]);
$practical = $stmt->fetch();

if (!$practical) {
    die("Practical not found.");
}

// 2. Check Expiry
$today = date('Y-m-d');
$is_expired = ($today > $practical['submission_last_date']);

// 3. Check if already submitted
$stmtCheck = $pdo->prepare("SELECT * FROM practical_submissions WHERE practical_id = ? AND student_id = ?");
$stmtCheck->execute([$practical_id, $student_id]);
$existing = $stmtCheck->fetch();

if ($existing) {
    $message = "You have already submitted this practical.";
    $messageType = "info";
}

// 4. Handle Submission
if (isset($_POST['submit_practical']) && !$existing && !$is_expired) {
    
    $file_path = '';
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $uploadDir = '../../assets/uploads/practical_submissions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
        // Rename: sub_{student_id}_{practical_id}_{timestamp}.pdf
        $fileName = 'sub_' . $student_id . '_' . $practical_id . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
            $file_path = 'assets/uploads/practical_submissions/' . $fileName;
        }
    }
    
    if ($file_path) {
        try {
            $sql = "INSERT INTO practical_submissions (practical_id, student_id, submission_file, status) VALUES (?, ?, ?, 'Submitted')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$practical_id, $student_id, $file_path]);
            
            // Redirect with success
            header("Location: index.php?msg=submitted");
            exit;
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "File upload failed. Please try again.";
        $messageType = "danger";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Practical - PACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; width: 100%; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
        .upload-area { border: 2px dashed #ddd; border-radius: 10px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .upload-area:hover { border-color: #0d6efd; background-color: #f8fbff; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        
                        <div class="mb-3">
                            <a href="index.php" class="text-decoration-none text-secondary"><i class="fas fa-arrow-left me-2"></i> Back to Practicals</a>
                        </div>
                        
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($practical['title']); ?></h4>
                                <div class="mb-4">
                                    <span class="badge bg-light text-dark border me-2"><?php echo htmlspecialchars($practical['subject_name']); ?></span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10">Last Date: <?php echo date('d M Y', strtotime($practical['submission_last_date'])); ?></span>
                                </div>
                                
                                <hr class="opacity-10 my-4">

                                <?php if ($message): ?>
                                    <div class="alert alert-<?php echo $messageType; ?> text-center py-4 rounded-3 border-0 bg-light">
                                        <div class="mb-2"><i class="fas <?php echo ($messageType=='success') ? 'fa-check-circle text-success' : 'fa-info-circle text-info'; ?> fa-2x"></i></div>
                                        <?php echo $message; ?>
                                        <br>
                                        <?php if($existing): ?>
                                        <a href="../../<?php echo htmlspecialchars($existing['submission_file']); ?>" target="_blank" class="btn btn-outline-primary mt-3 btn-sm rounded-pill">View My Submission</a>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($is_expired): ?>
                                     <div class="alert alert-danger text-center">
                                         <i class="fas fa-clock mb-2 fa-2x"></i><br>
                                         The submission deadline has passed.
                                     </div>
                                <?php else: ?>
                                
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-4">
                                            <h6 class="fw-bold mb-3">Upload your work (PDF only)</h6>
                                            <input type="file" name="submission_file" id="submission_file" class="form-control" accept="application/pdf" required>
                                            <div class="form-text mt-2">Make sure your PDF is clear and contains all required pages.</div>
                                        </div>
                                        
                                        <div class="d-grid mt-4">
                                            <button type="submit" name="submit_practical" class="btn btn-primary btn-lg rounded-pill">
                                                <i class="fas fa-paper-plane me-2"></i> Submit Practical
                                            </button>
                                        </div>
                                    </form>
                                    
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
