<?php
require_once '../../database/config.php';

header('Content-Type: application/json');

if (!isset($_GET['type'])) {
    echo json_encode(['error' => 'Invalid Request']);
    exit;
}

$type = $_GET['type'];

try {
    if ($type === 'get_sessions') {
        $course_id = intval($_GET['course_id']);
        $stmt = $pdo->prepare("SELECT id, session_name FROM academic_sessions WHERE course_id = ? AND is_active = 1 ORDER BY start_year DESC, start_month ASC");
        $stmt->execute([$course_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($type === 'get_course_details') {
        $course_id = intval($_GET['course_id']);
        $stmt = $pdo->prepare("SELECT id, has_units, unit_type FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));

    } elseif ($type === 'get_units') {
        $course_id = intval($_GET['course_id']);
        // Fetch distinct unit numbers from subjects table
        $stmt = $pdo->prepare("SELECT DISTINCT unit_no FROM subjects WHERE course_id = ? AND unit_no > 0 ORDER BY unit_no ASC");
        $stmt->execute([$course_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));

    } elseif ($type === 'get_subjects') {
        $course_id = intval($_GET['course_id']);
        $unit_no = isset($_GET['unit_no']) ? intval($_GET['unit_no']) : 0;
        
        $sql = "SELECT id, subject_name FROM subjects WHERE course_id = ?";
        $params = [$course_id];
        
        if ($unit_no > 0) {
            $sql .= " AND unit_no = ?";
            $params[] = $unit_no;
        }
        
        $sql .= " ORDER BY subject_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($type === 'get_subject_details') {
        $subject_id = intval($_GET['subject_id']);
        $stmt = $pdo->prepare("SELECT id, subject_name, theory_marks, practical_marks, passing_marks, exam_duration FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            // Ensure numbers are formatted
            $data['theory_marks'] = floatval($data['theory_marks']);
            $data['practical_marks'] = floatval($data['practical_marks']);
            $data['passing_marks'] = floatval($data['passing_marks']);
            $data['exam_duration'] = intval($data['exam_duration']); // minutes
        }
        echo json_encode($data);

    } elseif ($type === 'get_exams_by_subject') {
        $subject_id = intval($_GET['subject_id']);
        $stmt = $pdo->prepare("SELECT id, exam_serial_no, total_marks FROM exams WHERE subject_id = ? ORDER BY created_at DESC");
        $stmt->execute([$subject_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
