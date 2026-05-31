<?php
session_start();
require '../db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Sacred Remedies' => 'पवित्र उपाय',
            'Lab-Certified & Energized' => 'लैब-प्रमाणित और ऊर्जावान',
            'No spiritual essentials available.' => 'कोई आध्यात्मिक आवश्यक सामग्री उपलब्ध नहीं है।',
            'Buy Now' => 'अभी खरीदें',
            'Authentic Collection' => 'प्रामाणिक संग्रह',
            'Access curated spiritual essentials including lab-certified gemstones, energized Rudraksh, and Vastu materials designed to restore your astrological balance.' => 'आपके ज्योतिषीय संतुलन को बहाल करने के लिए डिज़ाइन किए गए लैब-प्रमाणित रत्नों, ऊर्जावान रुद्राक्ष और वास्तु सामग्री सहित क्यूरेटेड आध्यात्मिक आवश्यक चीजों तक पहुंचें।',
            'Our collection is currently being energized.' => 'हमारा संग्रह वर्तमान में ऊर्जावान किया जा रहा है।',
            'Inc. Tax' => 'कर सहित',
            'Purchase Item' => 'वस्तु खरीदें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Fetch user info
$user = null;
if (isset($_SESSION['userid'])) {
    $stmtUser = $conn->prepare("SELECT id, role_id, email FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['userid']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
}

// Fetch products
$stmt = $conn->query("SELECT id, name, price, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title><?= __('Sacred Remedies') ?> - Fortune Parth</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; color: #1a1a1a; overflow-x: hidden; transition: background 0.3s ease; }
    
    .bg-glow {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at 70% 30%, #f4f6ff 0%, #ffffff 100%);
        z-index: -1;
        transition: background 0.3s ease;
    }

    /* Professional Card Transition */
    .product-transition {
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }
    
    .web-product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px -12px rgba(0,0,0,0.12);
    }

    .image-zoom-container {
        overflow: hidden;
        border-radius: 1.5rem;
        transition: background 0.3s ease;
    }
    
    .web-product-card:hover .image-zoom {
        transform: scale(1.08);
    }

    .image-zoom {
        transition: transform 0.8s cubic-bezier(0.23, 1, 0.32, 1);
    }

    /* Mobile grid tweaks */
    @media (max-width: 1023px) {
        .mobile-content { padding: 80px 16px 100px; }
        .mobile-product-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: background 0.3s ease;
        }
    }

    @media (min-width: 1024px) {
        .desktop-container { max-width: 85rem; margin: 0 auto; padding: 0 2rem; }
    }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-glow { background: radial-gradient(circle at 70% 30%, #1f2937 0%, #121212 100%) !important; }
    
    body.dark-theme .mobile-product-card, body.dark-theme .web-product-card { background: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.5) !important; }
    body.dark-theme .bg-gray-50, body.dark-theme .image-zoom-container { background: #374151 !important; border-color: #4b5563 !important; }
    
    body.dark-theme .text-gray-900, body.dark-theme .text-gray-800, body.dark-theme .text-black { color: #ffffff !important; }
    body.dark-theme .text-gray-600, body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #d1d5db !important; }
    body.dark-theme .text-gray-300 { color: #9ca3af !important; }
    
    body.dark-theme .bg-black { background: #10b981 !important; color: #ffffff !important; border: none !important; }
    body.dark-theme .bg-black:hover { background: #059669 !important; }
</style>
</head>

<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="bg-glow"></div>

<div class="block lg:hidden">
    <?php include '../user/mobile_header.php'; ?>

    <div class="mobile-content">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold tracking-tight mb-2"><?= __('Sacred Remedies') ?></h2>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-medium"><?= __('Lab-Certified & Energized') ?></p>
        </div>

        <?php if (count($products) === 0): ?>
            <div class="py-20 text-center text-gray-400"><?= __('No spiritual essentials available.') ?></div>
        <?php else: ?>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach ($products as $p): ?>
            <div class="mobile-product-card p-3 flex flex-col" onclick="window.location.href='detail.php?id=<?= intval($p['id']) ?>'">
                <div class="bg-gray-50 rounded-2xl mb-3 aspect-square flex items-center justify-center overflow-hidden">
                    <img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-contain p-2" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <h3 class="text-sm font-bold text-gray-800 line-clamp-2 h-10 mb-1 leading-tight"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-lg font-black text-black mb-3">₹<?= number_format((float)$p['price'], 0) ?></p>
                
                <a href="<?= $user ? '/user/contact.php?product_id='.intval($p['id']) : '/login.php' ?>" class="w-full py-2.5 bg-black text-white rounded-xl text-xs font-bold text-center block">
                    <?= __('Buy Now') ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="hidden lg:block min-h-screen">
    <?php include '../user/web_header.php'; ?>

    <header class="desktop-container pt-20 pb-12">
        <div class="flex flex-col items-center text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black text-white text-[10px] font-bold uppercase tracking-[0.2em] mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                <?= __('Authentic Collection') ?>
            </div>
            <h1 class="text-6xl font-black tracking-tighter text-black mb-6">Sacred <span class="text-gray-300">Remedies.</span></h1>
            <p class="max-w-2xl text-gray-500 text-lg leading-relaxed">
                <?= __('Access curated spiritual essentials including lab-certified gemstones, energized Rudraksh, and Vastu materials designed to restore your astrological balance.') ?>
            </p>
        </div>
    </header>

    <main class="desktop-container pb-32">
        <?php if (count($products) === 0): ?>
            <div class="text-center py-32">
                <p class="text-gray-300 text-2xl font-medium"><?= __('Our collection is currently being energized.') ?></p>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $p): ?>
            <div class="web-product-card product-transition bg-white border border-gray-100 rounded-[2.5rem] p-5 flex flex-col cursor-pointer" onclick="window.location.href='detail.php?id=<?= intval($p['id']) ?>'">
                
                <div class="image-zoom-container bg-gray-50 mb-6 aspect-square flex items-center justify-center">
                    <img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" 
                         class="image-zoom w-[85%] h-[85%] object-contain" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                
                <div class="px-2 flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-bold text-gray-900 leading-tight pr-4"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <div class="flex items-center gap-2 mb-6">
                        <span class="text-2xl font-black text-black">₹<?= number_format((float)$p['price'], 0) ?></span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><?= __('Inc. Tax') ?></span>
                    </div>
                </div>

                <div class="mt-auto">
                    <a href="<?= $user ? '/user/contact.php?product_id='.intval($p['id']) : '/login.php' ?>" 
                       class="w-full py-4 bg-black text-white rounded-2xl font-bold text-center block transition-transform active:scale-95 text-sm hover:bg-gray-800">
                        <?= __('Purchase Item') ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <?php include '../user/web_footer.php'; ?>
</div>

<div class="lg:hidden">
<?php
if ($user && isset($user['role_id'])) {
    if ($user['role_id'] == 1) include '../astrologer/footer_astrologer.php';
    else if ($user['role_id'] == 2) include '../user/footer_user.php';
    else if ($user['role_id'] == 3) include '../admin/footer_admin.php';
    else include '../user/footer_user.php';
} else {
    include '../user/footer_user.php';
}
?>
</div>

</body>
</html>