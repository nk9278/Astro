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
            'Terms and Conditions' => 'नियम और शर्तें',
            'Legal Policies' => 'कानूनी नीतियां',
            'Welcome to The Fortune Pathway (TFP). By accessing or using our website, mobile application, or booking any consultation or purchasing any product or service from us, you agree to comply with and be bound by the following Terms and Conditions. Please read them carefully before using our services.' => 'द फॉर्च्यून पाथवे (TFP) में आपका स्वागत है। हमारी वेबसाइट, मोबाइल एप्लिकेशन तक पहुंचने या उपयोग करने, या हमसे कोई परामर्श बुक करने या कोई उत्पाद या सेवा खरीदने से, आप निम्नलिखित नियमों और शर्तों का अनुपालन करने और उनसे बंधे होने के लिए सहमत होते हैं। कृपया हमारी सेवाओं का उपयोग करने से पहले उन्हें ध्यान से पढ़ें।',
            
            '1. Services Provided' => '1. प्रदान की जाने वाली सेवाएं',
            'The Fortune Pathway provides astrology, numerology, vastu, spiritual guidance, and related services, including but not limited to:' => 'द फॉर्च्यून पाथवे ज्योतिष, अंक ज्योतिष, वास्तु, आध्यात्मिक मार्गदर्शन और संबंधित सेवाएं प्रदान करता है, जिनमें निम्नलिखित शामिल हैं लेकिन इन्हीं तक सीमित नहीं हैं:',
            'Astrology consultations and Kundli analysis' => 'ज्योतिष परामर्श और कुंडली विश्लेषण',
            'Numerology readings' => 'अंक ज्योतिष रीडिंग',
            'Vastu consultations and remedies' => 'वास्तु परामर्श और उपाय',
            'Poojas and spiritual services' => 'पूजा और आध्यात्मिक सेवाएं',
            'Astrology, vastu, and spiritual guidance reports' => 'ज्योतिष, वास्तु और आध्यात्मिक मार्गदर्शन रिपोर्ट',
            'Sale of spiritual products such as gemstones, rudraksha, malas, bracelets, pyrite items, energized items, and other astrology or vastu-related products' => 'रत्न, रुद्राक्ष, माला, ब्रेसलेट, पाइराइट आइटम, अभिमंत्रित आइटम और अन्य ज्योतिष या वास्तु-संबंधित आध्यात्मिक उत्पादों की बिक्री',
            'Astro shop and vastu shop products' => 'एस्ट्रो शॉप और वास्तु शॉप के उत्पाद',
            'Other guidance-based digital or physical services offered through our platform' => 'हमारे प्लेटफॉर्म के माध्यम से दी जाने वाली अन्य मार्गदर्शन-आधारित डिजिटल या भौतिक सेवाएं',
            'All services are spiritual, advisory, and interpretive in nature and are provided for personal guidance, self-reflection, and insight only.' => 'सभी सेवाएं प्रकृति में आध्यात्मिक, सलाहकार और व्याख्यात्मक हैं और केवल व्यक्तिगत मार्गदर्शन, आत्म-चिंतन और अंतर्दृष्टि के लिए प्रदान की जाती हैं।',

            '2. Eligibility' => '2. पात्रता',
            'By using our services, you confirm that:' => 'हमारी सेवाओं का उपयोग करके, आप पुष्टि करते हैं कि:',
            'You are at least 18 years old, or' => 'आपकी आयु कम से कम 18 वर्ष है, या',
            'You are using the service under the supervision and consent of a parent or legal guardian' => 'आप माता-पिता या कानूनी अभिभावक की देखरेख और सहमति के तहत सेवा का उपयोग कर रहे हैं',
            'If you create an account, submit details, make a booking, or use our website or app, you confirm that the information provided by you is accurate, current, and complete.' => 'यदि आप एक खाता बनाते हैं, विवरण जमा करते हैं, बुकिंग करते हैं, या हमारी वेबसाइट या ऐप का उपयोग करते हैं, तो आप पुष्टि करते हैं कि आपके द्वारा प्रदान की गई जानकारी सटीक, वर्तमान और पूर्ण है।',

            '3. Booking and Payments' => '3. बुकिंग और भुगतान',
            'All consultations and services must be booked in advance through our website, app, WhatsApp, phone, or other approved booking channels' => 'सभी परामर्श और सेवाएं हमारी वेबसाइट, ऐप, व्हाट्सएप, फोन या अन्य स्वीकृत बुकिंग माध्यमों से पहले ही बुक की जानी चाहिए',
            'Full payment must be made before the consultation, report preparation, pooja booking, product dispatch, or order processing begins, unless otherwise stated by us' => 'जब तक कि हमारे द्वारा अन्यथा न कहा जाए, परामर्श, रिपोर्ट तैयार करने, पूजा बुकिंग, उत्पाद प्रेषण या ऑर्डर प्रसंस्करण शुरू होने से पहले पूरा भुगतान किया जाना चाहिए',
            'Prices may vary from time to time and may be changed without prior notice' => 'कीमतें समय-समय पर भिन्न हो सकती हैं और बिना पूर्व सूचना के बदली जा सकती हैं',
            'Promotional offers, discounted sessions, and limited-time pricing may be withdrawn or changed at any time' => 'प्रचार प्रस्ताव, रियायती सत्र और सीमित समय के मूल्य निर्धारण को किसी भी समय वापस लिया या बदला जा सकता है',
            'If the app or website offers paid digital content, reports, subscriptions, recurring plans, or premium features, the applicable billing and cancellation terms shall also apply' => 'यदि ऐप या वेबसाइट सशुल्क डिजिटल सामग्री, रिपोर्ट, सदस्यता, आवर्ती योजनाएं या प्रीमियम सुविधाएँ प्रदान करती है, तो लागू बिलिंग और रद्दीकरण शर्तें भी लागू होंगी',
            'Any taxes, gateway charges, shipping charges, handling charges, or platform charges, where applicable, may be added separately' => 'जहां लागू हो, कोई भी कर, गेटवे शुल्क, शिपिंग शुल्क, हैंडलिंग शुल्क, या प्लेटफॉर्म शुल्क अलग से जोड़े जा सकते हैं',

            '4. Subscriptions and Digital Services' => '4. सदस्यता और डिजिटल सेवाएं',
            'If we offer subscriptions, memberships, recurring plans, premium access, paid reports, digital downloads, or other digital content:' => 'यदि हम सदस्यता, आवर्ती योजनाएं, प्रीमियम एक्सेस, सशुल्क रिपोर्ट, डिजिटल डाउनलोड या अन्य डिजिटल सामग्री प्रदान करते हैं:',
            'The applicable fees, billing cycle, renewal terms, and cancellation terms will be displayed at the time of purchase' => 'लागू शुल्क, बिलिंग चक्र, नवीनीकरण शर्तें और रद्दीकरण शर्तें खरीदारी के समय प्रदर्शित की जाएंगी',
            'Access to digital services may begin immediately upon successful payment' => 'सफल भुगतान होने पर डिजिटल सेवाओं तक पहुंच तुरंत शुरू हो सकती है',
            'Unless otherwise stated, digital services and delivered digital content are non-refundable once access has been granted or delivery has begun' => 'जब तक अन्यथा न कहा जाए, एक बार पहुंच प्रदान किए जाने या डिलीवरी शुरू होने के बाद डिजिटल सेवाएं और वितरित डिजिटल सामग्री गैर-वापसी योग्य हैं',
            'Subscription cancellation, where applicable, may stop future renewals but will not usually create a refund for the current billing period unless required by law or platform rules' => 'सदस्यता रद्द करने से भविष्य के नवीनीकरण रुक सकते हैं लेकिन आमतौर पर वर्तमान बिलिंग अवधि के लिए धनवापसी नहीं होती है, जब तक कि कानून या प्लेटफॉर्म नियमों द्वारा आवश्यक न हो',
            'Payments made through third-party app stores, payment gateways, or platform billing systems may also be subject to their terms and procedures' => 'थर्ड-पार्टी ऐप स्टोर, पेमेंट गेटवे या प्लेटफॉर्म बिलिंग सिस्टम के माध्यम से किए गए भुगतान भी उनके नियमों और प्रक्रियाओं के अधीन हो सकते हैं',

            '5. No Guarantees' => '5. कोई गारंटी नहीं',
            'Astrology, numerology, vastu, spiritual guidance, and related practices are subjective and interpretive disciplines. By using our services, you understand and agree that:' => 'ज्योतिष, अंक ज्योतिष, वास्तु, आध्यात्मिक मार्गदर्शन और संबंधित प्रथाएं व्यक्तिपरक और व्याख्यात्मक विषय हैं। हमारी सेवाओं का उपयोग करके, आप समझते हैं और सहमत हैं कि:',
            'We do not guarantee the accuracy, completeness, or reliability of any prediction, reading, consultation, remedy, recommendation, spiritual guidance, or product outcome' => 'हम किसी भी भविष्यवाणी, पढ़ने, परामर्श, उपाय, सिफारिश, आध्यात्मिक मार्गदर्शन या उत्पाद परिणाम की सटीकता, पूर्णता या विश्वसनीयता की गारंटी नहीं देते हैं',
            'We do not guarantee that any suggested remedy, pooja, consultation, report, product, gemstone, rudraksha, mala, bracelet, or recommendation will produce any specific result' => 'हम इस बात की गारंटी नहीं देते हैं कि कोई भी सुझाया गया उपाय, पूजा, परामर्श, रिपोर्ट, उत्पाद, रत्न, रुद्राक्ष, माला, कंगन या सिफारिश कोई विशिष्ट परिणाम देगी',
            'Outcomes may vary from person to person, depending on personal effort, belief systems, timing, circumstances, and external factors beyond our control' => 'व्यक्तिगत प्रयास, विश्वास प्रणाली, समय, परिस्थितियों और हमारे नियंत्रण से बाहर बाहरी कारकों के आधार पर परिणाम हर व्यक्ति में भिन्न हो सकते हैं',
            'Any decisions taken by the client based on our guidance are made at the client’s sole discretion and responsibility' => 'हमारे मार्गदर्शन के आधार पर ग्राहक द्वारा लिए गए कोई भी निर्णय ग्राहक के पूर्ण विवेक और जिम्मेदारी पर होते हैं',

            '6. Personal Responsibility' => '6. व्यक्तिगत जिम्मेदारी',
            'Clients remain fully responsible for their own decisions, actions, and outcomes. Our services are not a substitute for licensed or qualified professional advice, including but not limited to:' => 'ग्राहक अपने स्वयं के निर्णयों, कार्यों और परिणामों के लिए पूरी तरह जिम्मेदार रहते हैं। हमारी सेवाएं लाइसेंस प्राप्त या योग्य पेशेवर सलाह का विकल्प नहीं हैं, जिनमें निम्नलिखित शामिल हैं लेकिन इन्हीं तक सीमित नहीं हैं:',
            'Medical advice' => 'चिकित्सीय सलाह',
            'Legal advice' => 'कानूनी सलाह',
            'Financial or investment advice' => 'वित्तीय या निवेश सलाह',
            'Psychological or psychiatric counseling' => 'मनोवैज्ञानिक या मनोरोग परामर्श',
            'Relationship or family counselling' => 'रिश्ता या पारिवारिक परामर्श',
            'Emergency or crisis intervention services' => 'आपातकालीन या संकट हस्तक्षेप सेवाएं',
            'Where professional help is required, clients should consult an appropriately qualified professional.' => 'जहां पेशेवर मदद की आवश्यकता होती है, ग्राहकों को एक उचित रूप से योग्य पेशेवर से परामर्श लेना चाहिए।',

            '7. Refund Policy' => '7. धनवापसी नीति',
            'Consultation fees, report charges, pooja bookings, digital service payments, and delivered digital products are generally non-refundable once the service has been delivered, prepared, started, scheduled, accessed, or initiated' => 'एक बार सेवा वितरित, तैयार, शुरू, निर्धारित या एक्सेस किए जाने के बाद परामर्श शुल्क, रिपोर्ट शुल्क, पूजा बुकिंग, डिजिटल सेवा भुगतान और वितरित डिजिटल उत्पाद आम तौर पर गैर-वापसी योग्य होते हैं',
            'Refunds may only be considered in limited situations, such as duplicate payment, proven technical error, non-delivery of service from our side, or a booking error caused by us' => 'धनवापसी पर केवल सीमित स्थितियों में ही विचार किया जा सकता है, जैसे कि डुप्लिकेट भुगतान, सिद्ध तकनीकी त्रुटि, हमारी ओर से सेवा की गैर-वितरण, या हमारे कारण हुई बुकिंग त्रुटि',
            'Refund decisions are made by The Fortune Pathway at its reasonable discretion, subject to applicable law' => 'लागू कानून के अधीन, द फॉर्च्यून पाथवे द्वारा धनवापसी के निर्णय इसके उचित विवेक पर लिए जाते हैं',
            'Promotional bookings, discounted sessions, limited-time offers, special packages, bundled services, and delivered digital content are non-refundable unless required by law' => 'प्रचार बुकिंग, रियायती सत्र, सीमित समय के प्रस्ताव, विशेष पैकेज, बंडल सेवाएं और वितरित डिजिटल सामग्री तब तक गैर-वापसी योग्य हैं जब तक कि कानून द्वारा आवश्यक न हो',
            'Physical products purchased through our platform may be subject to a separate return, replacement, shipping, and damage policy' => 'हमारे प्लेटफॉर्म के माध्यम से खरीदे गए भौतिक उत्पाद एक अलग वापसी, प्रतिस्थापन, शिपिंग और क्षति नीति के अधीन हो सकते हैं',

            '8. Cancellation and Rescheduling' => '8. रद्दीकरण और पुनर्निर्धारण',
            'Clients must request cancellation or rescheduling at least 2 hours before the scheduled consultation time, unless otherwise stated for that service' => 'ग्राहकों को निर्धारित परामर्श समय से कम से कम 2 घंटे पहले रद्दीकरण या पुनर्निर्धारण का अनुरोध करना चाहिए, जब तक कि उस सेवा के लिए अन्यथा न कहा गया हो',
            'Eligible cancellations made within the allowed time may be rescheduled based on availability' => 'अनुमत समय के भीतर किए गए योग्य रद्दीकरण को उपलब्धता के आधार पर पुनर्निर्धारित किया जा सकता है',
            'If a client fails to attend a scheduled consultation without prior notice, the session may be treated as completed and non-refundable' => 'यदि कोई ग्राहक पूर्व सूचना के बिना निर्धारित परामर्श में शामिल होने में विफल रहता है, तो सत्र को पूरा माना जा सकता है और कोई धनवापसी नहीं होगी',
            'If the client arrives late, the session may be shortened, depending on scheduling availability, without a refund' => 'यदि ग्राहक देर से आता है, तो शेड्यूलिंग उपलब्धता के आधार पर बिना किसी रिफंड के सत्र को छोटा किया जा सकता है',
            'If we are unable to conduct the session due to technical, scheduling, platform, or internal reasons, we may reschedule the consultation or offer an appropriate refund or credit, at our discretion' => 'यदि हम तकनीकी, शेड्यूलिंग, प्लेटफ़ॉर्म या आंतरिक कारणों से सत्र आयोजित करने में असमर्थ हैं, तो हम अपने विवेक पर परामर्श को पुनर्निर्धारित कर सकते हैं या उचित धनवापसी या क्रेडिट प्रदान कर सकते हैं',

            '9. User Conduct' => '9. उपयोगकर्ता आचरण',
            'Users agree not to:' => 'उपयोगकर्ता ऐसा न करने के लिए सहमत हैं:',
            'Use abusive, offensive, threatening, defamatory, obscene, or inappropriate language during consultations or while using our platform' => 'परामर्श के दौरान या हमारे प्लेटफॉर्म का उपयोग करते समय अपमानजनक, आक्रामक, धमकी भरा, मानहानिकारक, अश्लील या अनुचित भाषा का प्रयोग करना',
            'Misuse, interfere with, disrupt, hack, scrape, reverse engineer, or attempt unauthorised access to our website, app, systems, servers, or services' => 'हमारी वेबसाइट, ऐप, सिस्टम, सर्वर या सेवाओं का दुरुपयोग, हस्तक्षेप, बाधित, हैक, स्क्रैप, रिवर्स इंजीनियर, या अनधिकृत पहुंच का प्रयास करना',
            'Submit false, misleading, incomplete, or fraudulent information' => 'झूठी, भ्रामक, अधूरी या कपटपूर्ण जानकारी जमा करना',
            'Record, reproduce, publish, sell, share, or distribute consultations, calls, chats, reports, videos, audio, or other content without our written permission' => 'हमारी लिखित अनुमति के बिना परामर्श, कॉल, चैट, रिपोर्ट, वीडियो, ऑडियो या अन्य सामग्री को रिकॉर्ड करना, पुनः पेश करना, प्रकाशित करना, बेचना, साझा करना या वितरित करना',
            'Use the service for unlawful, exploitative, fraudulent, harassing, or harmful purposes' => 'गैरकानूनी, शोषक, धोखाधड़ी, उत्पीड़न या हानिकारक उद्देश्यों के लिए सेवा का उपयोग करना',
            'Violation of these terms may lead to suspension or termination of access to our services without refund.' => 'इन शर्तों के उल्लंघन से बिना रिफंड के हमारी सेवाओं तक पहुंच निलंबित या समाप्त हो सकती है।',

            '10. Privacy' => '10. गोपनीयता',
            'We respect your privacy. Personal information shared by you through consultations, bookings, product purchases, reports, the website, or the app will be handled in accordance with our Privacy Policy.' => 'हम आपकी गोपनीयता का सम्मान करते हैं। परामर्श, बुकिंग, उत्पाद खरीद, रिपोर्ट, वेबसाइट या ऐप के माध्यम से आपके द्वारा साझा की गई व्यक्तिगत जानकारी को हमारी गोपनीयता नीति के अनुसार नियंत्रित किया जाएगा।',
            'By using our services, you agree that we may collect, use, store, and process your information for booking, consultation, service delivery, customer support, shipping, legal compliance, fraud prevention, account management, and service improvement purposes.' => 'हमारी सेवाओं का उपयोग करके, आप सहमत हैं कि हम बुकिंग, परामर्श, सेवा वितरण, ग्राहक सहायता, शिपिंग, कानूनी अनुपालन, धोखाधड़ी की रोकथाम, खाता प्रबंधन और सेवा सुधार उद्देश्यों के लिए आपकी जानकारी एकत्र, उपयोग, स्टोर और प्रोसेस कर सकते हैं।',

            '11. Third-Party Services' => '11. तृतीय-पक्ष सेवाएं',
            'We may use trusted third-party providers to operate our services, including but not limited to:' => 'हम अपनी सेवाओं को संचालित करने के लिए विश्वसनीय तृतीय-पक्ष प्रदाताओं का उपयोग कर सकते हैं, जिनमें निम्नलिखित शामिल हैं लेकिन इन्हीं तक सीमित नहीं हैं:',
            'Payment gateways' => 'पेमेंट गेटवे',
            'Website and cloud hosting providers' => 'वेबसाइट और क्लाउड होस्टिंग प्रदाता',
            'Communication platforms' => 'संचार प्लेटफॉर्म',
            'Analytics and crash reporting tools' => 'एनालिटिक्स और क्रैश रिपोर्टिंग टूल',
            'Delivery and logistics partners' => 'वितरण और रसद भागीदार',
            'Customer support tools' => 'ग्राहक सहायता टूल',
            'App stores, billing platforms, and software service providers' => 'ऐप स्टोर, बिलिंग प्लेटफॉर्म और सॉफ्टवेयर सेवा प्रदाता',
            'These third parties may process certain personal data in accordance with their own policies and applicable laws.' => 'ये तृतीय पक्ष अपनी स्वयं की नीतियों और लागू कानूनों के अनुसार कुछ व्यक्तिगत डेटा संसाधित कर सकते हैं।',

            '12. Intellectual Property' => '12. बौद्धिक संपदा',
            'All content, branding, service materials, reports, graphics, text, logos, designs, app content, website content, remedies, spiritual recommendations, and other materials provided by The Fortune Pathway are protected by intellectual property and other applicable laws.' => 'द फॉर्च्यून पाथवे द्वारा प्रदान की गई सभी सामग्री, ब्रांडिंग, सेवा सामग्री, रिपोर्ट, ग्राफिक्स, टेक्स्ट, लोगो, डिजाइन, ऐप सामग्री, वेबसाइट सामग्री, उपाय, आध्यात्मिक सिफारिशें और अन्य सामग्री बौद्धिक संपदा और अन्य लागू कानूनों द्वारा संरक्षित हैं।',
            'You may not copy, reproduce, modify, publish, distribute, sell, license, exploit, or create derivative works from any of our materials without prior written permission.' => 'आप पूर्व लिखित अनुमति के बिना हमारी किसी भी सामग्री की प्रतिलिपि, पुनरुत्पादन, संशोधन, प्रकाशन, वितरण, बिक्री, लाइसेंस, शोषण या व्युत्पन्न कार्य नहीं बना सकते हैं।',

            '13. User Accounts' => '13. उपयोगकर्ता खाते',
            'If user accounts are offered on the website or app:' => 'यदि वेबसाइट या ऐप पर उपयोगकर्ता खाते पेश किए जाते हैं:',
            'Users are responsible for maintaining the confidentiality of their login details and account access' => 'उपयोगकर्ता अपने लॉगिन विवरण और खाते की पहुंच की गोपनीयता बनाए रखने के लिए जिम्मेदार हैं',
            'Users are responsible for all activity carried out through their account' => 'उपयोगकर्ता अपने खाते के माध्यम से की गई सभी गतिविधियों के लिए जिम्मेदार हैं',
            'We reserve the right to suspend, restrict, or terminate accounts that violate these Terms or appear to involve misuse, fraud, abuse, or unlawful activity' => 'हम उन खातों को निलंबित, प्रतिबंधित या समाप्त करने का अधिकार सुरक्षित रखते हैं जो इन शर्तों का उल्लंघन करते हैं या जिनमें दुरुपयोग, धोखाधड़ी, दुर्व्यवहार या गैरकानूनी गतिविधि शामिल प्रतीत होती है',

            '14. Account and Data Deletion' => '14. खाता और डेटा हटाना',
            'Users may request the deletion of their account and associated personal data by contacting us through the details provided below' => 'उपयोगकर्ता नीचे दिए गए विवरणों के माध्यम से हमसे संपर्क करके अपना खाता और संबंधित व्यक्तिगत डेटा हटाने का अनुरोध कर सकते हैं',
            'Where required, account deletion options may also be made available within the app or through a linked webpage' => 'जहां आवश्यक हो, खाता हटाने के विकल्प ऐप के भीतर या लिंक्ड वेबपेज के माध्यम से भी उपलब्ध कराए जा सकते हैं',
            'Certain information may still be retained where required for legal compliance, dispute resolution, fraud prevention, accounting, tax, or security purposes' => 'कानूनी अनुपालन, विवाद समाधान, धोखाधड़ी की रोकथाम, लेखांकन, कर या सुरक्षा उद्देश्यों के लिए जहां आवश्यक हो वहां कुछ जानकारी अभी भी बरकरार रखी जा सकती है',

            '15. Limitation of Liability' => '15. दायित्व की सीमा',
            'To the maximum extent permitted by law, The Fortune Pathway shall not be liable for any direct, indirect, incidental, special, or consequential loss, damage, cost, or claim arising from:' => 'कानून द्वारा अनुमत अधिकतम सीमा तक, द फॉर्च्यून पाथवे किसी भी प्रत्यक्ष, अप्रत्यक्ष, आकस्मिक, विशेष या परिणामी नुकसान, क्षति, लागत या दावे के लिए उत्तरदायी नहीं होगा जो निम्नलिखित से उत्पन्न होता है:',
            'Use of or inability to use our website, app, consultations, products, or services' => 'हमारी वेबसाइट, ऐप, परामर्श, उत्पादों या सेवाओं का उपयोग या उपयोग करने में असमर्थता',
            'Reliance on any consultation, reading, report, prediction, guidance, product recommendation, or remedy' => 'किसी भी परामर्श, पढ़ने, रिपोर्ट, भविष्यवाणी, मार्गदर्शन, उत्पाद सिफारिश या उपाय पर निर्भरता',
            'Delays, interruptions, technical failures, app crashes, connectivity problems, or third-party service disruptions' => 'देरी, रुकावटें, तकनीकी विफलताएं, ऐप क्रैश, कनेक्टिविटी समस्याएं, या तृतीय-पक्ष सेवा व्यवधान',
            'Personal, emotional, relationship, business, legal, health, or financial decisions taken after using our services' => 'हमारी सेवाओं का उपयोग करने के बाद लिए गए व्यक्तिगत, भावनात्मक, रिश्ते, व्यवसाय, कानूनी, स्वास्थ्य या वित्तीय निर्णय',

            '16. Changes to Terms' => '16. शर्तों में बदलाव',
            'We reserve the right to modify, update, or replace these Terms and Conditions at any time. Updated versions will be posted on our website or app with a revised effective date. Continued use of our services after such changes means you accept the revised Terms.' => 'हम किसी भी समय इन नियमों और शर्तों को संशोधित, अद्यतन या बदलने का अधिकार सुरक्षित रखते हैं। अद्यतन संस्करण संशोधित प्रभावी तिथि के साथ हमारी वेबसाइट या ऐप पर पोस्ट किए जाएंगे। ऐसे बदलावों के बाद हमारी सेवाओं का निरंतर उपयोग करने का अर्थ है कि आप संशोधित शर्तों को स्वीकार करते हैं।',

            '17. Governing Law and Jurisdiction' => '17. शासी कानून और अधिकार क्षेत्र',
            'These Terms and Conditions shall be governed by and interpreted in accordance with the laws of India. Any disputes arising from the use of our website, app, products, or services shall be subject to the jurisdiction of the competent courts in Prayagraj, Uttar Pradesh, India.' => 'ये नियम और शर्तें भारत के कानूनों के अनुसार शासित और व्याख्यायित की जाएंगी। हमारी वेबसाइट, ऐप, उत्पादों या सेवाओं के उपयोग से उत्पन्न होने वाला कोई भी विवाद प्रयागराज, उत्तर प्रदेश, भारत में सक्षम अदालतों के अधिकार क्षेत्र के अधीन होगा।',

            '18. Contact Information' => '18. संपर्क जानकारी',
            'If you have any questions regarding these Terms and Conditions, please contact us at:' => 'यदि आपके पास इन नियमों और शर्तों के संबंध में कोई प्रश्न हैं, तो कृपया हमसे संपर्क करें:',
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
    <title><?= __('Terms and Conditions') ?> - The Fortune Pathway</title>
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
            <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Terms and Conditions') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed"><?= __('Welcome to The Fortune Pathway (TFP). By accessing or using our website, mobile application, or booking any consultation or purchasing any product or service from us, you agree to comply with and be bound by the following Terms and Conditions. Please read them carefully before using our services.') ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-[3rem] p-8 lg:p-16 shadow-xl space-y-12">
            
            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('1. Services Provided') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('The Fortune Pathway provides astrology, numerology, vastu, spiritual guidance, and related services, including but not limited to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Astrology consultations and Kundli analysis') ?></li>
                    <li><?= __('Numerology readings') ?></li>
                    <li><?= __('Vastu consultations and remedies') ?></li>
                    <li><?= __('Poojas and spiritual services') ?></li>
                    <li><?= __('Astrology, vastu, and spiritual guidance reports') ?></li>
                    <li><?= __('Sale of spiritual products such as gemstones, rudraksha, malas, bracelets, pyrite items, energized items, and other astrology or vastu-related products') ?></li>
                    <li><?= __('Astro shop and vastu shop products') ?></li>
                    <li><?= __('Other guidance-based digital or physical services offered through our platform') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('All services are spiritual, advisory, and interpretive in nature and are provided for personal guidance, self-reflection, and insight only.') ?></p>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('2. Eligibility') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('By using our services, you confirm that:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('You are at least 18 years old, or') ?></li>
                    <li><?= __('You are using the service under the supervision and consent of a parent or legal guardian') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('If you create an account, submit details, make a booking, or use our website or app, you confirm that the information provided by you is accurate, current, and complete.') ?></p>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('3. Booking and Payments') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('All consultations and services must be booked in advance through our website, app, WhatsApp, phone, or other approved booking channels') ?></li>
                    <li><?= __('Full payment must be made before the consultation, report preparation, pooja booking, product dispatch, or order processing begins, unless otherwise stated by us') ?></li>
                    <li><?= __('Prices may vary from time to time and may be changed without prior notice') ?></li>
                    <li><?= __('Promotional offers, discounted sessions, and limited-time pricing may be withdrawn or changed at any time') ?></li>
                    <li><?= __('If the app or website offers paid digital content, reports, subscriptions, recurring plans, or premium features, the applicable billing and cancellation terms shall also apply') ?></li>
                    <li><?= __('Any taxes, gateway charges, shipping charges, handling charges, or platform charges, where applicable, may be added separately') ?></li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('4. Subscriptions and Digital Services') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If we offer subscriptions, memberships, recurring plans, premium access, paid reports, digital downloads, or other digital content:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('The applicable fees, billing cycle, renewal terms, and cancellation terms will be displayed at the time of purchase') ?></li>
                    <li><?= __('Access to digital services may begin immediately upon successful payment') ?></li>
                    <li><?= __('Unless otherwise stated, digital services and delivered digital content are non-refundable once access has been granted or delivery has begun') ?></li>
                    <li><?= __('Subscription cancellation, where applicable, may stop future renewals but will not usually create a refund for the current billing period unless required by law or platform rules') ?></li>
                    <li><?= __('Payments made through third-party app stores, payment gateways, or platform billing systems may also be subject to their terms and procedures') ?></li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('5. No Guarantees') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Astrology, numerology, vastu, spiritual guidance, and related practices are subjective and interpretive disciplines. By using our services, you understand and agree that:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('We do not guarantee the accuracy, completeness, or reliability of any prediction, reading, consultation, remedy, recommendation, spiritual guidance, or product outcome') ?></li>
                    <li><?= __('We do not guarantee that any suggested remedy, pooja, consultation, report, product, gemstone, rudraksha, mala, bracelet, or recommendation will produce any specific result') ?></li>
                    <li><?= __('Outcomes may vary from person to person, depending on personal effort, belief systems, timing, circumstances, and external factors beyond our control') ?></li>
                    <li><?= __('Any decisions taken by the client based on our guidance are made at the client’s sole discretion and responsibility') ?></li>
                </ul>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('6. Personal Responsibility') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Clients remain fully responsible for their own decisions, actions, and outcomes. Our services are not a substitute for licensed or qualified professional advice, including but not limited to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Medical advice') ?></li>
                    <li><?= __('Legal advice') ?></li>
                    <li><?= __('Financial or investment advice') ?></li>
                    <li><?= __('Psychological or psychiatric counseling') ?></li>
                    <li><?= __('Relationship or family counselling') ?></li>
                    <li><?= __('Emergency or crisis intervention services') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('Where professional help is required, clients should consult an appropriately qualified professional.') ?></p>
            </div>

            <!-- Section 7 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('7. Refund Policy') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Consultation fees, report charges, pooja bookings, digital service payments, and delivered digital products are generally non-refundable once the service has been delivered, prepared, started, scheduled, accessed, or initiated') ?></li>
                    <li><?= __('Refunds may only be considered in limited situations, such as duplicate payment, proven technical error, non-delivery of service from our side, or a booking error caused by us') ?></li>
                    <li><?= __('Refund decisions are made by The Fortune Pathway at its reasonable discretion, subject to applicable law') ?></li>
                    <li><?= __('Promotional bookings, discounted sessions, limited-time offers, special packages, bundled services, and delivered digital content are non-refundable unless required by law') ?></li>
                    <li><?= __('Physical products purchased through our platform may be subject to a separate return, replacement, shipping, and damage policy') ?></li>
                </ul>
            </div>

            <!-- Section 8 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('8. Cancellation and Rescheduling') ?></h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Clients must request cancellation or rescheduling at least 2 hours before the scheduled consultation time, unless otherwise stated for that service') ?></li>
                    <li><?= __('Eligible cancellations made within the allowed time may be rescheduled based on availability') ?></li>
                    <li><?= __('If a client fails to attend a scheduled consultation without prior notice, the session may be treated as completed and non-refundable') ?></li>
                    <li><?= __('If the client arrives late, the session may be shortened, depending on scheduling availability, without a refund') ?></li>
                    <li><?= __('If we are unable to conduct the session due to technical, scheduling, platform, or internal reasons, we may reschedule the consultation or offer an appropriate refund or credit, at our discretion') ?></li>
                </ul>
            </div>

            <!-- Section 9 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('9. User Conduct') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Users agree not to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Use abusive, offensive, threatening, defamatory, obscene, or inappropriate language during consultations or while using our platform') ?></li>
                    <li><?= __('Misuse, interfere with, disrupt, hack, scrape, reverse engineer, or attempt unauthorised access to our website, app, systems, servers, or services') ?></li>
                    <li><?= __('Submit false, misleading, incomplete, or fraudulent information') ?></li>
                    <li><?= __('Record, reproduce, publish, sell, share, or distribute consultations, calls, chats, reports, videos, audio, or other content without our written permission') ?></li>
                    <li><?= __('Use the service for unlawful, exploitative, fraudulent, harassing, or harmful purposes') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('Violation of these terms may lead to suspension or termination of access to our services without refund.') ?></p>
            </div>

            <!-- Section 10 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('10. Privacy') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We respect your privacy. Personal information shared by you through consultations, bookings, product purchases, reports, the website, or the app will be handled in accordance with our Privacy Policy.') ?></p>
                <p class="text-gray-600 leading-relaxed"><?= __('By using our services, you agree that we may collect, use, store, and process your information for booking, consultation, service delivery, customer support, shipping, legal compliance, fraud prevention, account management, and service improvement purposes.') ?></p>
            </div>

            <!-- Section 11 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('11. Third-Party Services') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We may use trusted third-party providers to operate our services, including but not limited to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Payment gateways') ?></li>
                    <li><?= __('Website and cloud hosting providers') ?></li>
                    <li><?= __('Communication platforms') ?></li>
                    <li><?= __('Analytics and crash reporting tools') ?></li>
                    <li><?= __('Delivery and logistics partners') ?></li>
                    <li><?= __('Customer support tools') ?></li>
                    <li><?= __('App stores, billing platforms, and software service providers') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('These third parties may process certain personal data in accordance with their own policies and applicable laws.') ?></p>
            </div>

            <!-- Section 12 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('12. Intellectual Property') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('All content, branding, service materials, reports, graphics, text, logos, designs, app content, website content, remedies, spiritual recommendations, and other materials provided by The Fortune Pathway are protected by intellectual property and other applicable laws.') ?></p>
                <p class="text-gray-600 leading-relaxed"><?= __('You may not copy, reproduce, modify, publish, distribute, sell, license, exploit, or create derivative works from any of our materials without prior written permission.') ?></p>
            </div>

            <!-- Section 13 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('13. User Accounts') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If user accounts are offered on the website or app:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Users are responsible for maintaining the confidentiality of their login details and account access') ?></li>
                    <li><?= __('Users are responsible for all activity carried out through their account') ?></li>
                    <li><?= __('We reserve the right to suspend, restrict, or terminate accounts that violate these Terms or appear to involve misuse, fraud, abuse, or unlawful activity') ?></li>
                </ul>
            </div>

            <!-- Section 14 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('14. Account and Data Deletion') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If user accounts are offered on the website or app:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Users may request the deletion of their account and associated personal data by contacting us through the details provided below') ?></li>
                    <li><?= __('Where required, account deletion options may also be made available within the app or through a linked webpage') ?></li>
                    <li><?= __('Certain information may still be retained where required for legal compliance, dispute resolution, fraud prevention, accounting, tax, or security purposes') ?></li>
                </ul>
            </div>

            <!-- Section 15 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('15. Limitation of Liability') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('To the maximum extent permitted by law, The Fortune Pathway shall not be liable for any direct, indirect, incidental, special, or consequential loss, damage, cost, or claim arising from:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Use of or inability to use our website, app, consultations, products, or services') ?></li>
                    <li><?= __('Reliance on any consultation, reading, report, prediction, guidance, product recommendation, or remedy') ?></li>
                    <li><?= __('Delays, interruptions, technical failures, app crashes, connectivity problems, or third-party service disruptions') ?></li>
                    <li><?= __('Personal, emotional, relationship, business, legal, health, or financial decisions taken after using our services') ?></li>
                </ul>
            </div>

            <!-- Section 16 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('16. Changes to Terms') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('We reserve the right to modify, update, or replace these Terms and Conditions at any time. Updated versions will be posted on our website or app with a revised effective date. Continued use of our services after such changes means you accept the revised Terms.') ?></p>
            </div>

            <!-- Section 17 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('17. Governing Law and Jurisdiction') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('These Terms and Conditions shall be governed by and interpreted in accordance with the laws of India. Any disputes arising from the use of our website, app, products, or services shall be subject to the jurisdiction of the competent courts in Prayagraj, Uttar Pradesh, India.') ?></p>
            </div>

            <!-- Section 18 - Contact (Emails/Numbers hardcoded for intelligent display) -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('18. Contact Information') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-2"><?= __('If you have any questions regarding these Terms and Conditions, please contact us at:') ?></p>
                <div class="space-y-1 mt-4">
                    <p class="text-gray-900 font-semibold"><?= __('Business Name') ?>: <span class="font-normal text-gray-600">The Fortune Pathway</span></p>
                    <p class="text-gray-900 font-semibold"><?= __('Email') ?>: <a href="mailto:info@thefortunepathway.com" class="font-normal text-gray-600 hover:text-black transition">info@thefortunepathway.com</a></p>
                    <p class="text-gray-900 font-semibold"><?= __('Phone/WhatsApp') ?>: <a href="tel:+919336301113" class="font-normal text-gray-600 hover:text-black transition">+91 93363 01113</a></p>
                    <p class="text-gray-900 font-semibold"><?= __('Website') ?>: <a href="https://thefortunepathway.com" class="font-normal text-gray-600 hover:text-black transition">thefortunepathway.com</a></p>
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