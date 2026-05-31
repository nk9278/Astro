<?php
session_start();
require '../db.php';

// Only admin
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

// Include Admin Header
include '../admin/admin_header.php';

// Fetch referred users list
$stmt = $conn->prepare("
    SELECT 
        u.id AS new_user_id,
        u.email AS new_user_email,
        u.created_at AS signup_date,
        u.referred_by AS referrer_id
    FROM users u
    WHERE u.referred_by IS NOT NULL
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process bonus lookup
$rows = [];
if (!empty($users)) {
    $wtStmt = $conn->prepare("
        SELECT id, user_id, amount, description, created_at
        FROM wallet_transactions
        WHERE user_id = ? AND type = 'referral_bonus'
        AND created_at BETWEEN DATE_SUB(?, INTERVAL 10 MINUTE) AND DATE_ADD(?, INTERVAL 24 HOUR)
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) ASC
        LIMIT 1
    ");

    foreach ($users as $u) {
        $refEmail = null;
        if (!empty($u['referrer_id'])) {
            $q = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
            $q->execute([$u['referrer_id']]);
            $refRow = $q->fetch(PDO::FETCH_ASSOC);
            $refEmail = $refRow['email'] ?? null;
        }

        $bonus_amount = null;
        $bonus_date = null;

        if (!empty($u['referrer_id'])) {
            $wtStmt->execute([$u['referrer_id'], $u['signup_date'], $u['signup_date'], $u['signup_date']]);
            $tx = $wtStmt->fetch(PDO::FETCH_ASSOC);

            if ($tx) {
                $bonus_amount = $tx['amount'];
                $bonus_date = $tx['created_at'];
            }
        }

        $rows[] = [
            'new_user_email' => $u['new_user_email'],
            'signup_date' => $u['signup_date'],
            'referrer_email' => $refEmail,
            'bonus_amount' => $bonus_amount,
            'bonus_date' => $bonus_date
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Referral Signup List - Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }

    /* Mobile Responsive Table (Card View) */
    @media (max-width: 768px) {
        table thead { display: none; }
        table, tbody, tr, td { display: block; width: 100%; }
        tr {
            background: #ffffff; border: 1px solid #f3f4f6; border-radius: 16px;
            padding: 16px; margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        td {
            border: none !important; padding: 8px 0 !important; text-align: left !important;
            display: flex; flex-direction: column; gap: 4px; align-items: flex-start !important;
            border-bottom: 1px dashed #e5e7eb !important;
        }
        td:last-child { border-bottom: none !important; }
        td::before {
            content: attr(data-label); font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em;
        }
    }

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme tr { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme td[data-label] { border-bottom-color: #4b5563 !important; }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-8">
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="mb-8 border-b border-gray-200 pb-6 dark:border-gray-700">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Referral Signup List</h1>
        <p class="text-gray-500 font-medium mt-1">Track users who registered via referral links and their bonuses.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden overflow-x-auto p-1 sm:p-0">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">New User Email</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Referrer Email</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Signup Date</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-center">Bonus (₹)</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-right">Credited On</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $row): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 border-b border-gray-100 font-bold text-gray-900" data-label="New User Email">
                            <?= htmlspecialchars($row['new_user_email']) ?>
                        </td>
                        <td class="p-4 border-b border-gray-100 font-semibold text-gray-700" data-label="Referrer Email">
                            <?= $row['referrer_email'] ? htmlspecialchars($row['referrer_email']) : '<span class="text-gray-400">—</span>' ?>
                        </td>
                        <td class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600" data-label="Signup Date">
                            <?= date('d M Y, h:i A', strtotime($row['signup_date'])) ?>
                        </td>
                        <td class="p-4 border-b border-gray-100 font-black text-emerald-600 md:text-center text-lg dark:text-emerald-400" data-label="Bonus Amount (₹)">
                            <?= $row['bonus_amount'] !== null ? '₹' . number_format($row['bonus_amount'], 2) : '<span class="text-gray-400 text-sm font-bold">—</span>' ?>
                        </td>
                        <td class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600 md:text-right" data-label="Bonus Credited On">
                            <?= $row['bonus_date'] ? date('d M Y, h:i A', strtotime($row['bonus_date'])) : '<span class="text-gray-400">—</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 dark:bg-gray-800">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-gray-900 mb-1">No Signups Yet</h3>
                        <p class="text-sm font-medium text-gray-500">There are currently no users registered via referral links.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>