<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$message = '';
$messageType = '';

// Fetch all courses for dropdown
// Assuming centers can see all courses or just assigned ones?
// Usually centers are "Allotted" courses. Checking `center_course_allotment` ?
// For now, let's fetch ALL active courses as per usual flow, or check allotment if simple.
// Let's stick to ALL active courses as user didn't specify strict allotment check here.
$courses = $pdo->query("SELECT id, course_name FROM courses WHERE status = 'Active' ORDER BY course_name ASC")->fetchAll();


// Handle Form Submission
if (isset($_POST['save_practical'])) {
    $course_id = intval($_POST['course_id']);
    $session_id = intval($_POST['session_id']);
    $unit_no = isset($_POST['unit_no']) ? intval($_POST['unit_no']) : 0;
    $subject_id = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $start_date = $_POST['submission_start_date'];
    $last_date = $_POST['submission_last_date'];
    
    // File Upload
    $file_path = '';
    if (isset($_FILES['practical_file']) && $_FILES['practical_file']['error'] == 0) {
        $uploadDir = '../../assets/uploads/practicals/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['practical_file']['name'], PATHINFO_EXTENSION);
        $fileName = 'practical_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['practical_file']['tmp_name'], $targetPath)) {
            $file_path = 'assets/uploads/practicals/' . $fileName;
        }
    }
    
    if ($file_path) {
        try {
            $stmt = $pdo->prepare("INSERT INTO practicals (center_id, course_id, session_id, unit_no, subject_id, title, submission_start_date, submission_last_date, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$center_id, $course_id, $session_id, $unit_no, $subject_id, $title, $start_date, $last_date, $file_path]);
            $message = "Practical Created Successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "File Upload Failed!";
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Practical - Center Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                <div class="row items-center mb-4">
                    <div class="col">
                         <h4 class="fw-bold mb-0 text-dark">Create New Practical</h4>
                    </div>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            
                            <!-- 1. Course & Session -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-select" required>
                                        <option value="">-- Choose Course --</option>
                                        <?php foreach($courses as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id" class="form-select" required disabled>
                                        <option value="">-- Select Course First --</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- 2. Unit & Subject -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4" id="unit_container" style="display:none;">
                                    <label class="form-label fw-bold" id="unit_label">Unit/Semester</label>
                                    <select name="unit_no" id="unit_no" class="form-select">
                                        <!-- Populated via JS -->
                                    </select>
                                </div>
                                
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                                    <select name="subject_id" id="subject_id" class="form-select text-uppercase" required disabled>
                                        <option value="">-- Select Subject --</option>
                                    </select>
                                    <small id="marks_display" class="text-success fw-bold"></small>
                                </div>
                            </div>
                            
                            <!-- 3. Details -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Practical Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Ex: Physics Lab Experiment 1" required>
                            </div>
                            
                            <!-- 4. Dates -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Submission Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="submission_start_date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Last Date of Submission <span class="text-danger">*</span></label>
                                    <input type="date" name="submission_last_date" class="form-control" required>
                                </div>
                            </div>
                            
                            <!-- 5. File -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Upload Practical File (PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="practical_file" class="form-control" accept="application/pdf" required>
                                <div class="form-text">Upload specific instructions or question paper for the practical.</div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="save_practical" class="btn btn-primary px-5 rounded-pill">
                                    <i class="fas fa-save me-2"></i> Save Practical
                                </button>
                            </div>
                            
                        </form>
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
        // Course Change -> Get Sessions & Check Units
        $('#course_id').change(function() {
            var courseId = $(this).val();
            $('#session_id').html('<option value="">Loading...</option>').prop('disabled', true);
            $('#unit_container').hide();
            $('#subject_id').html('<option value="">-- Select Subject --</option>').prop('disabled', true);
            $('#marks_display').text('');
            
            if (courseId) {
                $.post('get-practical-data.php', { action: 'get_sessions', course_id: courseId }, function(res) {
                    if (res.status == 'success') {
                        // Populate Sessions
                        var sessHtml = '<option value="">-- Select Session --</option>';
                        res.sessions.forEach(function(s) {
                            sessHtml += `<option value="${s.id}">${s.session_name}</option>`;
                        });
                        $('#session_id').html(sessHtml).prop('disabled', false);
                        
                        // Check Units
                        if (res.course.has_units == 1) {
                            var uType = res.course.unit_type; // e.g. Semester
                            var uCount = res.course.unit_count;
                            var uHtml = `<option value="">-- Select ${uType} --</option>`;
                            for(var i=1; i<=uCount; i++) {
                                uHtml += `<option value="${i}">${uType} ${i}</option>`;
                            }
                            $('#unit_label').text('Select ' + uType);
                            $('#unit_no').html(uHtml);
                            $('#unit_container').show();
                        } else {
                            // No Units, directly enable subject fetch context (wait for session though?)
                            // Actually subject depends on Course + Unit. 
                            loadSubjects(courseId, 0); 
                        }
                    }
                }, 'json');
            }
        });
        
        // Unit Change -> Load Subjects
        $('#unit_no').change(function() {
            var unit = $(this).val();
            var course = $('#course_id').val();
            if (unit && course) {
                loadSubjects(course, unit);
            }
        });
        
        function loadSubjects(courseId, unitNo) {
             $('#subject_id').html('<option value="">Loading...</option>').prop('disabled', true);
             $('#marks_display').text('');
             
             $.post('get-practical-data.php', { 
                 action: 'get_subjects', 
                 course_id: courseId, 
                 unit_no: unitNo 
             }, function(res) {
                 if (res.status == 'success') {
                     var subHtml = '<option value="">-- Select Subject --</option>';
                     res.subjects.forEach(function(sub) {
                         subHtml += `<option value="${sub.id}" data-marks="${sub.practical_marks}">${sub.subject_name}</option>`;
                     });
                     $('#subject_id').html(subHtml).prop('disabled', false);
                 }
             }, 'json');
        }
        
        // Subject Change -> Show Marks
        $('#subject_id').change(function() {
            var marks = $(this).find(':selected').data('marks');
            if (marks) {
                $('#marks_display').text('Total Practical Marks: ' + marks);
            } else {
                $('#marks_display').text('');
            }
        });
    });
    </script>
</body>
</html>
