<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

// Fetch all orders joining with users and products table
$sql = "SELECT po.*, p.name as product_name, p.image as product_image, u.email as user_email 
        FROM product_orders po 
        JOIN products p ON po.product_id = p.id 
        JOIN users u ON po.user_id = u.id 
        ORDER BY po.id DESC";

$stmt = $conn->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../admin/admin_header.php';
include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Product Orders - Fortune Parth Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #f4f7f6; color: #1a1a1a; }
    
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
    .status-successful { background: #d1fae5; color: #047857; }
    .status-pending { background: #fef3c7; color: #b45309; }
    .status-failed { background: #fee2e2; color: #b91c1c; }

    /* Dark Theme */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme .text-gray-900 { color: #fff !important; }
</style>
</head>
<body class="pb-10">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 mt-8">
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-black mb-4 inline-block transition-colors">&larr; Back to Dashboard</a>
    
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">E-Commerce Orders</h1>
        <a href="/admin/sales.php" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            View Sales Report
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700">Order ID</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700">Product</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700">Customer Info</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700">Shipping Address</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700">Amount & Status</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 border-b border-gray-100 font-bold text-gray-900">
                                #<?= $order['id'] ?>
                                <div class="text-xs text-gray-400 mt-1 font-medium"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td class="p-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                        <img src="/uploads/products/<?= htmlspecialchars($order['product_image']) ?>" class="w-full h-full object-contain p-1" alt="Product">
                                    </div>
                                    <span class="font-bold text-sm max-w-[150px] truncate"><?= htmlspecialchars($order['product_name']) ?></span>
                                </div>
                            </td>
                            <td class="p-4 border-b border-gray-100 text-sm">
                                <p class="font-bold text-gray-900"><?= htmlspecialchars($order['shipping_name']) ?></p>
                                <p class="text-gray-500"><?= htmlspecialchars($order['shipping_phone']) ?></p>
                                <p class="text-gray-400 text-xs mt-0.5"><?= htmlspecialchars($order['user_email']) ?></p>
                            </td>
                            <td class="p-4 border-b border-gray-100 text-sm max-w-[250px]">
                                <p class="text-gray-700 leading-tight"><?= htmlspecialchars($order['shipping_address']) ?></p>
                                <p class="font-bold mt-1 text-gray-900">PIN: <?= htmlspecialchars($order['shipping_pincode']) ?></p>
                            </td>
                            <td class="p-4 border-b border-gray-100">
                                <p class="font-black text-lg text-gray-900 mb-1">₹<?= number_format((float)$order['amount'], 0) ?></p>
                                <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                    <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            </td>
                            <td class="p-4 border-b border-gray-100">
                                <?php if(strtolower($order['payment_status']) === 'successful'): ?>
                                    <div class="flex flex-col gap-2 items-center">
                                        <a href="/admin/print_invoice.php?id=<?= $order['id'] ?>" target="_blank" class="w-full inline-flex items-center justify-center px-3 py-2 bg-white border border-gray-200 text-gray-900 text-xs font-bold rounded-lg hover:bg-gray-50 transition-colors shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Invoice
                                        </a>
                                        <a href="/admin/print_shipping_label.php?id=<?= $order['id'] ?>" target="_blank" class="w-full inline-flex items-center justify-center px-3 py-2 bg-black text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm dark:bg-emerald-600 dark:hover:bg-emerald-700">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            Label
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <span class="text-xs text-gray-400 font-semibold italic">N/A</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="p-10 text-center text-gray-500 font-bold">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>