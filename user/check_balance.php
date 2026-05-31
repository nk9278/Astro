<?php
session_start();
require '../db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['userid'])) {
    echo json_encode(["balance" => 0]);
    exit;
}

$user_id = $_SESSION['userid'];

$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=?");
$stmt->execute([$user_id]);

echo json_encode(["balance" => floatval($stmt->fetchColumn())]);
