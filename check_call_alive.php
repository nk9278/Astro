<?php
// /check_call_alive.php
session_start();
require 'db.php';
header('Content-Type: application/json');

$call_id = intval($_GET['call_id'] ?? 0);
if ($call_id <= 0) {
    echo json_encode(['alive' => false]);
    exit;
}

$stmt = $conn->prepare("SELECT status FROM call_sessions WHERE id = ?");
$stmt->execute([$call_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['alive' => false]);
    exit;
}

echo json_encode(['alive' => $row['status'] === 'active']);