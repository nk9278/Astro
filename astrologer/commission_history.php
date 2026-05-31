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
            'Reports' => 'रिपोर्ट्स',
            'Today\'s Income' => 'आज की आय',
            'Today\'s Comm.' => 'आज का कमीशन',
            'Period Net' => 'अवधि का शुद्ध',
            'Period Comm.' => 'अवधि का कमीशन',
            'From Date' => 'प्रारंभ तिथि',
            'To Date' => 'अंतिम तिथि',
            'Apply Filter' => 'फ़िल्टर लागू करें',
            'Your Share' => 'आपका हिस्सा',
            'Duration' => 'अवधि',
            'Rate' => 'दर',
            'Total Charged' => 'कुल शुल्क लिया गया',
            'Platform Fee' => 'प्लेटफ़ॉर्म शुल्क',
            'No sessions found for the selected dates.' => 'चयनित तिथियों के लिए कोई सत्र नहीं मिला।'
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

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header("Location: /login.php");
    exit;
}

$astro_id = (int)$_SESSION['userid'];

// Date Filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default: Start of month
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

/* ---------- 1. Fetch Unified Today's Specific Stats ---------- */
$today = date('Y-m-d');
$todaySql = "
    SELECT 
        COALESCE(SUM(cost), 0) AS gross,
        COALESCE(SUM((cost * commission_percent) / 100), 0) AS platform_fee
    FROM (
        SELECT cost, commission_percent, end_time, astrologer_id, status FROM call_sessions
        UNION ALL
        SELECT cost, commission_percent, end_time, astrologer_id, status FROM chat_sessions
    ) AS combined
    WHERE astrologer_id = ? AND status = 'ended' AND DATE(end_time) = ?
";
$stmtToday = $conn->prepare($todaySql);
$stmtToday->execute([$astro_id, $today]);
$todayStats = $stmtToday->fetch(PDO::FETCH_ASSOC);
$todayNet = $todayStats['gross'] - $todayStats['platform_fee'];

