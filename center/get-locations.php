<?php
require_once '../database/config.php';

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($type == 'get_states' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM states WHERE country_id = ? ORDER BY name ASC");
        $stmt->execute([$id]);
        $states = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($states);
    } catch (Exception $e) { echo json_encode([]); }
} elseif ($type == 'get_cities' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ? ORDER BY name ASC");
        $stmt->execute([$id]);
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cities);
    } catch (Exception $e) { echo json_encode([]); }
} else {
    echo json_encode([]);
}
?>
