<?php
session_start();
header('Content-Type: application/json');
require_once '../database/config.php';

// Check Auth
if (!isset($_SESSION['online_exam_student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['online_exam_student_id'];

// Get Payload
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['schedule_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

$schedule_id = intval($input['schedule_id']);
$responses = isset($input['responses']) ? $input['responses'] : []; // { q_id: 'A', ... }

try {
    // 1. Fetch Exam Meta
    $stmt = $pdo->prepare("
        SELECT es.exam_id, sub.total_marks, sub.passing_marks, 
               e.marks_per_question, e.total_questions,
               sub.unit_no
        FROM exam_schedules es
        JOIN subjects sub ON es.subject_id = sub.id
        JOIN exams e ON es.exam_id = e.id
        WHERE es.id = ?
    ");
    $stmt->execute([$schedule_id]);
    $examMeta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$examMeta) {
        throw new Exception("Exam not found");
    }

    // 2. Fetch Correct Answers from DB
    $stmtQ = $pdo->prepare("SELECT id, correct_option FROM exam_questions WHERE exam_id = ?");
    $stmtQ->execute([$examMeta['exam_id']]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_KEY_PAIR); // [id => correct_option]

    // 3. Calculate Score
    $attempted = 0;
    $correct = 0;
    $wrong = 0;
    
    foreach ($questions as $qId => $corrOpt) {
        if (isset($responses[$qId]) && !empty($responses[$qId])) {
            $attempted++;
            if ($responses[$qId] === $corrOpt) {
                $correct++;
            } else {
                $wrong++;
            }
        }
    }
    
    $marksPerQ = floatval($examMeta['marks_per_question']);
    $negMarks = 0; // Negative marking removed
    
    $score = ($correct * $marksPerQ) - ($wrong * $negMarks);
    $score = max(0, $score); // Ensure non-negative? usually exam scores can't be negative but dependent on rule. Let's keep it real.
    
    // Pass/Fail
    // Use subject total marks or exam calculated marks?
    // usually passing marks is defined in subject.
    $totalMarks = $examMeta['total_questions'] * $marksPerQ; // Or use sub.total_marks? Let's use calculated from QP.
    
    $percentage = ($totalMarks > 0) ? ($score / $totalMarks) * 100 : 0;
    
    // Passing logic: using subject passing marks if available
    $passingMarks = floatval($examMeta['passing_marks']);
    $status = ($score >= $passingMarks) ? 'Pass' : 'Fail';
    
    // 4. Save to DB
    // Check if already exists?
    $stmtCheck = $pdo->prepare("SELECT id FROM exam_results WHERE student_id = ? AND exam_schedule_id = ?");
    $stmtCheck->execute([$student_id, $schedule_id]);
    if ($stmtCheck->fetch()) {
        // Already submitted
        echo json_encode(['success' => true, 'message' => 'Already Submitted']);
        exit;
    }
    
    $insertSql = "INSERT INTO exam_results (
        student_id, exam_schedule_id, 
        total_questions, attempted_questions, correct_answers, wrong_answers, 
        score, total_marks, percentage, result_status, unit_no
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmtIns = $pdo->prepare($insertSql);
    $stmtIns->execute([
        $student_id, $schedule_id,
        $examMeta['total_questions'], $attempted, $correct, $wrong,
        $score, $totalMarks, $percentage, $status, $examMeta['unit_no']
    ]);
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
