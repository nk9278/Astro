<?php
session_start();
require '../db.php';

// Strictly prevent caching of this endpoint
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$call_id = intval($_GET['call_id'] ?? 0);
if ($call_id <= 0) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

// 1) Read status from call_sessions
$stmt = $conn->prepare("SELECT status FROM call_sessions WHERE id=? LIMIT 1");
$stmt->execute([$call_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

$status = $row['status'];

// 2) Fallback via signaling: if a call_end was pushed, treat as rejected/ended
$signalStmt = $conn->prepare("
    SELECT type 
    FROM call_signals 
    WHERE call_id=? 
    ORDER BY id DESC 
    LIMIT 1
");
$signalStmt->execute([$call_id]);
$lastSignal = $signalStmt->fetch(PDO::FETCH_ASSOC);

if ($lastSignal && $lastSignal['type'] === 'call_end') {
    if ($status === 'requested') {
        $status = 'rejected';
    } elseif ($status === 'active') {
        $status = 'ended';
    }
}

echo json_encode(['status' => $status]);
exit;
?>