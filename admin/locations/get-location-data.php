<?php
require_once '../../database/config.php';

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'get_states' && isset($_GET['country_id'])) {
        $country_id = intval($_GET['country_id']);
        $stmt = $pdo->prepare("SELECT id, name FROM states WHERE country_id = :cid ORDER BY name ASC");
        $stmt->execute([':cid' => $country_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    if ($_GET['type'] == 'get_cities' && isset($_GET['state_id'])) {
        $state_id = intval($_GET['state_id']);
        $stmt = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = :sid ORDER BY name ASC");
        $stmt->execute([':sid' => $state_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?>
