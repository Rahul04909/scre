<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once '../database/config.php';

// Auth Check
if (!isset($_SESSION['online_exam_student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['online_exam_student_id'];

// 1. Fetch Student & Course Details
$stmt = $pdo->prepare("
    SELECT s.*, c.course_name, c.course_code, c.has_units, c.unit_type, c.unit_count, ac.session_name 
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN academic_sessions ac ON s.session_id = ac.id
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student record not found.");
}

// 2. Fetch Exams with Subject Info
$stmtExams = $pdo->prepare("
    SELECT es.*, sub.subject_name, sub.exam_duration, sub.unit_no, sub.theory_marks 
    FROM exam_schedules es
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE es.course_id = ? AND es.session_id = ?
    ORDER BY sub.unit_no ASC, es.exam_date ASC, es.start_time ASC
");
$stmtExams->execute([$student['course_id'], $student['session_id']]);
$all_exams = $stmtExams->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Attempted Exams
$stmtAttempted = $pdo->prepare("SELECT DISTINCT exam_schedule_id FROM exam_results WHERE student_id = ?");
$stmtAttempted->execute([$student_id]);
$attempted_exams = $stmtAttempted->fetchAll(PDO::FETCH_COLUMN);


// Helper to Group Exams by Unit
$grouped_exams = [];
if ($student['has_units']) {
    for ($i = 1; $i <= $student['unit_count']; $i++) {
        $grouped_exams[$i] = [];
    }
    foreach ($all_exams as $exam) {
        $u = $exam['unit_no'] ?: 1; // Default to 1 if null
        $grouped_exams[$u][] = $exam;
    }
} else {
    $grouped_exams[1] = $all_exams; // All in one group
}

// Helper for UI Text
$unit_label_uc = ucfirst($student['unit_type'] ?? 'Semester');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Online Exam Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        
        /* Navbar */
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #115E59 !important; }
        .top-nav { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 15px 0; }
        
        /* Profile Card */
        .profile-card {
            background: linear-gradient(135deg, #115E59 0%, #0f766e 100%);
            color: #fff;
            border-radius: 16px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(17, 94, 89, 0.2);
        }
        .profile-card::after {
            content: ''; position: absolute; right: -50px; bottom: -50px;
            width: 200px; height: 200px; background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .profile-img {
            width: 90px; height: 90px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3);
            object-fit: cover; background: #fff;
        }
        
        /* Tabs */
        .nav-pills { gap: 10px; }
        .nav-pills .nav-link {
            color: #64748b; font-weight: 600; border-radius: 10px; padding: 10px 20px; transition: all 0.2s;
            background: #fff; border: 1px solid #e2e8f0;
        }
        .nav-pills .nav-link:hover { background: #f1f5f9; }
        .nav-pills .nav-link.active {
            background-color: #115E59; color: #fff; border-color: #115E59; box-shadow: 0 4px 6px rgba(17, 94, 89, 0.2);
        }
        
        /* Table */
        .table-custom tr td { padding: 15px 10px; }
        .table-custom thead th { font-weight: 600; letter-spacing: 0.5px; padding: 15px 10px; }
        
        /* Buttons */
        .btn-start-exam {
            background-color: #10B981; color: white; border: none; font-weight: 600;
            padding: 8px 16px; border-radius: 8px; transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
            white-space: nowrap;
        }
        .btn-start-exam:hover { background-color: #059669; color: white; transform: translateY(-2px); }
        
        .timer-badge { 
            font-family: 'Courier New', monospace; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            white-space: nowrap; /* Prevent wrapping */
        }
        
        /* Primary overrides for Bootstrap */
        .btn-outline-danger { border-radius: 8px; }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="top-nav sticky-top">
        <div class="container-fluid px-5 d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap me-2"></i>PACE EXAM PORTAL</a>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                    <div class="small text-muted"><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                </div>
                <!-- Logout -->
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-5 py-4">
        <div class="row g-4">
            
            <!-- Left Sidebar: Profile -->
            <div class="col-lg-3">
                <div class="profile-card mb-4 position-sticky" style="top: 100px;">
                    <div class="text-center mb-3">
                        <?php 
                        $img = !empty($student['student_image']) ? '../'.$student['student_image'] : 'https://ui-avatars.com/api/?name='.$student['first_name'].'+'.$student['last_name'].'&background=random';
                        ?>
                        <img src="<?php echo $img; ?>" class="profile-img mb-3">
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h5>
                        <p class="mb-0 opacity-75 small"><?php echo htmlspecialchars($student['course_code']); ?></p>
                    </div>
                    <hr class="border-light opacity-25">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="opacity-75">Enrollment:</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($student['enrollment_no']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="opacity-75">Session:</span>
                            <span class="fw-semibold text-truncate ms-2" style="max-width: 120px;"><?php echo htmlspecialchars($student['session_name']); ?></span>
                        </div>
                         <div class="d-flex justify-content-between mb-2">
                            <span class="opacity-75">Gender:</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($student['gender']); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Quick Actions removed as per request -->
            </div>
            
            <!-- Right Content: Exams -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark m-0">Exam Schedule</h4>
                    <div class="text-muted small"><i class="fas fa-info-circle me-1"></i> Check your schedule below</div>
                </div>
                
                <!-- Unit Tabs -->
                <?php if ($student['has_units']): ?>
                <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                    <?php for ($i = 1; $i <= $student['unit_count']; $i++): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($i==1) ? 'active' : ''; ?>" id="pills-unit-<?php echo $i; ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-unit-<?php echo $i; ?>" type="button" role="tab">
                                <?php echo $unit_label_uc . ' ' . $i; ?>
                            </button>
                        </li>
                    <?php endfor; ?>
                </ul>
                <?php endif; ?>
                
                <div class="tab-content" id="pills-tabContent">
                    <?php 
                    $groups = $student['has_units'] ? $student['unit_count'] : 1;
                    
                    for ($u = 1; $u <= $groups; $u++): 
                        $current_exams = $grouped_exams[$u] ?? [];
                    ?>
                        <div class="tab-pane fade <?php echo ($u==1) ? 'show active' : ''; ?>" id="pills-unit-<?php echo $u; ?>" role="tabpanel">
                            
                            <?php if (empty($current_exams)): ?>
                                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                                    <div class="mb-3">
                                        <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    </div>
                                    <h5 class="text-muted">No Exams Scheduled</h5>
                                    <p class="text-secondary small">There are no exams scheduled for this <?php echo strtolower($unit_label_uc); ?> yet.</p>
                                </div>
                            <?php else: ?>
                            
                                <div class="bg-white rounded-4 shadow-sm overflow-hidden border border-light">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-custom align-middle mb-0">
                                            <thead class="bg-light text-secondary small text-uppercase">
                                                <tr>
                                                    <th class="ps-4">#</th>
                                                    <th>Subject</th>
                                                    <th>Date & Time</th>
                                                    <th>Duration</th>
                                                    <th style="min-width: 200px;">Status / Countdown</th>
                                                    <th class="text-end pe-4">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $sr = 1;
                                                foreach ($current_exams as $exam): 
                                                    $start_ts = strtotime($exam['exam_date'] . ' ' . $exam['start_time']);
                                                    // Calculate End Time based on duration
                                                    $end_ts = $start_ts + ($exam['exam_duration'] * 60); 
                                                    
                                                    // Check if exam is already attempted
                                                    $is_attempted = in_array($exam['id'], $attempted_exams);
                                                ?>
                                                <tr>
                                                    <td class="ps-4 text-muted fw-bold"><?php echo str_pad($sr++, 2, '0', STR_PAD_LEFT); ?></td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($exam['subject_name']); ?></div>
                                                        <div class="small text-muted">Theory: <?php echo $exam['theory_marks']; ?> Marks</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold text-dark"><?php echo date('d M Y', strtotime($exam['exam_date'])); ?></div>
                                                        <div class="small text-muted text-uppercase"><?php echo date('h:i A', strtotime($exam['start_time'])); ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-clock me-1 text-muted"></i> <?php echo $exam['exam_duration']; ?> Mins
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($is_attempted): ?>
                                                            <div class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Completed</div>
                                                        <?php else: ?>
                                                            <div class="timer-badge text-primary" 
                                                                 data-start="<?php echo $start_ts; ?>" 
                                                                 data-end="<?php echo $end_ts; ?>">
                                                                 Loading...
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <?php if ($is_attempted): ?>
                                                            <a href="result.php?schedule_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-success shadow-sm fw-semibold">
                                                                <i class="fas fa-chart-bar me-1"></i> See Result
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm shadow-sm action-btn" 
                                                                    data-exam-id="<?php echo $exam['id']; ?>" 
                                                                    disabled>
                                                                Please Wait
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            
                            <?php endif; ?>
                            
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-sign-out-alt text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Logout?</h5>
                    <p class="text-muted mb-4 small">Are you sure you want to end your session?</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light flex-fill rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <a href="logout.php" class="btn btn-danger flex-fill rounded-pill">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Server time sync 
        const serverTimeNow = <?php echo time(); ?> * 1000;
        const clientTimeNow = Date.now();
        const timeOffset = serverTimeNow - clientTimeNow; 
        
        function updateTimers() {
            const now = Date.now() + timeOffset;
            
            document.querySelectorAll('.timer-badge').forEach(el => {
                const start = parseInt(el.getAttribute('data-start')) * 1000;
                const end = parseInt(el.getAttribute('data-end')) * 1000;
                // Only find start button if it exists (might not if exam is completed)
                const btn = el.closest('tr').querySelector('.action-btn');
                
                if (now < start) {
                    // Not Started
                    const diff = start - now;
                    // Format diff to HH:MM:SS
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    el.innerHTML = `<span class="text-warning"><i class="fas fa-hourglass-start me-1"></i> Starts in ${hours}h ${minutes}m ${seconds}s</span>`;
                    
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-lock me-1"></i> Locked';
                        btn.className = 'btn btn-sm btn-secondary action-btn';
                    }
                    
                } else if (now >= start && now <= end) {
                    // Ongoing
                    const left = end - now;
                    const hours = Math.floor(left / (1000 * 60 * 60));
                    const minutes = Math.floor((left % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((left % (1000 * 60)) / 1000);
                    
                    el.innerHTML = `<span class="text-success pulse"><i class="fas fa-circle text-danger me-1 small"></i> Live: ${hours}h ${minutes}m ${seconds}s</span>`;
                    
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'Start Exam <i class="fas fa-play ms-1"></i>';
                        btn.className = 'btn btn-sm btn-start-exam action-btn shadow-sm';
                        const examId = btn.getAttribute('data-exam-id');
                        btn.onclick = function() { window.location.href = 'start-exam.php?exam_id=' + examId; };
                    }
                    
                } else {
                    // Expired
                    el.innerHTML = '<span class="text-muted">Exam Concluded</span>';
                    
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = 'Closed';
                        btn.className = 'btn btn-sm btn-outline-secondary action-btn border-0';
                    }
                }
            });
        }
        
        setInterval(updateTimers, 1000);
        updateTimers(); 
    </script>
</body>
</html>
