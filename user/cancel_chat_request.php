<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

// Ensure only logged-in users can cancel
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['userid'];
$session_id = (int)($_GET['session_id'] ?? 0);

if ($session_id > 0) {
    try {
        // Update status to 'user_rejected' so the astrologer's screen instantly stops ringing
        $stmt = $conn->prepare("UPDATE chat_sessions SET status = 'user_rejected' WHERE id = ? AND user_id = ? AND status = 'requested'");
        $stmt->execute([$session_id, $user_id]);
        
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'error']);
}
?>