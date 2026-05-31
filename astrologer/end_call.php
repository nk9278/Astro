<?php
session_start();
require '../db.php';
require '../functions/wallet_functions.php';

header('Content-Type: application/json');

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Unauthorized' => 'अनधिकृत',
            'Invalid request' => 'अमान्य अनुरोध',
            'Call not found' => 'कॉल नहीं मिली',
            'User insufficient wallet balance.' => 'उपयोगकर्ता के वॉलेट में अपर्याप्त शेष राशि।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Must be astrologer
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    echo json_encode(["status" => "error", "message" => __('Unauthorized')]);
    exit;
}

$call_id  = intval($_POST['call_id'] ?? 0);
$duration = floatval($_POST['duration'] ?? 0);

if ($call_id <= 0 || $duration <= 0) {
    echo json_encode(["status" => "error", "message" => __('Invalid request')]);
    exit;
}

// Round up to next full minute
$duration = ceil($duration);

// Fetch call data
$stmt = $conn->prepare("
    SELECT cs.user_id, cs.astrologer_id, u.price_per_minute, u.commission_percent
    FROM call_sessions cs
    JOIN users u ON cs.astrologer_id = u.id
    WHERE cs.id = ?
");
$stmt->execute([$call_id]);
$call = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$call) {
    echo json_encode(["status" => "error", "message" => __('Call not found')]);
    exit;
}

$user_id  = $call['user_id'];
$astro_id = $call['astrologer_id'];
$rate     = floatval($call['price_per_minute']);
$commission_percent = intval($call['commission_percent']);

// Calculate full call bill
$total_cost       = $rate * $duration;
$commission_amount = round(($total_cost * $commission_percent) / 100, 2);
$astro_amount      = round($total_cost - $commission_amount, 2);

try {
    $conn->beginTransaction();

    // 1) Deduct user wallet
    $stmt = $conn->prepare("
        UPDATE users 
        SET wallet_balance = wallet_balance - ? 
        WHERE id=? AND wallet_balance >= ?
    ");
    $stmt->execute([$total_cost, $user_id, $total_cost]);

    if ($stmt->rowCount() == 0) {
        throw new Exception(__('User insufficient wallet balance.'));
    }

    addTransaction($conn, $user_id, $astro_id, "call_deduct",
        -$total_cost, "Call deduction ($duration min)", "call");

    // 2) Pay astrologer
    $stmt = $conn->prepare("
        UPDATE users 
        SET wallet_balance = wallet_balance + ? 
        WHERE id=?
    ");
    $stmt->execute([$astro_amount, $astro_id]);

    addTransaction($conn, $astro_id, $user_id, "call_credit",
        $astro_amount, "Call earning ($duration min)", "payments");

    // 3) Pay admin commission
    $admin_id = $conn->query("SELECT id FROM users WHERE role_id=3 LIMIT 1")->fetchColumn();

    $stmt = $conn->prepare("
        UPDATE users 
        SET commission_balance = commission_balance + ? 
        WHERE id=?
    ");
    $stmt->execute([$commission_amount, $admin_id]);

    addTransaction($conn, $admin_id, $astro_id, "commission_credit",
        $commission_amount, "Commission from call", "attach_money");

    // Update call session
    $stmt = $conn->prepare("
        UPDATE call_sessions 
        SET status='ended', end_time=NOW(), duration_minutes=?, cost=? 
        WHERE id=?
    ");
    $stmt->execute([$duration, $total_cost, $call_id]);

    $conn->commit();

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>