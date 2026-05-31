<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['userid'];
$call_id = (int)($_GET['call_id'] ?? 0);

if ($call_id > 0) {
    // Crucial Update: Only change to user_rejected if it is still in requested state
    $stmt = $conn->prepare("UPDATE call_sessions SET status = 'user_rejected' WHERE id = ? AND user_id = ? AND status = 'requested'");
    $stmt->execute([$call_id, $user_id]);
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>