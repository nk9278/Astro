<?php
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
            'Initiating Chat' => 'चैट शुरू हो रही है',
            'Waiting for astrologer to accept...' => 'ज्योतिषी के स्वीकार करने की प्रतीक्षा में...',
            'Cancel Request' => 'अनुरोध रद्द करें',
            'Time remaining: ' => 'शेष समय: ',
            'Chat request cancelled by you.' => 'आपके द्वारा चैट अनुरोध रद्द कर दिया गया।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    header('Location: /login.php');
    exit;
}

$astro_id = intval($_GET['astro_id'] ?? 0);
$session_id = intval($_GET['session_id'] ?? 0);

if ($astro_id <= 0 || $session_id <= 0) {
    echo "<p style='text-align:center;color:red;padding:40px;'>Invalid chat link.</p>";
    exit;
}

include '../assets/header.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Waiting for Astrologer...</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fafafa; text-align: center; margin: 0; transition: 0.3s; }
    .chat-box { background: #fff; max-width: 430px; margin: 80px auto 0; padding: 35px 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: 0.3s;}
    
    .loader { border: 5px solid #eee; border-top: 5px solid #10b981; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 25px auto; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .timer-text { font-size: 14px; font-weight: 700; color: #ef4444; margin-top: 10px; }
    
    .cancel-btn {
        margin-top: 25px;
        display: inline-block;
        width: 100%;
        padding: 14px;
        background: #ef4444;
        border-radius: 10px;
        color: #fff;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: 0.2s;
        font-size: 15px;
    }
    .cancel-btn:hover { background: #dc2626; }

    /* Dark Theme */
    body.dark-theme { background: #121212; color: #fff; }
    body.dark-theme .chat-box { background: #1f2937; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
    body.dark-theme .loader { border-color: #374151; border-top-color: #10b981; }
    body.dark-theme .timer-text { color: #f87171; }
</style>
</head>
<body>
<script>if(localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-theme');</script>

<div class="chat-box">
    <h2 style="font-size:1.6rem; font-weight:800; margin-bottom: 5px;"><?= __('Initiating Chat') ?></h2>
    
    <div class="loader"></div>
    
    <p id="status" style="margin-top:15px; font-weight: 600; color: #555;" class="dark:text-gray-300">
        <?= __('Waiting for astrologer to accept...') ?>
    </p>
    
    <!-- 60s Timer Display -->
    <div class="timer-text"><?= __('Time remaining: ') ?> <span id="countdown">60</span>s</div>

    <!-- Manual Cancel Button -->
    <button onclick="cancelChatRequest()" class="cancel-btn">
        <i class="fas fa-times-circle mr-1"></i> <?= __('Cancel Request') ?>
    </button>
</div>

<script>
const sessionId = <?= $session_id ?>;
let timeLeft = 60;
let checkInterval;
let timerInterval;

// 1. Timer Logic (Auto cut at 0)
timerInterval = setInterval(() => {
    timeLeft--;
    document.getElementById('countdown').innerText = timeLeft;
    
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        cancelChatRequest(); 
    }
}, 1000);

// 2. Status Polling Logic 
async function checkStatus() {
    try {
        const res = await fetch(`/user/check_chat_status.php?session_id=${sessionId}&_t=${Date.now()}`, { cache: 'no-store' });
        const data = await res.json();

        if (data.status === 'active') {
            clearInterval(checkInterval);
            clearInterval(timerInterval);
            window.location.replace(`/user/active_chat.php?session_id=${sessionId}`);
        } else if (data.status === 'rejected' || data.status === 'error' || data.status === 'user_rejected') {
            clearInterval(checkInterval);
            clearInterval(timerInterval);
            window.location.replace("/index.php"); 
        }
    } catch(e) {
        console.error(e);
    }
}
checkInterval = setInterval(checkStatus, 2000);

// 3. Cancel Chat Function (Keepalive ensures DB updates before browser kills the tab)
function cancelChatRequest() {
    clearInterval(checkInterval);
    clearInterval(timerInterval);
    document.getElementById('status').innerText = "Cancelling request...";
    
    try {
        // keepalive: true allows the request to outlive the page redirect
        fetch(`/user/cancel_chat_request.php?session_id=${sessionId}`, { keepalive: true });
    } catch (e) {
        console.error(e);
    }
    
    // A slight delay guarantees the network request is initiated
    setTimeout(() => {
        window.location.replace("/index.php");
    }, 150);
}
</script>
</body>
</html>