<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

// Ensure user is logged in as an Astrologer
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    echo json_encode(['has_chat' => false]);
    exit;
}

$astro_id = (int)$_SESSION['userid'];

try {
    // Look for any chat session that is in 'requested' status for this astrologer
    $stmt = $conn->prepare("
        SELECT cs.id, u.name 
        FROM chat_sessions cs 
        JOIN users u ON cs.user_id = u.id 
        WHERE cs.astrologer_id = ? AND cs.status = 'requested' 
        ORDER BY cs.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$astro_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat) {
        echo json_encode([
            'has_chat' => true,
            'session_id' => $chat['id'],
            'user_name' => $chat['name']
        ]);
    } else {
        echo json_encode(['has_chat' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['has_chat' => false]);
}
?>