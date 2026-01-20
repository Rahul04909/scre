<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Fetch Courses including Unit details
$courses = $pdo->query("SELECT id, course_name, has_units, unit_type FROM courses ORDER BY course_name ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exam'])) {
    try {
        $course_id = intval($_POST['course_id']);
        $unit_no = isset($_POST['unit_no']) ? $_POST['unit_no'] : null;
        $subject_id = intval($_POST['subject_id']);
        $total_questions = intval($_POST['total_questions']);
        $marks_per_question = floatval($_POST['marks_per_question']);
        $total_marks = floatval($_POST['total_marks_hidden']); // Trusted from validation, but could verify

        // Generate Serial No QSP-XXXX
        $serial_no = 'QSP-' . rand(1000, 9999);
        // Simple check for uniqueness could be added here loop

        $pdo->beginTransaction();

        $stmtExam = $pdo->prepare("INSERT INTO exams (exam_serial_no, course_id, unit_no, subject_id, total_questions, marks_per_question, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtExam->execute([$serial_no, $course_id, $unit_no, $subject_id, $total_questions, $marks_per_question, $total_marks]);
        $exam_id = $pdo->lastInsertId();

        // Process Questions
        // questions[1][text], questions[1][options][A], questions[1][correct]
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            $stmtQ = $pdo->prepare("INSERT INTO exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['questions'] as $q) {
                $qText = $q['text'];
                $optA = $q['options']['A'];
                $optB = $q['options']['B'];
                $optC = $q['options']['C'];
                $optD = $q['options']['D'];
                $correct = $q['correct']; // A, B, C, or D

                $stmtQ->execute([$exam_id, $qText, $optA, $optB, $optC, $optD, $correct]);
            }
        }

        $pdo->commit();
        $message = "Exam Created Successfully! Serial No: <strong>$serial_no</strong>";
        $messageType = "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Exam Questions - Admin</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 10px; }
        .highlight-box { background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 15px; border-radius: 4px; }
        .question-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px; transition: all 0.2s; }
        .question-card:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .option-group .input-group-text { background-color: #f8f9fa; font-weight: bold; width: 60px; justify-content: center; align-items: center; }
        .correct-radio:checked + .form-control { border-color: #198754; background-color: #f0fff4; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <div id="page-content-wrapper" style="margin-left: 280px; flex-grow: 1;">
            <div class="container-fluid py-5 px-lg-5">
                
                <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-file-alt me-2"></i>Create Question Paper</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" id="examForm">
                    <!-- Step 1: Selection & Details -->
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">1. Select Subject</h5></div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Course</label>
                                        <select name="course_id" id="course_id" class="form-select select2" required>
                                            <option value="">-- Choose Course --</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" data-has-units="<?php echo $c['has_units']; ?>" data-unit-type="<?php echo $c['unit_type']; ?>">
                                                    <?php echo htmlspecialchars($c['course_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 d-none" id="unit_wrapper">
                                        <label class="form-label">Select <span id="unit_label">Unit</span></label>
                                        <select name="unit_no" id="unit_no" class="form-select select2">
                                            <option value="">-- Select Unit --</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Subject</label>
                                        <select name="subject_id" id="subject_id" class="form-select select2" required>
                                            <option value="">-- Select Course First --</option>
                                        </select>
                                    </div>

                                    <div id="subject_details" class="highlight-box mt-3 d-none">
                                        <h6 class="fw-bold mb-2 text-primary">Subject Details</h6>
                                        <div class="d-flex justify-content-between mb-1"><span>Theory Marks:</span> <span class="fw-bold" id="det_theory">0</span></div>
                                        <div class="d-flex justify-content-between mb-1"><span>Practical:</span> <span class="fw-bold" id="det_practical">0</span></div>
                                        <div class="d-flex justify-content-between mb-0"><span>Duration:</span> <span class="fw-bold" id="det_duration">0 min</span></div>
                                        <input type="hidden" name="expected_theory" id="expected_theory" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Configuration -->
                        <div class="col-lg-8 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">2. Exam Configuration</h5></div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Total Questions</label>
                                            <input type="number" name="total_questions" id="total_questions" class="form-control" min="1" placeholder="e.g. 50">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Marks per Question</label>
                                            <input type="number" step="0.5" name="marks_per_question" id="marks_per_question" class="form-control" min="0.5" placeholder="e.g. 2">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <button type="button" id="btnGenerate" class="btn btn-primary w-100"><i class="fas fa-magic"></i> Generate</button>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning d-none" id="calcError">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Only <strong><span id="calcTotal">0</span></strong> marks accounted for. Expected <strong><span id="expTotal">0</span></strong> marks matching Subject Theory.
                                    </div>
                                    <div class="alert alert-success d-none" id="calcSuccess">
                                        <i class="fas fa-check-circle me-2"></i> Configuration matches Theory Marks (<span id="matchTotal">0</span>).
                                    </div>
                                    <input type="hidden" name="total_marks_hidden" id="total_marks_hidden" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Questions List -->
                    <div id="questions_section" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold">Questions</h4>
                            <span class="text-muted small">Select the radio button for the correct answer.</span>
                        </div>
                        
                        <div id="questions_container">
                            <!-- Questions Injected Here -->
                        </div>

                        <div class="card">
                            <div class="card-body text-end">
                                <button type="submit" name="save_exam" class="btn btn-success btn-lg px-5"><i class="fas fa-save me-2"></i> Save Exam Paper</button>
                            </div>
                        </div>
                    </div>
                </form>

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
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

            const api = 'get-exam-data.php';

            // 1. Course Change
            $('#course_id').on('change', function() {
                let courseId = $(this).val();
                let $opt = $(this).find(':selected');
                let hasUnits = $opt.data('has-units');
                let unitType = $opt.data('unit-type') || 'Unit';

                // Reset
                $('#unit_no').empty().append('<option value="">-- Select Unit --</option>');
                $('#subject_id').empty().append('<option value="">Loading...</option>');
                $('#subject_details').addClass('d-none');
                $('#questions_section').addClass('d-none');

                if (courseId) {
                    if (hasUnits == 1) {
                        $('#unit_wrapper').removeClass('d-none');
                        $('#unit_label').text(unitType);
                        
                        // Fetch Units
                        $.get(api, { type: 'get_units', course_id: courseId }, function(data) {
                            if (data.length > 0) {
                                data.forEach(u => $('#unit_no').append(`<option value="${u}">${unitType} ${u}</option>`));
                            }
                        }, 'json');

                        // Wait for Unit Selection for Subjects
                        $('#subject_id').empty().append('<option value="">-- Select Unit First --</option>');
                    } else {
                        $('#unit_wrapper').addClass('d-none');
                        fetchSubjects(courseId, 0); // 0 = Direct
                    }
                }
            });

            // 2. Unit Change
            $('#unit_no').on('change', function() {
                let courseId = $('#course_id').val();
                let unitNo = $(this).val();
                if (courseId && unitNo) {
                    fetchSubjects(courseId, unitNo);
                }
            });

            function fetchSubjects(courseId, unitNo) {
                $.get(api, { type: 'get_subjects', course_id: courseId, unit_no: unitNo }, function(data) {
                    $('#subject_id').empty().append('<option value="">-- Select Subject --</option>');
                    if (data.length > 0) {
                        data.forEach(s => $('#subject_id').append(`<option value="${s.id}">${s.subject_name}</option>`));
                    } else {
                        $('#subject_id').append('<option value="">No Subjects Found</option>');
                    }
                }, 'json');
            }

            // 3. Subject Change -> Details
            $('#subject_id').on('change', function() {
                let subjectId = $(this).val();
                if (subjectId) {
                    $.get(api, { type: 'get_subject_details', subject_id: subjectId }, function(data) {
                        if (data) {
                            $('#det_theory').text(data.theory_marks);
                            $('#det_practical').text(data.practical_marks);
                            $('#det_duration').text(data.exam_duration + ' min');
                            
                            $('#expected_theory').val(data.theory_marks);

                            $('#subject_details').removeClass('d-none');
                            validateMath(); // Re-validate if inputs exist
                        }
                    }, 'json');
                } else {
                    $('#subject_details').addClass('d-none');
                }
            });

            // 4. Validate Math
            function validateMath() {
                let theory = parseFloat($('#expected_theory').val()) || 0;
                let questions = parseInt($('#total_questions').val()) || 0;
                let marks = parseFloat($('#marks_per_question').val()) || 0;
                let total = questions * marks;

                $('#calcTotal').text(total);
                $('#expTotal').text(theory);
                $('#matchTotal').text(total);
                $('#total_marks_hidden').val(total);

                if (questions > 0 && marks > 0) {
                    if (total === theory) {
                        $('#calcError').addClass('d-none');
                        $('#calcSuccess').removeClass('d-none');
                        return true;
                    } else {
                        $('#calcError').removeClass('d-none');
                        $('#calcSuccess').addClass('d-none');
                        return false;
                    }
                }
                return false;
            }

            $('#total_questions, #marks_per_question').on('input', validateMath);

            // 5. Generate Questions
            $('#btnGenerate').on('click', function() {
                if (!validateMath()) {
                    alert('Please ensure Total Questions * Marks per Question equals the Subject Theory Marks.');
                    return;
                }

                let count = parseInt($('#total_questions').val());
                let container = $('#questions_container');
                container.empty();

                for (let i = 1; i <= count; i++) {
                    let html = `
                        <div class="question-card p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="fw-bold">Question ${i}</h6>
                            </div>
                            <div class="mb-3">
                                <textarea name="questions[${i}][text]" class="form-control" rows="2" placeholder="Enter Question Text Here..." required></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group option-group">
                                        <div class="input-group-text">
                                            <input class="form-check-input mt-0 correct-radio" type="radio" name="questions[${i}][correct]" value="A" required title="Mark as Correct">
                                            <span class="ms-1">A</span>
                                        </div>
                                        <input type="text" name="questions[${i}][options][A]" class="form-control" placeholder="Option A" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group option-group">
                                        <div class="input-group-text">
                                            <input class="form-check-input mt-0 correct-radio" type="radio" name="questions[${i}][correct]" value="B" required title="Mark as Correct">
                                            <span class="ms-1">B</span>
                                        </div>
                                        <input type="text" name="questions[${i}][options][B]" class="form-control" placeholder="Option B" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group option-group">
                                        <div class="input-group-text">
                                            <input class="form-check-input mt-0 correct-radio" type="radio" name="questions[${i}][correct]" value="C" required title="Mark as Correct">
                                            <span class="ms-1">C</span>
                                        </div>
                                        <input type="text" name="questions[${i}][options][C]" class="form-control" placeholder="Option C" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group option-group">
                                        <div class="input-group-text">
                                            <input class="form-check-input mt-0 correct-radio" type="radio" name="questions[${i}][correct]" value="D" required title="Mark as Correct">
                                            <span class="ms-1">D</span>
                                        </div>
                                        <input type="text" name="questions[${i}][options][D]" class="form-control" placeholder="Option D" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(html);
                }

                $('#questions_section').removeClass('d-none');
                
                // Scroll to questions
                $('html, body').animate({
                    scrollTop: $("#questions_section").offset().top - 100
                }, 500);
            });
        });
    </script>
</body>
</html>
