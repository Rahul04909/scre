<?php
session_start();
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../login.php");
//     exit;
// }

require_once '../../database/config.php';

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$center_id = isset($_GET['center_id']) ? $_GET['center_id'] : '';
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : '';
$unit_no = isset($_GET['unit_no']) ? $_GET['unit_no'] : '';

// Build Query
$where = "WHERE 1=1";
$params = [];

if ($course_id) {
    $where .= " AND s.course_id = :course_id";
    $params[':course_id'] = $course_id;
}
if ($center_id) {
    $where .= " AND s.center_id = :center_id";
    $params[':center_id'] = $center_id;
}
if ($session_id) {
    $where .= " AND s.session_id = :session_id";
    $params[':session_id'] = $session_id;
}

// Fetch Courses, Centers, Sessions for Dropdowns
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);
$centers = $pdo->query("SELECT * FROM centers ORDER BY center_name")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY session_name DESC")->fetchAll(PDO::FETCH_ASSOC);

// Count Total
$sqlCount = "SELECT COUNT(*) FROM students s $where";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total_records = $stmtCount->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch Students
$sql = "SELECT s.*, c.course_name, c.has_units, c.unit_type, cen.center_name 
        FROM students s 
        JOIN courses c ON s.course_id = c.id
        JOIN centers cen ON s.center_id = cen.id
        $where 
        ORDER BY s.id DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sidebarPrefix = '../../';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Marksheet - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../admin/assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <?php include '../../admin/sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 380px;">
            <div class="container-fluid py-4 px-4">
                <h2 class="fw-bold text-dark mb-4">Generate Marksheet</h2>
                
                <!-- Filter Section -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $course_id == $c['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['course_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Unit Logic: If Course has units, show unit dropdown -->
                            <?php 
                            $selectedCourse = null;
                            if($course_id) {
                                foreach($courses as $c) { if($c['id'] == $course_id) { $selectedCourse = $c; break; } }
                            }
                            if ($selectedCourse && $selectedCourse['has_units']): 
                            ?>
                            <div class="col-md-2">
                                <label class="form-label">Unit (<?= ucfirst($selectedCourse['unit_type']) ?>)</label>
                                <select name="unit_no" class="form-select">
                                    <option value="">Select Unit</option>
                                    <option value="1" <?= $unit_no == '1' ? 'selected' : '' ?>>1st <?= ucfirst($selectedCourse['unit_type']) ?></option>
                                    <option value="2" <?= $unit_no == '2' ? 'selected' : '' ?>>2nd <?= ucfirst($selectedCourse['unit_type']) ?></option>
                                    <!-- Add more if needed logic is generic -->
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="col-md-3">
                                <label class="form-label">Center</label>
                                <select name="center_id" class="form-select">
                                    <option value="">Select Center</option>
                                    <?php foreach ($centers as $center): ?>
                                        <option value="<?= $center['id'] ?>" <?= $center_id == $center['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($center['center_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Session</label>
                                <select name="session_id" class="form-select">
                                    <option value="">Select Session</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= $session_id == $s['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['session_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Sr. No.</th>
                                        <th>Student Name</th>
                                        <th>Enrollment No</th>
                                        <th>Father's Name</th>
                                        <th>Center</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php 
                                        $sr = $offset + 1;
                                        foreach ($students as $stu): 
                                        ?>
                                            <tr>
                                                <td class="ps-4"><?= $sr++ ?></td>
                                                <td class="fw-bold">
                                                    <?= htmlspecialchars($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($stu['enrollment_no']) ?></td>
                                                <td><?= htmlspecialchars($stu['father_name']) ?></td>
                                                <td><?= htmlspecialchars($stu['center_name']) ?></td>
                                                <td>
                                                    <a href="generate-marksheet.php?student_id=<?= $stu['id'] ?>&unit=<?= $unit_no ?>" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-file-pdf me-1"></i> Generate Marksheet
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No students found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-end">
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for($i=1; $i<=$total_pages; $i++): ?>
                                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&course_id=<?= $course_id ?>&center_id=<?= $center_id ?>&session_id=<?= $session_id ?>&unit_no=<?= $unit_no ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
