<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

// 1. KPI Calculations (Successful Payments Only)
// Total Sales
$total_sales = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM product_orders WHERE payment_status = 'successful'")->fetchColumn();

// Today's Sales
$today_sales = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM product_orders WHERE payment_status = 'successful' AND DATE(created_at) = CURDATE()")->fetchColumn();

// Yesterday's Sales
$yesterday_sales = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM product_orders WHERE payment_status = 'successful' AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY")->fetchColumn();

// 2. Filter Logic (Daily vs Monthly)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'daily';

if ($filter === 'monthly') {
    $group_sql = "DATE_FORMAT(created_at, '%Y-%m')";
    $display_format = "F Y"; // e.g., May 2026
} else {
    $group_sql = "DATE(created_at)";
    $display_format = "d M Y"; // e.g., 04 May 2026
    $filter = 'daily'; // fallback safety
}

// Fetch Grouped Data
$sql = "SELECT 
            $group_sql as date_group, 
            COUNT(id) as total_orders, 
            SUM(amount) as total_revenue 
        FROM product_orders 
        WHERE payment_status = 'successful' 
        GROUP BY date_group 
        ORDER BY date_group DESC 
        LIMIT 50";

$stmt = $conn->query($sql);
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../admin/admin_header.php';
include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Sales Dashboard - Fortune Parth Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #f4f7f6; color: #1a1a1a; transition: background 0.3s ease; }
    
    .glass-card {
        background: #ffffff; border: 1px solid #eaeaea; border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    /* Dark Theme */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .glass-card, body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; color: #f9fafb !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme .text-gray-900 { color: #fff !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-100 { background-color: #374151 !important; color: #fff !important; border-color: #4b5563 !important; }
</style>
</head>
<body class="pb-10">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 mt-8">
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-black mb-4 inline-block transition-colors">&larr; Back to Dashboard</a>
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Sales Dashboard</h1>
            <p class="text-gray-500 font-medium mt-1">Track your e-commerce revenue and order volume.</p>
        </div>
    </div>

    <!-- KPIs / Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass-card p-6 border-l-4 border-l-emerald-500 relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-5 w-24 h-24 -mt-4 -mr-4">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Today's Sales</p>
            <p class="text-4xl font-black text-gray-900">₹<?= number_format((float)$today_sales, 0) ?></p>
        </div>
        
        <div class="glass-card p-6 border-l-4 border-l-blue-500">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Yesterday's Sales</p>
            <p class="text-4xl font-black text-gray-900">₹<?= number_format((float)$yesterday_sales, 0) ?></p>
        </div>
        
        <div class="glass-card p-6 border-l-4 border-l-purple-500">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Total All-Time Sales</p>
            <p class="text-4xl font-black text-gray-900">₹<?= number_format((float)$total_sales, 0) ?></p>
        </div>
    </div>

    <!-- Filter & Data Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-10">
        
        <!-- Toolbar -->
        <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
            <h2 class="text-xl font-bold text-gray-900">Sales Report</h2>
            <form method="GET" class="flex gap-2">
                <select name="filter" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-black focus:border-black block w-full p-2.5 font-semibold outline-none" onchange="this.form.submit()">
                    <option value="daily" <?= $filter == 'daily' ? 'selected' : '' ?>>Daily Sales</option>
                    <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>Monthly Sales</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-4 border-b border-gray-200 bg-white font-bold text-gray-700">Period</th>
                        <th class="p-4 border-b border-gray-200 bg-white font-bold text-gray-700 text-center">Successful Orders</th>
                        <th class="p-4 border-b border-gray-200 bg-white font-bold text-gray-700 text-right">Revenue Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sales_data) > 0): ?>
                        <?php foreach ($sales_data as $row): 
                            // Format the date label based on filter type
                            if($filter === 'monthly') {
                                $date_obj = DateTime::createFromFormat('Y-m', $row['date_group']);
                                $display_date = $date_obj ? $date_obj->format('F Y') : $row['date_group'];
                            } else {
                                $date_obj = DateTime::createFromFormat('Y-m-d', $row['date_group']);
                                $display_date = $date_obj ? $date_obj->format('d M Y') : $row['date_group'];
                            }
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 border-b border-gray-100 font-bold text-gray-900">
                                    <?= $display_date ?>
                                </td>
                                <td class="p-4 border-b border-gray-100 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-sm">
                                        <?= htmlspecialchars($row['total_orders']) ?> Orders
                                    </span>
                                </td>
                                <td class="p-4 border-b border-gray-100 text-right">
                                    <p class="font-black text-lg text-emerald-600">₹<?= number_format((float)$row['total_revenue'], 0) ?></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-10 text-center text-gray-500 font-bold">No sales data found for this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>