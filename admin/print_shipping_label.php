<?php
session_start();
require '../db.php';

// Security check
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    die("Invalid Order ID.");
}

// Fetch specific order details
$sql = "SELECT po.*, p.name as product_name 
        FROM product_orders po 
        JOIN products p ON po.product_id = p.id 
        WHERE po.id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label #<?= $order['id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #f3f4f6; color: #000; }
        .label-box {
            width: 4in; /* Standard shipping label width */
            height: 6in; /* Standard shipping label height */
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid #000;
            box-sizing: border-box;
            position: relative;
        }
        
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .label-box { box-shadow: none; margin: 0; border: 2px solid #000; }
            .no-print { display: none !important; }
            @page { size: 4in 6in; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Print Action Bar -->
    <div class="max-w-[4in] mx-auto mt-4 flex justify-end no-print mb-4">
        <button onclick="window.print()" class="px-6 py-2 bg-black text-white font-bold rounded-lg hover:bg-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Label
        </button>
    </div>

    <!-- 4x6 Shipping Label -->
    <div class="label-box flex flex-col">
        
        <!-- FROM Section -->
        <div class="border-b-2 border-black pb-4 mb-4">
            <p class="text-[10px] font-bold uppercase tracking-wider mb-1">From:</p>
            <h2 class="text-sm font-black uppercase">The Fortune Pathway</h2>
            <p class="text-xs font-semibold">(Operated by 2nd Code)</p>
            <p class="text-xs mt-1">Lucknow, Uttar Pradesh</p>
            <p class="text-xs">India</p>
            <p class="text-xs mt-1">Ph: +91 93363 01113</p>
        </div>

        <!-- TO Section (Ship To) -->
        <div class="flex-grow">
            <p class="text-xs font-bold uppercase tracking-wider mb-2">Ship To:</p>
            <h1 class="text-2xl font-black uppercase leading-tight"><?= htmlspecialchars($order['shipping_name']) ?></h1>
            <p class="text-base mt-2 font-semibold max-w-[90%] leading-snug">
                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
            </p>
            <p class="text-xl font-black mt-3 border-2 border-black inline-block px-3 py-1">
                PIN: <?= htmlspecialchars($order['shipping_pincode']) ?>
            </p>
            <p class="text-sm font-bold mt-4">Phone: <?= htmlspecialchars($order['shipping_phone']) ?></p>
        </div>

        <!-- Order Meta & Barcode Placeholder -->
        <div class="border-t-2 border-black pt-4 mt-auto">
            <div class="flex justify-between items-end mb-3">
                <div>
                    <p class="text-[10px] font-bold uppercase">Order ID</p>
                    <p class="text-lg font-black">#<?= htmlspecialchars($order['id']) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase">Date</p>
                    <p class="text-xs font-bold"><?= date('d M Y', strtotime($order['created_at'])) ?></p>
                </div>
            </div>
            
            <p class="text-xs font-bold truncate mb-3">Item: <?= htmlspecialchars($order['product_name']) ?></p>

            <!-- Decorative Barcode block for Courier scanning context -->
            <div class="w-full h-12 bg-black flex items-center justify-center text-white text-xs font-mono overflow-hidden" style="background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 4px, #000 4px, #000 5px, #fff 5px, #fff 8px, #000 8px, #000 12px, #fff 12px, #fff 14px);">
            </div>
            <p class="text-center font-mono text-[10px] mt-1 tracking-widest">*<?= htmlspecialchars($order['id']) ?>-FP*</p>
        </div>

    </div>
</body>
</html>