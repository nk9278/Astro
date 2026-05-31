<?php
// astrologer/save_token.php
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
            'Unauthorized' => 'अनधिकृत',
            'No token provided' => 'कोई टोकन प्रदान नहीं किया गया'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Must be logged in astrologer
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    echo json_encode(['status'=>'error','message'=>__('Unauthorized')]);
    exit;
}

$token = trim($_POST['token'] ?? '');
if (!$token) {
    echo json_encode(['status'=>'error','message'=>__('No token provided')]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
    $stmt->execute([$token, $_SESSION['userid']]);
    echo json_encode(['status'=>'ok']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>