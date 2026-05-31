<?php
session_start();

/* ============================
   HARDENED SECURITY & ERROR HANDLING
============================ */
$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
// Enforce strict reporting for security
error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING));

require 'db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM (CORE PHP)
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [], // Default fallback
        'hi' => [
            'Explore our services' => 'हमारी सेवाएं देखें',
            'Astrologers Online' => 'ऑनलाइन ज्योतिषी',
            'Expert guidance available now' => 'विशेषज्ञ मार्गदर्शन अब उपलब्ध है',
            'Astro Shop' => 'एस्ट्रो शॉप',
            'Vastu Shop' => 'वास्तु शॉप',
            'See All →' => 'सभी देखें →',
            'See All Products →' => 'सभी उत्पाद देखें →',
            'Authentic Guidance' => 'प्रामाणिक मार्गदर्शन',
            'For Your Journey.' => 'आपकी यात्रा के लिए।',
            'Connect instantly with certified astrologers and spiritual masters for trusted cosmic insights.' => 'विश्वसनीय ब्रह्मांडीय अंतर्दृष्टि के लिए प्रमाणित ज्योतिषियों और आध्यात्मिक गुरुओं से तुरंत जुड़ें।',
            'Start Consultation' => 'परामर्श शुरू करें',
            'Visit Store' => 'स्टोर पर जाएं',
            'Verified Vedic Experts' => 'सत्यापित वैदिक विशेषज्ञ',
            'Our Services' => 'हमारी सेवाएं',
            'Astro Store' => 'एस्ट्रो स्टोर',
            'Vastu Store' => 'वास्तु स्टोर',
            'Astrology' => 'ज्योतिष',
            'Vastu' => 'वास्तु',
            'Numerology' => 'अंक ज्योतिष',
            'Palmistry' => 'हस्तरेखा',
            'Face Reading' => 'चेहरा पढ़ना',
            'Pooja' => 'पूजा',
            'Shopping' => 'खरीदारी',
            'Reports' => 'रिपोर्ट्स'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

/* ============================
   ROLE REDIRECT
============================ */
if (isset($_SESSION['userid'], $_SESSION['role'])) {
    if ($_SESSION['role'] == 1) {
        header("Location: /astrologer/dashboard.php");
        exit;
    }
    if ($_SESSION['role'] == 3) {
        header("Location: /admin/dashboard.php");
        exit;
    }
}

/* ============================
   SECURE FETCH USER
============================ */
$user = null;
if (isset($_SESSION['userid'])) {
    $stmt = $conn->prepare("SELECT id, role_id, email, profile_photo, referral_code FROM users WHERE id=? LIMIT 1");
    $stmt->execute([intval($_SESSION['userid'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================
   SECURE FETCH PRODUCTS (Using explicit column selection)
============================ */
try {
    $astro_products = $conn->query("SELECT id, name, price, image FROM products WHERE category = 'Astro Shop' ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $vastu_products = $conn->query("SELECT id, name, price, image FROM products WHERE category = 'Vastu Shop' ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Product Fetch Error: " . $e->getMessage());
    $astro_products = [];
    $vastu_products = [];
}

$services = [
    [__('Astrology'),'astrology.png','services/astrology.php'],
    [__('Vastu'),'vastu.png','services/vastu.php'],
    [__('Numerology'),'numerology.png','services/numerology.php'],
    [__('Palmistry'),'palm_reading.png','services/palm_reading.php'],
    [__('Face Reading'),'Face_reading.png','services/Face_reading.php'],
    [__('Pooja'),'pooja.png','services/pooja.php'],
    [__('Shopping'),'shopping.jpg','products/list.php'],
    [__('Reports'),'ReportArea.png','services/Report_area.php'],
];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fortune Path</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .no-scrollbar { scrollbar-width:none; }
        .unified-frame { aspect-ratio: 1 / 1; background: #f9f9f9; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 1.5rem; }
        .unified-frame img { width: 100%; height: 100%; object-fit: contain; padding: 10%; }
        .service-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform: translateY(20px); opacity: 0; }
        .service-card.animate { transform: translateY(0); opacity: 1; }
        .service-card:hover { transform: translateY(-10px) scale(1.05); }
        .product-card-mobile { transition: all 0.3s ease; transform: translateX(30px); opacity: 0; }
        .product-card-mobile.animate { transform: translateX(0); opacity: 1; }
        .floating-hero-img { animation: floatHero 6s ease-in-out infinite; }
        @keyframes floatHero { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .pulse-online { animation: pulseOnline 2s infinite; }
        @keyframes pulseOnline { 0% { box-shadow: 0 0 0 0 rgba(107, 114, 128, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(107, 114, 128, 0); } 100% { box-shadow: 0 0 0 0 rgba(107, 114, 128, 0); } }

        /* =========================================
           ENHANCED DARK MODE STYLES (TEXT COLOR FIXES)
        ========================================= */
        body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
        
        /* Backgrounds */
        body.dark-theme .bg-white { background-color: #121212 !important; border-color: #374151 !important; }
        body.dark-theme .bg-gray-50, body.dark-theme .bg-gray-100 { background-color: #1f2937 !important; border-color: #374151 !important;}
        body.dark-theme .unified-frame { background: #1f2937 !important; }
        body.dark-theme .bg-gradient-to-b.from-gray-50.to-white { background: #121212 !important; }
        
        /* Text Colors - Ensuring text turns white/light gray */
        body.dark-theme .text-gray-900, 
        body.dark-theme .text-gray-800, 
        body.dark-theme .text-black { color: #ffffff !important; }
        
        body.dark-theme .text-gray-600, 
        body.dark-theme .text-gray-500,
        body.dark-theme .text-gray-700 { color: #d1d5db !important; }
        
        /* Borders */
        body.dark-theme .border-gray-100, body.dark-theme .border-gray-200 { border-color: #374151 !important; }
        
        /* Special Elements */
        body.dark-theme .bg-black { background-color: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
        body.dark-theme .bg-black:hover { background-color: #4b5563 !important; }
        
        body.dark-theme .bg-gray-800.pulse-online { background-color: #1f2937 !important; border: 1px solid #4b5563; box-shadow: none; }
        
        body.dark-theme .bg-gradient-to-r.from-gray-900.via-gray-500.to-gray-900 { 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-image: linear-gradient(to right, #f9fafb, #9ca3af, #f9fafb) !important; 
        }
    </style>
</head>

<body class="bg-white overflow-x-hidden">

<div class="block lg:hidden">
    <?php include 'user/mobile_header.php'; ?>
    
    <div class="max-w-7xl mx-auto p-4 space-y-10 pb-32">
        
        <section>
            <h2 class="mb-6 text-2xl font-bold text-gray-900"><?= __('Explore our services') ?></h2>
            <div class="grid grid-cols-4 gap-4">
                <?php foreach($services as $index => $s): ?>
                <a class="service-card group" href="<?= htmlspecialchars($s[2], ENT_QUOTES, 'UTF-8') ?>" style="--order: <?= $index ?>">
                    <div class="aspect-square rounded-2xl bg-cover bg-center shadow-md border border-gray-100" style="background-image:url('assets/services/<?= htmlspecialchars($s[1], ENT_QUOTES, 'UTF-8') ?>')"></div>
                    <p class="mt-2 text-[10px] font-bold text-gray-800 text-center truncate"><?= htmlspecialchars($s[0], ENT_QUOTES, 'UTF-8') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php include 'admin/banner_block.php'; ?>

        <div class="bg-gray-800 rounded-3xl p-6 text-white text-center pulse-online">
            <h2 class="text-xl font-bold mb-1 text-white">🔮 <?= __('Astrologers Online') ?></h2>
            <p class="text-sm opacity-70 text-gray-200"><?= __('Expert guidance available now') ?></p>
        </div>

        <?php include 'astrologer/live_astrologers_block.php'; ?>

        <section>
            <div class="flex justify-between mb-4 items-center">
                <h2 class="text-2xl font-bold text-gray-900"><?= __('Astro Shop') ?></h2>
                <a href="products/list.php?cat=Astro Shop" class="text-gray-500 font-semibold text-sm"><?= __('See All →') ?></a>
            </div>
            <div class="flex space-x-4 overflow-x-auto no-scrollbar pb-2">
                <?php foreach($astro_products as $index => $p): ?>
                <a href="/products/detail.php?id=<?= intval($p['id']) ?>" class="product-card-mobile w-44 flex-shrink-0 border border-gray-100 rounded-3xl p-4 text-center bg-white shadow-sm" style="--order: <?= $index ?>">
                    <div class="unified-frame mb-3"><img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="text-xs font-bold text-gray-800 mb-1 truncate"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-lg font-black text-gray-900">₹<?= number_format((float)$p['price']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <div class="flex justify-between mb-4 items-center">
                <h2 class="text-2xl font-bold text-gray-900"><?= __('Vastu Shop') ?></h2>
                <a href="products/list.php?cat=Vastu Shop" class="text-gray-500 font-semibold text-sm"><?= __('See All →') ?></a>
            </div>
            <div class="flex space-x-4 overflow-x-auto no-scrollbar pb-2">
                <?php foreach($vastu_products as $index => $p): ?>
                <a href="/products/detail.php?id=<?= intval($p['id']) ?>" class="product-card-mobile w-44 flex-shrink-0 border border-gray-100 rounded-3xl p-4 text-center bg-white shadow-sm" style="--order: <?= $index ?>">
                    <div class="unified-frame mb-3"><img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="text-xs font-bold text-gray-800 mb-1 truncate"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-lg font-black text-gray-900">₹<?= number_format((float)$p['price']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <?php include 'user/footer_user.php'; ?>
</div>

<div class="hidden lg:block bg-white text-gray-800">
    <?php include 'user/web_header.php'; ?>

    <section class="relative bg-gradient-to-b from-gray-50 to-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-12 py-32 grid grid-cols-2 gap-20 items-center">
            <div class="space-y-10">
                <span class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gray-100 border border-gray-200 text-gray-700 font-semibold uppercase tracking-widest text-xs">✦ <?= __('Verified Vedic Experts') ?></span>
                <h1 class="text-7xl font-black leading-tight text-gray-900"><?= __('Authentic Guidance') ?><br><span class="bg-gradient-to-r from-gray-900 via-gray-500 to-gray-900 bg-clip-text text-transparent"><?= __('For Your Journey.') ?></span></h1>
                <p class="text-xl text-gray-600 max-w-xl leading-relaxed"><?= __('Connect instantly with certified astrologers and spiritual masters for trusted cosmic insights.') ?></p>
                <div class="flex gap-6">
                    <a href="/services/astrology.php" class="px-12 py-6 bg-black text-white rounded-2xl font-bold text-lg shadow-xl hover:bg-gray-800 transition-all"><?= __('Start Consultation') ?></a>
                    <a href="/products/list.php" class="px-12 py-6 border-2 border-gray-200 rounded-2xl text-gray-800 font-semibold hover:bg-gray-50 transition-all"><?= __('Visit Store') ?></a>
                </div>
            </div>
            <div class="flex justify-end"><img src="/assets/desktop/hero-astro.png" alt="Hero" class="max-h-[550px] drop-shadow-2xl floating-hero-img"></div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-12 py-24">
        <div class="text-center mb-16">
            <h2 class="text-5xl font-black text-gray-900 mb-4"><?= __('Our Services') ?></h2>
        </div>
        <div class="grid grid-cols-4 gap-8">
            <?php foreach($services as $s): ?>
            <a href="/<?= htmlspecialchars($s[2], ENT_QUOTES, 'UTF-8') ?>" class="group bg-white border border-gray-100 rounded-[3rem] p-6 hover:shadow-2xl transition-all duration-500">
                <div class="aspect-square rounded-[2rem] overflow-hidden mb-6 bg-gray-50">
                    <img src="/assets/services/<?= htmlspecialchars($s[1], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($s[0], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                </div>
                <h3 class="text-xl font-bold text-center text-gray-800"><?= htmlspecialchars($s[0], ENT_QUOTES, 'UTF-8') ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-12 py-24">
        <div class="flex justify-between items-end mb-16">
            <h2 class="text-5xl font-black text-gray-900"><?= __('Astro Store') ?></h2>
            <a href="products/list.php?cat=Astro Shop" class="text-xl font-bold text-gray-500 hover:text-white transition-colors"><?= __('See All Products →') ?></a>
        </div>
        <div class="grid grid-cols-4 gap-8">
            <?php foreach($astro_products as $p): ?>
            <a href="/products/detail.php?id=<?= intval($p['id']) ?>" class="group bg-white border border-gray-100 rounded-[3rem] p-6 hover:shadow-xl transition-all duration-500">
                <div class="unified-frame mb-6 group-hover:bg-gray-100"><img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" class="group-hover:scale-110 transition duration-700"></div>
                <h3 class="text-base font-bold text-gray-800 mb-2 truncate"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="text-2xl font-black text-gray-900">₹<?= number_format((float)$p['price']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-12 py-24">
        <div class="flex justify-between items-end mb-16">
            <h2 class="text-5xl font-black text-gray-900"><?= __('Vastu Store') ?></h2>
            <a href="products/list.php?cat=Vastu Shop" class="text-xl font-bold text-gray-500 hover:text-white transition-colors"><?= __('See All Products →') ?></a>
        </div>
        <div class="grid grid-cols-4 gap-8">
            <?php foreach($vastu_products as $p): ?>
            <a href="/products/detail.php?id=<?= intval($p['id']) ?>" class="group bg-white border border-gray-100 rounded-[3rem] p-6 hover:shadow-xl transition-all duration-500">
                <div class="unified-frame mb-6 group-hover:bg-gray-100">
                    <img src="/uploads/products/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" class="group-hover:scale-110 transition duration-700">
                </div>
                <h3 class="text-base font-bold text-gray-800 mb-2 truncate"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="text-2xl font-black text-gray-900">₹<?= number_format((float)$p['price']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include 'user/web_footer.php'; ?>
</div>

<script>
window.addEventListener("load", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.service-card, .product-card-mobile').forEach(el => {
        observer.observe(el);
    });
});
</script>

</body>
</html>