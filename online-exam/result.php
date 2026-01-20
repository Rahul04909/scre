<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['online_exam_student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['online_exam_student_id'];
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

if ($schedule_id == 0) {
    header("Location: index.php");
    exit;
}

// Fetch Result
$stmt = $pdo->prepare("
    SELECT er.*, sub.subject_name, es.exam_date, sub.unit_no
    FROM exam_results er
    JOIN exam_schedules es ON er.exam_schedule_id = es.id
    JOIN subjects sub ON es.subject_id = sub.id
    WHERE er.student_id = ? AND er.exam_schedule_id = ?
");
$stmt->execute([$student_id, $schedule_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Result not found. Please contact admin.");
}

$statusColor = ($result['result_status'] == 'Pass') ? 'success' : 'danger';
$percentage = intval($result['percentage']);
$skipped = $result['total_questions'] - $result['attempted_questions'];

// Calculate grade letter
$grade = 'F';
if ($percentage >= 90) $grade = 'A+';
elseif ($percentage >= 80) $grade = 'A';
elseif ($percentage >= 70) $grade = 'B';
elseif ($percentage >= 60) $grade = 'C';
elseif ($percentage >= 50) $grade = 'D';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Result - <?php echo htmlspecialchars($result['subject_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --bg-color: #f8fafc;
        }
        
        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: var(--bg-color); 
            color: #1e293b;
        }

        .result-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        }

        /* Header Section */
        .result-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 3rem 2rem 8rem;
            color: white;
            border-radius: 0 0 30px 30px;
            margin-bottom: -5rem;
            position: relative;
            z-index: 0;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        /* Hero Score Card */
        .score-hero-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 20px 50px -12px rgba(99, 102, 241, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .grade-badge {
            width: 80px;
            height: 80px;
            background: <?php echo ($result['result_status'] == 'Pass') ? '#ecfdf5' : '#fef2f2'; ?>;
            color: <?php echo ($result['result_status'] == 'Pass') ? '#059669' : '#dc2626'; ?>;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .percentage-display {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        /* Stats Grid */
        .stat-item {
            padding: 1.5rem;
            border-radius: 16px;
            background: white;
            border: 1px solid #f1f5f9;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        /* Charts Area */
        .chart-container {
            height: 300px;
            position: relative;
            width: 100%;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-enter {
            animation: fadeInUp 0.5s ease forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        /* Custom Confetti Canvas */
        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }
    </style>
</head>
<body class="pb-5">

<!-- Header Background -->
<div class="result-header">
    <div class="container-fluid result-container">
        <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 opacity-75">
                        <li class="breadcrumb-item text-white">Home</li>
                        <li class="breadcrumb-item text-white">Exams</li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Result</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($result['subject_name']); ?></h2>
                <div class="d-flex align-items-center gap-2 opacity-90">
                    <span class="badge bg-white bg-opacity-20 fw-normal">
                        <i class="far fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($result['exam_date'])); ?>
                    </span>
                    <span class="badge bg-white bg-opacity-20 fw-normal">
                        Unit <?php echo $result['unit_no']; ?>
                    </span>
                </div>
            </div>
            
            <a href="index.php" class="btn btn-light rounded-pill px-4 fw-semibold">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container-fluid result-container mt-4 px-4">
    <div class="row g-4">
        
        <!-- Left Column: Main Score & Quick Stats -->
        <div class="col-lg-4 animate-enter">
            <div class="score-hero-card mb-4" style="margin-top: -5rem;">
                <div class="grade-badge">
                    <?php echo $grade; ?>
                </div>
                <div class="text-uppercase tracking-wider text-muted small fw-bold mb-1">Total Score</div>
                <div class="percentage-display counter" data-target="<?php echo $percentage; ?>">0</div>
                <div class="text-muted mb-4 h5">%</div>
                
                <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill bg-<?php echo ($result['result_status'] == 'Pass') ? 'success' : 'danger'; ?> bg-opacity-10 text-<?php echo ($result['result_status'] == 'Pass') ? 'success' : 'danger'; ?> fw-bold mb-4">
                    <?php echo ($result['result_status'] == 'Pass') ? '<i class="fas fa-check-circle me-2"></i> Passed' : '<i class="fas fa-times-circle me-2"></i> Failed'; ?>
                </div>

                <div class="border-top pt-4">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h3 fw-bold mb-0 text-primary"><?php echo $result['score']; ?></div>
                            <div class="small text-muted">Obtained Marks</div>
                        </div>
                        <div class="col-6">
                            <div class="h3 fw-bold mb-0 text-secondary"><?php echo $result['total_marks']; ?></div>
                            <div class="small text-muted">Total Marks</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card p-4 animate-enter delay-100">
                <h5 class="fw-bold mb-4">Performance Summary</h5>
                <div class="d-flex flex-column gap-3">
                    <!-- Progress Bars -->
                    <div>
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Correct Answers</span>
                            <span class="text-success"><?php echo $result['correct_answers']; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($result['correct_answers'] / $result['total_questions']) * 100; ?>%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Wrong Answers</span>
                            <span class="text-danger"><?php echo $result['wrong_answers']; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($result['wrong_answers'] / $result['total_questions']) * 100; ?>%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Skipped Questions</span>
                            <span class="text-warning"><?php echo $skipped; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($skipped / $result['total_questions']) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Analysis & Charts -->
        <div class="col-lg-8">
            <!-- Stats Grid -->
            <div class="row g-4 mb-4 animate-enter delay-200">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-question"></i>
                            </div>
                            <div>
                                <div class="h4 fw-bold mb-0"><?php echo $result['total_questions']; ?></div>
                                <div class="small text-muted">Total Qns</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <div class="h4 fw-bold mb-0"><?php echo $result['correct_answers']; ?></div>
                                <div class="small text-muted">Correct</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-times"></i>
                            </div>
                            <div>
                                <div class="h4 fw-bold mb-0"><?php echo $result['wrong_answers']; ?></div>
                                <div class="small text-muted">Wrong</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-minus"></i>
                            </div>
                            <div>
                                <div class="h4 fw-bold mb-0"><?php echo $skipped; ?></div>
                                <div class="small text-muted">Skipped</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 animate-enter delay-300">
                <div class="col-md-8">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="fw-bold m-0">Question Distribution</h5>
                            <button class="btn btn-sm btn-light rounded-pill"><i class="fas fa-download me-1"></i> Report</button>
                        </div>
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-center text-center">
                         <h5 class="fw-bold mb-4">Accuracy Rate</h5>
                         <div style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                             <canvas id="accuracyChart"></canvas>
                             <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                 <div class="h3 fw-bold mb-0"><?php echo round(($result['correct_answers'] / $result['attempted_questions']) * 100); ?>%</div>
                                 <small class="text-muted">Accuracy</small>
                             </div>
                         </div>
                         <p class="text-muted small mt-4 mb-0">Based on attempted questions only.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($result['result_status'] == 'Pass'): ?>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    var duration = 3 * 1000;
    var animationEnd = Date.now() + duration;
    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
      return Math.random() * (max - min) + min;
    }

    var interval = setInterval(function() {
      var timeLeft = animationEnd - Date.now();

      if (timeLeft <= 0) {
        return clearInterval(interval);
      }

      var particleCount = 50 * (timeLeft / duration);
      // since particles fall down, start a bit higher than random
      confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
      confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
    }, 250);
</script>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 1000; 
        const increment = target / (duration / 16); 
        
        let current = 0;
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.innerText = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target;
            }
        };
        updateCounter();
    });

    // Charts
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Correct', 'Wrong', 'Skipped'],
            datasets: [{
                label: 'Questions',
                data: [<?php echo $result['correct_answers']; ?>, <?php echo $result['wrong_answers']; ?>, <?php echo $skipped; ?>],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            response: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    const accuracyCtx = document.getElementById('accuracyChart').getContext('2d');
    new Chart(accuracyCtx, {
        type: 'doughnut',
        data: {
            labels: ['Accuracy', 'Missed'],
            datasets: [{
                data: [<?php echo $result['correct_answers']; ?>, <?php echo $result['attempted_questions'] - $result['correct_answers']; ?>],
                backgroundColor: ['#6366f1', '#e2e8f0'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
</script>

</body>
</html>
