<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = ['en' => [], 'hi' => [
        'Unauthorized' => 'अनधिकृत',
        'Astrologer not found or offline.' => 'ज्योतिषी नहीं मिला या ऑफ़लाइन है।',
        'Insufficient wallet balance. Please recharge.' => 'वॉलेट में अपर्याप्त बैलेंस। कृपया रिचार्ज करें।'
    ]];
    function __($key) { global $translations, $current_lang; return $translations[$current_lang][$key] ?? $key; }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    echo json_encode(["status" => "error", "message" => __("Unauthorized")]);
    exit;
}

$user_id = $_SESSION['userid'];
$astro_id = intval($_POST['astro_id'] ?? 0);

// Fetch Astrologer and check if Chat is enabled
$stmt = $conn->prepare("SELECT price_per_minute, commission_percent, is_chat_online FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$astro || $astro['is_chat_online'] == 0) {
    echo json_encode(["status" => "error", "message" => __("Astrologer not found or offline.")]);
    exit;
}

$rate = floatval($astro['price_per_minute']);
$commission = intval($astro['commission_percent'] ?? 30);

// Check User Balance (Ensuring they have at least 5 minutes of balance)
$min_balance_required = $rate * 5; 
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['wallet_balance'] < $min_balance_required) {
    echo json_encode(["status" => "low_balance", "message" => __("Insufficient wallet balance. Please recharge.")]);
    exit;
}

// Create Chat Session
try {
    $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, astrologer_id, status, commission_percent) VALUES (?, ?, 'requested', ?)");
    $stmt->execute([$user_id, $astro_id, $commission]);
    $session_id = $conn->lastInsertId();

    echo json_encode(["status" => "success", "session_id" => $session_id]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "System error. Please try again."]);
}
?>