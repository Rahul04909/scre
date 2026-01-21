<?php
require_once '../../database/config.php';

header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$action = $_POST['action'];

// 1. Get Sessions for a Course
if ($action === 'get_sessions') {
    $course_id = intval($_POST['course_id']);
    try {
        // Fetch sessions linked to this course
        $stmt = $pdo->prepare("SELECT id, session_name FROM academic_sessions WHERE course_id = ? AND is_active = 1 ORDER BY start_year DESC, start_month DESC");
        $stmt->execute([$course_id]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Also check course unit details
        $stmtC = $pdo->prepare("SELECT has_units, unit_type, unit_count FROM courses WHERE id = ?");
        $stmtC->execute([$course_id]);
        $course = $stmtC->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'sessions' => $sessions, 'course' => $course]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// 2. Get Subjects (Filtered by Practical)
elseif ($action === 'get_subjects') {
    $course_id = intval($_POST['course_id']);
    $unit_no = isset($_POST['unit_no']) ? intval($_POST['unit_no']) : 0;
    
    try {
        $sql = "SELECT id, subject_name, practical_marks FROM subjects WHERE course_id = ? AND practical_marks > 0";
        $params = [$course_id];

        if ($unit_no > 0) {
            $sql .= " AND unit_no = ?";
            $params[] = $unit_no;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'subjects' => $subjects]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
