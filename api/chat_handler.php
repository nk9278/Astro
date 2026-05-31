<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

/* ============================
   HARDENED SECURITY CHECK
============================ */
if (!isset($_SESSION['userid']) || !in_array($_SESSION['role'], [1, 2])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$user_id = (int)$_SESSION['userid'];
$role_type = ($_SESSION['role'] == 1) ? 'astrologer' : 'user';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$session_id = (int)($_POST['session_id'] ?? $_GET['session_id'] ?? 0);

if ($session_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid session parameters."]);
    exit;
}

// 1. Verify Ownership of Chat Session
$stmt = $conn->prepare("SELECT status FROM chat_sessions WHERE id = ? AND (user_id = ? OR astrologer_id = ?)");
$stmt->execute([$session_id, $user_id, $user_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat || $chat['status'] !== 'active') {
    echo json_encode(["status" => "error", "message" => "Chat session inactive or denied."]);
    exit;
}

// 2. Handle Actions
try {
    if ($action === 'send') {
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            echo json_encode(["status" => "error", "message" => "Empty message."]);
            exit;
        }

        // Sanitize Input heavily
        $clean_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender_id, sender_type, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$session_id, $user_id, $role_type, $clean_message]);
        
        echo json_encode(["status" => "success"]);
        
    } elseif ($action === 'fetch') {
        $last_id = (int)($_GET['last_id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT id, sender_type, message, DATE_FORMAT(created_at, '%h:%i %p') as time FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$session_id, $last_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["status" => "success", "messages" => $messages]);
    }
} catch (PDOException $e) {
    // Log error internally, do not expose to frontend
    error_log("Chat Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "A secure transmission error occurred."]);
}
?>