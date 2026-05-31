<?php
// Ensure DB & passed user id exists
if (!isset($conn)) {
    die("DB connection missing before including recent-transactions.php");
}

if (!isset($tx_user_id)) {
    die("User ID missing in recent-transactions include");
}

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Recent Transactions' => 'हाल के लेनदेन',
            'No recent transactions found.' => 'कोई हालिया लेनदेन नहीं मिला।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Fetch ALL transactions (no limit)
$stmtTx = $conn->prepare("
    SELECT 
        id,
        type,
        amount,
        description,
        icon,
        created_at,
        related_user_id,
        stars
    FROM wallet_transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmtTx->execute([$tx_user_id]);
$transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Base overrides for custom dark theme consistency */
body.dark-theme .text-text-primary-light { color: #f9fafb !important; }
body.dark-theme .text-text-secondary-light { color: #d1d5db !important; }
body.dark-theme .bg-slate-100 { background-color: #374151 !important; }
body.dark-theme .border-separator-light { border-color: #4b5563 !important; }
</style>

<!-- Heading -->
<div class="px-4 pt-4">
    <h3 class="text-[22px] font-bold text-text-primary-light dark:text-text-primary-dark">
        <?= __('Recent Transactions') ?>
    </h3>
</div>

<!-- Scroll Container -->
<div class="flex flex-col px-4 pb-6" style="max-height: 450px; overflow-y: auto;">

<?php if (!empty($transactions)): ?>

    <?php foreach ($transactions as $tx): ?>
    
    <div class="flex items-center gap-4 border-b border-separator-light dark:border-separator-dark py-4">

        <!-- ICON -->
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
            <span class="material-symbols-outlined text-text-primary-light dark:text-text-primary-dark">
                <?= htmlspecialchars($tx['icon'] ?? 'payments', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <!-- TRANSACTION DETAILS -->
        <div class="flex-1">

            <div class="flex justify-between">
                <p class="font-semibold text-text-primary-light dark:text-text-primary-dark">
                    <?= ucfirst(str_replace('_', ' ', htmlspecialchars($tx['type'], ENT_QUOTES, 'UTF-8'))) ?>
                </p>

                <p class="font-bold 
                    <?= ($tx['amount'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400') ?>">
                    <?= ($tx['amount'] < 0 ? '-' : '+') . '₹' . number_format(abs((float)$tx['amount']), 2) ?>
                </p>
            </div>

            <!-- DESCRIPTION -->
            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">
                <?= htmlspecialchars($tx['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <!-- RATING STARS -->
            <?php if (!empty($tx['stars'])): ?>
                <div class="flex items-center pt-1">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <span class="material-symbols-outlined text-yellow-500"
                              style="font-variation-settings: 'FILL' <?= ($i < $tx['stars'] ? 1 : 0) ?>;">
                            star
                        </span>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

            <!-- DATE (IST TIMEZONE) -->
            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">
                <?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?>
            </p>

        </div>

    </div>

    <?php endforeach; ?>

<?php else: ?>

    <p class="text-center py-6 text-text-secondary-light dark:text-text-secondary-dark">
        <?= __('No recent transactions found.') ?>
    </p>

<?php endif; ?>

</div>