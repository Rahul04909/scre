<?php
require_once 'database/config.php';

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    try {
        $sql = "SELECT c.id, c.center_name, c.center_code, ci.name as city_name 
                FROM centers c
                JOIN center_course_allotment cca ON c.id = cca.center_id
                LEFT JOIN cities ci ON c.city = ci.id
                WHERE cca.course_id = :cid
                ORDER BY c.center_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cid' => $course_id]);
        $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($centers);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
}
?>
