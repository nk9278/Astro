<?php
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Official Astrologer Management Portal. Manage your calls, earnings, and profile reports efficiently.' => 'आधिकारिक ज्योतिषी प्रबंधन पोर्टल। अपनी कॉल, कमाई और प्रोफ़ाइल रिपोर्ट को कुशलतापूर्वक प्रबंधित करें।',
            'Quick Links' => 'त्वरित लिंक',
            'Dashboard' => 'डैशबोर्ड',
            'Call Logs' => 'कॉल लॉग्स',
            'Wallet & Payouts' => 'वॉलेट और भुगतान',
            'Support' => 'सहायता',
            'Need help with your panel?' => 'क्या आपके पैनल में सहायता की आवश्यकता है?',
            'Contact Admin Support' => 'व्यवस्थापक सहायता से संपर्क करें',
            'All rights reserved.' => 'सर्वाधिकार सुरक्षित।',
            'Developed by' => 'द्वारा विकसित'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}
?>

<style>
/* =========================================
   ENHANCED DARK MODE STYLES FOR FOOTER
========================================= */
body.dark-theme .bg-\[\#f9fafb\] { background-color: #111827 !important; }
body.dark-theme .border-gray-200 { border-color: #374151 !important; }
body.dark-theme .text-gray-900 { color: #f9fafb !important; }
body.dark-theme .text-gray-600, body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
body.dark-theme .hover\:text-indigo-600:hover { color: #a5b4fc !important; }
body.dark-theme .text-indigo-600 { color: #818cf8 !important; }
</style>

<footer class="hidden lg:block bg-[#f9fafb] text-gray-600 py-12 border-t border-gray-200 mt-20 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            
            <div>
                <div class="flex items-center space-x-2 text-gray-900 text-xl font-bold mb-4">
                    <img src="/assets/images/FP Logo.png" class="w-8 h-8 rounded-full" alt="Logo">
                    <span>Fortune Parth</span>
                </div>
                <p class="text-sm leading-relaxed text-gray-500">
                    <?= __('Official Astrologer Management Portal. Manage your calls, earnings, and profile reports efficiently.') ?>
                </p>
            </div>

            <div>
                <h4 class="text-gray-900 font-bold mb-4 uppercase text-xs tracking-widest"><?= __('Quick Links') ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/astrologer/dashboard.php" class="hover:text-indigo-600 transition"><?= __('Dashboard') ?></a></li>
                    <li><a href="/astrologer/call_history.php" class="hover:text-indigo-600 transition"><?= __('Call Logs') ?></a></li>
                    <li><a href="/astrologer/wallet.php" class="hover:text-indigo-600 transition"><?= __('Wallet & Payouts') ?></a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-gray-900 font-bold mb-4 uppercase text-xs tracking-widest"><?= __('Support') ?></h4>
                <p class="text-sm text-gray-500 mb-2"><?= __('Need help with your panel?') ?></p>
                <a href="mailto:info@2ndcode.com" class="text-indigo-600 text-sm font-semibold hover:underline">
                    <?= __('Contact Admin Support') ?>
                </a>
            </div>

        </div>

        <div class="border-t border-gray-200 mt-12 pt-8 text-center">
            <p class="text-xs text-gray-400 tracking-wide">
                &copy; <?= date('Y') ?> Fortune Parth. <?= __('All rights reserved.') ?> 
                <span class="block mt-1"><?= __('Developed by') ?> 2nd Code.</span>
            </p>
        </div>
    </div>
</footer>