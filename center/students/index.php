<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

// Fetch Students
$sql = "SELECT s.*, c.course_name, ses.session_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        LEFT JOIN academic_sessions ses ON s.session_id = ses.id
        WHERE s.center_id = ? 
        ORDER BY s.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$center_id]);
$students = $stmt->fetchAll();

// Fetch Courses for Filter
$stmtCourses = $pdo->prepare("SELECT c.id, c.course_name FROM courses c 
    JOIN center_course_allotment cca ON c.id = cca.course_id 
    WHERE cca.center_id = ? ORDER BY c.course_name ASC");
$stmtCourses->execute([$center_id]);
$courses = $stmtCourses->fetchAll();

// Fetch Sessions for Filter
$stmtSessions = $pdo->query("SELECT id, session_name FROM academic_sessions ORDER BY id DESC");
$sessions = $stmtSessions->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - PACE Center</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .student-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd; }
        .dataTables_wrapper .row { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%; margin-left: 280px;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Student Management</h2>
                    <a href="add-student.php" class="btn btn-warning fw-bold text-dark"><i class="fas fa-plus me-2"></i> New Admission</a>
                </div>

                <!-- Filters (moved via JS) -->
                <div id="filter-container" class="d-none">
                    <div class="d-flex align-items-center gap-2">
                        <select id="courseFilter" class="form-select form-select-sm" style="width: 200px;">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo htmlspecialchars($c['course_name']); ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="sessionFilter" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Sessions</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['session_name']); ?>"><?php echo htmlspecialchars($s['session_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enrollment No</th>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Session</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($s['enrollment_no']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php $img = !empty($s['student_image']) ? '../../'.$s['student_image'] : 'https://ui-avatars.com/api/?name='.$s['first_name']; ?>
                                                    <img src="<?php echo $img; ?>" class="student-img me-2">
                                                    <div>
                                                        <span class="d-block fw-bold"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></span>
                                                        <small class="text-muted"><?php echo htmlspecialchars($s['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['course_name']); ?></span></td>
                                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($s['session_name'] ?? 'N/A'); ?></span></td>
                                            <td><?php echo htmlspecialchars($s['mobile']); ?></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $s['status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="view-student.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $s['id']; ?>)" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#studentsTable').DataTable({
                "order": [[ 0, "desc" ]],
                "initComplete": function() {
                    // Move custom filters to the right of "Show entries"
                    var filterNode = $('#filter-container').removeClass('d-none').addClass('ms-3');
                    $('.dataTables_length').addClass('d-flex align-items-center').append(filterNode);
                }
            });

            // Custom Filtering Function
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var selectedCourse = $('#courseFilter').val();
                    var selectedSession = $('#sessionFilter').val();
                    
                    var courseColumn = data[2]; // Index 2 is Course
                    var sessionColumn = data[3]; // Index 3 is Session
                    
                    if (
                        (selectedCourse === "" || courseColumn.includes(selectedCourse)) &&
                        (selectedSession === "" || sessionColumn.includes(selectedSession))
                    ) {
                        return true;
                    }
                    return false;
                }
            );

            $('#courseFilter, #sessionFilter').on('change', function() {
                table.draw();
            });
            
            // Show Status Messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                const status = urlParams.get('status');
                const msg = urlParams.get('msg');
                if (status === 'deleted') {
                    Swal.fire('Deleted!', 'Student has been deleted successfully.', 'success');
                    window.history.replaceState(null, null, window.location.pathname);
                } else if (status === 'error') {
                    Swal.fire('Error!', msg || 'Something went wrong.', 'error');
                }
            }
        });

        function confirmDelete(studentId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! All student data including qualifications will be deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete-student.php?id=' + studentId;
                }
            })
        }
    </script>
</body>
</html>
