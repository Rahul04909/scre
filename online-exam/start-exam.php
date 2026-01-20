<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once '../database/config.php';

// 1. Auth Check
if (!isset($_SESSION['online_exam_student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['online_exam_student_id'];
$schedule_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

if ($schedule_id == 0) {
    die("Invalid Exam ID.");
}

// 2. Fetch Exam Schedule & Question Paper Info
$stmt = $pdo->prepare("
    SELECT es.*, 
           sub.subject_name, sub.unit_no, sub.exam_duration,
           e.total_questions, e.marks_per_question, e.total_marks as exam_total_marks,
           s.first_name, s.last_name, s.enrollment_no, s.student_image
    FROM exam_schedules es
    JOIN subjects sub ON es.subject_id = sub.id
    JOIN students s ON s.id = ?
    JOIN exams e ON es.exam_id = e.id
    WHERE es.id = ? AND s.course_id = es.course_id AND s.session_id = es.session_id
");
$stmt->execute([$student_id, $schedule_id]);
$examData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$examData) {
    die("Exam not found or you are not authorized.");
}

// 3. Check Timing
$current_time = time();
$start_time = strtotime($examData['exam_date'] . ' ' . $examData['start_time']);
$end_time = $start_time + ($examData['exam_duration'] * 60);

// Allow 5 minutes buffer before start (waiting room) but redundant here as button logic handles it
if ($current_time < $start_time) {
    die("Exam has not started yet.");
}
if ($current_time > $end_time) {
    die("Exam has ended.");
}

// 4. Check if already submitted
$stmtCheck = $pdo->prepare("SELECT id FROM exam_results WHERE student_id = ? AND exam_schedule_id = ?");
$stmtCheck->execute([$student_id, $schedule_id]);
if ($stmtCheck->fetch()) {
    header("Location: result.php?schedule_id=" . $schedule_id);
    exit;
}

// 5. Fetch Questions
$stmtQ = $pdo->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM exam_questions WHERE exam_id = ? ORDER BY id ASC");
$stmtQ->execute([$examData['exam_id']]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

if (empty($questions)) {
    die("Active Question Paper has no questions. Please contact admin.");
}

// Prepare JSON for JS
$jsQuestions = json_encode($questions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Window - <?php echo htmlspecialchars($examData['subject_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; overflow: hidden; height: 100vh; user-select: none; }
        
        #exam-container { display: flex; height: 100vh; }
        
        /* Main Content */
        #main-area { flex: 1; display: flex; flex-direction: column; height: 100%; position: relative; }
        #header-bar { height: 60px; background: #fff; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
        #question-area { flex: 1; padding: 30px; overflow-y: auto; background: #fff; }
        #footer-bar { height: 70px; background: #f8fafc; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; }
        
        /* Sidebar */
        #sidebar { width: 320px; background: #fff; border-left: 1px solid #e5e7eb; display: flex; flex-direction: column; height: 100%; }
        #student-profile { padding: 15px; background: #f0fdfa; border-bottom: 1px solid #ccfbf1; display: flex; align-items: center; gap: 10px; }
        #student-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #115E59; }
        
        #timer-box { padding: 15px; background: #fff; border-bottom: 1px solid #e5e7eb; text-align: center; }
        .timer-display { font-size: 1.8rem; font-weight: 800; color: #115E59; font-family: monospace; }
        
        #palette-area { flex: 1; padding: 15px; overflow-y: auto; }
        .palette-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
        .q-btn {
            width: 40px; height: 40px; border-radius: 50%; border: 1px solid #cbd5e1; background: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer;
            transition: all 0.2s;
        }
        .q-btn:hover { background: #f1f5f9; }
        .q-btn.active { border-color: #0ea5e9; background: #e0f2fe; color: #0369a1; }
        .q-btn.answered { background: #10B981; color: #fff; border-color: #059669; }
        .q-btn.visited { background: #E11D48; color: #fff; border-color: #be123c; } /* Visited but not answered */
        .q-btn.marked { background: #8b5cf6; color: #fff; border-color: #7c3aed; }
        
        #submit-area { padding: 15px; border-top: 1px solid #e5e7eb; background: #fff; }
        
        /* Question Styling */
        .q-text { font-size: 1.15rem; font-weight: 600; margin-bottom: 25px; line-height: 1.6; }
        .option-label {
            display: flex; align-items: center; padding: 15px; border: 2px solid #e5e7eb; border-radius: 10px;
            margin-bottom: 12px; cursor: pointer; transition: all 0.2s;
        }
        .option-label:hover { border-color: #cbd5e1; background: #f8fafc; }
        .option-input:checked + .option-content { font-weight: 600; }
        .option-label.selected { border-color: #115E59; background: #f0fdfa; }
        .option-num { width: 30px; height: 30px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 700; color: #64748b; }
        .option-label.selected .option-num { background: #115E59; color: #fff; }
        
        /* Legend */
        .legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.8rem; margin-bottom: 5px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        
    </style>
</head>
<body oncontextmenu="return false;">

<div id="exam-container">
    
    <!-- MAIN QUESTION AREA -->
    <div id="main-area">
        <div id="header-bar">
            <h5 class="m-0 fw-bold text-primary"><?php echo htmlspecialchars($examData['subject_name']); ?></h5>
            <div class="text-secondary small">Total Questions: <?php echo count($questions); ?></div>
        </div>
        
        <div id="question-area">
            <div id="q-content">
                <!-- JS will inject question here -->
            </div>
        </div>
        
        <div id="footer-bar">
            <div>
                <button class="btn btn-outline-secondary" onclick="prevQ()" id="btn-prev"><i class="fas fa-chevron-left me-1"></i> Previous</button>
                <button class="btn btn-warning ms-2" onclick="clearResp()" id="btn-clear">Clear Response</button>
            </div>
            <button class="btn btn-success px-4" onclick="saveAndNext()" id="btn-next">Save & Next <i class="fas fa-chevron-right ms-1"></i></button>
        </div>
    </div>
    
    <!-- RIGHT SIDEBAR -->
    <div id="sidebar">
        <!-- Student Info -->
        <div id="student-profile">
            <?php $img = !empty($examData['student_image']) ? '../'.$examData['student_image'] : 'https://ui-avatars.com/api/?name='.$examData['first_name']; ?>
            <img src="<?php echo $img; ?>" id="student-img">
            <div style="line-height: 1.2;">
                <div class="fw-bold small"><?php echo htmlspecialchars($examData['first_name']); ?></div>
                <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($examData['enrollment_no']); ?></div>
            </div>
        </div>
        
        <!-- Timer -->
        <div id="timer-box">
            <div class="small text-muted text-uppercase fw-bold mb-1">Time Remaining</div>
            <div class="timer-display" id="timer">--:--:--</div>
        </div>
        
        <div class="px-3 py-2 border-bottom bg-light">
            <div class="row g-2">
                <div class="col-6 legend-item"><div class="dot bg-success"></div> Answered</div>
                <div class="col-6 legend-item"><div class="dot bg-danger"></div> Not Answered</div>
                <div class="col-6 legend-item"><div class="dot bg-white border"></div> Not Visited</div>
                <!-- Marked for review could be added later -->
            </div>
        </div>
        
        <!-- Palette -->
        <div id="palette-area">
            <div class="fw-bold mb-3 small text-secondary">Question Palette:</div>
            <div class="palette-grid" id="palette-grid">
                <!-- JS will inject buttons -->
            </div>
        </div>
        
        <!-- Submit -->
        <div id="submit-area">
            <button class="btn btn-primary w-100 py-2 fw-bold" onclick="confirmSubmit()">Submit Exam</button>
        </div>
    </div>

</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Exam?</h5>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit?</p>
                <table class="table table-sm table-bordered">
                     <tr><td>Total Questions</td><td id="sum-total">0</td></tr>
                     <tr><td>Answered</td><td id="sum-answered" class="text-success fw-bold">0</td></tr>
                     <tr><td>Not Answered</td><td id="sum-not-answered" class="text-danger fw-bold">0</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Resume</button>
                <button type="button" class="btn btn-primary" onclick="finalSubmit()">Yes, Submit</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Data
    const questions = <?php echo $jsQuestions; ?>;
    const totalQ = questions.length;
    let currentQIndex = 0;
    
    // State: 0=Not Visited, 1=Answered, 2=Not Answered(Visited but skipped)
    let answers = {}; // { qId: 'A' }
    let visited = new Set();
    
    // Timer
    const endTime = <?php echo $end_time; ?> * 1000;
    
    function init() {
        renderPalette();
        loadQuestion(0);
        setInterval(updateTimer, 1000);
        updateTimer();
        
        // Prevent accidental closing
        window.onbeforeunload = function() {
            return "Exam is in progress. Are you sure you want to leave?";
        };
    }
    
    function updateTimer() {
        const now = Date.now() + <?php echo (time() * 1000) - (round(microtime(true) * 1000)); ?>; // Sync logic approx
        // Note: For simplicity using client time but ideally sync offset passed from PHP
        // Let's rely on standard logic:
        // We need offset.
        // PHP passed $end_time (server time). We need to count down to it.
        // We will calc locally.
    }
    
    // Better Timer logic
    // We already have endTime in JS which is PHP timestamp * 1000.
    // We need to compare "now" vs endTime.
    // Ideally we sync "now" with server.
    // Let's assume user system time is correct or we calculate offset.
    // simpler: pass remainingSeconds from PHP
    let remainingSeconds = <?php echo $end_time - time(); ?>;
    
    function updateTimer() {
        if(remainingSeconds <= 0) {
            document.getElementById('timer').innerText = "00:00:00";
            document.getElementById('timer').style.color = "red";
            autoSubmit();
            return;
        }
        
        remainingSeconds--;
        
        let h = Math.floor(remainingSeconds / 3600);
        let m = Math.floor((remainingSeconds % 3600) / 60);
        let s = remainingSeconds % 60;
        
        let timeStr = 
            (h < 10 ? "0" + h : h) + ":" +
            (m < 10 ? "0" + m : m) + ":" +
            (s < 10 ? "0" + s : s);
            
        document.getElementById('timer').innerText = timeStr;
    }
    
    function renderPalette() {
        const grid = document.getElementById('palette-grid');
        grid.innerHTML = '';
        questions.forEach((q, idx) => {
            let btn = document.createElement('div');
            btn.className = 'q-btn';
            btn.innerText = idx + 1;
            btn.dataset.idx = idx;
            btn.onclick = () => loadQuestion(idx);
            
            // Classes
            if (idx === currentQIndex) {
                 // Active style is handled in updatePalette mainly
            }
            
            // Status Logic
            if (answers[q.id]) {
                btn.classList.add('answered');
            } else if (visited.has(q.id) && idx !== currentQIndex) {
                btn.classList.add('visited');
            }
            
            if(idx === currentQIndex) btn.classList.add('active'); // Current gets blue border
            
            grid.appendChild(btn);
        });
    }
    
    function loadQuestion(idx) {
        // Mark previous as visited
        if (questions[currentQIndex]) visited.add(questions[currentQIndex].id);
        
        currentQIndex = idx;
        const q = questions[idx];
        const container = document.getElementById('q-content');
        
        // Check if previously answered
        const selVal = answers[q.id] || '';
        
        let html = `
            <div class="q-text">
                <span class="badge bg-light text-dark border me-2">Q.${idx+1}</span>
                ${q.question_text.replace(/\\n/g, '<br>')}
            </div>
            <div class="options-container">
        `;
        
        ['A', 'B', 'C', 'D'].forEach(opt => {
            // Mapping db columns option_a, option_b...
            const optText = q['option_' + opt.toLowerCase()];
            const isSel = selVal === opt;
            const selClass = isSel ? 'selected' : '';
            const checked = isSel ? 'checked' : '';
            
            html += `
                <label class="option-label ${selClass}" onclick="selectOption('${opt}')">
                    <div class="option-num">${opt}</div>
                    <input type="radio" name="opt" class="option-input d-none" value="${opt}" ${checked}>
                    <div class="option-content">${optText}</div>
                </label>
            `;
        });
        
        html += `</div>`;
        container.innerHTML = html;
        
        // Buttons
        document.getElementById('btn-prev').disabled = (idx === 0);
        document.getElementById('btn-next').innerText = (idx === totalQ - 1) ? 'Save & Submit' : 'Save & Next';
        
        renderPalette();
    }
    
    function selectOption(opt) {
        // Just UI update, save happens on Next/Save
        document.querySelectorAll('.option-label').forEach(el => el.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        
        // Optionally auto-save selection to state immediately
        const qId = questions[currentQIndex].id;
        answers[qId] = opt;
        renderPalette(); // Update green color immediately
    }
    
    function clearResp() {
        const qId = questions[currentQIndex].id;
        delete answers[qId];
        loadQuestion(currentQIndex); // Reload to clear UI
    }
    
    function saveAndNext() {
        // Already saved in selectOption, just move.
        // If user didn't select anything, it stays unanswered.
        
        if(currentQIndex < totalQ - 1) {
            loadQuestion(currentQIndex + 1);
        } else {
            // Last question
            confirmSubmit();
        }
    }
    
    function prevQ() {
        if(currentQIndex > 0) loadQuestion(currentQIndex - 1);
    }
    
    function confirmSubmit() {
        // Calculate Summary
        const answeredCnt = Object.keys(answers).length;
        document.getElementById('sum-total').innerText = totalQ;
        document.getElementById('sum-answered').innerText = answeredCnt;
        document.getElementById('sum-not-answered').innerText = totalQ - answeredCnt;
        
        new bootstrap.Modal(document.getElementById('submitModal')).show();
    }
    
    function finalSubmit() {
        window.onbeforeunload = null; // Disable warning
        
        // Prepare Payload
        // We will send answers as JSON
        const payload = {
            schedule_id: <?php echo $schedule_id; ?>,
            responses: answers
        };
        
        $.ajax({
            url: 'submit-exam.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                if(res.success) {
                    window.location.href = 'result.php?schedule_id=' + <?php echo $schedule_id; ?>;
                } else {
                    alert("Error: " + (res.message || "Submission failed"));
                }
            },
            error: function() {
                alert("Network Error. Please try again.");
            }
        });
    }
    
    function autoSubmit() {
        window.onbeforeunload = null;
        alert("Time Up! Submitting exam automatically.");
        finalSubmit();
    }
    
    // Start
    init();

</script>

</body>
</html>
