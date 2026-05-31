<?php
/**
 * Desktop Web Header - Fortune Parth
 * Integrated with Mobile Menu functionalities and Database logic
 */

// 1. Database Logic to fetch User Name
$username = "Guest";
if (isset($_SESSION['userid'])) {
    require_once __DIR__ . '/../db.php'; 
    
    try {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['userid']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            
            // --- STRICT SERVER-SIDE PROFILE LOCK ---
            if (isset($_SESSION['role']) && $_SESSION['role'] == 2) {
                $current_page = basename($_SERVER['PHP_SELF']);
                if (empty($u['name']) && $current_page !== 'details.php') {
                    header("Location: /user/details.php");
                    exit;
                }
            }
            // ---------------------------------------

            $username = htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8');
        }
    } catch (PDOException $e) {
        $username = "User"; 
    }
}

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Astrology' => 'ज्योतिष',
            'Vastu' => 'वास्तु',
            'Numerology' => 'अंक ज्योतिष',
            'Palm Reading' => 'हस्तरेखा',
            'Pooja' => 'पूजा',
            'Store' => 'स्टोर',
            'Wallet' => 'वॉलेट',
            'My Account' => 'मेरा खाता',
            'Signed in as' => 'के रूप में साइन इन हैं',
            'My Profile' => 'मेरी प्रोफ़ाइल',
            'My Orders' => 'मेरे ऑर्डर', // Added translation for Orders
            'Call History' => 'कॉल इतिहास',
            'Wallet Transactions' => 'वॉलेट लेनदेन',
            'Customer Support' => 'ग्राहक सहायता',
            'Logout' => 'लॉग आउट',
            'Login / Sign Up' => 'लॉग इन / साइन अप'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" rel="stylesheet" />

<style>
/* =========================================
   ENHANCED DARK MODE STYLES FOR HEADER
========================================= */
body.dark-theme header.bg-white { background-color: #1f2937 !important; border-bottom: 1px solid #374151 !important; }
body.dark-theme header .text-gray-700 { color: #d1d5db !important; }
body.dark-theme header .text-gray-800, body.dark-theme header .text-gray-900 { color: #f9fafb !important; }
body.dark-theme header .hover\:text-indigo-600:hover { color: #10b981 !important; }

body.dark-theme header .bg-green-50 { background-color: rgba(16, 185, 129, 0.15) !important; border-color: rgba(16, 185, 129, 0.2) !important; color: #34d399 !important; }

/* Dropdown Menu */
body.dark-theme .group .absolute.bg-white { background-color: #374151 !important; border-color: #4b5563 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important; }
body.dark-theme .bg-gray-50\/50 { background-color: #1f2937 !important; border-color: #4b5563 !important; }
body.dark-theme .border-gray-100 { border-color: #4b5563 !important; }
body.dark-theme .hover\:bg-indigo-50:hover { background-color: #4b5563 !important; color: #10b981 !important; }

/* Login Button */
body.dark-theme .border-gray-900 { border-color: #10b981 !important; color: #10b981 !important; }
body.dark-theme .hover\:bg-gray-900:hover { background-color: #10b981 !important; color: #ffffff !important; }
</style>

<header class="hidden lg:block bg-white border-b border-gray-200 sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <a href="/" class="flex items-center space-x-2 text-xl font-bold tracking-wide text-indigo-700">
                <img src="/assets/images/FP Logo.png" alt="Fortune Path Way" class="w-9 h-9 object-contain rounded-full shadow-sm">
                <span>Fortune Path Way</span>
            </a>

            <nav class="flex items-center space-x-8 text-sm font-medium">
                <a href="/services/astrology.php" class="text-gray-700 hover:text-indigo-600 transition"><?= __('Astrology') ?></a>
                <a href="/services/vastu.php" class="text-gray-700 hover:text-indigo-600 transition"><?= __('Vastu') ?></a>
                <a href="/services/numerology.php" class="text-gray-700 hover:text-indigo-600 transition"><?= __('Numerology') ?></a>
                <a href="/services/palm_reading.php" class="text-gray-700 hover:text-indigo-600 transition"><?= __('Palm Reading') ?></a>
                <a href="/services/pooja.php" class="text-gray-700 hover:text-indigo-600 transition"><?= __('Pooja') ?></a>
                <a href="/products/list.php" class="text-gray-700 hover:text-indigo-600 transition border-l pl-6 border-gray-200"><?= __('Store') ?></a>
            </nav>

            <div class="flex items-center space-x-5">
                
                <?php if (isset($_SESSION['userid'])): ?>
                    
                    <a href="/user/wallet.php" class="flex items-center bg-green-50 px-3 py-1.5 rounded-full border border-green-200 text-green-700 hover:bg-green-100 transition">
                        <span class="material-symbols-outlined text-lg mr-1">account_balance_wallet</span>
                        <span class="text-xs font-bold uppercase tracking-tight"><?= __('Wallet') ?></span>
                    </a>

                    <div class="relative group h-16 flex items-center">
                        
                        <a href="/user/profile.php" class="flex items-center text-sm font-semibold text-gray-800 hover:text-indigo-600 transition">
                            <span class="material-symbols-outlined text-2xl mr-1 text-indigo-500">account_circle</span>
                            <span><?= __('My Account') ?></span>
                            <span class="material-symbols-outlined text-xs ml-1">expand_more</span>
                        </a>

                        <div class="absolute top-16 right-0 w-60 bg-white border border-gray-200 shadow-xl rounded-b-xl py-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-300 transform origin-top translate-y-2 group-hover:translate-y-0">
                            
                            <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 mb-2">
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider"><?= __('Signed in as') ?></p>
                                <p class="text-sm font-bold text-gray-900 truncate"><?= $username ?></p>
                            </div>

                            <a href="/user/profile.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <span class="material-symbols-outlined text-lg mr-3 opacity-70">person</span>
                                <?= __('My Profile') ?>
                            </a>

                            <!-- NEW: My Orders Link -->
                            <a href="/user/orders.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <span class="material-symbols-outlined text-lg mr-3 opacity-70">local_shipping</span>
                                <?= __('My Orders') ?>
                            </a>

                            <a href="/user/call_history.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <span class="material-symbols-outlined text-lg mr-3 opacity-70">call</span>
                                <?= __('Call History') ?>
                            </a>

                            <a href="/user/wallet.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <span class="material-symbols-outlined text-lg mr-3 opacity-70">receipt_long</span>
                                <?= __('Wallet Transactions') ?>
                            </a>

                            <a href="/user/support.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <span class="material-symbols-outlined text-lg mr-3 opacity-70">support_agent</span>
                                <?= __('Customer Support') ?>
                            </a>

                            <div class="my-2 border-t border-gray-100"></div>
                            
                            <a href="/logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-bold transition">
                                <span class="material-symbols-outlined text-lg mr-3">logout</span>
                                <?= __('Logout') ?>
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    
                    <a href="/auth/login.php"
                       class="px-6 py-2 text-sm border-2 border-gray-900 text-gray-900 rounded-lg hover:bg-gray-900 hover:text-white transition font-bold shadow-sm">
                        <?= __('Login / Sign Up') ?>
                    </a>

                <?php endif; ?>
            </div>

        </div>
    </div>
</header>