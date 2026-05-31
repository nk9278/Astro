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
            'Earnings & Wallet' => 'कमाई और वॉलेट',
            'Current Balance' => 'वर्तमान बैलेंस',
            'Refresh Balance' => 'बैलेंस रीफ्रेश करें',
            'Earnings are automatically credited to your wallet after every successful session.' => 'प्रत्येक सफल सत्र के बाद कमाई स्वचालित रूप से आपके वॉलेट में जमा हो जाती है।',
            'Transaction History' => 'लेनदेन इतिहास',
            'Live Updates' => 'लाइव अपडेट',
            'User:' => 'उपयोगकर्ता:',
            'No transactions recorded yet.' => 'अभी तक कोई लेनदेन रिकॉर्ड नहीं किया गया है।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Set India timezone
date_default_timezone_set("Asia/Kolkata");

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = (int)$_SESSION['userid'];

// Fetch astrologer wallet balance
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=?");
$stmt->execute([$astro_id]);
$wallet_balance = $stmt->fetchColumn();

// Fetch astrologer transactions + user names
$stmtTx = $conn->prepare("
    SELECT 
        w.*,
        u.name AS user_name,
        u.astrologer_name AS astro_name_related
    FROM wallet_transactions w
    LEFT JOIN users u ON w.related_user_id = u.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmtTx->execute([$astro_id]);
$transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title><?= __('Earnings & Wallet') ?> | 2nd Code</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #1e293b; 
            transition: background 0.3s ease;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Glassmorphism card */
        .glass-card { 
            background: white; 
            border: 1px solid #f1f5f9; 
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); 
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        @media (max-width: 1024px) { body { padding-bottom: 120px; } }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .bg-white { background: #1f2937 !important; }
        
        body.dark-theme .text-slate-900, body.dark-theme .text-slate-800 { color: #ffffff !important; }
        body.dark-theme .text-slate-500, body.dark-theme .text-slate-400 { color: #d1d5db !important; }

        body.dark-theme .bg-indigo-50 { background-color: rgba(99, 102, 241, 0.15) !important; color: #a5b4fc !important; }
        body.dark-theme .text-indigo-600 { color: #a5b4fc !important; }
        
        body.dark-theme .bg-slate-900 { background-color: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
        body.dark-theme .bg-slate-900:hover { background-color: #4b5563 !important; }

        body.dark-theme .divide-slate-50 > :not([hidden]) ~ :not([hidden]) { border-color: #374151 !important; }
        body.dark-theme .border-slate-50 { border-color: #374151 !important; }
        body.dark-theme .hover\:bg-slate-50:hover { background-color: #374151 !important; }

        body.dark-theme .bg-red-50 { background-color: rgba(239, 68, 68, 0.15) !important; }
        body.dark-theme .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.15) !important; }
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

<div class="max-w-[1000px] mx-auto px-4 py-8">
    
    <div class="flex flex-col lg:flex-row gap-8">
        
        <aside class="w-full lg:w-1/3">
            <div class="glass-card p-8 text-center sticky top-6">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined !text-3xl">account_balance_wallet</span>
                </div>
                <h2 class="text-sm font-extrabold text-slate-400 uppercase tracking-widest mb-1"><?= __('Current Balance') ?></h2>
                <p class="text-4xl font-800 text-slate-900 mb-6">
                    ₹<?= number_format($wallet_balance, 2) ?>
                </p>
                
                <div class="space-y-3">
                    <button onclick="window.location.reload()" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-black transition">
                        <span class="material-symbols-outlined text-sm">refresh</span> <?= __('Refresh Balance') ?>
                    </button>
                    <p class="text-[10px] text-slate-400 font-medium px-4"><?= __('Earnings are automatically credited to your wallet after every successful session.') ?></p>
                </div>
            </div>
        </aside>

        <main class="flex-1">
            <div class="glass-card overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center bg-white sticky top-0 z-10">
                    <h3 class="font-800 text-slate-800 text-lg"><?= __('Transaction History') ?></h3>
                    <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full uppercase"><?= __('Live Updates') ?></span>
                </div>

                <div class="custom-scrollbar" style="max-height: 650px; overflow-y: auto;">
                    <?php if (!empty($transactions)): ?>
                        <div class="divide-y divide-slate-50">
                            <?php foreach ($transactions as $tx): 
                                $isDebit = ($tx['amount'] < 0);
                            ?>
                            <div class="p-5 flex items-start gap-4 hover:bg-slate-50 transition">
                                
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl <?= $isDebit ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-500' ?>">
                                    <span class="material-symbols-outlined">
                                        <?= htmlspecialchars($tx['icon'] ?? 'payments', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <div>
                                            <p class="font-800 text-slate-900 truncate">
                                                <?= ucfirst(str_replace('_',' ', htmlspecialchars($tx['type'], ENT_QUOTES, 'UTF-8'))) ?>
                                            </p>
                                            <p class="text-xs text-slate-500 font-medium line-clamp-1">
                                                <?= htmlspecialchars($tx['description'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-800 <?= $isDebit ? 'text-red-600' : 'text-emerald-600' ?>">
                                                <?= ($isDebit ? '-' : '+') . ' ₹' . number_format(abs((float)$tx['amount']), 2) ?>
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">
                                                <?= date('d M, h:i A', strtotime($tx['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <?php if (!empty($tx['user_name'])): ?>
                                        <div class="inline-flex items-center gap-1.5 mt-2 bg-indigo-50 px-2 py-1 rounded-md">
                                            <span class="material-symbols-outlined !text-sm text-indigo-500">person</span>
                                            <span class="text-[11px] text-indigo-600 font-bold"><?= __('User:') ?> <?= htmlspecialchars($tx['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="py-20 text-center">
                            <span class="material-symbols-outlined !text-5xl text-slate-200 mb-2">history_toggle_off</span>
                            <p class="text-slate-400 font-medium"><?= __('No transactions recorded yet.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>
</div>

<div class="lg:hidden"><?php include '../astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include '../astrologer/web-astrologer-footer.php'; ?></div>

</body>
</html>