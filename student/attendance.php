<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$message = "";

// Handle Attendance Marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    try {
        // Check if already marked
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM student_attendance WHERE student_id = ? AND attendance_date = ?");
        $stmtCheck->execute([$student_id, $today]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO student_attendance (student_id, attendance_date, status) VALUES (?, ?, 'Present')");
            $stmtInsert->execute([$student_id, $today]);
            $message = "<div class='alert alert-success'>Attendance marked successfully for today!</div>";
        } else {
            $message = "<div class='alert alert-warning'>You have already marked attendance for today.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error marking attendance.</div>";
    }
}

// Fetch Attendance History
$stmtHistory = $pdo->prepare("SELECT attendance_date, status FROM student_attendance WHERE student_id = ? ORDER BY attendance_date DESC");
$stmtHistory->execute([$student_id]);
$attendance_records = $stmtHistory->fetchAll(PDO::FETCH_KEY_PAIR); // Date => Status

// Calendar Logic
$curMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$curYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$firstDay = mktime(0, 0, 0, $curMonth, 1, $curYear);
$numDays = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay); // 0 (Sun) - 6 (Sat)
$monthName = date('F', $firstDay);

$prevMonth = $curMonth - 1;
$prevYear = $curYear;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $curMonth + 1;
$nextYear = $curYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$isTodayMarked = isset($attendance_records[$today]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <style>
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar-table th {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        .calendar-table td {
            height: 80px;
            width: 14.28%;
            vertical-align: top;
            padding: 5px;
            border: 1px solid #eee;
        }
        .day-number {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        .attendance-badge {
            display: block;
            text-align: center;
            padding: 4px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-top: 5px;
        }
        .badge-present {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-absent {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-today {
            background-color: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <h2 class="mb-4">My Attendance</h2>
                <?php echo $message; ?>

                <div class="row g-4">
                    <!-- Mark Attendance Card -->
                    <div class="col-md-4">
                        <div class="content-card h-100 p-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-calendar-check fa-3x text-primary"></i>
                            </div>
                            <h5>Mark Today's Attendance</h5>
                            <p class="text-muted"><?php echo date('l, d F Y'); ?></p>
                            
                            <form method="POST">
                                <?php if ($isTodayMarked): ?>
                                    <button type="button" class="btn btn-success w-100" disabled>
                                        <i class="fas fa-check-circle me-2"></i>Marked Present
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="mark_attendance" class="btn btn-primary w-100">
                                        Mark Attendance
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <hr class="my-4">
                            <div class="d-flex justify-content-between text-start small">
                                <div>
                                    <span class="d-block text-muted">Total Present</span>
                                    <span class="fw-bold fs-5 text-success"><?php echo count($attendance_records); ?> Days</span>
                                </div>
                                <div>
                                    <span class="d-block text-muted">Current Month</span>
                                    <span class="fw-bold fs-5 text-primary">
                                        <?php 
                                            // Count present days in current month
                                            $currMonthPrefix = date('Y-m');
                                            $monthCount = 0;
                                            foreach($attendance_records as $date => $status) {
                                                if(strpos($date, $currMonthPrefix) === 0) $monthCount++;
                                            }
                                            echo $monthCount;
                                        ?> Days
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View -->
                    <div class="col-md-8">
                        <div class="content-card h-100">
                            <div class="card-header-clean d-flex justify-content-between align-items-center">
                                <h5 class="card-title-clean mb-0">Record</h5>
                                <div>
                                    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>
                                    <span class="fw-bold mx-2"><?php echo $monthName . ' ' . $curYear; ?></span>
                                    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-right"></i></a>
                                </div>
                            </div>
                            <div class="p-3">
                                <table class="calendar-table">
                                    <thead>
                                        <tr>
                                            <th>Sun</th>
                                            <th>Mon</th>
                                            <th>Tue</th>
                                            <th>Wed</th>
                                            <th>Thu</th>
                                            <th>Fri</th>
                                            <th>Sat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php
                                            // Empty slots before first day
                                            for ($i = 0; $i < $dayOfWeek; $i++) {
                                                echo "<td></td>";
                                            }

                                            for ($day = 1; $day <= $numDays; $day++) {
                                                $dateStr = sprintf('%04d-%02d-%02d', $curYear, $curMonth, $day);
                                                $isToday = ($dateStr == $today);
                                                $status = $attendance_records[$dateStr] ?? null;
                                                
                                                echo "<td class='" . ($isToday ? 'bg-light' : '') . "'>";
                                                echo "<span class='day-number " . ($isToday ? 'text-primary' : '') . "'>$day</span>";
                                                
                                                if ($status == 'Present') {
                                                    echo "<span class='attendance-badge badge-present'><i class='fas fa-check'></i> Present</span>";
                                                } elseif ($isToday && !$status) {
                                                     echo "<span class='attendance-badge badge-today'>Today</span>";
                                                }
                                                
                                                echo "</td>";

                                                $dayOfWeek++;
                                                if ($dayOfWeek == 7) {
                                                    echo "</tr><tr>";
                                                    $dayOfWeek = 0;
                                                }
                                            }

                                            // Empty slots after last day
                                            if ($dayOfWeek != 0) {
                                                while ($dayOfWeek < 7) {
                                                    echo "<td></td>";
                                                    $dayOfWeek++;
                                                }
                                            }
                                            ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
