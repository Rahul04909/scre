<?php
require_once '../../database/config.php';

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    try {
        $stmt = $pdo->prepare("SELECT course_type, duration_value, duration_type, has_units, unit_type, unit_count FROM courses WHERE id = :id");
        $stmt->execute([':id' => $course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($course) {
            echo json_encode(['success' => true, 'data' => $course]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>
