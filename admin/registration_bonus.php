<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

// Include Admin Header
include '../admin/admin_header.php';

$message = '';
$message_type = '';

$stmt = $conn->query("SELECT registration_bonus_amount FROM admin_settings WHERE id=1");
$current_bonus = $stmt->fetchColumn() ?: 50;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_bonus = floatval($_POST['registration_bonus'] ?? 0);

    if ($new_bonus > 0) {
        $update = $conn->prepare("UPDATE admin_settings SET registration_bonus_amount = ? WHERE id=1");
        if ($update->execute([$new_bonus])) {
            $message = "Success: Registration bonus amount updated to ₹" . number_format($new_bonus, 2);
            $message_type = "success";
            $current_bonus = $new_bonus;
        } else {
            $message = "Error: Failed to update registration bonus.";
            $message_type = "error";
        }
    } else {
        $message = "Invalid: Enter a valid positive amount.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Registration Bonus Settings - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme input[type="number"] { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
    body.dark-theme input:focus { border-color: #ef4444 !important; }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[600px] mx-auto px-4 sm:px-6 mt-8">
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="mb-8 border-b border-gray-200 pb-6 dark:border-gray-700">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Registration Bonus</h1>
        <p class="text-gray-500 font-medium mt-1">Manage the sign-up bonus credited to new users upon registering.</p>
    </div>

    <!-- Alert Banner -->
    <?php if ($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold flex items-center justify-center gap-2 border <?= $message_type === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="registration_bonus" class="block text-sm font-bold text-gray-700 mb-2">Registration Bonus Amount (₹)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                    <input id="registration_bonus" name="registration_bonus" type="number" min="1" step="0.01" value="<?= htmlspecialchars($current_bonus) ?>" required
                           class="w-full pl-9 pr-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-lg outline-none focus:border-red-500 transition-all font-black text-gray-900" />
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-5 bg-gray-900 text-white rounded-xl font-black text-lg hover:bg-black transition-all shadow-lg active:scale-95 dark:bg-red-600 dark:hover:bg-red-700">
                    Update Bonus Amount
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>