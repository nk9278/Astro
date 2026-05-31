<?php
session_start();
require '../db.php';

// CALL ID
$call_id = intval($_GET['call_id'] ?? 0);
if ($call_id <= 0) exit("invalid");

// Update call as ended (auto timeout)
$stmt = $conn->prepare("UPDATE call_sessions SET status='ended', end_time=NOW() WHERE id=?");
$stmt->execute([$call_id]);

// Also push a signal so astrologer device knows
$sig = $conn->prepare("
    INSERT INTO call_signals (call_id, sender, type, payload, created_at)
    VALUES (?, 'system', 'call_end', '{}', NOW())
");
$sig->execute([$call_id]);

echo "OK";
