<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

// Fetch session ID from GET or POST for flexibility
$session_id = intval($_GET['session_id'] ?? $_POST['session_id'] ?? 0);
$user_id = $_SESSION['userid'];
$role = $_SESSION['role'];

// Fetch the active session
$stmt = $conn->prepare("SELECT * FROM chat_sessions WHERE id = ?");
$stmt->execute([$session_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

// Only process if the chat is currently active
if ($chat && $chat['status'] === 'active') {
    
    // Fetch Astrologer Rate
    $stmt = $conn->prepare("SELECT price_per_minute FROM users WHERE id = ?");
    $stmt->execute([$chat['astrologer_id']]);
    $rate = floatval($stmt->fetchColumn());

    // Calculate Exact Duration in Minutes (Ceil to next minute)
    $start_time = strtotime($chat['start_time']);
    $end_time = time();
    $duration_minutes = ceil(($end_time - $start_time) / 60);
    $duration_minutes = max(1, $duration_minutes); // Minimum 1 minute charge

    $total_cost = $duration_minutes * $rate;
    $astrologer_cut = $total_cost - (($total_cost * $chat['commission_percent']) / 100);

    // 1. End the Session
    $stmt = $conn->prepare("UPDATE chat_sessions SET end_time = NOW(), status = 'ended', duration_minutes = ?, cost = ? WHERE id = ?");
    $stmt->execute([$duration_minutes, $total_cost, $session_id]);

    // 2. Deduct from User Wallet
    $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
    $stmt->execute([$total_cost, $chat['user_id']]);

    // 3. Add Commission to Astrologer
    $stmt = $conn->prepare("UPDATE users SET commission_balance = commission_balance + ? WHERE id = ?");
    $stmt->execute([$astrologer_cut, $chat['astrologer_id']]);

    // 4. Record Transaction for User
    $stmtTx1 = $conn->prepare("INSERT INTO wallet_transactions (user_id, related_user_id, amount, type, description, icon) VALUES (?, ?, ?, 'chat_deduction', 'Payment for Chat Session', 'chat')");
    $stmtTx1->execute([$chat['user_id'], $chat['astrologer_id'], -$total_cost]);

    // 5. Record Transaction for Astrologer
    $stmtTx2 = $conn->prepare("INSERT INTO wallet_transactions (user_id, related_user_id, amount, type, description, icon) VALUES (?, ?, ?, 'chat_earning', 'Earnings from Chat Session', 'account_balance_wallet')");
    $stmtTx2->execute([$chat['astrologer_id'], $chat['user_id'], $astrologer_cut]);
}

// Route back to the correct page based on role
if ($role == 1) {
    header("Location: /astrologer/dashboard.php");
} else {
    header("Location: /index.php"); 
}
exit;
?>