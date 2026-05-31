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
// Enforce strict reporting for security (Per User Directive)
error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING));

/* ============================
   CUSTOM LOCALIZATION SYSTEM (CORE PHP)
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [], // Default fallback
        'hi' => [
            'Shipping, Return, and Replacement Policy' => 'शिपिंग, वापसी और प्रतिस्थापन नीति',
            'Legal Policies' => 'कानूनी नीतियां',
            'Thank you for shopping with The Fortune Pathway (TFP). Please read this Shipping, Return, and Replacement Policy carefully before placing an order for physical products.' => 'द फॉर्च्यून पाथवे (TFP) के साथ खरीदारी करने के लिए धन्यवाद। भौतिक उत्पादों के लिए ऑर्डर देने से पहले कृपया इस शिपिंग, वापसी और प्रतिस्थापन नीति को ध्यान से पढ़ें।',
            
            '1. Product Coverage' => '1. उत्पाद कवरेज',
            'This policy applies to physical goods sold through our platform, including, but not limited to, gemstones, rudraksha, malas, bracelets, pyrite products, energized items, spiritual items, and astrology or vastu-related products.' => 'यह नीति हमारे प्लेटफॉर्म के माध्यम से बेचे जाने वाले भौतिक सामानों पर लागू होती है, जिनमें रत्न, रुद्राक्ष, माला, कंगन, पाइराइट उत्पाद, अभिमंत्रित वस्तुएं, आध्यात्मिक वस्तुएं और ज्योतिष या वास्तु-संबंधित उत्पाद शामिल हैं, लेकिन इन्हीं तक सीमित नहीं हैं।',

            '2. Shipping and Dispatch' => '2. शिपिंग और प्रेषण',
            'Orders are usually processed and dispatched within a reasonable time, depending on product type, stock availability, customization, energizing process, and operational conditions' => 'उत्पाद के प्रकार, स्टॉक की उपलब्धता, अनुकूलन, अभिमंत्रित करने की प्रक्रिया और परिचालन स्थितियों के आधार पर, ऑर्डर आमतौर पर उचित समय के भीतर संसाधित और भेजे जाते हैं',
            'Dispatch and delivery timelines are estimates only and may vary by location, courier service, public holidays, weather conditions, or other factors beyond our control' => 'प्रेषण और वितरण समय-सीमा केवल अनुमान हैं और स्थान, कूरियर सेवा, सार्वजनिक छुट्टियों, मौसम की स्थिति, या हमारे नियंत्रण से परे अन्य कारकों के आधार पर भिन्न हो सकते हैं',
            'Customers must provide accurate shipping information. Delays or losses caused by incorrect address details may not be our responsibility' => 'ग्राहकों को सटीक शिपिंग जानकारी प्रदान करनी चाहिए। गलत पते के विवरण के कारण होने वाली देरी या नुकसान हमारी जिम्मेदारी नहीं हो सकती है',

            '3. Returns and Replacements' => '3. वापसी और प्रतिस्थापन',
            'Damaged, defective, or wrongly delivered items may be eligible for replacement or other suitable support, subject to verification' => 'क्षतिग्रस्त, दोषपूर्ण, या गलत तरीके से वितरित आइटम सत्यापन के अधीन, प्रतिस्थापन या अन्य उपयुक्त समर्थन के लिए पात्र हो सकते हैं',
            'Customers must report such issues within 48 hours of delivery with clear photos, unboxing evidence if available, and order details' => 'ग्राहकों को डिलीवरी के 48 घंटों के भीतर स्पष्ट तस्वीरों, यदि उपलब्ध हो तो अनबॉक्सिंग साक्ष्य और ऑर्डर विवरण के साथ ऐसी समस्याओं की रिपोर्ट करनी चाहिए',
            'We reserve the right to verify the issue before approving any replacement or support request' => 'हम किसी भी प्रतिस्थापन या समर्थन अनुरोध को स्वीकार करने से पहले समस्या को सत्यापित करने का अधिकार सुरक्षित रखते हैं',

            '4. Non-Returnable Items' => '4. गैर-वापसी योग्य वस्तुएं',
            'The following items may not be eligible for return, refund, or replacement unless damaged, defective, or wrongly delivered:' => 'निम्नलिखित वस्तुएं वापसी, धनवापसी या प्रतिस्थापन के लिए पात्र नहीं हो सकती हैं जब तक कि वे क्षतिग्रस्त, दोषपूर्ण या गलत तरीके से वितरित न हों:',
            'Used items' => 'उपयोग किए गए आइटम',
            'Opened items' => 'खोले गए आइटम',
            'Customized items' => 'अनुकूलित आइटम',
            'Energized items' => 'अभिमंत्रित आइटम',
            'Blessed items' => 'आशीर्वाद दिए गए आइटम',
            'Size-altered items' => 'आकार-परिवर्तित आइटम',
            'Specially sourced items' => 'विशेष रूप से प्राप्त आइटम',
            'Personal spiritual remedy items prepared specifically for a customer' => 'विशेष रूप से किसी ग्राहक के लिए तैयार की गई व्यक्तिगत आध्यात्मिक उपाय वस्तुएं',

            '5. Shipping Charges' => '5. शिपिंग शुल्क',
            'Shipping, packaging, handling, platform, and similar charges may be non-refundable unless otherwise determined by us' => 'शिपिंग, पैकेजिंग, हैंडलिंग, प्लेटफॉर्म और इसी तरह के शुल्क गैर-वापसी योग्य हो सकते हैं जब तक कि हमारे द्वारा अन्यथा निर्धारित न किया जाए',
            'In some cases, return shipping may need to be borne by the customer unless the error was from our side' => 'कुछ मामलों में, वापसी शिपिंग का खर्च ग्राहक को वहन करना पड़ सकता है जब तक कि त्रुटि हमारी ओर से न हुई हो',

            '6. Contact' => '6. संपर्क',
            'For shipping, return, replacement, or support requests, contact:' => 'शिपिंग, वापसी, प्रतिस्थापन, या समर्थन अनुरोधों के लिए संपर्क करें:'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= __('Shipping, Return, and Replacement Policy') ?> - The Fortune Pathway</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .no-scrollbar { scrollbar-width:none; }
        
        /* =========================================
           ENHANCED DARK MODE STYLES (TEXT COLOR FIXES)
        ========================================= */
        body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
        
        /* Backgrounds */
        body.dark-theme .bg-white { background-color: #121212 !important; border-color: #374151 !important; }
        body.dark-theme .bg-gray-50, body.dark-theme .bg-gray-100 { background-color: #1f2937 !important; border-color: #374151 !important;}
        body.dark-theme .bg-gradient-to-b.from-gray-50.to-white { background: #121212 !important; }
        
        /* Text Colors */
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
        
        body.dark-theme .bg-gradient-to-r.from-gray-900.via-gray-500.to-gray-900 { 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-image: linear-gradient(to right, #f9fafb, #9ca3af, #f9fafb) !important; 
        }
    </style>
</head>

<body class="bg-gray-50 overflow-x-hidden">

<!-- MOBILE HEADER -->
<div class="block lg:hidden">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/mobile_header.php'; ?>
</div>

<!-- DESKTOP HEADER -->
<div class="hidden lg:block bg-white text-gray-800">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_header.php'; ?>
</div>

<!-- MAIN CONTENT AREA -->
<section class="relative bg-gradient-to-b from-gray-50 to-white overflow-hidden py-12 lg:py-24">
    <div class="max-w-4xl mx-auto px-6 lg:px-12">
        
        <div class="text-center mb-16">
            <span class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gray-100 border border-gray-200 text-gray-700 font-semibold uppercase tracking-widest text-xs mb-6">✦ <?= __('Legal Policies') ?></span>
            <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Shipping, Return, and Replacement Policy') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed"><?= __('Thank you for shopping with The Fortune Pathway (TFP). Please read this Shipping, Return, and Replacement Policy carefully before placing an order for physical products.') ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-[3rem] p-8 lg:p-16 shadow-xl space-y-12">
            
            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('1. Product Coverage') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('This policy applies to physical goods sold through our platform, including, but not limited to, gemstones, rudraksha, malas, bracelets, pyrite products, energized items, spiritual items, and astrology or vastu-related products.') ?></p>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('2. Shipping and Dispatch') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Orders are usually processed and dispatched within a reasonable time, depending on product type, stock availability, customization, energizing process, and operational conditions') ?></li>
                    <li><?= __('Dispatch and delivery timelines are estimates only and may vary by location, courier service, public holidays, weather conditions, or other factors beyond our control') ?></li>
                    <li><?= __('Customers must provide accurate shipping information. Delays or losses caused by incorrect address details may not be our responsibility') ?></li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('3. Returns and Replacements') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Damaged, defective, or wrongly delivered items may be eligible for replacement or other suitable support, subject to verification') ?></li>
                    <li><?= __('Customers must report such issues within 48 hours of delivery with clear photos, unboxing evidence if available, and order details') ?></li>
                    <li><?= __('We reserve the right to verify the issue before approving any replacement or support request') ?></li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('4. Non-Returnable Items') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('The following items may not be eligible for return, refund, or replacement unless damaged, defective, or wrongly delivered:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Used items') ?></li>
                    <li><?= __('Opened items') ?></li>
                    <li><?= __('Customized items') ?></li>
                    <li><?= __('Energized items') ?></li>
                    <li><?= __('Blessed items') ?></li>
                    <li><?= __('Size-altered items') ?></li>
                    <li><?= __('Specially sourced items') ?></li>
                    <li><?= __('Personal spiritual remedy items prepared specifically for a customer') ?></li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('5. Shipping Charges') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Shipping, packaging, handling, platform, and similar charges may be non-refundable unless otherwise determined by us') ?></li>
                    <li><?= __('In some cases, return shipping may need to be borne by the customer unless the error was from our side') ?></li>
                </ul>
            </div>

            <!-- Section 6 - Contact (Emails/Numbers hardcoded for intelligent display) -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('6. Contact') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-2"><?= __('For shipping, return, replacement, or support requests, contact:') ?></p>
                <div class="space-y-1 mt-4">
                    <p class="text-gray-900 font-semibold"><?= __('Business Name') ?>: <span class="font-normal text-gray-600">The Fortune Pathway</span></p>
                    <p class="text-gray-900 font-semibold"><?= __('Email') ?>: <a href="mailto:info@thefortunepathway.com" class="font-normal text-gray-600 hover:text-black transition">info@thefortunepathway.com</a></p>
                    <p class="text-gray-900 font-semibold"><?= __('Phone/WhatsApp') ?>: <a href="tel:+919336301113" class="font-normal text-gray-600 hover:text-black transition">+91 93363 01113</a></p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- MOBILE FOOTER -->
<div class="block lg:hidden">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/footer_user.php'; ?>
</div>

<!-- DESKTOP FOOTER -->
<div class="hidden lg:block bg-white text-gray-800">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_footer.php'; ?>
</div>

</body>
</html>