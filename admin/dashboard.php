<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

/* ============================
   HARDENED SECURITY & ERROR HANDLING
============================ */
$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING));

include '../admin/admin_header.php';

/* ------- Original Stats ------- */
$total_users         = $conn->query("SELECT COUNT(*) FROM users WHERE role_id=2")->fetchColumn();
$total_astrologers   = $conn->query("SELECT COUNT(*) FROM users WHERE role_id=1")->fetchColumn();
$online_astrologers  = $conn->query("SELECT COUNT(*) FROM users WHERE role_id=1 AND online_status=1")->fetchColumn();
$offline_astrologers = $conn->query("SELECT COUNT(*) FROM users WHERE role_id=1 AND online_status=0")->fetchColumn();
$pending_astrologers = $conn->query("SELECT COUNT(*) FROM users WHERE role_id=1 AND approval_status='pending'")->fetchColumn();
$total_products      = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_inquiries     = $conn->query("SELECT COUNT(*) FROM product_inquiries")->fetchColumn();

/* ------- NEW: E-Commerce Stats ------- */
$total_orders = $conn->query("SELECT COUNT(*) FROM product_orders WHERE payment_status='successful'")->fetchColumn();
$total_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM product_orders WHERE payment_status='successful'")->fetchColumn();

$stmt = $conn->query("SELECT commission_percent FROM admin_settings WHERE id=1");
$current_commission = $stmt->fetchColumn();
if ($current_commission === false) { $current_commission = 30; } // default
?>

