<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    
    try {
        // 1. Verify Student belongs to this Center
        $checkStmt = $pdo->prepare("SELECT id, enrollment_no FROM students WHERE id = ? AND center_id = ?");
        $checkStmt->execute([$student_id, $center_id]);
        $student = $checkStmt->fetch();

        if ($student) {
            $pdo->beginTransaction();

            // 2. Delete from student_qualifications (files should ideally be unlinked too)
            // Fetch files to unlink later if needed:
            // $filesStmt = $pdo->prepare("SELECT file_path FROM student_qualifications WHERE student_id = ?");
            // ...
            // For now, simple DB cleanup
            $delQual = $pdo->prepare("DELETE FROM student_qualifications WHERE student_id = ?");
            $delQual->execute([$student_id]);

            // 3. Delete from students table
            $delStudent = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $delStudent->execute([$student_id]);

            $pdo->commit();
            
            // Redirect with Success
            header("Location: index.php?status=deleted");
            exit;
        } else {
            // Student not found or belongs to another center
            header("Location: index.php?status=error&msg=Unauthorized Access");
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: index.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
