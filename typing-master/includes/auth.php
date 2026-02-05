<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id']) || !isset($_SESSION['typing_master_access']) || $_SESSION['typing_master_access'] !== true) {
    header("Location: login.php");
    exit;
}
?>
