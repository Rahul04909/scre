<?php
require_once '../../database/config.php';

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) &&  $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : '';
$center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : '';

// Base Query
$sql = "SELECT s.id, s.enrollment_no, s.first_name, s.last_name, s.father_name, s.mobile, s.student_image, 
               s.enrollment_date,
               c.course_name, c.course_fees, c.admission_fees, c.exam_fees, c.exam_fees_enabled,
               ctr.center_name, ctr.center_code,
               (SELECT COALESCE(SUM(amount), 0) FROM student_fees sf WHERE sf.student_id = s.id) as total_paid
        FROM students s
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN centers ctr ON s.center_id = ctr.id
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.enrollment_no LIKE ? OR s.father_name LIKE ? OR s.mobile LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}

if (!empty($course_id)) {
    $sql .= " AND s.course_id = ?";
    $params[] = $course_id;
}

if (!empty($center_id)) {
    $sql .= " AND s.center_id = ?";
    $params[] = $center_id;
}

// Count Total for Pagination
$countSql = str_replace("SELECT s.id, s.enrollment_no, s.first_name, s.last_name, s.father_name, s.mobile, s.student_image, 
               s.enrollment_date,
               c.course_name, c.course_fees, c.admission_fees, c.exam_fees, c.exam_fees_enabled,
               ctr.center_name, ctr.center_code,
               (SELECT COALESCE(SUM(amount), 0) FROM student_fees sf WHERE sf.student_id = s.id) as total_paid", "SELECT COUNT(*)", $sql);
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$total_records = $stmtCount->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Final Data Query
$sql .= " ORDER BY s.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Fetch Courses for Filter
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();
// Fetch Centers for Filter
$centers = $pdo->query("SELECT id, center_name, center_code FROM centers ORDER BY center_name ASC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .student-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .fee-badge { font-size: 0.8rem; font-weight: 600; padding: 5px 10px; border-radius: 20px; }
        .bg-paid { background-color: #d1fae5; color: #065f46; }
        .bg-pending { background-color: #fee2e2; color: #b91c1c; }
        .pagination .page-link { color: #115E59; }
        .pagination .page-item.active .page-link { background-color: #115E59; border-color: #115E59; color: white; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%; margin-left: 280px;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Manage Students</h2>
                    <!-- Optional: Add Student (Usually done by centers, but admin might need it too) -->
                    <!-- <a href="add-student.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add Student</a> -->
                </div>

                <!-- Filters -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search by Name, Roll No, Mobile..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="course_id" class="form-select">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo $course_id == $c['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="center_id" class="form-select">
                                    <option value="">All Centers</option>
                                    <?php foreach ($centers as $ctr): ?>
                                        <option value="<?php echo $ctr['id']; ?>" <?php echo $center_id == $ctr['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ctr['center_name'] . ' (' . $ctr['center_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i> Filter</button>
                            </div>
                            <div class="col-md-1">
                                <a href="index.php" class="btn btn-outline-secondary w-100" title="Reset"><i class="fas fa-redo"></i></a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Student List -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Sr. No.</th>
                                        <th>Student Details</th>
                                        <th>Course & Center</th>
                                        <th>Fees Status</th>
                                        <th>Registration Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students) > 0): ?>
                                        <?php 
                                        $sr = $offset + 1;
                                        foreach ($students as $s): 
                                            // Fee Calculation
                                            $courseFee = floatval($s['course_fees']);
                                            $admFee = floatval($s['admission_fees']);
                                            $examFee = $s['exam_fees_enabled'] ? floatval($s['exam_fees']) : 0;
                                            $totalFee = $courseFee + $admFee + $examFee;
                                            $paid = floatval($s['total_paid']);
                                            $pending = $totalFee - $paid;
                                            
                                            $isPaid = $pending <= 0;
                                            $statusText = $isPaid ? 'Completed' : 'Pending: ₹' . number_format($pending, 2);
                                            $badgeClass = $isPaid ? 'bg-paid' : 'bg-pending';
                                            
                                            // Image
                                            $img = !empty($s['student_image']) ? '../../'.$s['student_image'] : 'https://ui-avatars.com/api/?name='.$s['first_name'].'+'.$s['last_name'];
                                        ?>
                                            <tr>
                                                <td class="ps-4 text-muted"><?php echo str_pad($sr++, 2, '0', STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $img; ?>" class="student-img me-3">
                                                        <div>
                                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></div>
                                                            <div class="small text-muted"><i class="fas fa-id-card me-1"></i> <?php echo htmlspecialchars($s['enrollment_no']); ?></div>
                                                            <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($s['mobile']); ?></div>
                                                            <div class="small text-muted"><i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($s['father_name']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-primary"><?php echo htmlspecialchars($s['course_name']); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($s['center_name']); ?> (<?php echo htmlspecialchars($s['center_code']); ?>)</div>
                                                </td>
                                                <td>
                                                    <span class="fee-badge <?php echo $badgeClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                    <?php if (!$isPaid): ?>
                                                        <div class="small text-muted mt-1">Total: ₹<?php echo number_format($totalFee, 0); ?> | Paid: ₹<?php echo number_format($paid, 0); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="small text-muted"><?php echo date('d M, Y', strtotime($s['enrollment_date'])); ?></div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-user-graduate fa-3x mb-3 opacity-50"></i>
                                                <p>No students found matching your criteria.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&course_id=<?php echo $course_id; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&course_id=<?php echo $course_id; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&course_id=<?php echo $course_id; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
