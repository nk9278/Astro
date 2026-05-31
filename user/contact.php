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
            'Contact for' => 'के लिए संपर्क करें',
            'Price:' => 'मूल्य:',
            'Your Message (Optional)' => 'आपका संदेश (वैकल्पिक)',
            'Write your message (optional)…' => 'अपना संदेश लिखें (वैकल्पिक)…',
            'Send Inquiry' => 'पूछताछ भेजें',
            'Your inquiry has been submitted successfully!' => 'आपकी पूछताछ सफलतापूर्वक सबमिट कर दी गई है!',
            'Product Inquiry' => 'उत्पाद पूछताछ',
            'Connect with our' => 'हमारे विशेषज्ञों',
            'Experts.' => 'से जुड़ें।',
            'Have questions about' => 'क्या आपके पास',
            '? Send us a message and our specialists will guide you on its spiritual benefits.' => 'के बारे में प्रश्न हैं? हमें एक संदेश भेजें और हमारे विशेषज्ञ इसके आध्यात्मिक लाभों पर आपका मार्गदर्शन करेंगे।',
            'Trusted by 10k+ Seekers' => '10k+ साधकों द्वारा विश्वसनीय',
            'We will get back to you shortly.' => 'हम आपसे शीघ्र ही संपर्क करेंगे।',
            'Continue Shopping' => 'खरीदारी जारी रखें',
            'Your Inquiry Message' => 'आपका पूछताछ संदेश',
            'How can we help you with this product?' => 'हम इस उत्पाद के साथ आपकी कैसे मदद कर सकते हैं?',
            'Submit Inquiry' => 'पूछताछ सबमिट करें',
            'Your request will be prioritized by our support team.' => 'आपकी प्राथमिकता हमारी सहायता टीम द्वारा दी जाएगी।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

$user_id    = (int)$_SESSION['userid'];
$product_id = (int)($_GET['product_id'] ?? 0);

if ($product_id <= 0) { die('Invalid product.'); }

// Fetch user
$stmtUser = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Fetch product
$stmtProduct = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
$stmtProduct->execute([$product_id]);
$product = $stmtProduct->fetch(PDO::FETCH_ASSOC);
if (!$product) { die('Product not found.'); }

