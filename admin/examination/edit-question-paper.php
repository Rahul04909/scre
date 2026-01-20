<?php
require_once '../../database/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage-question-paper.php");
    exit;
}

$exam_id = intval($_GET['id']);
$message = '';
$messageType = '';

// Fetch Exam Data
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Exam not found");
}

// Fetch Questions
$stmtQ = $pdo->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY id ASC");
$stmtQ->execute([$exam_id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

// Fetch Courses (for dropdown)
$courses = $pdo->query("SELECT id, course_name, has_units, unit_type FROM courses ORDER BY course_name ASC")->fetchAll();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exam'])) {
    try {
        $course_id = intval($_POST['course_id']);
        $unit_no = isset($_POST['unit_no']) ? $_POST['unit_no'] : null;
        $subject_id = intval($_POST['subject_id']);
        $total_questions = intval($_POST['total_questions']);
        $marks_per_question = floatval($_POST['marks_per_question']);
        $total_marks = floatval($_POST['total_marks_hidden']); 

        $pdo->beginTransaction();

        // Update Exam Record
        $stmtUpd = $pdo->prepare("UPDATE exams SET course_id=?, unit_no=?, subject_id=?, total_questions=?, marks_per_question=?, total_marks=? WHERE id=?");
        $stmtUpd->execute([$course_id, $unit_no, $subject_id, $total_questions, $marks_per_question, $total_marks, $exam_id]);

        // Replace Questions (Delete Old -> Insert New to avoid ID sync issues)
        $pdo->prepare("DELETE FROM exam_questions WHERE exam_id = ?")->execute([$exam_id]);

        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            $stmtQ = $pdo->prepare("INSERT INTO exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($_POST['questions'] as $q) {
                $stmtQ->execute([$exam_id, $q['text'], $q['options']['A'], $q['options']['B'], $q['options']['C'], $q['options']['D'], $q['correct']]);
            }
        }

        $pdo->commit();
        $message = "Exam Updated Successfully!";
        $messageType = "success";
        
        // Refresh Data
        header("Refresh:1"); 
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
    <title>Edit Question Paper - Admin</title>
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
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary fw-bold">Edit Question Paper (<?php echo htmlspecialchars($exam['exam_serial_no']); ?>)</h2>
                    <a href="manage-question-paper.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" id="examForm">
                    <!-- Step 1: Selection -->
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
                                                <option value="<?php echo $c['id']; ?>" 
                                                    data-has-units="<?php echo $c['has_units']; ?>" 
                                                    data-unit-type="<?php echo $c['unit_type']; ?>"
                                                    <?php echo ($c['id'] == $exam['course_id']) ? 'selected' : ''; ?>
                                                >
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
                                            <option value="">-- Loading --</option>
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
                                            <input type="number" name="total_questions" id="total_questions" class="form-control" min="1" value="<?php echo $exam['total_questions']; ?>">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Marks per Question</label>
                                            <input type="number" step="0.5" name="marks_per_question" id="marks_per_question" class="form-control" min="0.5" value="<?php echo $exam['marks_per_question']; ?>">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <button type="button" id="btnGenerate" class="btn btn-warning w-100"><i class="fas fa-sync"></i> Re-Gen</button>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning d-none" id="calcError">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Only <strong><span id="calcTotal">0</span></strong> marks. Expected <strong><span id="expTotal">0</span></strong>.
                                    </div>
                                    <div class="alert alert-success d-none" id="calcSuccess">
                                        <i class="fas fa-check-circle me-2"></i> Configuration Valid (<span id="matchTotal">0</span>).
                                    </div>
                                    <input type="hidden" name="total_marks_hidden" id="total_marks_hidden" value="<?php echo $exam['total_marks']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Questions List -->
                    <div id="questions_section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold">Questions</h4>
                            <span class="text-muted small">Update questions below.</span>
                        </div>
                        
                        <div id="questions_container">
                            <!-- Questions Injected by Backend + JS -->
                        </div>

                        <div class="card mt-4">
                            <div class="card-body text-end">
                                <button type="submit" name="update_exam" class="btn btn-success btn-lg px-5"><i class="fas fa-save me-2"></i> Update Exam</button>
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

            // Initial Data from Backend
            const initialUnit = "<?php echo $exam['unit_no']; ?>";
            const initialSubject = "<?php echo $exam['subject_id']; ?>";
            const dbQuestions = <?php echo json_encode($questions); ?>;
            const api = 'get-exam-data.php';

            // Function to Populate Questions
            function renderQuestions(qList) {
                let container = $('#questions_container');
                container.empty();
                
                if (!qList || qList.length === 0) return;

                qList.forEach((q, index) => {
                    let i = index + 1;
                    let html = `
                        <div class="question-card p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <h6 class="fw-bold">Question ${i}</h6>
                            </div>
                            <div class="mb-3">
                                <textarea name="questions[${i}][text]" class="form-control" rows="2" required>${q.question_text}</textarea>
                            </div>
                            <div class="row g-3">
                                ${['A','B','C','D'].map(opt => `
                                    <div class="col-md-6">
                                        <div class="input-group option-group">
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0 correct-radio" type="radio" name="questions[${i}][correct]" value="${opt}" ${q.correct_option === opt ? 'checked' : ''} required>
                                                <span class="ms-1">${opt}</span>
                                            </div>
                                            <input type="text" name="questions[${i}][options][${opt}]" class="form-control" value="${q['option_' + opt.toLowerCase()]}" required>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                    container.append(html);
                });
            }

            // Render existing questions on load
            renderQuestions(dbQuestions);

            // Trigger Load Logic for Dropdowns
            let $courseSelect = $('#course_id');
            let $opt = $courseSelect.find(':selected');
            let hasUnits = $opt.data('has-units');
            let unitType = $opt.data('unit-type') || 'Unit';

            if (hasUnits == 1) {
                $('#unit_wrapper').removeClass('d-none');
                $('#unit_label').text(unitType);
                
                // Fetch Units & Set Selected
                $.get(api, { type: 'get_units', course_id: $courseSelect.val() }, function(data) {
                    if (data.length > 0) {
                        data.forEach(u => {
                            let selected = (u == initialUnit) ? 'selected' : '';
                            $('#unit_no').append(`<option value="${u}" ${selected}>${unitType} ${u}</option>`);
                        });
                        // Fetch Subjects after Unit populated
                        fetchSubjects($courseSelect.val(), initialUnit);
                    }
                }, 'json');
            } else {
                fetchSubjects($courseSelect.val(), 0);
            }

            // Helper to fetch subjects and select current
            function fetchSubjects(courseId, unitNo) {
                $.get(api, { type: 'get_subjects', course_id: courseId, unit_no: unitNo }, function(data) {
                    $('#subject_id').empty().append('<option value="">-- Select Subject --</option>');
                    if (data.length > 0) {
                        data.forEach(s => {
                            let selected = (s.id == initialSubject) ? 'selected' : '';
                            $('#subject_id').append(`<option value="${s.id}" ${selected}>${s.subject_name}</option>`);
                        });
                        // Trigger Details
                        $('#subject_id').trigger('change');
                    }
                }, 'json');
            }

            // 1. Course Change
            $('#course_id').on('change', function() {
                // ... (Copy Logic from create-exam or keep simple reload expectation)
                // For Edit, users usually don't change Course drastically.
                // Keeping simpler logic:
                location.reload(); // Simplest way to avoid complex re-init logic mismatch
            });

            // 3. Subject Change -> Details (Duplicate logic from create-exam)
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
                            validateMath();
                        }
                    }, 'json');
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

            // 5. Re-Generate (Clear and New)
            $('#btnGenerate').on('click', function() {
                if(confirm("This will clear all current question text. Continue?")) {
                     if (!validateMath()) { alert('Math mismatch'); return; }
                     
                     let count = parseInt($('#total_questions').val());
                     let newQuestions = [];
                     for(let i=0; i<count; i++) {
                         newQuestions.push({
                             question_text: '', option_a: '', option_b: '', option_c: '', option_d: '', correct_option: ''
                         });
                     }
                     renderQuestions(newQuestions);
                }
            });
        });
    </script>
</body>
</html>
