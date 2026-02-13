<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../database/config.php';

$center_id = $_SESSION['center_id'];
$message = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $date = $_POST['class_date'];
    $time = $_POST['class_time'];
    $link = trim($_POST['link']);

    if ($course_id && $title && $date && $time && $link) {
        try {
            $stmt = $pdo->prepare("INSERT INTO live_classes (center_id, course_id, title, class_date, class_time, link) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$center_id, $course_id, $title, $date, $time, $link]);
            $message = "<div class='alert alert-success'>Live Class scheduled successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmtDel = $pdo->prepare("DELETE FROM live_classes WHERE id = ? AND center_id = ?");
    $stmtDel->execute([$delete_id, $center_id]);
    header("Location: manage-live-class.php?msg=deleted");
    exit;
}

// Fetch Courses
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch Upcoming Classes (Today or Future)
$today = date('Y-m-d');
$stmtClasses = $pdo->prepare("SELECT l.*, c.course_name 
                              FROM live_classes l 
                              JOIN courses c ON l.course_id = c.id 
                              WHERE l.center_id = ? AND l.class_date >= ? 
                              ORDER BY l.class_date ASC, l.class_time ASC");
$stmtClasses->execute([$center_id, $today]);
$upcoming_classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Live Classes - PACE Center</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        .content-card { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php include '../sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include '../header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <h2 class="fw-bold mb-4" style="color: #115E59;">Manage Live Classes</h2>
            <?php echo $message; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted') echo "<div class='alert alert-success'>Class deleted successfully.</div>"; ?>

            <div class="row">
                <!-- Create Class Form -->
                <div class="col-lg-4">
                    <div class="content-card">
                        <h5 class="fw-bold mb-3">Schedule New Class</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php foreach($courses as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Class Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to PHP" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="class_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Time</label>
                                    <input type="time" name="class_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meeting Link</label>
                                <input type="url" name="link" class="form-control" placeholder="https://zoom.us/..." required>
                            </div>
                            <button type="submit" name="create_class" class="btn btn-primary w-100" style="background-color: #115E59; border-color: #115E59;">Schedule Class</button>
                        </form>
                    </div>
                </div>

                <!-- Upcoming Classes List -->
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Upcoming Classes</h5>
                            <a href="history.php" class="btn btn-sm btn-outline-secondary">View History</a>
                        </div>
                        
                        <?php if (empty($upcoming_classes)): ?>
                            <div class="alert alert-info text-center">No upcoming classes scheduled.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Course & Title</th>
                                            <th>Link</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($upcoming_classes as $class): 
                                            $classDateTime = strtotime($class['class_date'] . ' ' . $class['class_time']);
                                            $isLive = ($class['class_date'] == date('Y-m-d') && time() >= $classDateTime && time() <= ($classDateTime + 3600)); // Assumes 1 hour duration logic for "Live Now" badge
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo date('d M Y', strtotime($class['class_date'])); ?></div>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($class['class_time'])); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary mb-1"><?php echo htmlspecialchars($class['course_name']); ?></span>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($class['title']); ?></div>
                                                </td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($class['link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-video me-1"></i> Join
                                                    </a>
                                                    <?php if ($isLive): ?>
                                                        <span class="badge bg-danger animate__animated animate__flash animate__infinite">LIVE</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="?delete_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this class?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
