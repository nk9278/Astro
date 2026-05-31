<?php
// accept_call.php
session_start();
require '../db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Insufficient Wallet Balance' => 'वॉलेट में अपर्याप्त बैलेंस',
            'The client does not have enough balance to start this consultation.' => 'इस परामर्श को शुरू करने के लिए क्लाइंट के पास पर्याप्त बैलेंस नहीं है।',
            'Return to Dashboard' => 'डैशबोर्ड पर लौटें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// 1. Security Check: Only Astrologers can access
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = $_SESSION['userid'];
$call_id = intval($_GET['call_id'] ?? 0);

if ($call_id <= 0) {
    die('Invalid Call Request');
}

// 2. Fetch Call Session
$stmt = $conn->prepare("SELECT user_id FROM call_sessions WHERE id=? AND astrologer_id=? AND status='requested'");
$stmt->execute([$call_id, $astro_id]);
$call = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$call) {
    die('Call session not found or already accepted.');
}

$user_id = $call['user_id'];

// 3. Wallet Validation
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=? AND role_id=2");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User not found.');
}

if ($user['wallet_balance'] <= 0) {
    // Basic Dark/Light Theme inline styles for the error page
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark' ? 'dark' : 'light';
    $bg = $theme == 'dark' ? '#121212' : '#fafafa';
    $cardBg = $theme == 'dark' ? '#1f2937' : '#fff';
    $textH2 = $theme == 'dark' ? '#ef4444' : '#dc3545';
    $textP = $theme == 'dark' ? '#d1d5db' : '#666';

    echo "<!DOCTYPE html>
    <html lang='" . htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') . "'>
    <head>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>" . __('Insufficient Wallet Balance') . "</title>
    </head>
    <body style='text-align:center; padding:50px; font-family: Segoe UI, sans-serif; background:" . $bg . "; margin:0;'>
        <div style='background:" . $cardBg . "; padding:30px; border-radius:10px; display:inline-block; box-shadow:0 4px 10px rgba(0,0,0,0.1); max-width: 90%;'>
            <h2 style='color:" . $textH2 . ";'>" . __('Insufficient Wallet Balance') . "</h2>
            <p style='color:" . $textP . ";'>" . __('The client does not have enough balance to start this consultation.') . "</p>
            <br>
            <a href='/astrologer/dashboard.php' style='text-decoration:none; color:#10b981; font-weight:bold; padding: 10px 20px; border: 1px solid #10b981; border-radius: 8px; display: inline-block;'>" . __('Return to Dashboard') . "</a>
        </div>
    </body>
    </html>";
    exit;
}

// 4. Activate Call
$stmt = $conn->prepare("UPDATE call_sessions SET status='active', start_time=NOW() WHERE id=?");
$stmt->execute([$call_id]);

// 5. Success: Redirect to the WebRTC Active Call page
header("Location: /astrologer/active_call.php?call_id=$call_id");
exit;
?>