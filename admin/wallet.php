<?php
session_start();
require '../db.php';

// Set India timezone
date_default_timezone_set("Asia/Kolkata");

// Allow only admin
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

$admin_id = $_SESSION['userid'];

include '../admin/admin_header.php';

// 1) Fetch wallet balance stored in DB
$stmt = $conn->prepare("SELECT commission_balance FROM users WHERE id=?");
$stmt->execute([$admin_id]);
$commission_balance = $stmt->fetchColumn();

// 2) Fetch actual admin earnings from astrologers (per call)
$stmtCommTotal = $conn->query("SELECT SUM(cost * commission_percent / 100) AS total_commission FROM call_sessions WHERE status = 'ended'");
$total_admin_earnings = $stmtCommTotal->fetchColumn() ?: 0;

// 3) Fetch Wallet Transactions
$stmtTx = $conn->prepare("
    SELECT w.*, u.name AS user_name, u.astrologer_name AS astro_name
    FROM wallet_transactions w
    LEFT JOIN users u ON w.related_user_id = u.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmtTx->execute([$admin_id]);
$transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commission Wallet - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-800 { color: #f3f4f6 !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme .border-gray-100 { border-color: #374151 !important; }
</style>
</head>
<body class="pb-24">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1000px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Commission Wallet</h1>
        <p class="text-gray-500 font-medium mt-1">Track platform earnings and wallet history.</p>
    </div>

    <!-- Earnings Summary Card -->
    <div class="bg-white border border-gray-200 rounded-3xl p-8 text-center shadow-[0_8px_30px_rgba(0,0,0,0.04)] mb-10 relative overflow-hidden group">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-red-500 to-rose-600"></div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Admin Earnings (From Calls)</p>
        <h2 class="text-5xl sm:text-6xl font-black text-gray-900 tracking-tight">₹<?= number_format($total_admin_earnings, 2) ?></h2>
    </div>

    <!-- Transactions List -->
    <h3 class="text-xl font-black text-gray-900 mb-4 border-b border-gray-200 pb-2 dark:border-gray-700">Recent Transactions</h3>

    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden mb-6">
        <div class="max-h-[600px] overflow-y-auto p-2 sm:p-4">
            
            <?php if (!empty($transactions)): ?>
                <div class="flex flex-col gap-2">
                <?php foreach ($transactions as $tx): 
                    $isNeg = ($tx['amount'] < 0);
                    $amtText = ($isNeg ? '-' : '+') . '₹' . number_format(abs($tx['amount']), 2);
                ?>
                    <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100 dark:hover:bg-gray-800 dark:hover:border-gray-700">
                        
                        <!-- Icon -->
                        <div class="w-12 h-12 shrink-0 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center dark:bg-gray-700 dark:border-gray-600">
                            <span class="material-symbols-outlined text-gray-500 dark:text-gray-300">
                                <?= htmlspecialchars($tx['icon'] ?? 'savings') ?>
                            </span>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-900 text-sm sm:text-base truncate pr-2">
                                    <?= ucfirst(str_replace('_',' ', $tx['type'])) ?>
                                </h4>
                                <span class="font-black text-sm sm:text-base whitespace-nowrap <?= $isNeg ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                                    <?= $amtText ?>
                                </span>
                            </div>

                            <?php if (!empty($tx['description'])): ?>
                                <p class="text-xs sm:text-sm text-gray-500 font-medium mb-2 line-clamp-2"><?= htmlspecialchars($tx['description']) ?></p>
                            <?php endif; ?>

                            <!-- Meta Tags -->
                            <div class="flex flex-wrap gap-2 mb-2">
                                <?php if (!empty($tx['astro_name'])): ?>
                                    <span class="inline-block px-2.5 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg uppercase tracking-wider dark:bg-gray-700 dark:text-gray-300">
                                        Astro: <?= htmlspecialchars($tx['astro_name']) ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($tx['user_name'])): ?>
                                    <span class="inline-block px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg uppercase tracking-wider dark:bg-blue-900/30 dark:text-blue-400">
                                        User: <?= htmlspecialchars($tx['user_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                <?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 dark:bg-gray-800">
                        <span class="material-symbols-outlined text-3xl text-gray-400">receipt_long</span>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">No Transactions</h3>
                    <p class="text-sm font-medium text-gray-500">Your wallet history is currently empty.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>

</body>
</html>