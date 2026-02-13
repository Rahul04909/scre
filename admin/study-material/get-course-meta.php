<?php
require_once '../../database/config.php';

header('Content-Type: application/json');

if (!isset($_GET['course_id'])) {
    echo json_encode(['error' => 'Course ID required']);
    exit;
}

$course_id = intval($_GET['course_id']);

try {
    // Fetch Course Details (Units info)
    $stmt = $pdo->prepare("SELECT has_units, unit_type, unit_count FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(['error' => 'Course not found']);
        exit;
    }

    // Fetch Subjects linked to this course
    $stmtSub = $pdo->prepare("SELECT id, subject_name, unit_no FROM subjects WHERE course_id = ? ORDER BY unit_no ASC, subject_name ASC");
    $stmtSub->execute([$course_id]);
    $subjects = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'has_units' => (bool)$course['has_units'],
        'unit_type' => $course['unit_type'],
        'unit_count' => (int)$course['unit_count'],
        'subjects' => $subjects
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
