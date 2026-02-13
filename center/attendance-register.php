<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../database/config.php';

$center_id = $_SESSION['center_id'];

// filters
$filter_month = $_GET['month'] ?? date('m');
$filter_year = $_GET['year'] ?? date('Y');
$filter_course = $_GET['course_id'] ?? '';
$filter_session = $_GET['session_id'] ?? '';

// Fetch Meta Data
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_KEY_PAIR);
$sessions = $pdo->query("SELECT id, session_name FROM academic_sessions ORDER BY id DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

// Calculations for Matrix
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $filter_month, $filter_year);
$monthName = date('F', mktime(0, 0, 0, $filter_month, 10));

$students = [];
$attendance_data = [];

if ($filter_course) {
    try {
        // 1. Fetch Students
        $sql = "SELECT s.id, s.first_name, s.last_name, s.enrollment_no 
                FROM students s 
                WHERE s.center_id = ? AND s.course_id = ?";
        $params = [$center_id, $filter_course];
        
        if ($filter_session) {
            $sql .= " AND s.session_id = ?";
            $params[] = $filter_session;
        }
        $sql .= " ORDER BY s.first_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Attendance for whole month
        if (!empty($students)) {
            $student_ids = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
            
            // Start and End date of selected month
            $startDate = "$filter_year-$filter_month-01";
            $endDate = "$filter_year-$filter_month-$daysInMonth";
            
            $sqlAtt = "SELECT student_id, attendance_date, status 
                       FROM student_attendance 
                       WHERE attendance_date BETWEEN ? AND ? 
                       AND student_id IN ($placeholders)";
            
            $paramsAtt = array_merge([$startDate, $endDate], $student_ids);
            
            $stmtAtt = $pdo->prepare($sqlAtt);
            $stmtAtt->execute($paramsAtt);
            $raw_attendance = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

            // Reorganize: [student_id][day_number] = status
            foreach ($raw_attendance as $row) {
                $day = intval(date('d', strtotime($row['attendance_date'])));
                $attendance_data[$row['student_id']][$day] = $row['status'];
            }
        }

    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Register - PACE Center</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        .content-card { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        
        /* Register Table Styling */
        .register-container {
            overflow-x: auto;
        }
        .register-table {
            font-size: 0.85rem;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .register-table th, .register-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }
        .register-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        /* Sticky Names Column */
        .col-name {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 20;
            text-align: left !important;
            padding-left: 10px !important;
            border-right: 2px solid #e5e7eb !important;
            min-width: 180px;
        }
        .register-table th.col-name {
            background-color: #f9fafb;
            z-index: 30;
        }

        /* Status colors */
        .day-cell { min-width: 30px; }
        .status-p { color: #059669; background-color: #d1fae5; font-weight: bold; } /* Green */
        .status-a { color: #dc2626; background-color: #fee2e2; font-weight: bold; } /* Red */
        .status-l { color: #d97706; background-color: #fef3c7; font-weight: bold; } /* Yellow */
        .status-empty { color: #e5e7eb; }

        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include 'header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold" style="color: #115E59;">Attendance Register</h2>
                <a href="attendance.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Mark Attendance</a>
            </div>

            <!-- Filter -->
            <div class="content-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Month</label>
                        <select name="month" class="form-select form-select-sm">
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo ($filter_month == sprintf("%02d", $m)) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0,0,0,$m, 10)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Year</label>
                        <select name="year" class="form-select form-select-sm">
                            <?php for($y=date('Y'); $y>=2023; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($filter_year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Course</label>
                        <select name="course_id" class="form-select form-select-sm" required>
                            <option value="">Select Course</option>
                            <?php foreach($courses as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ($filter_course == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                         <label class="form-label small fw-bold">Session</label>
                         <select name="session_id" class="form-select form-select-sm">
                            <option value="">All Sessions</option>
                            <?php foreach($sessions as $sid => $sname): ?>
                                <option value="<?php echo $sid; ?>" <?php echo ($filter_session == $sid) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sname); ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100" style="background-color: #115E59; border-color: #115E59;">View</button>
                    </div>
                </form>
            </div>

            <?php if ($filter_course && !empty($students)): ?>
                <div class="content-card">
                    <h5 class="fw-bold mb-3 text-center"><?php echo $monthName . ' ' . $filter_year; ?></h5>
                    
                    <div class="register-container">
                        <table class="register-table">
                            <thead>
                                <tr>
                                    <th class="col-name">Student Name</th>
                                    <?php for($d=1; $d<=$daysInMonth; $d++): 
                                        $checkDate = "$filter_year-$filter_month-" . sprintf('%02d', $d);
                                        $dayName = date('D', strtotime($checkDate));
                                        $isWeekend = ($dayName == 'Sun');
                                    ?>
                                        <th class="day-cell <?php echo $isWeekend ? 'bg-light text-danger' : ''; ?>">
                                            <?php echo $d; ?><br>
                                            <span style="font-size: 0.65rem; font-weight:normal;"><?php echo substr($dayName, 0, 1); ?></span>
                                        </th>
                                    <?php endfor; ?>
                                    <th style="min-width: 60px;">Total<br>(P)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $student): 
                                    $presentCount = 0;
                                ?>
                                    <tr>
                                        <td class="col-name">
                                            <div class="fw-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($student['enrollment_no']); ?></small>
                                        </td>
                                        
                                        <?php for($d=1; $d<=$daysInMonth; $d++): 
                                            $status = $attendance_data[$student['id']][$d] ?? null;
                                            $class = 'status-empty';
                                            $symbol = '-';
                                            
                                            if ($status == 'Present') {
                                                $class = 'status-p';
                                                $symbol = 'P';
                                                $presentCount++;
                                            } elseif ($status == 'Absent') {
                                                $class = 'status-a';
                                                $symbol = 'A';
                                            } elseif ($status == 'Leave') {
                                                $class = 'status-l';
                                                $symbol = 'L';
                                            }
                                        ?>
                                            <td class="<?php echo $class; ?>"><?php echo $symbol; ?></td>
                                        <?php endfor; ?>

                                        <td class="fw-bold fs-6 text-primary"><?php echo $presentCount; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($filter_course): ?>
                <div class="alert alert-info text-center">No results found.</div>
            <?php endif; ?>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
