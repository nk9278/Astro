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
            'Refund and Cancellation Policy' => 'धनवापसी और रद्दीकरण नीति',
            'Legal Policies' => 'कानूनी नीतियां',
            'Thank you for choosing The Fortune Pathway (TFP) for astrology, numerology, vastu, pooja, spiritual guidance, and related services. Please read this Refund and Cancellation Policy carefully before making any booking or purchase.' => 'ज्योतिष, अंक ज्योतिष, वास्तु, पूजा, आध्यात्मिक मार्गदर्शन और संबंधित सेवाओं के लिए द फॉर्च्यून पाथवे (TFP) को चुनने के लिए धन्यवाद। कोई भी बुकिंग या खरीदारी करने से पहले कृपया इस धनवापसी और रद्दीकरण नीति को ध्यान से पढ़ें।',
            
            '1. Consultation Fees' => '1. परामर्श शुल्क',
            'All consultation fees must be paid in advance to confirm the booking, unless otherwise specified by us' => 'जब तक हमारे द्वारा अन्यथा निर्दिष्ट न किया जाए, बुकिंग की पुष्टि करने के लिए सभी परामर्श शुल्क का भुगतान अग्रिम रूप से किया जाना चाहिए',
            'Booking confirmation will only be processed after successful receipt of payment' => 'बुकिंग की पुष्टि केवल भुगतान की सफल प्राप्ति के बाद ही की जाएगी',

            '2. Refund Policy' => '2. धनवापसी नीति',
            'Once a consultation session has been completed, no refund will be issued' => 'एक बार परामर्श सत्र पूरा हो जाने के बाद, कोई धनवापसी जारी नहीं की जाएगी',
            'Once a personalised report, remedy plan, spiritual recommendation, or digital service has been prepared, started, sent, delivered, unlocked, or accessed, no refund will be issued' => 'एक बार व्यक्तिगत रिपोर्ट, उपाय योजना, आध्यात्मिक सिफारिश या डिजिटल सेवा तैयार, शुरू, भेजी, वितरित, अनलॉक या एक्सेस की गई हो, तो कोई धनवापसी जारी नहीं की जाएगी',
            'Refund requests may only be considered in limited cases, such as:' => 'धनवापसी अनुरोधों पर केवल सीमित मामलों में विचार किया जा सकता है, जैसे:',
            'Duplicate payment' => 'डुप्लिकेट भुगतान',
            'Proven technical failure from our side' => 'हमारी ओर से प्रमाणित तकनीकी विफलता',
            'Non-delivery of the booked service due to our fault' => 'हमारी गलती के कारण बुक की गई सेवा की गैर-वितरण',
            'Incorrect booking caused by our error' => 'हमारी त्रुटि के कारण हुई गलत बुकिंग',
            'Refund approval is solely at the reasonable discretion of The Fortune Pathway, subject to applicable law' => 'लागू कानून के अधीन, धनवापसी की मंजूरी पूरी तरह से द फॉर्च्यून पाथवे के उचित विवेक पर है',
            'Approved refunds, if any, may take a reasonable processing period depending on the payment method, gateway, app store, banking system, or platform used' => 'स्वीकृत धनवापसी, यदि कोई हो, में उपयोग की गई भुगतान विधि, गेटवे, ऐप स्टोर, बैंकिंग सिस्टम या प्लेटफ़ॉर्म के आधार पर उचित प्रसंस्करण अवधि लग सकती है',
            'Promotional, discounted, free-bonus, bundled, or special offer consultations and services are non-refundable unless required by law' => 'प्रचार, रियायती, मुफ्त-बोनस, बंडल, या विशेष प्रस्ताव परामर्श और सेवाएं तब तक गैर-वापसी योग्य हैं जब तक कि कानून द्वारा आवश्यक न हो',

            '3. Cancellation Policy' => '3. रद्दीकरण नीति',
            'Clients must cancel or request rescheduling at least 2 hours before the scheduled consultation time unless a different notice period is specifically mentioned for that service' => 'ग्राहकों को निर्धारित परामर्श समय से कम से कम 2 घंटे पहले रद्दीकरण या पुनर्निर्धारण का अनुरोध करना चाहिए जब तक कि उस सेवा के लिए विशेष रूप से एक अलग नोटिस अवधि का उल्लेख न किया गया हो',
            'Cancellations made within the allowed time may be eligible for rescheduling, subject to availability' => 'अनुमत समय के भीतर किए गए रद्दीकरण उपलब्धता के अधीन, पुनर्निर्धारण के लिए पात्र हो सकते हैं',
            'Repeated cancellations, abuse of the booking system, or repeated rescheduling may result in the refusal of future rescheduling requests' => 'बार-बार रद्दीकरण, बुकिंग प्रणाली के दुरुपयोग, या बार-बार पुनर्निर्धारण के परिणामस्वरूप भविष्य के पुनर्निर्धारण अनुरोधों को अस्वीकार किया जा सकता है',

            '4. Missed Appointments' => '4. छूटे हुए अपॉइंटमेंट',
            'If a client does not attend the scheduled consultation session without prior notice, the booking may be treated as completed and non-refundable' => 'यदि कोई ग्राहक पूर्व सूचना के बिना निर्धारित परामर्श सत्र में शामिल नहीं होता है, तो बुकिंग को पूर्ण माना जा सकता है और कोई धनवापसी नहीं की जाएगी',
            'If the client is significantly late, the session duration may be shortened based on scheduling limitations without a refund' => 'यदि ग्राहक काफी देर से आता है, तो शेड्यूलिंग सीमाओं के आधार पर बिना किसी धनवापसी के सत्र की अवधि कम की जा सकती है',
            'If our consultant is unavailable or there is a verified issue from our side, the session may be rescheduled or otherwise reasonably resolved' => 'यदि हमारे सलाहकार अनुपलब्ध हैं या हमारी ओर से कोई सत्यापित समस्या है, तो सत्र को पुनर्निर्धारित किया जा सकता है या अन्यथा उचित रूप से हल किया जा सकता है',

            '5. Poojas, Remedies, and Spiritual Services' => '5. पूजा, उपाय और आध्यात्मिक सेवाएं',
            'Once a pooja, spiritual process, ritual-related arrangement, or remedy-related service has been initiated, arranged, booked, assigned, or performed, it is non-refundable' => 'एक बार जब कोई पूजा, आध्यात्मिक प्रक्रिया, अनुष्ठान-संबंधी व्यवस्था या उपाय-संबंधी सेवा शुरू, व्यवस्थित, बुक, सौंपी या निष्पादित की जाती है, तो यह गैर-वापसी योग्य है',
            'Costs related to ritual materials, arrangements, priest bookings, customised spiritual items, or third-party service providers may be non-cancellable once confirmed' => 'पुष्टि होने के बाद अनुष्ठान सामग्री, व्यवस्था, पुजारी बुकिंग, अनुकूलित आध्यात्मिक वस्तुओं या तृतीय-पक्ष सेवा प्रदाताओं से संबंधित लागतें गैर-रद्द करने योग्य हो सकती हैं',

            '6. Physical Products' => '6. भौतिक उत्पाद',
            'If you purchase products from our Astro shop or Vastu shop:' => 'यदि आप हमारे एस्ट्रो शॉप या वास्तु शॉप से उत्पाद खरीदते हैं:',
            'Damaged, defective, or wrongly delivered items may be eligible for replacement or other support, subject to verification' => 'क्षतिग्रस्त, दोषपूर्ण, या गलत तरीके से वितरित वस्तुएं सत्यापन के अधीन, प्रतिस्थापन या अन्य समर्थन के लिए पात्र हो सकती हैं',
            'This includes gemstones, rudraksha, malas, bracelets, pyrite products, energized items, and other spiritual, astrology, and vastu-related goods sold through our platform' => 'इसमें रत्न, रुद्राक्ष, माला, कंगन, पाइराइट उत्पाद, अभिमंत्रित वस्तुएं, और हमारे मंच के माध्यम से बेची जाने वाली अन्य आध्यात्मिक, ज्योतिष और वास्तु-संबंधित वस्तुएं शामिल हैं',
            'Used, opened, customized, energized, size-altered, blessed, specially sourced, or otherwise non-returnable items may not be eligible for return, replacement, or refund unless they are damaged, defective, or wrongly delivered' => 'उपयोग किए गए, खोले गए, अनुकूलित, अभिमंत्रित, आकार-परिवर्तित, आशीर्वाद दिए गए, विशेष रूप से प्राप्त या अन्यथा गैर-वापसी योग्य आइटम वापसी, प्रतिस्थापन या धनवापसी के लिए पात्र नहीं हो सकते हैं जब तक कि वे क्षतिग्रस्त, दोषपूर्ण या गलत तरीके से वितरित न हों',
            'Shipping fees, packaging charges, platform charges, handling charges, and similar costs may be non-refundable unless otherwise determined by us' => 'शिपिंग शुल्क, पैकेजिंग शुल्क, प्लेटफॉर्म शुल्क, हैंडलिंग शुल्क और इसी तरह की लागतें गैर-वापसी योग्य हो सकती हैं जब तक कि हमारे द्वारा अन्यथा निर्धारित न किया जाए',
            'A separate product return, replacement, and shipping policy may apply where relevant' => 'जहां प्रासंगिक हो, एक अलग उत्पाद वापसी, प्रतिस्थापन और शिपिंग नीति लागू हो सकती है',

            '7. Digital Products, Reports, and Subscriptions' => '7. डिजिटल उत्पाद, रिपोर्ट और सदस्यता',
            'If you purchase digital reports, downloadable content, premium features, subscriptions, recurring plans, or other digital services:' => 'यदि आप डिजिटल रिपोर्ट, डाउनलोड करने योग्य सामग्री, प्रीमियम सुविधाएँ, सदस्यता, आवर्ती योजनाएँ या अन्य डिजिटल सेवाएँ खरीदते हैं:',
            'Such products or services may be non-refundable once access has been granted, delivery has begun, or the content has been generated or unlocked' => 'पहुंच प्रदान किए जाने, वितरण शुरू होने, या सामग्री उत्पन्न या अनलॉक होने के बाद ऐसे उत्पाद या सेवाएं गैर-वापसी योग्य हो सकती हैं',
            'Cancellation of a subscription, where applicable, may prevent future billing but will not normally result in a refund for the current billing cycle' => 'सदस्यता रद्द करना, जहां लागू हो, भविष्य के बिलिंग को रोक सकता है लेकिन आमतौर पर वर्तमान बिलिंग चक्र के लिए धनवापसी नहीं होगी',
            'Third-party billing systems, app store purchase rules, or payment gateway procedures may also apply where relevant' => 'जहां प्रासंगिक हो, तृतीय-पक्ष बिलिंग सिस्टम, ऐप स्टोर खरीद नियम, या पेमेंट गेटवे प्रक्रियाएं भी लागू हो सकती हैं',

            '8. Promotional Offers' => '8. प्रचार प्रस्ताव',
            'Special promotional consultations, discounted bookings, bundled services, trial offers, free-bonus packages, and limited-time offers are non-refundable and non-transferable unless required by law' => 'विशेष प्रचार परामर्श, रियायती बुकिंग, बंडल सेवाएं, परीक्षण प्रस्ताव, मुफ्त-बोनस पैकेज, और सीमित समय के प्रस्ताव गैर-वापसी योग्य और गैर-हस्तांतरणीय हैं जब तक कि कानून द्वारा आवश्यक न हो',

            '9. Shipping and Delivery' => '9. शिपिंग और डिलीवरी',
            'For physical products purchased through our platform:' => 'हमारे प्लेटफॉर्म के माध्यम से खरीदे गए भौतिक उत्पादों के लिए:',
            'Dispatch and delivery timelines may vary depending on product availability, location, courier services, customization, energizing process, or other operational factors' => 'प्रेषण और वितरण समय-सीमा उत्पाद की उपलब्धता, स्थान, कूरियर सेवाओं, अनुकूलन, अभिमंत्रित करने की प्रक्रिया या अन्य परिचालन कारकों के आधार पर भिन्न हो सकती है',
            'Delivery dates are estimates only and are not guaranteed unless specifically stated' => 'वितरण तिथियां केवल अनुमान हैं और जब तक विशेष रूप से न कहा जाए तब तक गारंटी नहीं दी जाती है',
            'Customers should inspect parcels upon delivery and report damaged, defective, missing, or incorrect items within 48 hours of delivery' => 'ग्राहकों को डिलीवरी पर पार्सल का निरीक्षण करना चाहिए और डिलीवरी के 48 घंटों के भीतर क्षतिग्रस्त, दोषपूर्ण, गायब या गलत वस्तुओं की रिपोर्ट करनी चाहिए',
            'Claims made after an unreasonable delay may not be accepted' => 'अनुचित देरी के बाद किए गए दावों को स्वीकार नहीं किया जा सकता है',
            'Delays caused by couriers, weather, strikes, force majeure events, incorrect address details, or customer unavailability may not create refund liability on our part' => 'कोरियर, मौसम, हड़ताल, अप्रत्याशित घटनाओं, गलत पते के विवरण, या ग्राहक की अनुपलब्धता के कारण होने वाली देरी से हमारी ओर से कोई धनवापसी देयता उत्पन्न नहीं हो सकती है',

            '10. Payment Platform Rules' => '10. भुगतान प्लेटफ़ॉर्म नियम',
            'If payments are made through third-party gateways, app stores, or platform billing systems, their processing timelines, technical systems, and procedural rules may also apply' => 'यदि भुगतान तृतीय-पक्ष गेटवे, ऐप स्टोर या प्लेटफ़ॉर्म बिलिंग सिस्टम के माध्यम से किए जाते हैं, तो उनकी प्रसंस्करण समय-सीमा, तकनीकी प्रणाली और प्रक्रियात्मक नियम भी लागू हो सकते हैं',
            'Any platform-mandated refund process may operate separately where required' => 'कोई भी प्लेटफ़ॉर्म-अनिवार्य धनवापसी प्रक्रिया जहां आवश्यक हो अलग से संचालित हो सकती है',

            '11. Contact for Support' => '11. समर्थन के लिए संपर्क करें',
            'For cancellation, rescheduling, refund, return, replacement, shipping, or support requests, please contact us at:' => 'रद्दीकरण, पुनर्निर्धारण, धनवापसी, वापसी, प्रतिस्थापन, शिपिंग या समर्थन अनुरोधों के लिए, कृपया हमसे संपर्क करें:',
            'Business Name' => 'व्यापार का नाम',
            'Email' => 'ईमेल',
            'Phone/WhatsApp' => 'फोन/व्हाट्सएप',
            'Website' => 'वेबसाइट'
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
    <title><?= __('Refund and Cancellation Policy') ?> - The Fortune Pathway</title>
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
            <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Refund and Cancellation Policy') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed"><?= __('Thank you for choosing The Fortune Pathway (TFP) for astrology, numerology, vastu, pooja, spiritual guidance, and related services. Please read this Refund and Cancellation Policy carefully before making any booking or purchase.') ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-[3rem] p-8 lg:p-16 shadow-xl space-y-12">
            
            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('1. Consultation Fees') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('All consultation fees must be paid in advance to confirm the booking, unless otherwise specified by us') ?></li>
                    <li><?= __('Booking confirmation will only be processed after successful receipt of payment') ?></li>
                </ul>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('2. Refund Policy') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Once a consultation session has been completed, no refund will be issued') ?></li>
                    <li><?= __('Once a personalised report, remedy plan, spiritual recommendation, or digital service has been prepared, started, sent, delivered, unlocked, or accessed, no refund will be issued') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Refund requests may only be considered in limited cases, such as:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Duplicate payment') ?></li>
                    <li><?= __('Proven technical failure from our side') ?></li>
                    <li><?= __('Non-delivery of the booked service due to our fault') ?></li>
                    <li><?= __('Incorrect booking caused by our error') ?></li>
                </ul>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Refund approval is solely at the reasonable discretion of The Fortune Pathway, subject to applicable law') ?></li>
                    <li><?= __('Approved refunds, if any, may take a reasonable processing period depending on the payment method, gateway, app store, banking system, or platform used') ?></li>
                    <li><?= __('Promotional, discounted, free-bonus, bundled, or special offer consultations and services are non-refundable unless required by law') ?></li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('3. Cancellation Policy') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Clients must cancel or request rescheduling at least 2 hours before the scheduled consultation time unless a different notice period is specifically mentioned for that service') ?></li>
                    <li><?= __('Cancellations made within the allowed time may be eligible for rescheduling, subject to availability') ?></li>
                    <li><?= __('Repeated cancellations, abuse of the booking system, or repeated rescheduling may result in the refusal of future rescheduling requests') ?></li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('4. Missed Appointments') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('If a client does not attend the scheduled consultation session without prior notice, the booking may be treated as completed and non-refundable') ?></li>
                    <li><?= __('If the client is significantly late, the session duration may be shortened based on scheduling limitations without a refund') ?></li>
                    <li><?= __('If our consultant is unavailable or there is a verified issue from our side, the session may be rescheduled or otherwise reasonably resolved') ?></li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('5. Poojas, Remedies, and Spiritual Services') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Once a pooja, spiritual process, ritual-related arrangement, or remedy-related service has been initiated, arranged, booked, assigned, or performed, it is non-refundable') ?></li>
                    <li><?= __('Costs related to ritual materials, arrangements, priest bookings, customised spiritual items, or third-party service providers may be non-cancellable once confirmed') ?></li>
                </ul>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('6. Physical Products') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If you purchase products from our Astro shop or Vastu shop:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Damaged, defective, or wrongly delivered items may be eligible for replacement or other support, subject to verification') ?></li>
                    <li><?= __('This includes gemstones, rudraksha, malas, bracelets, pyrite products, energized items, and other spiritual, astrology, and vastu-related goods sold through our platform') ?></li>
                    <li><?= __('Used, opened, customized, energized, size-altered, blessed, specially sourced, or otherwise non-returnable items may not be eligible for return, replacement, or refund unless they are damaged, defective, or wrongly delivered') ?></li>
                    <li><?= __('Shipping fees, packaging charges, platform charges, handling charges, and similar costs may be non-refundable unless otherwise determined by us') ?></li>
                    <li><?= __('A separate product return, replacement, and shipping policy may apply where relevant') ?></li>
                </ul>
            </div>

            <!-- Section 7 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('7. Digital Products, Reports, and Subscriptions') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If you purchase digital reports, downloadable content, premium features, subscriptions, recurring plans, or other digital services:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Such products or services may be non-refundable once access has been granted, delivery has begun, or the content has been generated or unlocked') ?></li>
                    <li><?= __('Cancellation of a subscription, where applicable, may prevent future billing but will not normally result in a refund for the current billing cycle') ?></li>
                    <li><?= __('Third-party billing systems, app store purchase rules, or payment gateway procedures may also apply where relevant') ?></li>
                </ul>
            </div>

            <!-- Section 8 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('8. Promotional Offers') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('Special promotional consultations, discounted bookings, bundled services, trial offers, free-bonus packages, and limited-time offers are non-refundable and non-transferable unless required by law') ?></p>
            </div>

            <!-- Section 9 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('9. Shipping and Delivery') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('For physical products purchased through our platform:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Dispatch and delivery timelines may vary depending on product availability, location, courier services, customization, energizing process, or other operational factors') ?></li>
                    <li><?= __('Delivery dates are estimates only and are not guaranteed unless specifically stated') ?></li>
                    <li><?= __('Customers should inspect parcels upon delivery and report damaged, defective, missing, or incorrect items within 48 hours of delivery') ?></li>
                    <li><?= __('Claims made after an unreasonable delay may not be accepted') ?></li>
                    <li><?= __('Delays caused by couriers, weather, strikes, force majeure events, incorrect address details, or customer unavailability may not create refund liability on our part') ?></li>
                </ul>
            </div>

            <!-- Section 10 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('10. Payment Platform Rules') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('If payments are made through third-party gateways, app stores, or platform billing systems, their processing timelines, technical systems, and procedural rules may also apply') ?></li>
                    <li><?= __('Any platform-mandated refund process may operate separately where required') ?></li>
                </ul>
            </div>

            <!-- Section 11 - Contact (Emails/Numbers hardcoded for intelligent display) -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('11. Contact for Support') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-2"><?= __('For cancellation, rescheduling, refund, return, replacement, shipping, or support requests, please contact us at:') ?></p>
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