<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = $_SESSION['userid'];
$call_id  = intval($_GET['call_id'] ?? 0);
if ($call_id <= 0) die('Invalid call');

// Always mark the call as rejected (no extra status filter)
$stmt = $conn->prepare("
    UPDATE call_sessions 
    SET status='rejected', end_time=NOW()
    WHERE id=? AND astrologer_id=?
");
$stmt->execute([$call_id, $astro_id]);

// Push a 'call_end' signal so all clients instantly see it
try {
    $sig = $conn->prepare("
        INSERT INTO call_signals (call_id, sender, type, payload, created_at)
        VALUES (?, 'astrologer', 'call_end', '{}', NOW())
    ");
    $sig->execute([$call_id]);
} catch (Exception $e) {
    // fail-safe: don't block redirect
}

// Redirect to astrologer dashboard
header("Location: ../astrologer/dashboard.php");
exit;
?>