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
            'Complete Your Astrologer Profile' => 'अपनी ज्योतिषी प्रोफ़ाइल पूरी करें',
            'Name (English) *' => 'नाम (अंग्रेज़ी में) *',
            'Name (Hindi)' => 'नाम (हिंदी में)',
            'Phone Number *' => 'फ़ोन नंबर *',
            'About (English) *' => 'परिचय (अंग्रेज़ी में) *',
            'About (Hindi)' => 'परिचय (हिंदी में)',
            'Experience (Years) *' => 'अनुभव (वर्ष) *',
            'Skills (comma separated)' => 'कौशल (अल्पविराम द्वारा अलग)',
            'e.g. Vedic, Tarot, Love' => 'उदा. वैदिक, टैरो, प्रेम',
            'Languages Spoken' => 'बोली जाने वाली भाषाएं',
            'e.g. Hindi, English' => 'उदा. हिंदी, अंग्रेजी',
            'Original Price Per Minute (₹) *' => 'मूल मूल्य प्रति मिनट (₹) *',
            'Offer Price Per Minute (₹) *' => 'ऑफ़र मूल्य प्रति मिनट (₹) *',
            'Save Profile' => 'प्रोफ़ाइल सहेजें',
            'Please fill all mandatory fields.' => 'कृपया सभी अनिवार्य फ़ील्ड भरें।',
            'KYC & Bank Verification' => 'केवाईसी और बैंक सत्यापन',
            'Update KYC Details' => 'केवाईसी विवरण अपडेट करें',
            'Profile Setup' => 'प्रोफ़ाइल सेटअप'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header("Location: /astrologer/register.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $name_hi = trim($_POST['name_hi'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $about_hi = trim($_POST['about_hi'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $skills = trim($_POST['skills'] ?? '');
    $languages = trim($_POST['languages'] ?? '');
    $base_price_per_minute = floatval($_POST['base_price_per_minute'] ?? 0);
    $price_per_minute = floatval($_POST['price_per_minute'] ?? 0);

    // Calculate offer percent if valid
    $offer_percent = 0;
    if ($base_price_per_minute > $price_per_minute && $base_price_per_minute > 0) {
        $offer_percent = round(100 * (1 - $price_per_minute / $base_price_per_minute));
    }

    // Validation
    if (!$name || !$phone || !$about || !$experience_years || !$price_per_minute || !$base_price_per_minute) {
        $error = __('Please fill all mandatory fields.');
    } else {
        $stmt = $conn->prepare("UPDATE users SET astrologer_name=?, astrologer_name_hi=?, phone=?, about=?, about_hi=?, experience_years=?, skills=?, languages=?, price_per_minute=?, base_price_per_minute=?, offer_percent=? WHERE id=?");
        $stmt->execute([$name, $name_hi, $phone, $about, $about_hi, $experience_years, $skills, $languages, $price_per_minute, $base_price_per_minute, $offer_percent, $_SESSION['userid']]);
        header("Location: /astrologer/dashboard.php");
        exit;
    }
}

