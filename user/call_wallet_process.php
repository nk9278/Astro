<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['userid'];
$astro_id = intval($_POST['astro_id'] ?? 0);

$stmt = $conn->prepare("SELECT price_per_minute FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$astro) {
    echo json_encode(["status" => "error", "message" => "Astrologer not found"]);
    exit;
}

$rate = floatval($astro['price_per_minute']);
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=? AND role_id=2");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

if ($user['wallet_balance'] < $rate) {
    echo json_encode(["status" => "low_balance", "message" => "Insufficient wallet balance"]);
    exit;
}

echo json_encode(["status" => "success"]);
