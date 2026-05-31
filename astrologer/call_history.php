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
            'Call History' => 'कॉल हिस्ट्री',
            'Live Sync' => 'लाइव सिंक',
            'From' => 'से',
            'To' => 'तक',
            'Apply Filters' => 'फ़िल्टर लागू करें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid'])) {
    header("Location: /login.php");
    exit;
}
$astro_id = intval($_SESSION['userid']);

$stmtRole = $conn->prepare("SELECT role_id, astrologer_name, email FROM users WHERE id=?");
$stmtRole->execute([$astro_id]);
$me = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$me || intval($me['role_id']) !== 1) {
    header("Location: /login.php");
    exit;
}

function safe($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$from = $_GET['from'] ?? "";
$to   = $_GET['to'] ?? "";

$where = "WHERE cs.astrologer_id = ?";
$params = [$astro_id];

if ($from && $to) {
    $where .= " AND DATE(cs.start_time) BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

$stmt = $conn->prepare("SELECT cs.*, u.name AS user_name FROM call_sessions cs JOIN users u ON u.id = cs.user_id $where ORDER BY cs.id DESC LIMIT 25");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <title><?= __('Call History') ?> | Fortune Pathway</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background:#fdfdfd; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
        .ringing-card { border: 2px solid #10b981 !important; background: #f0fdf4 !important; border-radius: 32px !important; transition: all 0.3s ease; }
        .pulse-live { animation: pulse-red 2s infinite; }
        @keyframes pulse-red { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        @media (min-width: 1024px) {
            .history-grid { display: grid; grid-template-columns: 320px 1fr; gap: 2.5rem; max-width: 1200px; margin: 0 auto; padding: 3rem 1.5rem; }
            .premium-card { background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 32px; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.04); padding: 2rem; transition: background 0.3s ease; }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        
        /* Premium Card & Inputs */
        body.dark-theme .premium-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme input[type="date"] { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
        body.dark-theme input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); } /* Make calendar icon visible */
        
        /* Text Color Overrides for Dark Mode */
        body.dark-theme .text-gray-900, 
        body.dark-theme .text-gray-800,
        body.dark-theme .text-black { color: #ffffff !important; }
        
        body.dark-theme .text-gray-500, 
        body.dark-theme .text-gray-400 { color: #d1d5db !important; }

        /* Sync Button area */
        body.dark-theme .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.2) !important; }
        
        /* Button */
        body.dark-theme button.bg-black { background-color: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
        body.dark-theme button.bg-black:hover { background-color: #4b5563 !important; }

        /* Dynamic list container background fix if needed */
        body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; }
    </style>
</head>
<body class="antialiased">

<header>
    <div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
    <div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>
</header>

<main class="history-grid">
    <aside class="px-4 lg:px-0">
        <div class="premium-card mt-4 lg:mt-0">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl lg:text-2xl font-bold tracking-tight text-gray-900"><?= __('Call History') ?></h2>
                <div class="flex items-center gap-2 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full pulse-live"></span>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest"><?= __('Live Sync') ?></span>
                </div>
            </div>
            <form method="GET" class="space-y-4">
                <div class="flex flex-row lg:flex-col gap-3">
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-400 block mb-1"><?= __('From') ?></label>
                        <input type="date" name="from" value="<?= safe($from) ?>" class="w-full text-sm p-3 border border-gray-100 rounded-2xl bg-gray-50 outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-bold text-gray-400 block mb-1"><?= __('To') ?></label>
                        <input type="date" name="to" value="<?= safe($to) ?>" class="w-full text-sm p-3 border border-gray-100 rounded-2xl bg-gray-50 outline-none">
                    </div>
                </div>
                <button class="w-full bg-black text-white py-4 rounded-2xl text-sm font-bold shadow-lg mt-2"><?= __('Apply Filters') ?></button>
            </form>
        </div>
    </aside>

    <section class="px-4 lg:px-0 mb-20">
        <!-- Assuming fetch_history_partial.php contains responsive HTML that will inherit global dark-theme classes -->
        <div id="dynamicCallList" class="space-y-4">
            <?php include 'fetch_history_partial.php'; ?>
        </div>
    </section>
</main>

<footer>
    <div class="lg:hidden"><?php include '../astrologer/footer_astrologer.php'; ?></div>
    <div class="hidden lg:block"><?php include '../astrologer/web-astrologer-footer.php'; ?></div>
</footer>

<script>
    // Apply dark theme initially if set
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }

    // Pure, silent background refresh
    async function refreshHistory() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('from')) return; // Stop auto-refresh if user is applying date filters

            const response = await fetch('fetch_history_partial.php');
            const html = await response.text();
            document.getElementById('dynamicCallList').innerHTML = html;
        } catch (err) { 
            console.error("Silent Sync Error:", err); 
        }
    }

    // Refresh the list seamlessly every 3 seconds
    setInterval(refreshHistory, 3000); 
</script>
</body>
</html>