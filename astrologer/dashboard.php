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
            'Astrologer Dashboard' => 'ज्योतिषी डैशबोर्ड',
            'Track your daily performance and availability.' => 'अपने दैनिक प्रदर्शन और उपलब्धता को ट्रैक करें।',
            'Status:' => 'स्थिति:',
            'Online' => 'ऑनलाइन',
            'Offline' => 'ऑफ़लाइन',
            'GO OFFLINE' => 'ऑफ़लाइन जाएं',
            'GO ONLINE' => 'ऑनलाइन जाएं',
            'Call' => 'कॉल',
            'Chat' => 'चैट',
            'ON' => 'चालू',
            'OFF' => 'बंद',
            'Today\'s Income' => 'आज की आय',
            'Platform Fees' => 'प्लेटफ़ॉर्म फीस',
            'Total Calls' => 'कुल कॉल',
            'Total Minutes' => 'कुल मिनट',
            'Data is updated in real-time based on ended sessions.' => 'डेटा समाप्त हुए सत्रों के आधार पर रीयल-टाइम में अपडेट किया जाता है।',
            'KYC Pending Approval' => 'केवाईसी स्वीकृति लंबित है',
            'Your account is currently under review. Please wait for the Admin to verify your KYC documents. You will be able to receive calls and view stats once approved.' => 'आपका खाता वर्तमान में समीक्षा के अधीन है। कृपया व्यवस्थापक द्वारा आपके दस्तावेज़ों के सत्यापन की प्रतीक्षा करें। स्वीकृत होने के बाद आप कॉल प्राप्त कर सकेंगे और आंकड़े देख सकेंगे।',
            'Update / View Profile' => 'प्रोफ़ाइल अपडेट / देखें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Professional Error Reporting & Timezone
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');

// Security Check
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$user_id = (int)$_SESSION['userid'];

