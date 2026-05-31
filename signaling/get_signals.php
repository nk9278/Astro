<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

$call_id  = intval($_GET['call_id'] ?? 0);
$since_id = intval($_GET['since_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT id, sender_role AS sender, type, payload
    FROM call_signals
    WHERE call_id=? AND id > ?
    ORDER BY id ASC
");
$stmt->execute([$call_id,$since_id]);

echo json_encode(["signals"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
