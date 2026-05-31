<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$astro_id = (int)$_SESSION['userid'];

if ($id > 0) {
    if ($type === 'call') {
        $stmt = $conn->prepare("UPDATE call_sessions SET status = 'rejected' WHERE id = ? AND astrologer_id = ? AND status = 'requested'");
        $stmt->execute([$id, $astro_id]);
    } else if ($type === 'chat') {
        $stmt = $conn->prepare("UPDATE chat_sessions SET status = 'rejected' WHERE id = ? AND astrologer_id = ? AND status = 'requested'");
        $stmt->execute([$id, $astro_id]);
    }
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>