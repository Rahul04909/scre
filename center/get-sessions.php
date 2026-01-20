<?php
require_once '../database/config.php';

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, session_name FROM academic_sessions WHERE course_id = ? AND is_active = 1 ORDER BY id DESC");
        $stmt->execute([$course_id]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($sessions);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
