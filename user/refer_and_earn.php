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
            'Refer & Earn' => 'रेफर करें और कमाएं',
            'Share your referral link. When someone signs up, both of you earn rewards.' => 'अपना रेफरल लिंक साझा करें। जब कोई साइन अप करता है, तो आप दोनों को पुरस्कार मिलते हैं।',
            'Your Referral Code:' => 'आपका रेफरल कोड:',
            'Your Referral Link:' => 'आपका रेफरल लिंक:',
            'Copy Link' => 'लिंक कॉपी करें',
            'Referral link copied!' => 'रेफरल लिंक कॉपी हो गया!'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['userid'];

/* -------------------------------
   AUTO-GENERATE REFERRAL CODE
--------------------------------- */

$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id=?");
$stmt->execute([$userId]);
$referralCode = $stmt->fetchColumn();

if (empty($referralCode)) {
    // Create new code like REF000123
    $referralCode = "REF" . str_pad($userId, 6, '0', STR_PAD_LEFT);
    $update = $conn->prepare("UPDATE users SET referral_code=? WHERE id=?");
    $update->execute([$referralCode, $userId]);
}

/* -------------------------------
   AUTO-GENERATE REFERRAL LINK
--------------------------------- */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https"
    : "http";

$domain = $protocol . "://" . $_SERVER['HTTP_HOST'];

$referralLink = $domain . "/login.php?ref=" . urlencode($referralCode);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= __('Refer & Earn') ?></title>

<style>
body { 
    font-family: Arial, sans-serif; 
    padding: 20px; 
    max-width: 600px; 
    margin: auto; 
    background-color: #fafafa;
    color: #222222;
    transition: background 0.3s ease;
}
.box { 
    background: #f8f8f8; 
    padding: 15px; 
    border-radius: 10px; 
    margin-bottom: 18px; 
    border: 1px solid #ddd; 
    transition: all 0.3s ease;
}
h2 { margin-bottom: 10px; }
.copy-input {
    width: 100%; padding: 10px; font-size: 1rem; border-radius: 6px;
    border: 1px solid #ccc; background: #fff; color: #222;
    transition: all 0.3s ease; box-sizing: border-box;
}
.copy-btn {
    padding: 10px 16px; margin-top: 10px;
    background: #1766f7; border: none; color: white;
    border-radius: 6px; cursor: pointer; font-size: 1rem;
    transition: background 0.3s ease;
}
.copy-btn:hover { background: #0e52c8; }
.code-box {
    font-size: 1.4rem; font-weight: bold; padding: 12px;
    background: #fff; border: 1px solid #ccc; border-radius: 6px;
    text-align: center; color: #222; transition: all 0.3s ease;
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
body.dark-theme .box { background-color: #1f2937 !important; border-color: #374151 !important; }
body.dark-theme h2, body.dark-theme strong { color: #f9fafb !important; }
body.dark-theme .copy-input, body.dark-theme .code-box { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
body.dark-theme .copy-btn { background-color: #10b981 !important; }
body.dark-theme .copy-btn:hover { background-color: #059669 !important; }
</style>

</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<h2><?= __('Refer & Earn') ?></h2>
<p><?= __('Share your referral link. When someone signs up, both of you earn rewards.') ?></p>

<!-- SHOW REFERRAL CODE -->
<div class="box">
  <strong><?= __('Your Referral Code:') ?></strong>
  <div class="code-box"><?= htmlspecialchars($referralCode, ENT_QUOTES, 'UTF-8') ?></div>
</div>

<!-- SHOW REFERRAL LINK -->
<div class="box">
  <strong><?= __('Your Referral Link:') ?></strong>
  <input type="text" id="refLink" value="<?= htmlspecialchars($referralLink, ENT_QUOTES, 'UTF-8') ?>" 
         class="copy-input" readonly onclick="this.select()">

  <button class="copy-btn" onclick="copyLink()"><?= __('Copy Link') ?></button>
</div>

<script>
function copyLink() {
    let link = document.getElementById("refLink");
    link.select();
    navigator.clipboard.writeText(link.value);
    alert("<?= __('Referral link copied!') ?>");
}
</script>

</body>
</html>