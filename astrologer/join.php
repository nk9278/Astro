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

/* ============================
   CUSTOM LOCALIZATION SYSTEM (CORE PHP)
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [], // Default fallback
        'hi' => [
            'Information for Astrologers' => 'ज्योतिषियों के लिए जानकारी',
            'Partner With Us' => 'हमारे साथ भागीदार बनें',
            'Join The Fortune Pathway Family' => 'द फॉर्च्यून पाथवे परिवार में शामिल हों',
            'Review the necessary details, documentation requirements, and our operational model before registering as an expert on our platform.' => 'हमारे प्लेटफॉर्म पर एक विशेषज्ञ के रूप में पंजीकरण करने से पहले आवश्यक विवरण, दस्तावेज़ीकरण आवश्यकताओं और हमारे परिचालन मॉडल की समीक्षा करें।',
            
            'Why Join Us?' => 'हमारे साथ क्यों जुड़ें?',
            'Global Reach' => 'वैश्विक पहुंच',
            'Connect with clients worldwide and expand your consultation services without geographical limits.' => 'दुनिया भर के ग्राहकों से जुड़ें और भौगोलिक सीमाओं के बिना अपनी परामर्श सेवाओं का विस्तार करें।',
            'Flexible Schedule' => 'लचीला समय',
            'Work on your own terms. Log in and provide consultations whenever you are available.' => 'अपनी शर्तों पर काम करें। जब भी आप उपलब्ध हों, लॉग इन करें और परामर्श प्रदान करें।',
            'Secure Platform' => 'सुरक्षित मंच',
            'Your data and earnings are protected with our high-level security and transparent system.' => 'आपका डेटा और कमाई हमारे उच्च-स्तरीय सुरक्षा और पारदर्शी प्रणाली के साथ सुरक्षित हैं।',

            'Required Documentation' => 'आवश्यक दस्तावेज़',
            'To maintain the authenticity and quality of our platform, all astrologers must undergo a verification process. Please keep clear digital copies of the following documents ready:' => 'हमारे प्लेटफॉर्म की प्रामाणिकता और गुणवत्ता बनाए रखने के लिए, सभी ज्योतिषियों को एक सत्यापन प्रक्रिया से गुजरना होगा। कृपया निम्नलिखित दस्तावेजों की स्पष्ट डिजिटल प्रतियां तैयार रखें:',
            'Government Identity Proof' => 'सरकारी पहचान प्रमाण',
            '(e.g., Aadhaar Card, PAN Card, Passport)' => '(उदा., आधार कार्ड, पैन कार्ड, पासपोर्ट)',
            'Address Proof' => 'पते का प्रमाण',
            '(e.g., Voter ID, Driving License, Recent Utility Bill)' => '(उदा., वोटर आईडी, ड्राइविंग लाइसेंस, हालिया उपयोगिता बिल)',
            'Professional Certifications' => 'पेशेवर प्रमाणपत्र',
            'Degrees, diplomas, or certificates in Astrology, Vastu, Numerology, or related fields.' => 'ज्योतिष, वास्तु, अंक ज्योतिष या संबंधित क्षेत्रों में डिग्री, डिप्लोमा या प्रमाण पत्र।',
            'Bank Account Details' => 'बैंक खाता विवरण',
            'A cancelled cheque or passbook copy for processing your payouts securely.' => 'आपके भुगतान को सुरक्षित रूप से संसाधित करने के लिए एक रद्द किया गया चेक या पासबुक की प्रति।',
            'Recent Photograph' => 'हालिया तस्वीर',
            'A professional, clear headshot for your public profile.' => 'आपकी सार्वजनिक प्रोफ़ाइल के लिए एक पेशेवर, स्पष्ट हेडशॉट।',

            'Commission & Earnings' => 'कमीशन और कमाई',
            'The Fortune Pathway operates on a mutually beneficial commission-based model. We believe in growing together.' => 'द फॉर्च्यून पाथवे पारस्परिक रूप से लाभकारी कमीशन-आधारित मॉडल पर काम करता है। हम एक साथ बढ़ने में विश्वास करते हैं।',
            'Revenue Sharing' => 'राजस्व साझाकरण',
            'Earnings from consultations (chat, call, video) and related services are shared based on platform guidelines.' => 'परामर्श (चैट, कॉल, वीडियो) और संबंधित सेवाओं से होने वाली कमाई को प्लेटफॉर्म के दिशानिर्देशों के आधार पर साझा किया जाता है।',
            'Transparent Payouts' => 'पारदर्शी भुगतान',
            'All your earnings are tracked in real-time within your Astrologer Dashboard and paid out on a regular schedule.' => 'आपकी सभी कमाई को आपके ज्योतिषी डैशबोर्ड के भीतर वास्तविक समय में ट्रैक किया जाता है और नियमित कार्यक्रम पर भुगतान किया जाता है।',
            'Detailed Rate Information' => 'विस्तृत दर की जानकारी',
            'For specific details regarding commission percentages, tiered structures, and payout schedules, please contact the Admin via Customer Support.' => 'कमीशन प्रतिशत, स्तरीय संरचनाओं और भुगतान कार्यक्रम के बारे में विशिष्ट विवरण के लिए, कृपया ग्राहक सहायता के माध्यम से एडमिन से संपर्क करें।',

            'Contact Support for Queries' => 'प्रश्नों के लिए सहायता से संपर्क करें',
            'Have questions about our commission structure or documentation? Reach out to our admin team:' => 'क्या हमारे कमीशन संरचना या दस्तावेज़ीकरण के बारे में प्रश्न हैं? हमारी एडमिन टीम से संपर्क करें:',
            'Business Name' => 'व्यापार का नाम',
            'Email' => 'ईमेल',
            'Phone/WhatsApp' => 'फोन/व्हाट्सएप',

            'Ready to Start?' => 'शुरू करने के लिए तैयार हैं?',
            'Once you have your documents ready and understand our model, proceed to the registration page to create your account.' => 'एक बार जब आपके दस्तावेज़ तैयार हो जाएं और आप हमारे मॉडल को समझ लें, तो अपना खाता बनाने के लिए पंजीकरण पृष्ठ पर जाएं।',
            'Proceed to Registration →' => 'पंजीकरण के लिए आगे बढ़ें →'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Information for Astrologers') ?> - The Fortune Pathway</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .no-scrollbar { scrollbar-width:none; }
        
        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .bg-gray-50, body.dark-theme .bg-gray-100 { background-color: #121212 !important; border-color: #374151 !important;}
        body.dark-theme .bg-gradient-to-b.from-gray-50.to-white { background: #121212 !important; }
        
        body.dark-theme .text-gray-900, body.dark-theme .text-gray-800, body.dark-theme .text-black { color: #ffffff !important; }
        body.dark-theme .text-gray-600, body.dark-theme .text-gray-500, body.dark-theme .text-gray-700 { color: #d1d5db !important; }
        
        body.dark-theme .border-gray-100, body.dark-theme .border-gray-200 { border-color: #374151 !important; }
        
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; border: 1px solid #059669 !important; }
        body.dark-theme .bg-black:hover { background-color: #059669 !important; }
        body.dark-theme .bg-green-50 { background-color: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.2) !important; }
        body.dark-theme .text-green-800 { color: #34d399 !important; }
    </style>
</head>

<body class="bg-gray-50 overflow-x-hidden">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

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
    <div class="max-w-5xl mx-auto px-6 lg:px-12">
        
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <span class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gray-100 border border-gray-200 text-gray-700 font-semibold uppercase tracking-widest text-xs mb-6">✦ <?= __('Partner With Us') ?></span>
            <h1 class="text-4xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Join The Fortune Pathway Family') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed max-w-3xl mx-auto"><?= __('Review the necessary details, documentation requirements, and our operational model before registering as an expert on our platform.') ?></p>
        </div>

        <div class="space-y-8">
            
            <!-- Why Join Us Section -->
            <div class="bg-white border border-gray-100 rounded-[2.5rem] p-8 lg:p-12 shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-3">
                    <span class="material-icons text-indigo-600">stars</span> <?= __('Why Join Us?') ?>
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 text-indigo-600"><span class="material-icons">public</span></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= __('Global Reach') ?></h3>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= __('Connect with clients worldwide and expand your consultation services without geographical limits.') ?></p>
                    </div>
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 text-indigo-600"><span class="material-icons">schedule</span></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= __('Flexible Schedule') ?></h3>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= __('Work on your own terms. Log in and provide consultations whenever you are available.') ?></p>
                    </div>
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 text-indigo-600"><span class="material-icons">security</span></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= __('Secure Platform') ?></h3>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= __('Your data and earnings are protected with our high-level security and transparent system.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Required Documentation Section -->
            <div class="bg-white border border-gray-100 rounded-[2.5rem] p-8 lg:p-12 shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                    <span class="material-icons text-amber-500">folder_shared</span> <?= __('Required Documentation') ?>
                </h2>
                <p class="text-gray-600 leading-relaxed mb-8"><?= __('To maintain the authenticity and quality of our platform, all astrologers must undergo a verification process. Please keep clear digital copies of the following documents ready:') ?></p>
                
                <ul class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <li class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="material-icons text-amber-500 mt-1">badge</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Government Identity Proof') ?></h4>
                            <p class="text-sm text-gray-500"><?= __('(e.g., Aadhaar Card, PAN Card, Passport)') ?></p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="material-icons text-amber-500 mt-1">home</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Address Proof') ?></h4>
                            <p class="text-sm text-gray-500"><?= __('(e.g., Voter ID, Driving License, Recent Utility Bill)') ?></p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="material-icons text-amber-500 mt-1">school</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Professional Certifications') ?></h4>
                            <p class="text-sm text-gray-500"><?= __('Degrees, diplomas, or certificates in Astrology, Vastu, Numerology, or related fields.') ?></p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="material-icons text-amber-500 mt-1">account_balance</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Bank Account Details') ?></h4>
                            <p class="text-sm text-gray-500"><?= __('A cancelled cheque or passbook copy for processing your payouts securely.') ?></p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100 lg:col-span-2">
                        <span class="material-icons text-amber-500 mt-1">account_circle</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Recent Photograph') ?></h4>
                            <p class="text-sm text-gray-500"><?= __('A professional, clear headshot for your public profile.') ?></p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Commission & Earnings Section -->
            <div class="bg-white border border-gray-100 rounded-[2.5rem] p-8 lg:p-12 shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                    <span class="material-icons text-green-600">payments</span> <?= __('Commission & Earnings') ?>
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6"><?= __('The Fortune Pathway operates on a mutually beneficial commission-based model. We believe in growing together.') ?></p>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <span class="material-icons text-green-600 mt-1">pie_chart</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Revenue Sharing') ?></h4>
                            <p class="text-gray-600"><?= __('Earnings from consultations (chat, call, video) and related services are shared based on platform guidelines.') ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="material-icons text-green-600 mt-1">receipt_long</span>
                        <div>
                            <h4 class="font-bold text-gray-900"><?= __('Transparent Payouts') ?></h4>
                            <p class="text-gray-600"><?= __('All your earnings are tracked in real-time within your Astrologer Dashboard and paid out on a regular schedule.') ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-6 bg-green-50 border border-green-200 rounded-2xl">
                        <h4 class="font-bold text-green-800 flex items-center gap-2 mb-2">
                            <span class="material-icons text-xl">info</span> <?= __('Detailed Rate Information') ?>
                        </h4>
                        <p class="text-green-800 text-sm leading-relaxed"><?= __('For specific details regarding commission percentages, tiered structures, and payout schedules, please contact the Admin via Customer Support.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Contact & CTA Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Contact Info -->
                <div class="bg-gray-50 border border-gray-200 p-8 rounded-[2.5rem]">
                    <h3 class="text-xl font-bold text-gray-900 mb-4"><?= __('Contact Support for Queries') ?></h3>
                    <p class="text-gray-600 text-sm mb-6"><?= __('Have questions about our commission structure or documentation? Reach out to our admin team:') ?></p>
                    <div class="space-y-3">
                        <p class="text-gray-900 font-semibold text-sm"><?= __('Business Name') ?>: <span class="font-normal text-gray-600">The Fortune Pathway</span></p>
                        <p class="text-gray-900 font-semibold text-sm"><?= __('Email') ?>: <a href="mailto:info@thefortunepathway.com" class="font-normal text-gray-600 hover:text-black transition">info@thefortunepathway.com</a></p>
                        <p class="text-gray-900 font-semibold text-sm"><?= __('Phone/WhatsApp') ?>: <a href="tel:+919336301113" class="font-normal text-gray-600 hover:text-black transition">+91 93363 01113</a></p>
                    </div>
                </div>

                <!-- Final Call to Action -->
                <div class="bg-white border border-gray-200 p-8 rounded-[2.5rem] flex flex-col justify-center items-center text-center shadow-sm">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4"><?= __('Ready to Start?') ?></h3>
                    <p class="text-gray-600 text-sm mb-8 px-4"><?= __('Once you have your documents ready and understand our model, proceed to the registration page to create your account.') ?></p>
                    <a href="/astrologer/register.php" class="w-full max-w-xs py-4 bg-black text-white rounded-2xl font-bold text-lg shadow-xl hover:bg-gray-800 transition-all transform hover:-translate-y-1"><?= __('Proceed to Registration →') ?></a>
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