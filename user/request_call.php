<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Unauthorized access. Please login again.' => 'अनधिकृत पहुंच। कृपया पुनः लॉगिन करें।',
            'Invalid astrologer ID.' => 'अमान्य ज्योतिषी आईडी।',
            'User not found.' => 'उपयोगकर्ता नहीं मिला।',
            'Astrologer not found.' => 'ज्योतिषी नहीं मिला।',
            'Insufficient wallet balance. Please add funds.' => 'वॉलेट में अपर्याप्त बैलेंस। कृपया फंड जोड़ें।',
            'Database error: ' => 'डेटाबेस त्रुटि: ',
            'Client' => 'क्लाइंट'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Only logged-in USER (role = 2)
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    echo json_encode([
        'status'   => 'error',
        'message'  => __('Unauthorized access. Please login again.'),
        'redirect' => '/login.php'
    ]);
    exit;
}

$user_id  = intval($_SESSION['userid']);
$astro_id = intval($_POST['astro_id'] ?? 0);

if ($astro_id <= 0) {
    echo json_encode([
        'status'=>'error',
        'message'=>__('Invalid astrologer ID.')
    ]);
    exit;
}

// Fetch user balance
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=? AND role_id=2");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        'status'=>'error',
        'message'=>__('User not found.'),
        'redirect'=>'/login.php'
    ]);
    exit;
}

// Fetch astrologer details
$stmt = $conn->prepare("
    SELECT price_per_minute, commission_percent, fcm_token 
    FROM users 
    WHERE id=? AND role_id=1
");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$astro) {
    echo json_encode([
        'status'=>'error',
        'message'=>__('Astrologer not found.')
    ]);
    exit;
}

$price = floatval($astro['price_per_minute']);
$fcm_token = trim($astro['fcm_token'] ?? '');

// Minimum balance check
if (floatval($user['wallet_balance']) < $price) {
    echo json_encode([
        'status'   => 'low_balance',
        'message'  => __('Insufficient wallet balance. Please add funds.'),
        'redirect' => '/user/wallet.php'
    ]);
    exit;
}

// Create call session
try {
    $stmt = $conn->prepare("
        INSERT INTO call_sessions 
        (user_id, astrologer_id, status, start_time, price_per_minute, commission_percent)
        VALUES (?, ?, 'requested', NOW(), ?, ?)
    ");
    $stmt->execute([$user_id, $astro_id, $price, intval($astro['commission_percent'])]);

    $call_id = $conn->lastInsertId();

    // ⭐ NEW FIX: Trigger Firebase to wake up the Astrologer's phone ⭐
    if (!empty($fcm_token)) {
        $fcm_payload = [
            'token' => $fcm_token,
            'call_id' => $call_id,
            'user_email' => __('Client'), // Name displayed on the incoming call screen
            'accept_url' => "https://thefortunepathway.com/astrologer/accept_call.php?call_id=" . $call_id
        ];
        
        // Background cURL request to send FCM
        $ch = curl_init("https://thefortunepathway.com/send_fcm_v1.php");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fcm_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Quick timeout so user doesn't wait
        curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode([
        'status'  => 'success',
        'call_id' => intval($call_id)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'=>'error',
        'message'=>__('Database error: ') . $e->getMessage()
    ]);
}
?>