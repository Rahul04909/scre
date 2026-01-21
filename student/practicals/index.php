<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// 1. Fetch Student Enrollment Details
$stmt = $pdo->prepare("SELECT center_id, course_id, session_id FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

$center_id = $student['center_id'];
$course_id = $student['course_id'];
$session_id = $student['session_id'];

// 2. Fetch Course Details (Units Logic)
$stmtC = $pdo->prepare("SELECT course_name, course_code, has_units, unit_type, unit_count FROM courses WHERE id = ?");
$stmtC->execute([$course_id]);
$course = $stmtC->fetch();
$has_units = $course['has_units'];
$unit_type = $course['unit_type'];
$unit_count = $course['unit_count'];

// 3. Fetch Practicals
$sql = "
    SELECT p.*, s.subject_name
    FROM practicals p
    JOIN subjects s ON p.subject_id = s.id
    WHERE p.center_id = ? AND p.course_id = ? AND p.session_id = ?
    ORDER BY p.unit_no ASC, p.created_at DESC
";
$stmtP = $pdo->prepare($sql);
$stmtP->execute([$center_id, $course_id, $session_id]);
$all_practicals = $stmtP->fetchAll();

// 4. Fetch Submissions
$stmtSub = $pdo->prepare("SELECT practical_id, marks_obtained, status FROM practical_submissions WHERE student_id = ?");
$stmtSub->execute([$student_id]);
$submissions = $stmtSub->fetchAll(PDO::FETCH_KEY_PAIR); // practical_id => marks_obtained logic needs object?
// FETCH_KEY_PAIR only suitable for id -> value. I need status and marks. 
// Let's use FETCH_GROUP | FETCH_UNIQUE to get practical_id as key.
$stmtSub = $pdo->prepare("SELECT practical_id, marks_obtained, status, created_at FROM practical_submissions WHERE student_id = ?");
$stmtSub->execute([$student_id]);
$submissions = $stmtSub->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

// 5. Group by Unit
$grouped_practicals = [];
if ($has_units) {
    // Initialize buckets for all units
    for ($i = 1; $i <= $unit_count; $i++) {
        $grouped_practicals[$i] = [];
    }
    // Fill buckets
    foreach ($all_practicals as $p) {
        $u = $p['unit_no'];
        if (isset($grouped_practicals[$u])) {
            $grouped_practicals[$u][] = $p;
        }
    }
} else {
    // Single bucket '0' or 'all'
    $grouped_practicals[0] = $all_practicals;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Practicals - PACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Inline CSS for Layout Fix matching center/practicals -->
    <style>
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; width: 100%; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
        
        .nav-tabs .nav-link { color: #555; font-weight: 500; }
        .nav-tabs .nav-link.active { color: #0d6efd; font-weight: 600; border-bottom: 2px solid #0d6efd; }
        .practical-card { transition: all 0.2s; border: 1px solid #f0f0f0; }
        .practical-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: #e0e0e0; }
        .date-badge { font-size: 0.8rem; background: #f8f9fa; padding: 4px 10px; border-radius: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                     <div>
                         <h4 class="fw-bold mb-1 text-dark">My Practicals</h4>
                         <p class="text-muted mb-0 small"><?php echo htmlspecialchars($course['course_name']); ?></p>
                     </div>
                </div>

                <!-- Content Area -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        
                        <?php if ($has_units): ?>
                            <!-- Tabs -->
                            <ul class="nav nav-tabs nav-fill mb-4 border-bottom-0" id="unitTabs" role="tablist">
                                <?php for($i=1; $i<=$unit_count; $i++): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?php echo ($i==1) ? 'active' : ''; ?>" 
                                                id="tab-unit-<?php echo $i; ?>" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#unit-<?php echo $i; ?>" 
                                                type="button" role="tab">
                                            <?php echo $unit_type . ' ' . $i; ?>
                                        </button>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="unitTabsContent">
                                <?php for($i=1; $i<=$unit_count; $i++): ?>
                                    <div class="tab-pane fade <?php echo ($i==1) ? 'show active' : ''; ?>" id="unit-<?php echo $i; ?>" role="tabpanel">
                                        <?php if(empty($grouped_practicals[$i])): ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-flask text-muted fa-3x mb-3 opacity-25"></i>
                                                <p class="text-muted">No practicals found for this <?php echo strtolower($unit_type); ?>.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="row g-3">
                                                <?php foreach($grouped_practicals[$i] as $p): renderPracticalCard($p, $submissions); endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>

                        <?php else: ?>
                            <!-- No Units List -->
                            <?php if(empty($grouped_practicals[0])): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-flask text-muted fa-3x mb-3 opacity-25"></i>
                                    <p class="text-muted">No practicals assigned yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach($grouped_practicals[0] as $p): renderPracticalCard($p, $submissions); endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>

<?php
// Helper to render card
function renderPracticalCard($p, $submissions) {
    // Relative path fix: file_path is relative to root, but we are in student/practicals/
    // file_path in DB is 'assets/uploads/...'
    // So link should be '../../' . $file_path
    $link = '../../' . htmlspecialchars($p['file_path']);
    $startDate = date('d M Y', strtotime($p['submission_start_date']));
    $lastDate = date('d M Y', strtotime($p['submission_last_date']));
    
    // Check Submission
    $pid = $p['id'];
    $isSubmitted = isset($submissions[$pid]);
    $subStatus = $isSubmitted ? $submissions[$pid]['status'] : 'Pending';
    $marks = $isSubmitted ? $submissions[$pid]['marks_obtained'] : null;
    
    echo '
    <div class="col-md-6 col-lg-4">
        <div class="practical-card p-3 rounded-3 bg-white h-100 position-relative">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Practical</span>
                <i class="fas fa-flask text-primary opacity-25"></i>
            </div>
            
            <h6 class="fw-bold text-dark mb-1">'.htmlspecialchars($p['title']).'</h6>
            <div class="text-secondary small mb-3">'.htmlspecialchars($p['subject_name']).'</div>
            
            <div class="d-flex flex-wrap gap-2 mb-3">
                <div class="date-badge"><i class="far fa-clock me-1"></i> Start: '.$startDate.'</div>
                <div class="date-badge text-danger"><i class="far fa-bell me-1"></i> End: '.$lastDate.'</div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="'.$link.'" target="_blank" class="btn btn-outline-dark w-100 rounded-pill btn-sm">
                    <i class="fas fa-download me-1"></i> File
                </a>
                ';
    
    if ($isSubmitted) {
        $marksDisplay = ($marks !== null) ? '<span class="fw-bold">'.$marks.' Marks</span>' : 'Submitted';
        $badgeClass = ($marks !== null) ? 'btn-success' : 'btn-info text-white';
        echo '<button class="btn '.$badgeClass.' w-100 rounded-pill btn-sm" disabled>
                <i class="fas fa-check-circle me-1"></i> '.$marksDisplay.'
              </button>';
    } else {
        echo '<a href="submit-practical.php?id='.$pid.'" class="btn btn-primary w-100 rounded-pill btn-sm">
                <i class="fas fa-upload me-1"></i> Submit
              </a>';
    }
    
    echo '
            </div>
        </div>
    </div>
    ';
}
?>