/* ---------- 2. Fetch Unified Filtered Records ---------- */
$stmt = $conn->prepare("
    SELECT * FROM (
        SELECT cs.id, cs.status, cs.start_time, cs.end_time, cs.duration_minutes, u.price_per_minute, cs.cost, cs.commission_percent, u_client.name AS user_name, 'Call' AS session_type
        FROM call_sessions cs
        JOIN users u_client ON cs.user_id = u_client.id
        JOIN users u ON cs.astrologer_id = u.id
        WHERE cs.astrologer_id = ? AND cs.status = 'ended' AND DATE(cs.end_time) BETWEEN ? AND ?
        
        UNION ALL
        
        SELECT ch.id, ch.status, ch.start_time, ch.end_time, ch.duration_minutes, u.price_per_minute, ch.cost, ch.commission_percent, u_client.name AS user_name, 'Chat' AS session_type
        FROM chat_sessions ch
        JOIN users u_client ON ch.user_id = u_client.id
        JOIN users u ON ch.astrologer_id = u.id
        WHERE ch.astrologer_id = ? AND ch.status = 'ended' AND DATE(ch.end_time) BETWEEN ? AND ?
    ) AS combined
    ORDER BY end_time DESC
");
$stmt->execute([$astro_id, $start_date, $end_date, $astro_id, $start_date, $end_date]);
$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------- 3. Calculate Accumulated Totals ---------- */
$total_period_net = 0;
$total_period_comm = 0;
foreach($calls as $c) {
    $fee = ($c['cost'] * $c['commission_percent']) / 100;
    $total_period_comm += $fee;
    $total_period_net += ($c['cost'] - $fee);
}

// CSV Export Logic
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Commission_Report_'.$start_date.'_to_'.$end_date.'.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Type', 'User', 'Date', 'Duration', 'Rate', 'Total Paid', 'Platform Fee', 'Net Earning']);
    foreach ($calls as $c) {
        $f = ($c['cost'] * $c['commission_percent']) / 100;
        fputcsv($output, [$c['id'], $c['session_type'], htmlspecialchars($c['user_name'], ENT_QUOTES, 'UTF-8'), $c['end_time'], $c['duration_minutes'], $c['price_per_minute'], $c['cost'], $f, ($c['cost'] - $f)]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title><?= __('Reports') ?> | 2nd Code</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { --base-txt: 13px; --label-txt: 10px; --heading-txt: 22px; }
        @media (min-width: 1024px) { :root { --base-txt: 15px; --label-txt: 12px; --heading-txt: 28px; } }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; font-size: var(--base-txt); padding-bottom: 110px; transition: background 0.3s ease; }
        @media (min-width: 1024px) { body { padding-bottom: 0; } }
        
        .glass-card { background: white; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: background 0.3s ease; }
        .label-text { font-size: var(--label-txt); font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
        .input-field { padding: 8px 12px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; }

        /* DARK MODE STYLES */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .text-slate-900, body.dark-theme .text-slate-800, body.dark-theme .text-slate-700 { color: #ffffff !important; }
        body.dark-theme .text-slate-500, body.dark-theme .text-slate-400 { color: #d1d5db !important; }
        body.dark-theme input[type="date"] { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
        body.dark-theme input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }
        body.dark-theme .bg-slate-50 { background-color: #374151 !important; }
        body.dark-theme .border-slate-50 { border-color: #4b5563 !important; }
        body.dark-theme button.bg-slate-900 { background-color: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
        body.dark-theme button.bg-slate-900:hover { background-color: #4b5563 !important; }
        body.dark-theme .bg-indigo-50 { background-color: rgba(99, 102, 241, 0.1) !important; border-color: rgba(99, 102, 241, 0.2) !important; }
    </style>
</head>

<body class="antialiased">

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<div class="max-w-[1100px] mx-auto px-4 py-6 lg:py-10">
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-card p-5 border-l-4 border-l-emerald-500">
            <span class="label-text"><?= __('Today\'s Income') ?></span>
            <p class="text-xl lg:text-2xl font-800 text-emerald-600 mt-1">₹<?= number_format((float)$todayNet, 2) ?></p>
        </div>
        <div class="glass-card p-5">
            <span class="label-text"><?= __('Today\'s Comm.') ?></span>
            <p class="text-xl lg:text-2xl font-800 text-slate-800 mt-1">₹<?= number_format((float)$todayStats['platform_fee'], 2) ?></p>
        </div>
        <div class="glass-card p-5 border-l-4 border-l-indigo-500">
            <span class="label-text"><?= __('Period Net') ?></span>
            <p class="text-xl lg:text-2xl font-800 text-indigo-600 mt-1">₹<?= number_format((float)$total_period_net, 2) ?></p>
        </div>
        <div class="glass-card p-5">
            <span class="label-text"><?= __('Period Comm.') ?></span>
            <p class="text-xl lg:text-2xl font-800 text-slate-800 mt-1">₹<?= number_format((float)$total_period_comm, 2) ?></p>
        </div>
    </div>

    <div class="glass-card p-5 mb-8">
        <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-4">
            <div class="flex-1 grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <span class="label-text"><?= __('From Date') ?></span>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ?>" class="input-field">
                </div>
                <div class="flex flex-col gap-1">
                    <span class="label-text"><?= __('To Date') ?></span>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ?>" class="input-field">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-700 text-sm hover:bg-black transition flex-1 lg:flex-none">
                    <?= __('Apply Filter') ?>
                </button>
                <a href="?<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '', ENT_QUOTES, 'UTF-8') ?>&export=1" class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-4 py-2.5 rounded-xl font-700 text-sm hover:bg-indigo-100 transition flex items-center justify-center">
                    <span class="material-symbols-outlined !text-lg">download</span>
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <?php if (!empty($calls)): ?>
        <?php foreach ($calls as $call): 
            $t_paid = (float)$call['cost'];
            $c_amt = ($t_paid * $call['commission_percent']) / 100;
            $n_earn = $t_paid - $c_amt;
            $isChat = $call['session_type'] === 'Chat';
        ?>
        <div class="glass-card p-5 hover:border-indigo-200 transition-colors">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-800 text-slate-900 text-lg leading-tight flex items-center gap-2">
                        <?= htmlspecialchars($call['user_name'] ?: 'Guest', ENT_QUOTES, 'UTF-8') ?>
                        <?php if($isChat): ?>
                            <i class="fas fa-comment-dots text-blue-500 text-sm"></i>
                        <?php else: ?>
                            <i class="fas fa-phone-alt text-emerald-500 text-sm"></i>
                        <?php endif; ?>
                    </h3>
                    <p class="text-slate-400 font-bold text-[10px] mt-1"><?= date("d M Y • h:i A", strtotime($call['end_time'])) ?></p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-800 text-emerald-500 uppercase tracking-tighter"><?= __('Your Share') ?></span>
                    <p class="text-xl font-900 text-emerald-600">₹<?= number_format($n_earn, 2) ?></p>
                </div>
            </div>

            <div class="flex gap-2 mb-4">
                <div class="flex-1 bg-slate-50 p-2 rounded-lg text-center">
                    <span class="label-text block"><?= __('Duration') ?></span>
                    <span class="font-700 text-slate-700"><?= (int)$call['duration_minutes'] ?>m</span>
                </div>
                <div class="flex-1 bg-slate-50 p-2 rounded-lg text-center">
                    <span class="label-text block"><?= __('Rate') ?></span>
                    <span class="font-700 text-slate-700">₹<?= (float)$call['price_per_minute'] ?>/m</span>
                </div>
            </div>

            <div class="space-y-1.5 pt-3 border-t border-slate-50">
                <div class="flex justify-between text-[11px] font-600">
                    <span class="text-slate-400"><?= __('Total Charged') ?></span>
                    <span class="text-slate-700">₹<?= number_format($t_paid, 2) ?></span>
                </div>
                <div class="flex justify-between text-[11px] font-600">
                    <span class="text-slate-400"><?= __('Platform Fee') ?> (<?= (float)$call['commission_percent'] ?>%)</span>
                    <span class="text-red-400">- ₹<?= number_format($c_amt, 2) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full py-20 text-center glass-card">
            <span class="material-symbols-outlined text-slate-200 !text-6xl mb-2">event_busy</span>
            <p class="text-slate-400 font-medium"><?= __('No sessions found for the selected dates.') ?></p>
        </div>
    <?php endif; ?>
    </div>
</div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/footer_astrologer.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-footer.php"; ?></div>

</body>
</html>