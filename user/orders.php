<?php
session_start();
require '../db.php';

// Security: Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: /auth/login.php');
    exit;
}

$user_id = $_SESSION['userid'];

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [], // Default fallback
        'hi' => [
            'My Orders' => 'मेरे ऑर्डर',
            'Your Spiritual Purchases' => 'आपकी आध्यात्मिक खरीदारी',
            'View and manage your recent orders, shipping details, and invoices.' => 'अपने हाल के ऑर्डर, शिपिंग विवरण और चालान देखें और प्रबंधित करें।',
            'Order ID' => 'ऑर्डर आईडी',
            'Date' => 'तारीख',
            'Amount' => 'राशि',
            'Status' => 'स्थिति',
            'Download Invoice' => 'चालान डाउनलोड करें',
            'Shipping To' => 'शिपिंग पता',
            'You haven\'t placed any orders yet.' => 'आपने अभी तक कोई ऑर्डर नहीं दिया है।',
            'Continue Shopping' => 'खरीदारी जारी रखें',
            'successful' => 'सफल',
            'failed' => 'विफल'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Fetch User's Orders (EXCLUDING 'pending' status)
$sql = "SELECT po.*, p.name as product_name, p.image as product_image 
        FROM product_orders po 
        JOIN products p ON po.product_id = p.id 
        WHERE po.user_id = ? AND po.payment_status != 'pending'
        ORDER BY po.id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('My Orders') ?> | The Fortune Pathway</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #fdfdfd; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
        
        .status-badge { padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-successful { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .status-failed { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; color: #f9fafb !important; }
        body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
        body.dark-theme .bg-gray-100 { background-color: #374151 !important; border-color: #4b5563 !important; color: #f9fafb !important; }
        
        body.dark-theme .text-gray-900, body.dark-theme .text-black { color: #f9fafb !important; }
        body.dark-theme .text-gray-700, body.dark-theme .text-gray-600 { color: #d1d5db !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
        
        body.dark-theme .border-gray-100, body.dark-theme .border-gray-200 { border-color: #374151 !important; }
        
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
        body.dark-theme .bg-black:hover { background-color: #059669 !important; }
    </style>
</head>
<body class="overflow-x-hidden bg-gray-50">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<!-- MOBILE VIEW -->
<div class="block lg:hidden">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/mobile_header.php'; ?>
    
    <div class="px-5 py-8 mt-4">
        <h1 class="text-3xl font-black text-gray-900 mb-2"><?= __('My Orders') ?></h1>
        <p class="text-sm text-gray-500 mb-8"><?= __('View and manage your recent orders, shipping details, and invoices.') ?></p>

        <div class="space-y-4">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white border border-gray-200 rounded-[2rem] p-5 shadow-sm">
                        
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1"><?= __('Order ID') ?>: #<?= htmlspecialchars($order['id']) ?></p>
                                <p class="text-xs text-gray-400 font-semibold"><?= date('d M Y', strtotime($order['created_at'])) ?></p>
                            </div>
                            <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                <?= __(strtolower($order['payment_status'])) ?>
                            </span>
                        </div>

                        <div class="flex gap-4 mb-4">
                            <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center p-2 border border-gray-100 shrink-0">
                                <img src="/uploads/products/<?= htmlspecialchars($order['product_image']) ?>" class="w-full h-full object-contain" alt="Product">
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm leading-snug mb-1"><?= htmlspecialchars($order['product_name']) ?></h3>
                                <p class="text-lg font-black text-gray-900">₹<?= number_format((float)$order['amount'], 0) ?></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 mb-4 border border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1"><?= __('Shipping To') ?></p>
                            <p class="text-xs text-gray-700 font-medium leading-tight line-clamp-2"><?= htmlspecialchars($order['shipping_address']) ?> - <?= htmlspecialchars($order['shipping_pincode']) ?></p>
                        </div>

                        <?php if(strtolower($order['payment_status']) === 'successful'): ?>
                            <a href="/user/invoice.php?id=<?= $order['id'] ?>" target="_blank" class="w-full py-3 bg-black text-white rounded-xl font-bold text-sm shadow-md flex justify-center items-center gap-2 active:scale-95 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <?= __('Download Invoice') ?>
                            </a>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white border border-gray-200 rounded-[2rem] p-10 text-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 text-gray-300">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2"><?= __('You haven\'t placed any orders yet.') ?></h3>
                    <a href="/products/list.php" class="inline-block mt-4 px-6 py-3 bg-black text-white rounded-full font-bold text-sm shadow-md"><?= __('Continue Shopping') ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="h-24"></div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/footer_user.php'; ?>
</div>

<!-- DESKTOP VIEW -->
<div class="hidden lg:block">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_header.php'; ?>
    
    <section class="min-h-[70vh] max-w-6xl mx-auto px-6 py-16">
        
        <div class="mb-12">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gray-200 text-gray-800 font-bold uppercase tracking-wider text-xs mb-4">✦ <?= __('Your Spiritual Purchases') ?></span>
            <h1 class="text-5xl font-black text-gray-900 tracking-tight"><?= __('My Orders') ?></h1>
            <p class="text-gray-500 font-medium mt-3 text-lg"><?= __('View and manage your recent orders, shipping details, and invoices.') ?></p>
        </div>

        <?php if (count($orders) > 0): ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white border border-gray-200 rounded-[2.5rem] p-8 shadow-sm flex items-center gap-8 hover:shadow-md transition-shadow">
                        
                        <!-- Image -->
                        <div class="w-32 h-32 bg-gray-50 rounded-3xl flex items-center justify-center p-3 border border-gray-100 shrink-0">
                            <img src="/uploads/products/<?= htmlspecialchars($order['product_image']) ?>" class="w-full h-full object-contain" alt="Product">
                        </div>
                        
                        <!-- Details -->
                        <div class="flex-grow">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($order['product_name']) ?></h3>
                                <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                    <?= __(strtolower($order['payment_status'])) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 font-semibold mb-4"><?= __('Order ID') ?>: #<?= htmlspecialchars($order['id']) ?> &nbsp;|&nbsp; <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                            
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 max-w-lg">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1"><?= __('Shipping To') ?></p>
                                <p class="text-sm text-gray-700 font-medium leading-relaxed">
                                    <?= htmlspecialchars($order['shipping_name']) ?> — <?= htmlspecialchars($order['shipping_phone']) ?><br>
                                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                                    <span class="font-bold">PIN:</span> <?= htmlspecialchars($order['shipping_pincode']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Price & Action -->
                        <div class="text-right min-w-[200px] flex flex-col items-end">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1"><?= __('Amount') ?></p>
                            <p class="text-3xl font-black text-gray-900 mb-6">₹<?= number_format((float)$order['amount'], 0) ?></p>
                            
                            <?php if(strtolower($order['payment_status']) === 'successful'): ?>
                                <a href="/user/invoice.php?id=<?= $order['id'] ?>" target="_blank" class="inline-flex items-center justify-center px-6 py-3 bg-black text-white rounded-xl font-bold text-sm shadow-md hover:bg-gray-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <?= __('Download Invoice') ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border border-gray-200 rounded-[3rem] p-16 text-center shadow-sm max-w-2xl mx-auto mt-10">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100 text-gray-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4"><?= __('You haven\'t placed any orders yet.') ?></h3>
                <p class="text-gray-500 mb-8"><?= __('Discover spiritual items, gemstones, and remedies in our Astro Store to support your journey.') ?></p>
                <a href="/products/list.php" class="inline-block px-8 py-4 bg-black text-white rounded-full font-bold shadow-lg hover:bg-gray-800 transition-colors"><?= __('Continue Shopping') ?></a>
            </div>
        <?php endif; ?>

    </section>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_footer.php'; ?>
</div>

</body>
</html>