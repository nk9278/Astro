<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$session_id = intval($_GET['session_id'] ?? 0);
$astro_id = $_SESSION['userid'];

if ($session_id > 0) {
    $stmt = $conn->prepare("UPDATE chat_sessions SET status='rejected' WHERE id=? AND astrologer_id=? AND status='requested'");
    $stmt->execute([$session_id, $astro_id]);
}

header("Location: /astrologer/chats.php");
exit;
?>