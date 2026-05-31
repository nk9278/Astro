<?php
// Desktop Footer
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Authentic astrology, vastu, numerology & spiritual services from verified experts.' => 'सत्यापित विशेषज्ञों से प्रामाणिक ज्योतिष, वास्तु, अंक ज्योतिष और आध्यात्मिक सेवाएं।',
            'Quick Links' => 'त्वरित लिंक',
            'About Us' => 'हमारे बारे में',
            'Contact' => 'संपर्क करें',
            'Legal' => 'कानूनी',
            'Privacy Policy' => 'गोपनीयता नीति',
            'Terms & Conditions' => 'नियम और शर्तें',
            'Refund & Cancellation' => 'धनवापसी और रद्दीकरण',
            'Shipping & Returns' => 'शिपिंग और वापसी',
            'Disclaimer' => 'अस्वीकरण',
            'Services' => 'सेवाएं',
            'Astrology' => 'ज्योतिष',
            'Vastu' => 'वास्तु',
            'Numerology' => 'अंक ज्योतिष',
            'Pooja Rituals' => 'पूजा अनुष्ठान',
            'All rights reserved.' => 'सर्वाधिकार सुरक्षित।'
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
body.dark-theme footer.bg-gray-50 { background-color: #111827 !important; border-color: #374151 !important; }
body.dark-theme footer .text-gray-900, body.dark-theme footer h3, body.dark-theme footer h4 { color: #f9fafb !important; }
body.dark-theme footer .text-gray-600, body.dark-theme footer .text-gray-500, body.dark-theme footer .text-gray-400 { color: #9ca3af !important; }
body.dark-theme footer a:hover { color: #10b981 !important; }
body.dark-theme footer .border-gray-200 { border-color: #374151 !important; }
</style>

<footer class="hidden lg:block bg-gray-50 border-t border-gray-200 mt-16 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6 py-10">

        <!-- Grid expanded to 4 columns to elegantly fit the Legal section -->
        <div class="grid grid-cols-4 gap-8">

            <!-- Brand -->
            <div class="col-span-1">
                <h3 class="text-lg font-bold mb-2">Fortune Path Way</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    <?= __('Authentic astrology, vastu, numerology & spiritual services from verified experts.') ?>
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-semibold mb-3"><?= __('Quick Links') ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/about.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('About Us') ?></a></li>
                    <li><a href="/contact.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Contact') ?></a></li>
                    <li><a href="/astrologer/register.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Become An Astrologer?') ?></a></li>
                </ul>
            </div>

            <!-- Services -->
            <div>
                <h4 class="font-semibold mb-3"><?= __('Services') ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/services/astrology.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Astrology') ?></a></li>
                    <li><a href="/services/vastu.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Vastu') ?></a></li>
                    <li><a href="/services/numerology.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Numerology') ?></a></li>
                    <li><a href="/services/pooja.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Pooja Rituals') ?></a></li>
                </ul>
            </div>

            <!-- Legal Policies -->
            <div>
                <h4 class="font-semibold mb-3"><?= __('Legal') ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/assets/t%26c/terms.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Terms & Conditions') ?></a></li>
                    <li><a href="/assets/t%26c/policy.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Privacy Policy') ?></a></li>
                    <li><a href="/assets/t%26c/Refund-Cancellation-Policy.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Refund & Cancellation') ?></a></li>
                    <li><a href="/assets/t%26c/shipping.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Shipping & Returns') ?></a></li>
                    <li><a href="/assets/t%26c/disclaimer.php" class="hover:underline transition-colors hover:text-indigo-600"><?= __('Disclaimer') ?></a></li>
                </ul>
            </div>

        </div>

        <div class="border-t border-gray-200 mt-8 pt-6 text-center text-xs text-gray-500">
            © <?= date('Y') ?> Fortune Path Way. <?= __('All rights reserved.') ?>
        </div>

    </div>
</footer>