/* ---------- Fetch Astrologer Profile, KYC & Service Status ---------- */
$stmt = $conn->prepare("SELECT astrologer_name, profile_photo, online_status, is_call_online, is_chat_online, approval_status FROM users WHERE id=?");
$stmt->execute([$user_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if astrologer is approved by admin
$is_approved = (isset($astro['approval_status']) && $astro['approval_status'] === 'approved');

/* ---------- Manual Online/Offline & Service Toggles (Only if Approved) ---------- */
if ($is_approved && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_master'])) {
        $new_status = $astro['online_status'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE users SET online_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        $_SESSION['online_status'] = $new_status;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['toggle_call'])) {
        $new_status = $astro['is_call_online'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE users SET is_call_online = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['toggle_chat'])) {
        $new_status = $astro['is_chat_online'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE users SET is_chat_online = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

/* ---------- Fetch Today's Stats (Only if Approved) ---------- */
$dailyNetIncome = 0;
if ($is_approved) {
    $today = date('Y-m-d');
    
    // Calculate Call Stats
    $statsSql = "SELECT 
                    COUNT(id) AS total_calls,
                    COALESCE(SUM(duration_minutes), 0) AS total_minutes,
                    COALESCE(SUM(cost), 0) AS gross_paid,
                    COALESCE(SUM((cost * commission_percent) / 100), 0) AS total_fees
                 FROM call_sessions 
                 WHERE astrologer_id = :aid AND status = 'ended' AND DATE(end_time) = :today";

    $stmtStats = $conn->prepare($statsSql);
    $stmtStats->execute([':aid' => $user_id, ':today' => $today]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // Calculate Chat Stats
    $chatStatsSql = "SELECT 
                        COUNT(id) AS total_chats,
                        COALESCE(SUM(duration_minutes), 0) AS total_chat_minutes,
                        COALESCE(SUM(cost), 0) AS chat_gross_paid,
                        COALESCE(SUM((cost * commission_percent) / 100), 0) AS chat_total_fees
                     FROM chat_sessions 
                     WHERE astrologer_id = :aid AND status = 'ended' AND DATE(end_time) = :today";

    $stmtChatStats = $conn->prepare($chatStatsSql);
    $stmtChatStats->execute([':aid' => $user_id, ':today' => $today]);
    $chatStats = $stmtChatStats->fetch(PDO::FETCH_ASSOC);

    // Combined Daily Net Income
    $dailyNetIncome = ((float)$stats['gross_paid'] - (float)$stats['total_fees']) + ((float)$chatStats['chat_gross_paid'] - (float)$chatStats['chat_total_fees']);
    
    // Combined View Stats
    $total_platform_fees = (float)$stats['total_fees'] + (float)$chatStats['chat_total_fees'];
    $combined_sessions = (int)$stats['total_calls'] + (int)$chatStats['total_chats'];
    $combined_minutes = (float)$stats['total_minutes'] + (float)$chatStats['total_chat_minutes'];
}

$status_text  = $astro['online_status'] ? __('Online') : __('Offline');
$status_color = $astro['online_status'] ? '#10b981' : '#ef4444';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title><?= __('Astrologer Dashboard') ?> | Fortune Pathway</title>

    <link rel="stylesheet" href="/assets/css/header.css" />
    <link rel="stylesheet" href="/assets/css/footer.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --base-txt: 13px;
            --label-txt: 10px;
            --heading-txt: 22px;
        }
        @media (min-width: 1024px) {
            :root {
                --base-txt: 15px;
                --label-txt: 12px;
                --heading-txt: 30px;
            }
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #1e293b;
            font-size: var(--base-txt);
            transition: background 0.3s ease;
        }
        
        /* Fixed bottom padding for mobile to handle bottom nav */
        @media (max-width: 1024px) { body { padding-bottom: 120px; } }

        .glass-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: all 0.3s ease; }
        .stat-box { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 20px; padding: 20px; text-align: center; transition: transform 0.2s, background 0.3s; }
        .stat-box:hover { transform: translateY(-3px); }
        
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 8px 16px rgba(0,0,0,0.08); }
        
        .btn-status { 
            border-radius: 16px; 
            padding: 14px; 
            font-weight: 800; 
            width: 100%; 
            transition: all 0.3s ease; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 10px;
            font-size: var(--base-txt);
        }
        .online-bg { background: #10b981; color: white; }
        .offline-bg { background: #ef4444; color: white; }
        
        @media (min-width: 1024px) { .page-wrap { max-width: 1000px; margin: 40px auto; } }

        /* =========================================
             ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .stat-box { background: #374151 !important; border-color: #4b5563 !important; }
        body.dark-theme .profile-img { border-color: #374151 !important; }
        
        body.dark-theme .text-slate-900, 
        body.dark-theme .text-slate-800 { color: #ffffff !important; }
        
        body.dark-theme .text-slate-500, 
        body.dark-theme .text-slate-400 { color: #d1d5db !important; }
    </style>
</head>
<body class="antialiased">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<div class="page-wrap px-4 py-6">
    <div class="flex flex-col gap-6">
        
        <div class="mb-2 text-center lg:text-left">
            <h1 class="text-[var(--heading-txt)] font-800"><?= __('Astrologer Dashboard') ?></h1>
            <p class="text-slate-500 font-medium opacity-80"><?= __('Track your daily performance and availability.') ?></p>
        </div>

        <?php if ($is_approved): ?>
            <div class="glass-card p-6 lg:p-8">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="relative">
                        <img class="profile-img" src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="absolute bottom-1 right-1 w-6 h-6 rounded-full border-4 border-white" style="background:<?= $status_color ?>;"></div>
                    </div>
                    <div class="flex-1 text-center md:text-left w-full">
                        <h2 class="text-2xl font-800 text-slate-900"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-1">
                            <?= __('Status:') ?> <span style="color:<?= $status_color ?>;"><?= $status_text ?></span>
                        </p>
                        
                        <form method="POST" class="mt-4 max-w-xs mx-auto md:mx-0">
                            <button type="submit" name="toggle_master" class="btn-status <?= $astro['online_status'] ? 'offline-bg' : 'online-bg' ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <?= $astro['online_status'] ? __('GO OFFLINE') : __('GO ONLINE') ?>
                            </button>
                        </form>

                        <div class="flex gap-3 mt-3 max-w-xs mx-auto md:mx-0">
                            <form method="POST" class="flex-1">
                                <button type="submit" name="toggle_call" class="w-full py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm <?= $astro['is_call_online'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700' ?>">
                                    <i class="fas fa-phone"></i> <?= __('Call') ?>: <?= $astro['is_call_online'] ? __('ON') : __('OFF') ?>
                                </button>
                            </form>
                            <form method="POST" class="flex-1">
                                <button type="submit" name="toggle_chat" class="w-full py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm <?= $astro['is_chat_online'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700' ?>">
                                    <i class="fas fa-comment"></i> <?= __('Chat') ?>: <?= $astro['is_chat_online'] ? __('ON') : __('OFF') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="glass-card stat-box">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Today\'s Income') ?></p>
                    <p class="text-2xl font-800 text-indigo-600 mt-2">₹<?= number_format($dailyNetIncome, 0) ?></p>
                </div>
                <div class="glass-card stat-box">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Platform Fees') ?></p>
                    <p class="text-2xl font-800 text-slate-900 mt-2">₹<?= number_format($total_platform_fees, 0) ?></p>
                </div>
                <div class="glass-card stat-box">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Total Calls') ?> & Chats</p>
                    <p class="text-2xl font-800 text-slate-900 mt-2"><?= $combined_sessions ?></p>
                </div>
                <div class="glass-card stat-box">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Total Minutes') ?></p>
                    <p class="text-2xl font-800 text-slate-900 mt-2"><?= (int)$combined_minutes ?></p>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-[10px] text-slate-400 font-medium italic"><?= __('Data is updated in real-time based on ended sessions.') ?></p>
            </div>

        <?php else: ?>
            <div class="glass-card p-6 lg:p-12 text-center mt-4">
                <div class="w-20 h-20 mx-auto bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-6 shadow-sm">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-800 text-slate-900 mb-3"><?= __('KYC Pending Approval') ?></h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto leading-relaxed">
                    <?= __('Your account is currently under review. Please wait for the Admin to verify your KYC documents. You will be able to receive calls and view stats once approved.') ?>
                </p>
                <a href="/astrologer/details.php" class="inline-block bg-slate-900 text-white font-bold py-3 px-8 rounded-xl hover:bg-slate-800 transition shadow-lg">
                    <?= __('Update / View Profile') ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/web-astrologer-footer.php'; ?></div>

</body>
</html>