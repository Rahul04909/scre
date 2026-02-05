<?php
session_start();
require_once '../database/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $student_id = $_SESSION['student_id'];
        $test_id = isset($_POST['test_id']) ? intval($_POST['test_id']) : null;
        $wpm = floatval($_POST['wpm']);
        $accuracy = floatval($_POST['accuracy']);
        $errors = intval($_POST['errors']);
        $duration = intval($_POST['duration_seconds']);
        $typed_content = $_POST['typed_content'] ?? '';
        $total_words = intval($_POST['total_words'] ?? 0);
        
        // Prepare JSON Result (if we want to store more detailed stats later)
        $result_data = json_encode([
            'wpm' => $wpm,
            'accuracy' => $accuracy,
            'errors' => $errors,
            'time_taken' => $duration
        ]);
        
        $sql = "INSERT INTO typing_test_results 
                (student_id, test_id, wpm, accuracy, errors, total_words, student_input, result_json, duration_seconds) 
                VALUES (:sid, :tid, :wpm, :acc, :err, :words, :input, :json, :dur)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sid' => $student_id,
            ':tid' => $test_id,
            ':wpm' => $wpm,
            ':acc' => $accuracy,
            ':err' => $errors,
            ':words' => $total_words,
            ':input' => $typed_content,
            ':json' => $result_data,
            ':dur' => $duration
        ]);
        
        $result_id = $pdo->lastInsertId();
        
        echo json_encode(['success' => true, 'redirect' => 'report.php?id=' . $result_id]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>
