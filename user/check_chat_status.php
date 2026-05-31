<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$session_id = intval($_GET['session_id'] ?? 0);
$stmt = $conn->prepare("SELECT status FROM chat_sessions WHERE id = ?");
$stmt->execute([$session_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($chat) {
    echo json_encode(['status' => $chat['status']]);
} else {
    echo json_encode(['status' => 'error']);
}
?>