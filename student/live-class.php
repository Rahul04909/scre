<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

$student_id = $_SESSION['student_id'];

// Fetch Student Details (Course & Center)
$stmtStudent = $pdo->prepare("SELECT center_id, course_id FROM students WHERE id = ?");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

$center_id = $student['center_id'];
$course_id = $student['course_id'];

// Fetch Upcoming Classes
$today = date('Y-m-d');
$stmtClasses = $pdo->prepare("SELECT * FROM live_classes 
                              WHERE center_id = ? AND course_id = ? AND class_date >= ? 
                              ORDER BY class_date ASC, class_time ASC");
$stmtClasses->execute([$center_id, $course_id, $today]);
$upcoming_classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Live Classes - PACE Student</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <style>
        .class-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            background: #fff;
        }
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .class-date-box {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            min-width: 80px;
        }
        .live-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>

            <div class="container-fluid px-4 py-5">
                <h2 class="fw-bold mb-4" style="color: #115E59;">My Live Classes</h2>

                <?php if (empty($upcoming_classes)): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>No upcoming live classes scheduled for your course at the moment.</div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($upcoming_classes as $class): 
                             $classDateTime = strtotime($class['class_date'] . ' ' . $class['class_time']);
                             $isLive = ($class['class_date'] == date('Y-m-d') && time() >= $classDateTime && time() <= ($classDateTime + 3600)); 
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="class-card <?php echo $isLive ? 'border-danger' : ''; ?>">
                                    <?php if ($isLive): ?>
                                        <span class="badge bg-danger live-badge animate__animated animate__flash animate__infinite">LIVE NOW</span>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="class-date-box me-3">
                                            <div class="fw-bold fs-4 text-dark" style="line-height:1;"><?php echo date('d', strtotime($class['class_date'])); ?></div>
                                            <small class="text-uppercase text-muted" style="font-size: 0.7rem;"><?php echo date('M', strtotime($class['class_date'])); ?></small>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 title-truncate"><?php echo htmlspecialchars($class['title']); ?></h5>
                                            <div class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo date('h:i A', strtotime($class['class_time'])); ?></div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-3 text-muted opacity-25">
                                    
                                    <a href="<?php echo htmlspecialchars($class['link']); ?>" target="_blank" class="btn w-100 <?php echo $isLive ? 'btn-danger' : 'btn-primary'; ?>" style="<?php echo $isLive ? '' : 'background-color: #115E59; border-color: #115E59;'; ?>">
                                        <i class="fas fa-video me-2"></i> <?php echo $isLive ? 'Join Class Now' : 'Join Class'; ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
