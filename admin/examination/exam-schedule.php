<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Handle Add
if (isset($_POST['save_schedule'])) {
    $course_id = intval($_POST['course_id']);
    $session_id = intval($_POST['session_id']);
    $subject_id = intval($_POST['subject_id']);
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time']; 
    $exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : null;

    try {
        $sql = "INSERT INTO exam_schedules (course_id, session_id, subject_id, exam_id, exam_date, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$course_id, $session_id, $subject_id, $exam_id, $exam_date, $start_time, $end_time]);

        // Redirect to List with Success Message
        header("Location: index.php?msg=created");
        exit;
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Courses for Dropdown
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Schedule - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Custom CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 10px; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; }
        .highlight-info { background-color: #e0f2fe; border-left: 4px solid #0ea5e9; padding: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="margin-left: 280px; flex-grow: 1;">
            <div class="container-fluid py-5 px-lg-5">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary fw-bold"><i class="fas fa-calendar-plus me-2"></i>Create Exam Schedule</h2>
                    <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row justify-content-center">
                    <!-- Form Section -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Schedule Details</h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Select Course</label>
                                        <select name="course_id" id="course_id" class="form-select select2" required>
                                            <option value="">-- Choose Course --</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Select Session</label>
                                        <select name="session_id" id="session_id" class="form-select select2" required>
                                            <option value="">-- Select Course First --</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Select Subject</label>
                                        <select name="subject_id" id="subject_id" class="form-select select2" required>
                                            <option value="">-- Select Course First --</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Select Question Paper</label>
                                        <select name="exam_id" id="exam_id" class="form-select select2" required>
                                            <option value="">-- Select Subject First --</option>
                                        </select>
                                    </div>

                                    <!-- Subject Info Block -->
                                    <div id="subjectInfo" class="mb-3 highlight-info" style="display: none;">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small text-muted">Theory:</span>
                                            <span class="fw-bold" id="infoTheory">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small text-muted">Practical:</span>
                                            <span class="fw-bold" id="infoPractical">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-0">
                                            <span class="small text-muted">Duration:</span>
                                            <span class="fw-bold text-primary"><span id="infoDuration">0</span> Mins</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Exam Date</label>
                                        <input type="date" name="exam_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label">End Time</label>
                                            <input type="time" name="end_time" id="end_time" class="form-control" readonly required tabindex="-1" style="background-color: #f8f9fa;">
                                            <small class="text-muted" style="font-size: 0.75rem;">Auto-calculated</small>
                                        </div>
                                    </div>

                                    <button type="submit" name="save_schedule" class="btn btn-primary w-100"><i class="fas fa-check-circle me-2"></i> Save Schedule</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>

    <script>
        $(document).ready(function() {
            // Init Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            const api = 'get-exam-data.php';
            let currentDuration = 0;

            // Course Change -> Load Sessions & Subjects
            $('#course_id').on('change', function() {
                let courseId = $(this).val();
                
                // Reset
                $('#session_id').empty().append('<option value="">Loading...</option>');
                $('#subject_id').empty().append('<option value="">Loading...</option>');
                $('#exam_id').empty().append('<option value="">-- Select Subject First --</option>');
                $('#subjectInfo').slideUp();
                
                if (courseId) {
                    // Fetch Sessions
                    $.get(api, { type: 'get_sessions', course_id: courseId }, function(data) {
                        $('#session_id').empty().append('<option value="">-- Select Session --</option>');
                        if (data.length > 0) {
                            data.forEach(item => {
                                $('#session_id').append(`<option value="${item.id}">${item.session_name}</option>`);
                            });
                        } else {
                            $('#session_id').append('<option value="">No Active Sessions Found</option>');
                        }
                    });

                    // Fetch Subjects
                    $.get(api, { type: 'get_subjects', course_id: courseId }, function(data) {
                        $('#subject_id').empty().append('<option value="">-- Select Subject --</option>');
                        if (data.length > 0) {
                            data.forEach(item => {
                                $('#subject_id').append(`<option value="${item.id}">${item.subject_name}</option>`);
                            });
                        } else {
                            $('#subject_id').append('<option value="">No Subjects Found</option>');
                        }
                    });
                }
            });

            // Subject Change -> Load Details
            $('#subject_id').on('change', function() {
                let subjectId = $(this).val();
                if (subjectId) {
                    $.get(api, { type: 'get_subject_details', subject_id: subjectId }, function(data) {
                        if (data) {
                            $('#infoTheory').text(data.theory_marks);
                            $('#infoPractical').text(data.practical_marks);
                            $('#infoDuration').text(data.exam_duration);
                            currentDuration = parseInt(data.exam_duration) || 0;
                            
                            $('#subjectInfo').slideDown();
                            calculateEndTime(); // Recalculate if time is already there
                            
                            // Load Question Papers
                            $('#exam_id').empty().append('<option value="">Loading...</option>');
                            $.get(api, { type: 'get_exams_by_subject', subject_id: subjectId }, function(exams) {
                                $('#exam_id').empty().append('<option value="">-- Select Question Paper --</option>');
                                if (exams.length > 0) {
                                    exams.forEach(e => {
                                        $('#exam_id').append(`<option value="${e.id}">${e.exam_serial_no} (MM: ${e.total_marks})</option>`);
                                    });
                                } else {
                                    $('#exam_id').append('<option value="">No Question Papers Found</option>');
                                }
                            }, 'json');
                        }
                    });
                } else {
                    $('#subjectInfo').slideUp();
                    $('#exam_id').empty().append('<option value="">-- Select Subject First --</option>');
                    currentDuration = 0;
                }
            });

            // Start Time Change -> Calculate End Time
            $('#start_time').on('change', calculateEndTime);

            function calculateEndTime() {
                let startTime = $('#start_time').val();
                if (startTime && currentDuration > 0) {
                    // Create Dummy Date
                    let date = new Date(`2000-01-01T${startTime}`);
                    date.setMinutes(date.getMinutes() + currentDuration);
                    
                    // Format to HH:MM
                    let hours = String(date.getHours()).padStart(2, '0');
                    let minutes = String(date.getMinutes()).padStart(2, '0');
                    
                    $('#end_time').val(`${hours}:${minutes}`);
                }
            }
        });
    </script>
</body>
</html>
