<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = $_SESSION['userid'];
$session_id = intval($_GET['session_id'] ?? 0);

if ($session_id <= 0) die('Invalid Request');

// Fetch Session
$stmt = $conn->prepare("SELECT user_id FROM chat_sessions WHERE id=? AND astrologer_id=? AND status='requested'");
$stmt->execute([$session_id, $astro_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat) die('Chat session not found or already handled.');

$user_id = $chat['user_id'];

// Check Client Wallet
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=? AND role_id=2");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['wallet_balance'] <= 0) {
    // Reject chat if no balance
    $stmt = $conn->prepare("UPDATE chat_sessions SET status='rejected' WHERE id=?");
    $stmt->execute([$session_id]);
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:red;'>Insufficient Balance</h2>
            <p>The client does not have enough balance.</p>
            <a href='/astrologer/chats.php'>Back to Chats</a>
         </div>");
}

// Activate Chat
$stmt = $conn->prepare("UPDATE chat_sessions SET status='active', start_time=NOW() WHERE id=?");
$stmt->execute([$session_id]);

// Redirect to Active Chat interface
header("Location: /astrologer/active_chat.php?session_id=$session_id");
exit;
?>