$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');

    // Insert inquiry
    $stmtInsert = $conn->prepare("
        INSERT INTO product_inquiries (user_id, product_id, message, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmtInsert->execute([$user_id, $product_id, $msg]);

    $flash = __('Your inquiry has been submitted successfully!');
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8" />
<title><?= __('Contact for') ?> <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> - Fortune Parth</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">

<style>
/* ======================================================
   MOBILE STYLES
====================================================== */
:root{
    --bg:#f2f2f2; --card:#ffffff; --text:#222; --muted:#666; --line:#e9e9e9;
    --btn:#222; --btn-hover:#444; --accent:#ffd41a;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:'Space Grotesk', sans-serif; transition: background 0.3s ease;}

.container{max-width:480px;margin:72px auto 86px;padding:0 14px;}
.card{
    background:var(--card); border:1px solid var(--line); border-radius:16px;
    padding:16px; box-shadow:0 4px 16px rgba(0,0,0,.06); transition: all 0.3s ease;
}
.title{font-size:18px;font-weight:800;margin:0 0 6px;}
.price{color:var(--muted);font-weight:700;margin:0 0 14px;}
label{display:block;font-weight:800;margin:10px 0 6px;}
textarea{
    width:100%; height:150px; resize:none; border:1px solid #ccc; border-radius:12px;
    padding:12px; font-size:15px; background:#fafafa; transition: all 0.3s ease;
}
textarea::placeholder{color:#888;}
.btn{
    width:100%; background:var(--btn); color:#fff; border:none; border-radius:12px;
    padding:14px; font-weight:800; font-size:16px; margin-top:14px; cursor:pointer; transition: all 0.3s ease;
}
.btn:hover{background:var(--btn-hover);}
.flash{
    margin:8px 0 0; padding:10px 12px; text-align:center; font-weight:800;
    background:#f0fff4; border:1px solid #b7ebc6; border-radius:10px; color:#146c2e;
}

/* ======================================================
   DESKTOP STYLES (FORTUNE PARTH 7XL)
====================================================== */
@media (min-width: 1024px) {
    body {
        background: #ffffff;
    }
    .desktop-max-width {
        max-width: 80rem;
        margin: 0 auto;
        padding: 0 3rem;
    }
    .inquiry-card-web {
        background: #ffffff;
        border-radius: 2.5rem;
        border: 1px solid #f0f0f0;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.05);
        padding: 3rem;
        transition: transform 0.4s ease, background 0.3s ease, border-color 0.3s ease;
    }
    .inquiry-card-web:hover {
        transform: translateY(-5px);
    }
    .web-textarea {
        width: 100%;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 1.5rem;
        padding: 1.5rem;
        font-family: inherit;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    .web-textarea:focus {
        background: #ffffff;
        border-color: #000;
        outline: none;
        box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: slideUp 0.6s ease forwards; }
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }

/* Mobile Dark Overrides */
body.dark-theme .card { background: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 4px 16px rgba(0,0,0,0.5) !important; }
body.dark-theme .title { color: #ffffff !important; }
body.dark-theme .price { color: #d1d5db !important; }
body.dark-theme label { color: #d1d5db !important; }
body.dark-theme textarea { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
body.dark-theme textarea::placeholder { color: #9ca3af !important; }
body.dark-theme .btn { background: #10b981 !important; color: #ffffff !important; }
body.dark-theme .btn:hover { background: #059669 !important; }

/* Desktop Dark Overrides */
body.dark-theme .bg-gradient-to-b.from-gray-50.to-white { background: #121212 !important; }
body.dark-theme .text-gray-900, body.dark-theme .text-black { color: #ffffff !important; }
body.dark-theme .text-gray-600, body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #d1d5db !important; }

body.dark-theme .inquiry-card-web { background: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
body.dark-theme .web-textarea { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
body.dark-theme .web-textarea:focus { background: #374151 !important; border-color: #10b981 !important; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2) !important; }

body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; }
body.dark-theme .bg-black:hover { background-color: #059669 !important; }

body.dark-theme .bg-green-50 { background-color: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.2) !important; }
body.dark-theme .text-green-800 { color: #34d399 !important; }
body.dark-theme .text-green-600 { color: #10b981 !important; }
body.dark-theme .border-black { border-color: #10b981 !important; color: #10b981 !important; }
</style>
</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden">
    <?php include '../user/mobile_header.php'; ?>
    <div class="container">
        <div class="card">
            <div class="title"><?= __('Contact for') ?> “<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>”</div>
            <div class="price"><?= __('Price:') ?> ₹<?= number_format((float)$product['price'], 2) ?></div>

            <?php if ($flash): ?>
                <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
                <form method="post" novalidate>
                    <label><?= __('Your Message (Optional)') ?></label>
                    <textarea name="message" placeholder="<?= __('Write your message (optional)…') ?>"></textarea>
                    <button class="btn" type="submit"><?= __('Send Inquiry') ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>

    <main class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-[80vh]">
        <div class="desktop-max-width">
            <div class="grid grid-cols-12 gap-16 items-center">
                
                <div class="col-span-6 space-y-8 animate-in">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-black text-white text-xs font-bold tracking-widest uppercase">
                        <?= __('Product Inquiry') ?>
                    </span>
                    <h1 class="text-6xl font-black text-gray-900 leading-tight">
                        <?= __('Connect with our') ?> <br> <span class="text-gray-400"><?= __('Experts.') ?></span>
                    </h1>
                    <p class="text-xl text-gray-600 max-w-md leading-relaxed">
                        <?= __('Have questions about') ?> <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></strong><?= __('? Send us a message and our specialists will guide you on its spiritual benefits.') ?>
                    </p>
                    <div class="flex items-center gap-6 pt-4">
                        <div class="flex -space-x-3">
                            <div class="w-12 h-12 rounded-full border-2 border-white bg-gray-200"></div>
                            <div class="w-12 h-12 rounded-full border-2 border-white bg-gray-300"></div>
                            <div class="w-12 h-12 rounded-full border-2 border-white bg-gray-400"></div>
                        </div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-tighter"><?= __('Trusted by 10k+ Seekers') ?></p>
                    </div>
                </div>

                <div class="col-span-6 animate-in" style="animation-delay: 0.2s;">
                    <div class="inquiry-card-web">
                        <div class="mb-8">
                            <h2 class="text-2xl font-black text-gray-900 mb-1"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="text-xl font-bold text-gray-400 tracking-tight"><?= __('Price:') ?> ₹<?= number_format((float)$product['price'], 2) ?></p>
                        </div>

                        <?php if ($flash): ?>
                            <div class="bg-green-50 border border-green-100 p-8 rounded-[2rem] text-center">
                                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <p class="text-green-800 font-bold text-xl mb-2"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-green-600"><?= __('We will get back to you shortly.') ?></p>
                                <a href="list.php" class="inline-block mt-6 font-bold text-black border-b-2 border-black"><?= __('Continue Shopping') ?></a>
                            </div>
                        <?php else: ?>
                            <form method="post" class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('Your Inquiry Message') ?></label>
                                    <textarea name="message" class="web-textarea h-48" placeholder="<?= __('How can we help you with this product?') ?>"></textarea>
                                </div>
                                <button type="submit" class="w-full py-6 bg-black text-white rounded-2xl font-bold text-xl hover:bg-gray-800 transition-all shadow-xl">
                                    <?= __('Submit Inquiry') ?>
                                </button>
                                <p class="text-center text-gray-400 text-xs font-medium"><?= __('Your request will be prioritized by our support team.') ?></p>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

<?php
// Role-based footer logic preserved
$stmtRole = $conn->prepare("SELECT role_id FROM users WHERE id = ?");
$stmtRole->execute([$user_id]);
$role = (int)$stmtRole->fetchColumn();

// Only show mobile footer for mobile devices
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobi') !== false):
    switch ($role) {
      case 1: include '../astrologer/footer_astrologer.php'; break;
      case 3: include '../admin/footer_admin.php'; break;
      default: include '../user/footer_user.php';
    }
endif;
?>

</body>
</html>