// Fetch existing profile data
$stmt = $conn->prepare("SELECT astrologer_name, astrologer_name_hi, phone, about, about_hi, experience_years, skills, languages, price_per_minute, base_price_per_minute FROM users WHERE id=?");
$stmt->execute([$_SESSION['userid']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ==========================================
// FETCH REVIEWS FOR DASHBOARD
// ==========================================
$stmtReviews = $conn->prepare("
    SELECT r.rating, r.review_text, r.created_at, u.name as user_name 
    FROM astrologer_reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.astrologer_id = ? 
    ORDER BY r.created_at DESC
");
$stmtReviews->execute([$_SESSION['userid']]);
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

$totalReviews = count($reviews);
$averageRating = 0;
if ($totalReviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $averageRating = round($sum / $totalReviews, 1);
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content"/>
    <title><?= __('Complete Your Astrologer Profile') ?> | Fortune Path</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { background: #fdfdfd; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
        body.dark-theme .auth-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2 { color: #f9fafb !important; }
        body.dark-theme .text-gray-700 { color: #d1d5db !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
        
        body.dark-theme .input-field { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
        body.dark-theme .input-field:focus { border-color: #10b981 !important; }
        body.dark-theme .input-field::placeholder { color: #9ca3af !important; }
        
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; }
        body.dark-theme .bg-black:hover { background-color: #059669 !important; }
        
        body.dark-theme .kyc-section { border-color: #4b5563 !important; background: rgba(220, 38, 38, 0.05) !important; }
        body.dark-theme .kyc-heading { color: #ef4444 !important; }
        body.dark-theme .kyc-btn { background-color: #ef4444 !important; color: white !important; }
        body.dark-theme .kyc-btn:hover { background-color: #dc2626 !important; }

        body.dark-theme .review-card { background: #374151 !important; border-color: #4b5563 !important; }
    </style>
</head>
<body class="overflow-x-hidden">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<section class="min-h-screen flex items-center justify-center bg-gray-50 py-10 px-4">
    <div class="w-full max-w-4xl auth-card p-8 md:p-12 rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl border border-gray-100 mb-20">
        
        <div class="text-center mb-10">
            <div class="inline-block px-4 py-1.5 rounded-full bg-gray-200 text-xs font-bold uppercase tracking-wider mb-4 text-gray-800">✦ <?= __('Profile Setup') ?></div>
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-4"><?= __('Complete Your Astrologer Profile') ?></h2>
            <p class="text-gray-500 font-medium"><?= __('Please fill all mandatory fields.') ?></p>
        </div>

        <?php if ($error): ?>
            <div class="mb-8 p-4 rounded-2xl bg-red-50 text-red-600 text-sm font-bold text-center border border-red-100"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Name (English) *') ?></label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['astrologer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Name (Hindi)') ?> <span class="text-xs font-normal text-gray-400">(Optional)</span></label>
                <input type="text" name="name_hi" value="<?= htmlspecialchars($user['astrologer_name_hi'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="अपना नाम हिंदी में लिखें..." class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Phone Number *') ?></label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="उदा. 9876543210" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Experience (Years) *') ?></label>
                <input type="number" name="experience_years" value="<?= htmlspecialchars($user['experience_years'] ?? '', ENT_QUOTES, 'UTF-8') ?>" min="0" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Skills (comma separated)') ?></label>
                <input type="text" name="skills" value="<?= htmlspecialchars($user['skills'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= __('e.g. Vedic, Tarot, Love') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Languages Spoken') ?></label>
                <input type="text" name="languages" value="<?= htmlspecialchars($user['languages'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= __('e.g. Hindi, English') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Original Price Per Minute (₹) *') ?></label>
                <input type="number" name="base_price_per_minute" value="<?= htmlspecialchars($user['base_price_per_minute'] ?? '', ENT_QUOTES, 'UTF-8') ?>" step="0.01" min="0" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Offer Price Per Minute (₹) *') ?></label>
                <input type="number" name="price_per_minute" value="<?= htmlspecialchars($user['price_per_minute'] ?? '', ENT_QUOTES, 'UTF-8') ?>" step="0.01" min="0" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('About (English) *') ?></label>
                <textarea name="about" rows="3" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all resize-none" required><?= htmlspecialchars($user['about'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('About (Hindi)') ?> <span class="text-xs font-normal text-gray-400">(Optional)</span></label>
                <textarea name="about_hi" rows="3" placeholder="अपने बारे में हिंदी में लिखें..." class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all resize-none"><?= htmlspecialchars($user['about_hi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="md:col-span-2 mt-4">
                <button type="submit" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl">
                    <?= __('Save Profile') ?>
                </button>
            </div>

            <div class="md:col-span-2 mt-8 p-8 border border-dashed border-red-200 bg-red-50/50 rounded-3xl text-center kyc-section transition-colors">
                <h3 class="text-xl md:text-2xl font-black text-red-600 mb-3 kyc-heading"><?= __('KYC & Bank Verification') ?></h3>
                <p class="text-sm md:text-base text-gray-600 mb-6 font-medium">अपना आधार, पैन और बैंक विवरण अपडेट करें ताकि आपकी प्रोफ़ाइल एडमिन द्वारा अप्रूव हो सके।</p>
                <a href="/astrologer/kyc_update.php" class="inline-block bg-red-600 text-white font-bold py-4 px-8 rounded-2xl hover:bg-red-700 transition shadow-lg active:scale-95 kyc-btn">
                    <?= __('Update KYC Details') ?> &rarr;
                </a>
            </div>

        </form>

        <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-2xl font-black text-gray-900 mb-6">Client Feedback & Ratings</h3>
            
            <div class="flex items-center gap-6 mb-8 bg-amber-50 dark:bg-amber-900/20 p-6 rounded-3xl border border-amber-100 dark:border-amber-800">
                <div class="text-5xl font-black text-amber-500"><?= $averageRating ?></div>
                <div>
                    <div class="flex text-amber-400 text-xl mb-1">
                        <?php 
                            for($i=1; $i<=5; $i++) {
                                echo $i <= round($averageRating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                        ?>
                    </div>
                    <p class="text-gray-500 font-bold text-sm">Based on <?= $totalReviews ?> review(s)</p>
                </div>
            </div>

            <?php if ($totalReviews > 0): ?>
                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                    <?php foreach($reviews as $rev): ?>
                        <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50 review-card">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-900"><?= htmlspecialchars($rev['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></h4>
                                    <div class="text-amber-400 text-sm mt-1">
                                        <?php 
                                            for($i=1; $i<=5; $i++) {
                                                echo $i <= $rev['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                        ?>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 font-medium"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <?php if(!empty($rev['review_text'])): ?>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-3 leading-relaxed">"<?= htmlspecialchars($rev['review_text'], ENT_QUOTES, 'UTF-8') ?>"</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 italic text-center py-6">No reviews received yet. Your ratings will appear here after users complete a call with you.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/web-astrologer-footer.php'; ?></div>

</body>
</html>