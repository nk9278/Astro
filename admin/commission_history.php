<?php
session_start();
require '../db.php';

// Admin authentication
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header("Location: /auth/login.php");
    exit;
}

// -----------------------------
//  DATE FILTER LOGIC
// -----------------------------
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

$conditions = [];
$params = [];

if ($from) {
    $conditions[] = "DATE(cs.end_time) >= ?";
    $params[] = $from;
}
if ($to) {
    $conditions[] = "DATE(cs.end_time) <= ?";
    $params[] = $to;
}

$whereExtra = "";
if (!empty($conditions)) {
    $whereExtra = " AND " . implode(" AND ", $conditions);
}

// -----------------------------
//  Fetch Calls (Filtered)
// -----------------------------
$sql = "
    SELECT 
        cs.id,
        cs.duration_minutes,
        cs.cost,
        cs.price_per_minute,
        cs.commission_percent,
        cs.end_time,
        u.name AS user_name,
        u.email AS user_email,
        a.astrologer_name AS astro_name
    FROM call_sessions cs
    JOIN users u ON cs.user_id = u.id
    JOIN users a ON cs.astrologer_id = a.id
    WHERE cs.status = 'ended'
    $whereExtra
    ORDER BY cs.end_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../admin/admin_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
<title>Admin Commission Summary</title>

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-800 { color: #f3f4f6 !important; }
    body.dark-theme .text-gray-600 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    
    body.dark-theme .border-gray-200 { border-color: #374151 !important; }
    body.dark-theme .border-gray-100 { border-color: #4b5563 !important; }
    body.dark-theme .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #374151 !important; }
    
    body.dark-theme input[type="date"] { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
    body.dark-theme input[type="date"]:focus { border-color: #ef4444 !important; }
    
    /* Chrome/Safari Dark mode Date Picker Icon fix */
    body.dark-theme ::-webkit-calendar-picker-indicator { filter: invert(1); }
</style>
</head>

<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1000px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">Commission Summary</h1>

    <!-- DATE FILTER FORM -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 mb-8">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:flex-1">
                <label for="from" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">From Date</label>
                <input type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>" 
                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            </div>

            <div class="w-full sm:flex-1">
                <label for="to" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">To Date</label>
                <input type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>" 
                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            </div>

            <div class="w-full sm:w-auto flex gap-3 mt-2 sm:mt-0">
                <button type="submit" class="flex-1 sm:flex-none bg-red-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md active:scale-95">
                    Filter
                </button>
                
                <?php if ($from || $to): ?>
                    <a href="admin_commission.php" class="flex-1 sm:flex-none bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors text-center border border-gray-200">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- RESULTS GRID -->
    <div>
        <?php if (!empty($calls)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($calls as $call):
                $total_paid = (float)$call['cost'];
                $commission_percent = (int)$call['commission_percent'];
                $commission_amount = round(($total_paid * $commission_percent) / 100, 2);
                $astro_earning = round($total_paid - $commission_amount, 2);
            ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgba(0,0,0,0.06)] transition-shadow">
                    
                    <!-- Header: Astro Name & Date -->
                    <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-4">
                        <div class="overflow-hidden pr-3">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Astrologer</p>
                            <h3 class="font-black text-gray-900 text-lg truncate" title="<?= htmlspecialchars($call['astro_name']) ?>">
                                <?= htmlspecialchars($call['astro_name']) ?>
                            </h3>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-block bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg dark:bg-gray-700 dark:text-gray-300">
                                <?= date("d M Y", strtotime($call['end_time'])) ?><br>
                                <span class="font-medium"><?= date("h:i A", strtotime($call['end_time'])) ?></span>
                            </span>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Client User</p>
                        <p class="text-sm font-semibold text-gray-700 truncate">
                            <?= htmlspecialchars($call['user_name'] ?: $call['user_email']) ?>
                        </p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2 bg-gray-50 rounded-xl p-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Duration</p>
                            <p class="text-sm font-black text-gray-900"><?= (int)$call['duration_minutes'] ?> <span class="text-xs font-semibold text-gray-500">Min</span></p>
                        </div>
                        
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Price / Min</p>
                            <p class="text-sm font-black text-gray-900">₹<?= number_format((float)$call['price_per_minute'], 2) ?></p>
                        </div>
                        
                        <div class="col-span-2 h-[1px] bg-gray-200 dark:bg-gray-700 my-1"></div>

                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Total Paid</p>
                            <p class="text-base font-black text-gray-900">₹<?= number_format($total_paid, 2) ?></p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-0.5">Platform Cut (<?= $commission_percent ?>%)</p>
                            <p class="text-base font-black text-red-600">₹<?= number_format($commission_amount, 2) ?></p>
                        </div>

                    </div>

                    <!-- Final Earning Footer -->
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-600">Astro Earning:</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">₹<?= number_format($astro_earning, 2) ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border border-gray-200 border-dashed rounded-2xl p-12 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 dark:bg-gray-800">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-1">No Records Found</h3>
                <p class="text-sm font-medium text-gray-500">There are no commission records for the selected date range.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>