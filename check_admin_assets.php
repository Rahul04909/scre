<?php
require 'database/config.php';
$stmt = $pdo->query("SELECT id, signature, stamp FROM admins WHERE id = 1");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
echo "ID: " . $res['id'] . "\n";
echo "Sign: " . $res['signature'] . "\n";
echo "Stamp: " . $res['stamp'] . "\n";
if (file_exists($res['signature'])) echo "Sign Exists: YES\n"; else echo "Sign Exists: NO (" . realpath($res['signature']) . ")\n";
if (file_exists($res['stamp'])) echo "Stamp Exists: YES\n"; else echo "Stamp Exists: NO (" . realpath($res['stamp']) . ")\n";
?>