<?php include '../assets/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard - The Fortune Pathway</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<link rel="stylesheet" href="/assets/css/header.css" />
<link rel="stylesheet" href="/assets/css/footer.css" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #f4f7f6; color: #1a1a1a; transition: background 0.3s ease; }
    
    .glass-card {
        background: #ffffff; border: 1px solid #eaeaea; border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: all 0.3s ease;
    }
    
    .action-card {
        display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem;
        background: #ffffff; border: 1px solid #eaeaea; border-radius: 1rem;
        text-decoration: none; color: #1a1a1a; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative; overflow: hidden;
    }
    .action-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.06); border-color: #d1d5db; }
    
    .icon-box {
        width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center;
        justify-content: center; flex-shrink: 0; background: #f8fafc;
    }
    
    /* Dark Mode Overrides */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .glass-card, body.dark-theme .action-card { background: #1f2937 !important; border-color: #374151 !important; color: #f9fafb !important; }
    body.dark-theme .action-card:hover { border-color: #4b5563 !important; box-shadow: 0 12px 25px rgba(0,0,0,0.3); }
    body.dark-theme .icon-box { background: #374151 !important; }
    body.dark-theme .text-gray-900, body.dark-theme .text-gray-800 { color: #f9fafb !important; }
    body.dark-theme .text-gray-600, body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-100 { background: #374151 !important; color: #e5e7eb !important; }
</style>
</head>
<body class="pb-10">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight">Admin Control Panel</h1>
            <p class="text-gray-500 font-medium mt-1">Manage users, e-commerce sales, and platform settings.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold uppercase tracking-wider dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Online: <?= (int)$online_astrologers ?>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-100 text-rose-800 border border-rose-200 rounded-lg text-xs font-bold uppercase tracking-wider dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-400">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Offline: <?= (int)$offline_astrologers ?>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-800 border border-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                Commission: <?= (int)$current_commission ?>%
            </span>
        </div>
    </div>

    <!-- KPIs / Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-10">
        <!-- Sales Revenue (NEW) -->
        <div class="glass-card p-5 border-l-4 border-l-emerald-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Revenue</p>
            <p class="text-2xl font-black text-emerald-600">₹<?= number_format((float)$total_revenue, 0) ?></p>
        </div>
        <!-- Product Orders (NEW) -->
        <div class="glass-card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Product Orders</p>
            <p class="text-2xl font-black text-gray-900"><?= number_format((int)$total_orders) ?></p>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Users</p>
            <p class="text-2xl font-black text-gray-900"><?= number_format((int)$total_users) ?></p>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Astrologers</p>
            <p class="text-2xl font-black text-gray-900"><?= number_format((int)$total_astrologers) ?></p>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Products</p>
            <p class="text-2xl font-black text-gray-900"><?= number_format((int)$total_products) ?></p>
        </div>
        <div class="glass-card p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Inquiries</p>
            <p class="text-2xl font-black text-gray-900"><?= number_format((int)$total_inquiries) ?></p>
        </div>
    </div>

    <!-- MAIN NAVIGATION GRID -->
    
    <a href="/admin/manage_users.php" class="action-card border-green-200 hover:border-green-400 dark:border-green-900">
            <div class="icon-box text-green-600 bg-green-50 dark:bg-green-900/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-green-700 dark:text-green-400">Manage Users</h4>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Suspend & remove accounts</p>
            </div>
        </a>
    
    <!-- 1. Astrologer & Approvals -->
    <h3 class="text-lg font-black text-gray-900 mb-4 border-b border-gray-200 pb-2 dark:border-gray-700">Service & Approvals</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-10">
        
        <!-- Pending Approvals Highlighted -->
        <a href="/admin/kyc_approvals.php" class="action-card" style="border-color:#f59e0b; background:<?= (int)$pending_astrologers > 0 ? '#fffbeb' : '' ?>;">
            <div class="icon-box text-amber-500 bg-amber-100 dark:bg-amber-900/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 <?= (int)$pending_astrologers > 0 ? 'text-amber-700 dark:text-amber-500' : '' ?>">Pending Approvals</h4>
                <p class="text-xs font-medium text-gray-500 mt-0.5"><?= (int)$pending_astrologers ?> requests awaiting</p>
            </div>
        </a>

        <a href="/admin/astrologers.php" class="action-card">
            <div class="icon-box text-blue-500 bg-blue-50 dark:bg-blue-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Manage Astrologers</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Profiles & Commissions</p></div>
        </a>

        <a href="/admin/commission_history.php" class="action-card">
            <div class="icon-box text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Commission Logs</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Call earnings history</p></div>
        </a>

        <a href="/admin/wallet.php" class="action-card">
            <div class="icon-box text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">User Wallets</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Manage credits & funds</p></div>
        </a>
    </div>

    <!-- 2. E-Commerce Section -->
    <h3 class="text-lg font-black text-gray-900 mb-4 border-b border-gray-200 pb-2 dark:border-gray-700">E-Commerce & Shop</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-10">
        
        <!-- NEW SALES DASHBOARD BUTTON -->
        <a href="/admin/sales.php" class="action-card border-blue-200 hover:border-blue-400 dark:border-blue-900">
            <div class="icon-box text-blue-600 bg-blue-50 dark:bg-blue-900/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div><h4 class="font-bold text-blue-700 dark:text-blue-400">Sales Dashboard</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Revenue & performance</p></div>
        </a>

        <!-- NEW PRODUCT ORDERS BUTTON -->
        <a href="/admin/product_orders.php" class="action-card border-emerald-200 hover:border-emerald-400 dark:border-emerald-900">
            <div class="icon-box text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div><h4 class="font-bold text-emerald-700 dark:text-emerald-400">Product Orders</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Track & Ship sales</p></div>
        </a>

        <a href="/admin/products_list.php" class="action-card">
            <div class="icon-box text-sky-500 bg-sky-50 dark:bg-sky-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Manage Products</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Store catalog inventory</p></div>
        </a>

        <a href="/admin/product_inquiries.php" class="action-card">
            <div class="icon-box text-purple-500 bg-purple-50 dark:bg-purple-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Product Inquiries</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Customer questions</p></div>
        </a>
    </div>

    <!-- 3. Marketing & Settings -->
    <h3 class="text-lg font-black text-gray-900 mb-4 border-b border-gray-200 pb-2 dark:border-gray-700">Marketing & Analytics</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-10">
        
        <a href="/admin/referral_settings.php" class="action-card">
            <div class="icon-box text-pink-500 bg-pink-50 dark:bg-pink-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Referral Settings</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Control invite rewards</p></div>
        </a>

        <a href="/admin/registration_bonus.php" class="action-card">
            <div class="icon-box text-orange-500 bg-orange-50 dark:bg-orange-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Registration Bonus</h4><p class="text-xs font-medium text-gray-500 mt-0.5">New user promos</p></div>
        </a>

        <a href="/admin/referral_signup_list.php" class="action-card">
            <div class="icon-box text-teal-500 bg-teal-50 dark:bg-teal-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Signup Lists</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Track referred users</p></div>
        </a>

        <a href="/admin/banner_upload.php" class="action-card">
            <div class="icon-box text-cyan-500 bg-cyan-50 dark:bg-cyan-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Upload Banners</h4><p class="text-xs font-medium text-gray-500 mt-0.5">App/Web slider media</p></div>
        </a>
    </div>

    <!-- 4. System -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
        <a href="/admin/reports.php" class="action-card">
            <div class="icon-box text-gray-600 bg-gray-50 dark:bg-gray-800"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
            <div><h4 class="font-bold text-gray-900">Full System Reports</h4><p class="text-xs font-medium text-gray-500 mt-0.5">Generate and view platform analytics</p></div>
        </a>

        <a href="/logout.php" class="action-card border-red-100 hover:border-red-300 dark:border-red-900/30">
            <div class="icon-box text-red-500 bg-red-50 dark:bg-red-900/30"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></div>
            <div><h4 class="font-bold text-red-600">Logout</h4><p class="text-xs font-medium text-red-400 mt-0.5">Securely end session</p></div>
        </a>
    </div>

</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>