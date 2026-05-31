<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    header("Location: /login.php");
    exit;
}

$call_id = intval($_GET['call_id'] ?? 0);
if ($call_id <= 0) die("Invalid call");

// Mark call as ended by user
$stmt = $conn->prepare("
    UPDATE call_sessions 
    SET status='user_rejected', end_time=NOW()
    WHERE id=? AND status='requested'
");
$stmt->execute([$call_id]);

header("Location: /index.php");
exit;
?>