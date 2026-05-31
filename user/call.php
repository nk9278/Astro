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
            'Invalid call link.' => 'अमान्य कॉल लिंक।',
            'Active Call' => 'सक्रिय कॉल',
            'Waiting for astrologer to accept...' => 'ज्योतिषी के स्वीकार करने की प्रतीक्षा में...',
            'View Call History' => 'कॉल इतिहास देखें'
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

$user_id = $_SESSION['userid'];
$astro_id = intval($_GET['astro_id'] ?? 0);
$call_id = intval($_GET['call_id'] ?? 0);

if ($astro_id <= 0 || $call_id <= 0) {
    echo "<p style='text-align:center;color:red;padding:40px;'>" . __('Invalid call link.') . "</p>";
    exit;
}

include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= __('Active Call') ?></title>

<style>
body {
    font-family: 'Space Grotesk', sans-serif;
    background: #fafafa;
    color: #222;
    margin: 0;
    padding: 0;
    text-align: center;
    transition: background 0.3s ease;
}

.call-box {
    background: #ffffff;
    max-width: 430px;
    margin: 80px auto 0;
    padding: 25px 20px 35px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.10);
    transition: all 0.3s ease;
}

.loader {
    border: 6px solid #eee;
    border-top: 6px solid #222;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    animation: spin 1s linear infinite;
    margin: 25px auto;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.call-history-btn {
    margin-top: 22px;
    display: block;
    width: 100%;
    padding: 14px;
    background: #222;
    border-radius: 10px;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
}
.call-history-btn:hover { background:#444; }

#status { margin-top: 10px; font-size: 1rem; color: #333; }

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme .call-box { background: #1f2937 !important; color: #ffffff !important; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
body.dark-theme #status { color: #d1d5db !important; }
body.dark-theme .loader { border: 6px solid #374151; border-top: 6px solid #f9fafb; }
body.dark-theme .call-history-btn { background: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
body.dark-theme .call-history-btn:hover { background: #4b5563 !important; }
</style>

</head>

<body>
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="call-box">
    <h2 style="font-size:1.6rem; font-weight:700; margin-bottom:10px;"><?= __('Active Call') ?></h2>

    <div class="loader"></div>

    <p id="status"><?= __('Waiting for astrologer to accept...') ?></p>

    <a href="/user/wallet.php" class="call-history-btn">📜 <?= __('View Call History') ?></a>
</div>

<script>
const callId = <?= $call_id ?>;
const astroId = <?= $astro_id ?>;

async function checkStatus() {
  const res = await fetch(`/user/check_call_status.php?call_id=${callId}&astro_id=${astroId}`);
  const data = await res.json();

  if (data.status === 'active') {
    window.location.href = `/user/active_call.php?astro_id=${astroId}&call_id=${callId}`;
  }
  else if (data.status === 'rejected' || data.status === 'ended') {

      // ⭐⭐ INSTANT REDIRECT ⭐⭐
      if (data.redirect) {
          window.location.href = data.redirect;
      } else {
          window.location.href = "/index.php";
      }
  }
}

const checkInterval = setInterval(checkStatus, 3000);
</script>

<?php include '../user/footer_user.php'; ?>

</body>
</html>