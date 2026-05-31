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
            'Disclaimer Policy' => 'अस्वीकरण नीति',
            'Legal Policies' => 'कानूनी नीतियां',
            'All services provided by The Fortune Pathway (TFP) are based on traditional astrology, numerology, vastu, spiritual principles, and interpretive methods.' => 'द फॉर्च्यून पाथवे (TFP) द्वारा प्रदान की गई सभी सेवाएं पारंपरिक ज्योतिष, अंक ज्योतिष, वास्तु, आध्यात्मिक सिद्धांतों और व्याख्यात्मक तरीकों पर आधारित हैं।',
            
            '1. For Guidance Purposes Only' => '1. केवल मार्गदर्शन उद्देश्यों के लिए',
            'All consultations, predictions, reports, remedies, poojas, products, and recommendations are provided for personal guidance, self-reflection, spiritual insight, and advisory purposes only' => 'सभी परामर्श, भविष्यवाणियां, रिपोर्ट, उपाय, पूजा, उत्पाद और सिफारिशें केवल व्यक्तिगत मार्गदर्शन, आत्म-चिंतन, आध्यात्मिक अंतर्दृष्टि और सलाहकार उद्देश्यों के लिए प्रदान की जाती हैं',
            'These services are interpretive in nature and should not be understood as statements of fact or guaranteed future outcomes' => 'ये सेवाएं प्रकृति में व्याख्यात्मक हैं और इन्हें तथ्य के बयान या भविष्य के गारंटीकृत परिणामों के रूप में नहीं समझा जाना चाहिए',

            '2. No Guaranteed Results' => '2. कोई गारंटीकृत परिणाम नहीं',
            'Astrology, numerology, vastu, spiritual remedies, and related systems are not exact sciences' => 'ज्योतिष, अंक ज्योतिष, वास्तु, आध्यात्मिक उपाय और संबंधित प्रणालियां सटीक विज्ञान नहीं हैं',
            'Predictions, interpretations, guidance, and recommendations may vary' => 'भविष्यवाणियां, व्याख्याएं, मार्गदर्शन और सिफारिशें भिन्न हो सकती हैं',
            'Outcomes cannot be guaranteed' => 'परिणामों की गारंटी नहीं दी जा सकती है',
            'Results depend on many personal, practical, emotional, karmic, spiritual, environmental, and external factors beyond our control' => 'परिणाम हमारे नियंत्रण से परे कई व्यक्तिगत, व्यावहारिक, भावनात्मक, कर्म, आध्यात्मिक, पर्यावरणीय और बाहरी कारकों पर निर्भर करते हैं',
            'No consultation, product, remedy, gemstone, rudraksha, mala, bracelet, pooja, or suggestion is guaranteed to create a specific result' => 'किसी भी परामर्श, उत्पाद, उपाय, रत्न, रुद्राक्ष, माला, कंगन, पूजा या सुझाव के किसी विशिष्ट परिणाम की गारंटी नहीं है',

            '3. Not a Substitute for Professional Advice' => '3. पेशेवर सलाह का विकल्प नहीं',
            'Our services do not replace professional advice, diagnosis, treatment, or assistance, including but not limited to:' => 'हमारी सेवाएं पेशेवर सलाह, निदान, उपचार या सहायता का स्थान नहीं लेती हैं, जिनमें निम्नलिखित शामिल हैं लेकिन इन्हीं तक सीमित नहीं हैं:',
            'Medical advice' => 'चिकित्सीय सलाह',
            'Legal advice' => 'कानूनी सलाह',
            'Financial or investment advice' => 'वित्तीय या निवेश सलाह',
            'Psychological or psychiatric counseling' => 'मनोवैज्ञानिक या मनोरोग परामर्श',
            'Relationship, marriage, or family counseling' => 'रिश्ता, विवाह या पारिवारिक परामर्श',
            'Emergency support or crisis intervention' => 'आपातकालीन सहायता या संकट हस्तक्षेप',
            'Clients should consult qualified professionals wherever such assistance is needed.' => 'जहां भी ऐसी सहायता की आवश्यकता हो, ग्राहकों को योग्य पेशेवरों से परामर्श करना चाहिए।',

            '4. Personal Responsibility' => '4. व्यक्तिगत जिम्मेदारी',
            'Any decision, action, or inaction taken by a client after receiving a consultation, report, product recommendation, remedy, or spiritual guidance is the client’s own responsibility' => 'परामर्श, रिपोर्ट, उत्पाद अनुशंसा, उपाय या आध्यात्मिक मार्गदर्शन प्राप्त करने के बाद ग्राहक द्वारा लिया गया कोई भी निर्णय, कार्रवाई या निष्क्रियता ग्राहक की अपनी जिम्मेदारी है',
            'The Fortune Pathway shall not be responsible for any personal, emotional, relationship, legal, financial, professional, health, or business outcome resulting from use of our services, products, or recommendations' => 'द फॉर्च्यून पाथवे हमारी सेवाओं, उत्पादों या सिफारिशों के उपयोग के परिणामस्वरूप किसी भी व्यक्तिगत, भावनात्मक, संबंध, कानूनी, वित्तीय, पेशेवर, स्वास्थ्य या व्यावसायिक परिणाम के लिए जिम्मेदार नहीं होगा',

            '5. Spiritual and Interpretive Nature of Services' => '5. सेवाओं की आध्यात्मिक और व्याख्यात्मक प्रकृति',
            'Our consultations and recommendations are based on traditional systems, practitioner interpretation, and information provided by the client' => 'हमारे परामर्श और सिफारिशें पारंपरिक प्रणालियों, व्यवसायी की व्याख्या और ग्राहक द्वारा प्रदान की गई जानकारी पर आधारित हैं',
            'Different practitioners may interpret the same information differently' => 'विभिन्न व्यवसायी एक ही जानकारी की अलग-अलग व्याख्या कर सकते हैं',
            'The client understands that spiritual guidance and predictive systems are not exact or universally verifiable methods' => 'ग्राहक समझता है कि आध्यात्मिक मार्गदर्शन और भविष्य कहनेवाला प्रणालियां सटीक या सार्वभौमिक रूप से सत्यापन योग्य तरीके नहीं हैं',

            '6. Product and Remedy Disclaimer' => '6. उत्पाद और उपाय अस्वीकरण',
            'Any gemstone, rudraksha, mala, bracelet, pyrite item, spiritual product, vastu item, pooja, mantra, or remedy recommended or sold through our platform is offered as a traditional, spiritual, and belief-based suggestion only' => 'हमारे प्लेटफॉर्म के माध्यम से अनुशंसित या बेचा जाने वाला कोई भी रत्न, रुद्राक्ष, माला, कंगन, पाइराइट आइटम, आध्यात्मिक उत्पाद, वास्तु आइटम, पूजा, मंत्र या उपाय केवल एक पारंपरिक, आध्यात्मिक और विश्वास-आधारित सुझाव के रूप में पेश किया जाता है',
            'Such products or remedies are not guaranteed cures, guaranteed protections, or guaranteed solutions to personal, health, relationship, financial, legal, or business problems' => 'ऐसे उत्पाद या उपाय व्यक्तिगत, स्वास्थ्य, संबंध, वित्तीय, कानूनी या व्यावसायिक समस्याओं के लिए गारंटीकृत इलाज, गारंटीकृत सुरक्षा या गारंटीकृत समाधान नहीं हैं',
            'Results, experiences, and beliefs may vary from person to person depending on individual circumstances, faith, effort, and other external factors beyond our control' => 'व्यक्तिगत परिस्थितियों, विश्वास, प्रयास और हमारे नियंत्रण से बाहर के अन्य बाहरी कारकों के आधार पर परिणाम, अनुभव और विश्वास एक व्यक्ति से दूसरे व्यक्ति में भिन्न हो सकते हैं',

            '7. Website, App, and Technical Availability' => '7. वेबसाइट, ऐप और तकनीकी उपलब्धता',
            'We do not guarantee that the website, mobile app, booking systems, payment systems, or related digital platforms will always be uninterrupted, error-free, or available at all times' => 'हम इस बात की गारंटी नहीं देते हैं कि वेबसाइट, मोबाइल ऐप, बुकिंग सिस्टम, भुगतान सिस्टम या संबंधित डिजिटल प्लेटफॉर्म हमेशा निर्बाध, त्रुटि मुक्त या हर समय उपलब्ध रहेंगे',
            'Temporary interruptions may occur due to technical maintenance, app issues, connectivity problems, third-party failures, or force majeure events' => 'तकनीकी रखरखाव, ऐप समस्याओं, कनेक्टिविटी समस्याओं, तृतीय-पक्ष विफलताओं या अप्रत्याशित घटनाओं के कारण अस्थायी रुकावटें आ सकती हैं',

            '8. Acceptance of Disclaimer' => '8. अस्वीकरण की स्वीकृति',
            'By using our website, app, products, or services, or by booking a consultation with The Fortune Pathway, you acknowledge that:' => 'हमारी वेबसाइट, ऐप, उत्पादों या सेवाओं का उपयोग करके, या द फॉर्च्यून पाथवे के साथ परामर्श बुक करके, आप स्वीकार करते हैं कि:',
            'You have read and understood this Disclaimer' => 'आपने इस अस्वीकरण को पढ़ और समझ लिया है',
            'You accept the interpretive and non-guaranteed nature of the services and products' => 'आप सेवाओं और उत्पादों की व्याख्यात्मक और गैर-गारंटीकृत प्रकृति को स्वीकार करते हैं',
            'You agree to use the services and products at your own discretion and responsibility.' => 'आप अपने विवेक और जिम्मेदारी पर सेवाओं और उत्पादों का उपयोग करने के लिए सहमत हैं।'
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
    <title><?= __('Disclaimer Policy') ?> - The Fortune Pathway</title>
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
            <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Disclaimer Policy') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed"><?= __('All services provided by The Fortune Pathway (TFP) are based on traditional astrology, numerology, vastu, spiritual principles, and interpretive methods.') ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-[3rem] p-8 lg:p-16 shadow-xl space-y-12">
            
            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('1. For Guidance Purposes Only') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('All consultations, predictions, reports, remedies, poojas, products, and recommendations are provided for personal guidance, self-reflection, spiritual insight, and advisory purposes only') ?></li>
                    <li><?= __('These services are interpretive in nature and should not be understood as statements of fact or guaranteed future outcomes') ?></li>
                </ul>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('2. No Guaranteed Results') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Astrology, numerology, vastu, spiritual remedies, and related systems are not exact sciences') ?></li>
                    <li><?= __('Predictions, interpretations, guidance, and recommendations may vary') ?></li>
                    <li><?= __('Outcomes cannot be guaranteed') ?></li>
                    <li><?= __('Results depend on many personal, practical, emotional, karmic, spiritual, environmental, and external factors beyond our control') ?></li>
                    <li><?= __('No consultation, product, remedy, gemstone, rudraksha, mala, bracelet, pooja, or suggestion is guaranteed to create a specific result') ?></li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('3. Not a Substitute for Professional Advice') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Our services do not replace professional advice, diagnosis, treatment, or assistance, including but not limited to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Medical advice') ?></li>
                    <li><?= __('Legal advice') ?></li>
                    <li><?= __('Financial or investment advice') ?></li>
                    <li><?= __('Psychological or psychiatric counseling') ?></li>
                    <li><?= __('Relationship, marriage, or family counseling') ?></li>
                    <li><?= __('Emergency support or crisis intervention') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('Clients should consult qualified professionals wherever such assistance is needed.') ?></p>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('4. Personal Responsibility') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Any decision, action, or inaction taken by a client after receiving a consultation, report, product recommendation, remedy, or spiritual guidance is the client’s own responsibility') ?></li>
                    <li><?= __('The Fortune Pathway shall not be responsible for any personal, emotional, relationship, legal, financial, professional, health, or business outcome resulting from use of our services, products, or recommendations') ?></li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('5. Spiritual and Interpretive Nature of Services') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Our consultations and recommendations are based on traditional systems, practitioner interpretation, and information provided by the client') ?></li>
                    <li><?= __('Different practitioners may interpret the same information differently') ?></li>
                    <li><?= __('The client understands that spiritual guidance and predictive systems are not exact or universally verifiable methods') ?></li>
                </ul>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('6. Product and Remedy Disclaimer') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Any gemstone, rudraksha, mala, bracelet, pyrite item, spiritual product, vastu item, pooja, mantra, or remedy recommended or sold through our platform is offered as a traditional, spiritual, and belief-based suggestion only') ?></li>
                    <li><?= __('Such products or remedies are not guaranteed cures, guaranteed protections, or guaranteed solutions to personal, health, relationship, financial, legal, or business problems') ?></li>
                    <li><?= __('Results, experiences, and beliefs may vary from person to person depending on individual circumstances, faith, effort, and other external factors beyond our control') ?></li>
                </ul>
            </div>

            <!-- Section 7 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('7. Website, App, and Technical Availability') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('We do not guarantee that the website, mobile app, booking systems, payment systems, or related digital platforms will always be uninterrupted, error-free, or available at all times') ?></li>
                    <li><?= __('Temporary interruptions may occur due to technical maintenance, app issues, connectivity problems, third-party failures, or force majeure events') ?></li>
                </ul>
            </div>

            <!-- Section 8 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('8. Acceptance of Disclaimer') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('By using our website, app, products, or services, or by booking a consultation with The Fortune Pathway, you acknowledge that:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('You have read and understood this Disclaimer') ?></li>
                    <li><?= __('You accept the interpretive and non-guaranteed nature of the services and products') ?></li>
                    <li><?= __('You agree to use the services and products at your own discretion and responsibility.') ?></li>
                </ul>
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