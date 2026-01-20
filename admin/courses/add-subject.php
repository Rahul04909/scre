<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Fetch Courses
try {
    $stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (isset($_POST['add_subject'])) {
    $course_id = intval($_POST['course_id']);
    $unit_no = isset($_POST['unit_no']) ? intval($_POST['unit_no']) : 0;
    $subject_name = trim($_POST['subject_name']);
    
    $theory_marks = floatval($_POST['theory_marks']);
    $has_practical = isset($_POST['has_practical']) ? 1 : 0;
    $practical_marks = $has_practical ? floatval($_POST['practical_marks']) : 0.00;
    
    $total_marks = $theory_marks + $practical_marks;
    $passing_marks = floatval($_POST['passing_marks']);
    $exam_duration = intval($_POST['exam_duration']);

    try {
        $sql = "INSERT INTO subjects (course_id, unit_no, subject_name, theory_marks, has_practical, practical_marks, passing_marks, total_marks, exam_duration) 
                VALUES (:cid, :uno, :name, :tm, :hp, :pm, :passm, :totm, :dur)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cid' => $course_id, ':uno' => $unit_no, ':name' => $subject_name,
            ':tm' => $theory_marks, ':hp' => $has_practical, ':pm' => $practical_marks,
            ':passm' => $passing_marks, ':totm' => $total_marks, ':dur' => $exam_duration
        ]);
        $message = "Subject added successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Subject - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .info-box { background: #e9ecef; border-radius: 5px; padding: 10px; margin-bottom: 15px; font-size: 0.9rem; }
        .info-label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add New Subject</h2>
                    <a href="manage-subjects.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>
                <?php if ($message): ?><div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3 text-primary">Course Selection</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Select Course</label>
                                        <select name="course_id" id="courseSelect" class="form-select" required>
                                            <option value="">-- Select Course --</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Dynamic Course Info -->
                                    <div id="courseInfo" class="d-none">
                                        <div class="info-box">
                                            <div class="row">
                                                <div class="col-6"><span class="info-label">Type:</span> <span id="cType">-</span></div>
                                                <div class="col-6"><span class="info-label">Duration:</span> <span id="cDuration">-</span></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Dynamic Unit Dropdown -->
                                        <div class="mb-3 d-none" id="unitWrapper">
                                            <label class="form-label">Select Unit (<span id="unitTypeName">Semester</span>)</label>
                                            <select name="unit_no" id="unitSelect" class="form-select">
                                                <!-- Options populated via JS -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="mb-3 text-success">Subject Details</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Subject Name</label>
                                        <input type="text" name="subject_name" class="form-control" required>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Exam Duration (Minutes)</label>
                                            <input type="number" name="exam_duration" class="form-control" placeholder="e.g. 180" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Passing Marks</label>
                                            <input type="number" step="0.5" name="passing_marks" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <h5 class="mb-3 text-info mt-4">Marks Distribution</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Theory Marks</label>
                                        <input type="number" step="0.5" name="theory_marks" id="theoryMarks" class="form-control" value="0" required>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="practicalToggle" name="has_practical">
                                        <label class="form-check-label" for="practicalToggle">Does this subject have practicals?</label>
                                    </div>
                                    
                                    <div class="mb-3 d-none" id="practicalInputWrapper">
                                        <label class="form-label">Practical Marks</label>
                                        <input type="number" step="0.5" name="practical_marks" id="practicalMarks" class="form-control" value="0">
                                    </div>
                                    
                                    <div class="alert alert-info py-2">
                                        <strong>Total Marks:</strong> <span id="totalMarksDisplay">0</span>
                                    </div>
                                    
                                    <button type="submit" name="add_subject" class="btn btn-primary w-100 btn-lg mt-3"><i class="fas fa-save me-2"></i> Save Subject</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            // Course Change Handler
            $('#courseSelect').change(function() {
                var courseId = $(this).val();
                if(courseId) {
                    $.ajax({
                        url: 'get-course-details.php',
                        type: 'GET',
                        data: { course_id: courseId },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                var data = response.data;
                                $('#courseInfo').removeClass('d-none');
                                $('#cType').text(data.course_type.replace('_', ' ').toUpperCase());
                                $('#cDuration').text(data.duration_value + ' ' + data.duration_type.toUpperCase());
                                
                                if(data.has_units == 1) {
                                    $('#unitWrapper').removeClass('d-none');
                                    $('#unitTypeName').text(data.unit_type.charAt(0).toUpperCase() + data.unit_type.slice(1));
                                    var options = '<option value="">Select ' + data.unit_type + '</option>';
                                    for(var i=1; i<=data.unit_count; i++) {
                                        options += '<option value="'+i+'">' + data.unit_type.charAt(0).toUpperCase() + data.unit_type.slice(1) + ' ' + i + '</option>';
                                    }
                                    $('#unitSelect').html(options).attr('required', true);
                                } else {
                                    $('#unitWrapper').addClass('d-none');
                                    $('#unitSelect').html('').attr('required', false);
                                }
                            }
                        }
                    });
                } else {
                    $('#courseInfo').addClass('d-none');
                }
            });

            // Marks Calculation
            function calculateTotal() {
                var theory = parseFloat($('#theoryMarks').val()) || 0;
                var practical = $('#practicalToggle').is(':checked') ? (parseFloat($('#practicalMarks').val()) || 0) : 0;
                $('#totalMarksDisplay').text(theory + practical);
            }

            $('#theoryMarks, #practicalMarks').on('input', calculateTotal);

            $('#practicalToggle').change(function() {
                if(this.checked) {
                    $('#practicalInputWrapper').removeClass('d-none');
                } else {
                    $('#practicalInputWrapper').addClass('d-none');
                    $('#practicalMarks').val(0); // Reset if unchecked
                }
                calculateTotal();
            });
        });
    </script>
</body>
</html>
