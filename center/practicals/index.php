<?php
// Enable Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

// 1. Fetch Courses for Filter
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

// 2. Handle Filtering
$where = "WHERE p.center_id = :cid";
$params = [':cid' => $center_id];

if (!empty($_GET['course_id'])) {
    $where .= " AND p.course_id = :course_id";
    $params[':course_id'] = $_GET['course_id'];
}

if (!empty($_GET['session_id'])) {
    $where .= " AND p.session_id = :session_id";
    $params[':session_id'] = $_GET['session_id'];
}

if (!empty($_GET['unit_no'])) {
    $where .= " AND p.unit_no = :unit_no";
    $params[':unit_no'] = $_GET['unit_no'];
}

// 3. Fetch Practicals
$sql = "
    SELECT p.*, 
           c.course_name, c.course_code, c.has_units, c.unit_type,
           ac.session_name,
           s.subject_name
    FROM practicals p
    JOIN courses c ON p.course_id = c.id
    JOIN academic_sessions ac ON p.session_id = ac.id
    JOIN subjects s ON p.subject_id = s.id
    $where
    ORDER BY p.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$practicals = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Practicals - Center Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
        /* Custom Table Styling */
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="width: 100%;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                     <h4 class="fw-bold mb-0 text-dark">Practicals List</h4>
                     <a href="create-practical.php" class="btn btn-primary rounded-pill px-4">
                         <i class="fas fa-plus me-2"></i> Create New
                     </a>
                </div>
                
                <!-- Filter Section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 bg-light rounded-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Filter by Course</label>
                                <select name="course_id" id="course_id" class="form-select">
                                    <option value="">All Courses</option>
                                    <?php foreach($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php if(isset($_GET['course_id']) && $_GET['course_id'] == $c['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Session</label>
                                <select name="session_id" id="session_id" class="form-select">
                                    <option value="">All Sessions</option>
                                    <!-- Populated JS -->
                                </select>
                            </div>
                            <div class="col-md-3" id="unit_container" style="display:none;">
                                <label class="form-label fw-bold small" id="unit_label">Unit</label>
                                <select name="unit_no" id="unit_no" class="form-select">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter me-2"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Content -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3">Title / Subject</th>
                                        <th>Course Info</th>
                                        <th>Dates</th>
                                        <th>File</th>
                                        <th class="text-end pe-4">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($practicals) > 0): ?>
                                        <?php foreach($practicals as $p): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['title']); ?></div>
                                                    <small class="text-secondary"><?php echo htmlspecialchars($p['subject_name']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1 w-fit-content">
                                                            <?php echo htmlspecialchars($p['course_code']); ?>
                                                        </span>
                                                        <small class="text-muted"><?php echo htmlspecialchars($p['session_name']); ?></small>
                                                        <?php if($p['unit_no'] > 0): ?>
                                                            <small class="text-info fw-bold"><?php echo $p['unit_type'] . ' ' . $p['unit_no']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="d-block text-muted">Start: <span class="text-dark fw-bold"><?php echo date('d M Y', strtotime($p['submission_start_date'])); ?></span></small>
                                                    <small class="d-block text-muted">End: <span class="text-danger fw-bold"><?php echo date('d M Y', strtotime($p['submission_last_date'])); ?></span></small>
                                                </td>
                                                <td>
                                                    <a href="../../<?php echo htmlspecialchars($p['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-file-pdf me-1"></i> View
                                                    </a>
                                                </td>
                                                <td class="text-end pe-4 text-muted small">
                                                    <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" style="width: 150px; opacity: 0.5;">
                                                <p class="mt-3 mb-0">No practicals found.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sidebar.js"></script>
    
    <script>
    $(document).ready(function() {
        // Pre-fill Logic (for active filters)
        var activeCourse = "<?php echo isset($_GET['course_id']) ? $_GET['course_id'] : ''; ?>";
        var activeSession = "<?php echo isset($_GET['session_id']) ? $_GET['session_id'] : ''; ?>";
        var activeUnit = "<?php echo isset($_GET['unit_no']) ? $_GET['unit_no'] : ''; ?>";

        function loadSessionAndUnit(courseId) {
             $.post('get-practical-data.php', { action: 'get_sessions', course_id: courseId }, function(res) {
                 if (res.status == 'success') {
                     // Populate Sessions
                     var sessHtml = '<option value="">All Sessions</option>';
                     res.sessions.forEach(function(s) {
                         var sel = (s.id == activeSession) ? 'selected' : '';
                         sessHtml += `<option value="${s.id}" ${sel}>${s.session_name}</option>`;
                     });
                     $('#session_id').html(sessHtml);
                     
                     // Check Units
                     if (res.course.has_units == 1) {
                         var uType = res.course.unit_type;
                         var uCount = res.course.unit_count;
                         var uHtml = `<option value="">All ${uType}s</option>`;
                         for(var i=1; i<=uCount; i++) {
                             var sel = (i == activeUnit) ? 'selected' : '';
                             uHtml += `<option value="${i}" ${sel}>${uType} ${i}</option>`;
                         }
                         $('#unit_label').text(uType);
                         $('#unit_no').html(uHtml);
                         $('#unit_container').show();
                     } else {
                         $('#unit_container').hide();
                         $('#unit_no').html('<option value="">All</option>');
                     }
                 }
             }, 'json');
        }

        if(activeCourse) {
            loadSessionAndUnit(activeCourse);
        }

        $('#course_id').change(function() {
            var courseId = $(this).val();
            // Reset filters on manual change
            activeSession = ''; activeUnit = ''; 
            
            $('#session_id').html('<option value="">Loading...</option>');
            $('#unit_container').hide();
            
            if (courseId) {
                loadSessionAndUnit(courseId);
            } else {
                 $('#session_id').html('<option value="">All Sessions</option>');
            }
        });
    });
    </script>
</body>
</html>
