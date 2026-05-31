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

// Fetch specific order details along with product and user data
$sql = "SELECT po.*, p.name as product_name, p.price as base_price, u.email as user_email 
        FROM product_orders po 
        JOIN products p ON po.product_id = p.id 
        JOIN users u ON po.user_id = u.id 
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
    <title>Invoice #<?= $order['id'] ?> - The Fortune Pathway</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #f3f4f6; color: #111827; }
        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        
        /* Print Specific Styles to ensure it fits perfectly on an A4 page */
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; }
            .invoice-box { box-shadow: none; margin: 0 auto; padding: 20px; border-radius: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Print Action Bar (Hidden when actually printing) -->
    <div class="max-w-[800px] mx-auto mt-8 flex justify-end px-4 no-print">
        <button onclick="window.print()" class="px-6 py-2 bg-black text-white font-bold rounded-lg shadow hover:bg-gray-800 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Invoice
        </button>
    </div>

    <div class="invoice-box border border-gray-100">
        
        <!-- Header: Logo & Invoice Details -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-8 mb-8">
            <div class="flex flex-col">
                <img src="/assets/images/FP%20Logo.png" alt="The Fortune Pathway" class="w-24 h-24 object-contain mb-2 rounded-full border border-gray-100 p-1">
                <h1 class="text-3xl font-black tracking-tight text-gray-900 mt-2">INVOICE</h1>
                <p class="text-gray-500 font-semibold mt-1">Order ID: #<?= htmlspecialchars($order['id']) ?></p>
            </div>
            
            <div class="text-right text-sm">
                <h2 class="text-xl font-bold text-gray-900">The Fortune Pathway</h2>
                <p class="text-gray-500 font-medium text-xs uppercase tracking-widest mt-1 mb-2">Operated by 2nd Code</p>
                <p class="text-gray-600 mt-2">Lucknow, Uttar Pradesh</p>
                <p class="text-gray-600">India</p>
                <p class="text-gray-600 mt-3 font-semibold">info@thefortunepathway.com</p>
                <p class="text-gray-600 font-semibold">+91 93363 01113</p>
            </div>
        </div>

        <!-- Billing & Shipping Information -->
        <div class="flex justify-between mb-10">
            <div class="w-1/2 pr-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Ship To</p>
                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($order['shipping_name']) ?></h3>
                <p class="text-gray-700 mt-1 leading-relaxed max-w-[280px]">
                    <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                </p>
                <p class="text-gray-700 font-semibold mt-1">PIN: <?= htmlspecialchars($order['shipping_pincode']) ?></p>
                
                <div class="mt-4 text-sm">
                    <p class="text-gray-600"><span class="font-semibold text-gray-800">Phone:</span> <?= htmlspecialchars($order['shipping_phone']) ?></p>
                    <p class="text-gray-600"><span class="font-semibold text-gray-800">Email:</span> <?= htmlspecialchars($order['user_email']) ?></p>
                </div>
            </div>
            
            <div class="w-1/2 pl-4 text-right">
                <div class="mb-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Invoice Date</p>
                    <p class="font-semibold text-gray-800"><?= date('F d, Y', strtotime($order['created_at'])) ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Payment Status</p>
                    <p class="font-bold text-emerald-600 uppercase"><?= htmlspecialchars($order['payment_status']) ?></p>
                </div>
                <?php if(!empty($order['razorpay_payment_id'])): ?>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Transaction ID</p>
                    <p class="font-mono text-sm text-gray-600"><?= htmlspecialchars($order['razorpay_payment_id']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items Table -->
        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr>
                    <th class="py-3 border-b-2 border-gray-900 font-bold text-gray-900 uppercase tracking-wider text-sm">Description</th>
                    <th class="py-3 border-b-2 border-gray-900 font-bold text-gray-900 uppercase tracking-wider text-sm text-center">Qty</th>
                    <th class="py-3 border-b-2 border-gray-900 font-bold text-gray-900 uppercase tracking-wider text-sm text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="py-4 border-b border-gray-200">
                        <p class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($order['product_name']) ?></p>
                        <p class="text-xs text-gray-500 mt-1">Spiritual / Astrological Product</p>
                    </td>
                    <td class="py-4 border-b border-gray-200 text-center font-medium text-gray-800">1</td>
                    <td class="py-4 border-b border-gray-200 text-right font-black text-gray-900 text-lg">
                        ₹<?= number_format((float)$order['amount'], 2) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-12">
            <div class="w-1/2">
                <div class="flex justify-between py-2 text-gray-600">
                    <span class="font-medium">Subtotal</span>
                    <span class="font-semibold text-gray-800">₹<?= number_format((float)$order['amount'], 2) ?></span>
                </div>
                <div class="flex justify-between py-2 text-gray-600 border-b border-gray-200">
                    <span class="font-medium">Shipping & Taxes</span>
                    <span class="font-semibold text-gray-800">Included</span>
                </div>
                <div class="flex justify-between py-4">
                    <span class="font-black text-xl text-gray-900">Total Paid</span>
                    <span class="font-black text-2xl text-emerald-600">₹<?= number_format((float)$order['amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center pt-8 border-t border-gray-200 text-gray-500 text-sm">
            <p class="font-semibold text-gray-700 mb-1">Thank you for your purchase!</p>
            <p>This is a computer-generated invoice and does not require a physical signature.</p>
            <p class="mt-2 text-xs">For support, please contact us at info@thefortunepathway.com</p>
        </div>

    </div>
</body>
</html>