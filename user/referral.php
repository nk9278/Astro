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
            'Your Referral' => 'आपका रेफरल',
            'Refer & Earn' => 'रेफर करें और कमाएं',
            'Share your referral code/link with your friends.  When they sign up, both of you will earn rewards!' => 'अपने दोस्तों के साथ अपना रेफरल कोड/लिंक साझा करें। जब वे साइन अप करेंगे, तो आप दोनों को पुरस्कार मिलेंगे!',
            'Your Referral Code' => 'आपका रेफरल कोड',
            'Your Referral Link' => 'आपका रेफरल लिंक',
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
   FETCH OR AUTO-GENERATE REFERRAL CODE
--------------------------------- */

$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id=?");
$stmt->execute([$userId]);
$referralCode = $stmt->fetchColumn();

if (empty($referralCode)) {
    // Auto-generate new referral code REF000123
    $referralCode = "REF" . str_pad($userId, 6, "0", STR_PAD_LEFT);

    $update = $conn->prepare("UPDATE users SET referral_code=? WHERE id=?");
    $update->execute([$referralCode, $userId]);
}

/* -------------------------------
   MAKE REFERRAL LINK
--------------------------------- */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$domain = $protocol . "://" . $_SERVER['HTTP_HOST'];

$referralLink = $domain . "/login.php?ref=" . urlencode($referralCode);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= __('Your Referral') ?></title>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 600px;
    margin: auto;
    background: #fafafa;
    color: #222;
    transition: background 0.3s ease;
}

h2 {
    font-size: 1.8rem;
    margin-bottom: 18px;
}

.box {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 18px;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
}

.label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.code-box {
    background: #f3f3f3;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    text-align: center;
    font-size: 1.4rem;
    font-weight: bold;
    letter-spacing: 1px;
    color: #222;
    transition: all 0.3s ease;
}

.copy-input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: white;
    font-size: 1rem;
    color: #222;
    box-sizing: border-box;
    transition: all 0.3s ease;
}

.copy-btn {
    background: #1766f7;
    padding: 10px 16px;
    border-radius: 8px;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 10px;
    border: none;
    transition: background 0.3s ease;
}
.copy-btn:hover { background: #0f52c8; }

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
body.dark-theme .box { background-color: #1f2937 !important; border-color: #374151 !important; }
body.dark-theme h2, body.dark-theme .label { color: #f9fafb !important; }
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

<p><?= __('Share your referral code/link with your friends.  When they sign up, both of you will earn rewards!') ?></p>

<!-- SHOW REFERRAL CODE -->
<div class="box">
    <span class="label"><?= __('Your Referral Code') ?></span>
    <div class="code-box"><?= htmlspecialchars($referralCode, ENT_QUOTES, 'UTF-8') ?></div>
</div>

<!-- SHOW REFERRAL LINK -->
<div class="box">
    <span class="label"><?= __('Your Referral Link') ?></span>
    <input type="text" class="copy-input" id="refLink" value="<?= htmlspecialchars($referralLink, ENT_QUOTES, 'UTF-8') ?>" readonly onclick="this.select()">
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