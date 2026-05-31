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
            'Privacy Policy' => 'गोपनीयता नीति',
            'Legal Policies' => 'कानूनी नीतियां',
            'At The Fortune Pathway (TFP), we respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, store, process, disclose, and safeguard your information when you use our website, mobile app, or services.' => 'द फॉर्च्यून पाथवे (TFP) में, हम आपकी गोपनीयता का सम्मान करते हैं और आपकी व्यक्तिगत जानकारी की सुरक्षा के लिए प्रतिबद्ध हैं। यह गोपनीयता नीति बताती है कि जब आप हमारी वेबसाइट, मोबाइल ऐप या सेवाओं का उपयोग करते हैं तो हम आपकी जानकारी कैसे एकत्र, उपयोग, स्टोर, प्रोसेस, प्रकट और सुरक्षित करते हैं।',
            
            '1. Information We Collect' => '1. हम जो जानकारी एकत्र करते हैं',
            'We may collect the following categories of personal and usage information, depending on the services you use:' => 'आप जिन सेवाओं का उपयोग करते हैं, उनके आधार पर हम व्यक्तिगत और उपयोग जानकारी की निम्नलिखित श्रेणियां एकत्र कर सकते हैं:',
            'Name' => 'नाम',
            'Phone number' => 'फोन नंबर',
            'Email address' => 'ईमेल पता',
            'Date of birth' => 'जन्म तिथि',
            'Time of birth' => 'जन्म का समय',
            'Place of birth' => 'जन्म स्थान',
            'Gender, where relevant for the service' => 'लिंग, जहां सेवा के लिए प्रासंगिक हो',
            'Address, city, state, pin code, and delivery information where physical products are purchased' => 'पता, शहर, राज्य, पिन कोड, और वितरण जानकारी जहां भौतिक उत्पाद खरीदे जाते हैं',
            'Payment-related information, subject to payment gateway processing' => 'भुगतान से संबंधित जानकारी, पेमेंट गेटवे प्रोसेसिंग के अधीन',
            'Booking details and appointment history' => 'बुकिंग विवरण और अपॉइंटमेंट इतिहास',
            'Consultation details, client responses, preferences, and service history' => 'परामर्श विवरण, ग्राहक प्रतिक्रियाएं, प्राथमिकताएं और सेवा इतिहास',
            'Uploaded documents, photos, screenshots, recordings, or other materials submitted by you' => 'आपके द्वारा सबमिट किए गए अपलोड किए गए दस्तावेज़, फ़ोटो, स्क्रीनशॉट, रिकॉर्डिंग या अन्य सामग्री',
            'Chat messages, emails, support requests, call details, and communication history' => 'चैट संदेश, ईमेल, समर्थन अनुरोध, कॉल विवरण और संचार इतिहास',
            'Device information, IP address, app version, browser type, operating system, and general technical information' => 'डिवाइस की जानकारी, आईपी पता, ऐप संस्करण, ब्राउज़र प्रकार, ऑपरेटिंग सिस्टम और सामान्य तकनीकी जानकारी',
            'Website and app usage data, analytics, crash logs, diagnostics, and interaction history' => 'वेबसाइट और ऐप उपयोग डेटा, एनालिटिक्स, क्रैश लॉग, डायग्नोस्टिक्स और इंटरैक्शन इतिहास',
            'Cookies, pixels, SDK data, and similar technologies, where applicable' => 'कुकीज़, पिक्सेल, SDK डेटा और समान तकनीकें, जहां लागू हों',
            'Subscription, membership, renewal, and transaction records, where relevant' => 'सदस्यता, नवीनीकरण, और लेनदेन रिकॉर्ड, जहां प्रासंगिक हो',
            'We collect only such information as is reasonably necessary to provide, operate, improve, secure, and support our services.' => 'हम केवल उतनी ही जानकारी एकत्र करते हैं जो हमारी सेवाओं को प्रदान करने, संचालित करने, सुधारने, सुरक्षित करने और समर्थन करने के लिए उचित रूप से आवश्यक है।',

            '2. How We Collect Information' => '2. हम जानकारी कैसे एकत्र करते हैं',
            'We may collect information:' => 'हम जानकारी एकत्र कर सकते हैं:',
            'Directly from you when you fill forms, create an account, make a booking, place an order, upload information, or contact us' => 'सीधे आपसे जब आप फॉर्म भरते हैं, खाता बनाते हैं, बुकिंग करते हैं, ऑर्डर देते हैं, जानकारी अपलोड करते हैं, या हमसे संपर्क करते हैं',
            'Automatically through the website, app, analytics tools, cookies, SDKs, device permissions, or technical systems' => 'वेबसाइट, ऐप, एनालिटिक्स टूल, कुकीज़, SDK, डिवाइस अनुमतियों या तकनीकी प्रणालियों के माध्यम से स्वचालित रूप से',
            'Through payment gateways, service providers, logistics partners, or other third-party integrations connected to our platform' => 'हमारे प्लेटफॉर्म से जुड़े पेमेंट गेटवे, सेवा प्रदाताओं, लॉजिस्टिक्स भागीदारों या अन्य तृतीय-पक्ष एकीकरणों के माध्यम से',
            'Through customer support, consultations, chats, calls, messages, or email interactions' => 'ग्राहक सहायता, परामर्श, चैट, कॉल, संदेश या ईमेल इंटरैक्शन के माध्यम से',

            '3. How We Use Your Information' => '3. हम आपकी जानकारी का उपयोग कैसे करते हैं',
            'Your information may be used for the following purposes:' => 'आपकी जानकारी का उपयोग निम्नलिखित उद्देश्यों के लिए किया जा सकता है:',
            'Providing astrology, numerology, vastu, pooja, spiritual, and related consultations or services' => 'ज्योतिष, अंक ज्योतिष, वास्तु, पूजा, आध्यात्मिक और संबंधित परामर्श या सेवाएं प्रदान करना',
            'Preparing personalized analysis, reports, remedies, or recommendations' => 'व्यक्तिगत विश्लेषण, रिपोर्ट, उपाय या सिफारिशें तैयार करना',
            'Booking, scheduling, confirming, rescheduling, or managing appointments' => 'अपॉइंटमेंट बुक करना, शेड्यूल करना, पुष्टि करना, पुनर्निर्धारित करना या प्रबंधित करना',
            'Processing orders, renewals, subscriptions, and payments' => 'ऑर्डर, नवीनीकरण, सदस्यता और भुगतान संसाधित करना',
            'Delivering physical products and digital services' => 'भौतिक उत्पाद और डिजिटल सेवाएं वितरित करना',
            'Customer support and communication' => 'ग्राहक सहायता और संचार',
            'Sending booking confirmations, reminders, updates, invoices, support responses, and service-related notifications' => 'बुकिंग की पुष्टि, रिमाइंडर, अपडेट, चालान, समर्थन प्रतिक्रियाएं और सेवा-संबंधित सूचनाएं भेजना',
            'Improving our website, app, content, product offerings, and customer experience' => 'हमारी वेबसाइट, ऐप, सामग्री, उत्पाद पेशकश और ग्राहक अनुभव में सुधार करना',
            'Monitoring technical performance, app crashes, fraud prevention, misuse detection, and platform security' => 'तकनीकी प्रदर्शन, ऐप क्रैश, धोखाधड़ी की रोकथाम, दुरुपयोग का पता लगाने और प्लेटफॉर्म सुरक्षा की निगरानी करना',
            'Sending newsletters, promotional offers, or marketing communications where permitted by law or where you have consented' => 'न्यूज़लेटर, प्रचार प्रस्ताव या मार्केटिंग संचार भेजना जहां कानून द्वारा अनुमति हो या जहां आपने सहमति दी हो',
            'Legal compliance, dispute handling, internal records, operational administration, and business management' => 'कानूनी अनुपालन, विवाद प्रबंधन, आंतरिक रिकॉर्ड, परिचालन प्रशासन और व्यवसाय प्रबंधन',
            'We do not sell your personal information to third parties.' => 'हम आपकी व्यक्तिगत जानकारी तीसरे पक्ष को नहीं बेचते हैं।',

            '4. Sharing of Information' => '4. जानकारी साझा करना',
            'We may share limited information with trusted service providers only to the extent reasonably necessary to operate our services, including:' => 'हम अपनी सेवाओं को संचालित करने के लिए केवल उचित रूप से आवश्यक सीमा तक विश्वसनीय सेवा प्रदाताओं के साथ सीमित जानकारी साझा कर सकते हैं, जिनमें शामिल हैं:',
            'Payment gateways and payment processors' => 'पेमेंट गेटवे और पेमेंट प्रोसेसर',
            'Website hosting and cloud service providers' => 'वेबसाइट होस्टिंग और क्लाउड सेवा प्रदाता',
            'App infrastructure providers' => 'ऐप इंफ्रास्ट्रक्चर प्रदाता',
            'Analytics and crash reporting providers' => 'एनालिटिक्स और क्रैश रिपोर्टिंग प्रदाता',
            'Communication and customer support tools' => 'संचार और ग्राहक सहायता उपकरण',
            'Delivery, shipping, logistics, or courier partners' => 'डिलीवरी, शिपिंग, लॉजिस्टिक्स या कूरियर पार्टनर',
            'Account management, software, or technical service providers' => 'खाता प्रबंधन, सॉफ्टवेयर या तकनीकी सेवा प्रदाता',
            'Legal, regulatory, tax, compliance, or law enforcement authorities, where required by law' => 'कानूनी, विनियामक, कर, अनुपालन, या कानून प्रवर्तन अधिकारी, जहां कानून द्वारा आवश्यक हो',
            'These service providers are expected to process information appropriately in accordance with their own privacy policies and applicable law.' => 'इन सेवा प्रदाताओं से अपेक्षा की जाती है कि वे अपनी स्वयं की गोपनीयता नीतियों और लागू कानून के अनुसार उचित रूप से जानकारी संसाधित करें।',

            '5. Data Security' => '5. डेटा सुरक्षा',
            'We implement reasonable technical, organisational, and administrative safeguards to protect your personal information against unauthorised access, misuse, alteration, disclosure, loss, or destruction. However:' => 'हम आपकी व्यक्तिगत जानकारी को अनधिकृत पहुंच, दुरुपयोग, परिवर्तन, प्रकटीकरण, हानि या विनाश से बचाने के लिए उचित तकनीकी, संगठनात्मक और प्रशासनिक सुरक्षा उपाय लागू करते हैं। हालाँकि:',
            'No website, app, server, platform, or internet transmission can be guaranteed to be completely secure' => 'किसी भी वेबसाइट, ऐप, सर्वर, प्लेटफॉर्म या इंटरनेट ट्रांसमिशन के पूरी तरह से सुरक्षित होने की गारंटी नहीं दी जा सकती है',
            'We cannot guarantee the absolute security of any information transmitted electronically or stored digitally' => 'हम इलेक्ट्रॉनिक रूप से प्रेषित या डिजिटल रूप से संग्रहीत किसी भी जानकारी की पूर्ण सुरक्षा की गारंटी नहीं दे सकते हैं',

            '6. Data Retention' => '6. डेटा प्रतिधारण',
            'We retain personal information only for as long as reasonably necessary for:' => 'हम व्यक्तिगत जानकारी को केवल तब तक बनाए रखते हैं जब तक उचित रूप से आवश्यक हो:',
            'Service delivery' => 'सेवा वितरण',
            'Booking and transaction history' => 'बुकिंग और लेनदेन का इतिहास',
            'Customer support' => 'ग्राहक सहायता',
            'Product delivery and return handling' => 'उत्पाद वितरण और वापसी प्रबंधन',
            'Legal, tax, accounting, and compliance requirements' => 'कानूनी, कर, लेखांकन और अनुपालन आवश्यकताएं',
            'Fraud prevention and dispute resolution' => 'धोखाधड़ी की रोकथाम और विवाद समाधान',
            'Internal record keeping and operational needs' => 'आंतरिक रिकॉर्ड रखना और परिचालन आवश्यकताएं',
            'Improvement of services and platform performance' => 'सेवाओं और प्लेटफॉर्म के प्रदर्शन में सुधार',
            'After this period, information may be deleted, anonymised, archived, or securely retained as legally required.' => 'इस अवधि के बाद, जानकारी को कानूनी रूप से आवश्यक होने पर हटाया, गुमनाम किया, संग्रहीत किया या सुरक्षित रूप से बनाए रखा जा सकता है।',

            '7. Cookies and Similar Technologies' => '7. कुकीज़ और समान तकनीकें',
            'Our website and app may use cookies, pixels, SDKs, and similar technologies to:' => 'हमारी वेबसाइट और ऐप कुकीज़, पिक्सेल, SDK और समान तकनीकों का उपयोग कर सकते हैं:',
            'Improve user experience' => 'उपयोगकर्ता अनुभव में सुधार के लिए',
            'Remember preferences and session details' => 'प्राथमिकताएं और सत्र विवरण याद रखने के लिए',
            'Analyse traffic, app usage, and customer interactions' => 'ट्रैफिक, ऐप के उपयोग और ग्राहक इंटरैक्शन का विश्लेषण करने के लिए',
            'Support security and fraud prevention' => 'सुरक्षा और धोखाधड़ी की रोकथाम का समर्थन करने के लिए',
            'Measure feature performance, service quality, and campaign effectiveness' => 'सुविधा के प्रदर्शन, सेवा की गुणवत्ता और अभियान की प्रभावशीलता को मापने के लिए',
            'You may manage some cookie or device permissions through your browser, app, or device settings, subject to technical limitations.' => 'आप तकनीकी सीमाओं के अधीन, अपने ब्राउज़र, ऐप या डिवाइस सेटिंग्स के माध्यम से कुछ कुकी या डिवाइस अनुमतियों का प्रबंधन कर सकते हैं।',

            '8. Third-Party Services' => '8. तृतीय-पक्ष सेवाएं',
            'Our platform may contain links, payment integrations, SDKs, tools, services, or plug-ins operated by third parties. These may include:' => 'हमारे प्लेटफ़ॉर्म में तीसरे पक्षों द्वारा संचालित लिंक, भुगतान एकीकरण, SDK, टूल, सेवाएँ या प्लग-इन शामिल हो सकते हैं। इनमें शामिल हो सकते हैं:',
            'Payment providers' => 'भुगतान प्रदाता',
            'App analytics tools' => 'ऐप एनालिटिक्स टूल',
            'Crash reporting tools' => 'क्रैश रिपोर्टिंग टूल',
            'Hosting providers' => 'होस्टिंग प्रदाता',
            'Messaging and communication services' => 'मैसेजिंग और संचार सेवाएं',
            'Delivery or logistics providers' => 'डिलीवरी या लॉजिस्टिक्स प्रदाता',
            'Social media links or integrations' => 'सोशल मीडिया लिंक या एकीकरण',
            'App store or platform billing systems' => 'ऐप स्टोर या प्लेटफॉर्म बिलिंग सिस्टम',
            'We are not responsible for the privacy practices of independent third-party providers, and users should review their respective privacy policies where relevant.' => 'हम स्वतंत्र तृतीय-पक्ष प्रदाताओं की गोपनीयता प्रथाओं के लिए ज़िम्मेदार नहीं हैं, और उपयोगकर्ताओं को प्रासंगिक होने पर उनकी संबंधित गोपनीयता नीतियों की समीक्षा करनी चाहिए।',

            '9. User Rights' => '9. उपयोगकर्ता अधिकार',
            'Subject to applicable law, you may have the right to:' => 'लागू कानून के अधीन, आपको निम्नलिखित अधिकार हो सकते हैं:',
            'Request access to your personal data' => 'अपने व्यक्तिगत डेटा तक पहुंच का अनुरोध करना',
            'Request correction of inaccurate or incomplete information' => 'गलत या अधूरी जानकारी में सुधार का अनुरोध करना',
            'Request deletion of your personal data' => 'अपने व्यक्तिगत डेटा को हटाने का अनुरोध करना',
            'Withdraw consent for certain communications where applicable' => 'जहां लागू हो, कुछ संचारों के लिए सहमति वापस लेना',
            'Request that we stop using your information for specific purposes where legally permitted' => 'अनुरोध करना कि हम कानूनी रूप से अनुमत होने पर विशिष्ट उद्देश्यों के लिए आपकी जानकारी का उपयोग करना बंद कर दें',
            'Request account deletion where accounts are offered' => 'जहां खाते पेश किए जाते हैं वहां खाता हटाने का अनुरोध करना',
            'Ask how your information is being collected, used, and processed' => 'पूछना कि आपकी जानकारी कैसे एकत्र, उपयोग और संसाधित की जा रही है',
            'To exercise these rights, you may contact us using the details provided below.' => 'इन अधिकारों का प्रयोग करने के लिए, आप नीचे दिए गए विवरणों का उपयोग करके हमसे संपर्क कर सकते हैं।',

            '10. Account Deletion' => '10. खाता हटाना',
            'If our website or app allows account creation:' => 'यदि हमारी वेबसाइट या ऐप खाता बनाने की अनुमति देता है:',
            'Users may request the deletion of their account and associated personal information by contacting us' => 'उपयोगकर्ता हमसे संपर्क करके अपना खाता और संबंधित व्यक्तिगत जानकारी हटाने का अनुरोध कर सकते हैं',
            'Where supported, users may also be able to delete their account through an in-app option or a linked account deletion page' => 'जहां समर्थित हो, उपयोगकर्ता इन-ऐप विकल्प या लिंक्ड खाता विलोपन पृष्ठ के माध्यम से भी अपना खाता हटा सकते हैं',
            'Certain records may still be retained for legal compliance, fraud prevention, dispute handling, accounting, tax, or security reasons' => 'कानूनी अनुपालन, धोखाधड़ी की रोकथाम, विवाद प्रबंधन, लेखांकन, कर या सुरक्षा कारणों से कुछ रिकॉर्ड अभी भी बनाए रखे जा सकते हैं',

            '11. Children’s Privacy' => '11. बच्चों की गोपनीयता',
            'Our services are intended primarily for adults.' => 'हमारी सेवाएं मुख्य रूप से वयस्कों के लिए हैं।',
            'Users under 18 years of age should use the services only with the involvement and consent of a parent or legal guardian' => '18 वर्ष से कम आयु के उपयोगकर्ताओं को माता-पिता या कानूनी अभिभावक की भागीदारी और सहमति से ही सेवाओं का उपयोग करना चाहिए',
            'We do not knowingly collect personal data from children in violation of applicable law' => 'हम जानबूझकर लागू कानून के उल्लंघन में बच्चों से व्यक्तिगत डेटा एकत्र नहीं करते हैं',
            'If you believe a child has submitted personal information improperly, please contact us so that we can review and take appropriate action.' => 'यदि आपको लगता है कि किसी बच्चे ने अनुचित तरीके से व्यक्तिगत जानकारी जमा की है, तो कृपया हमसे संपर्क करें ताकि हम समीक्षा कर सकें और उचित कार्रवाई कर सकें।',

            '12. International Processing' => '12. अंतर्राष्ट्रीय प्रसंस्करण',
            'If any of our service providers, software tools, servers, support systems, or technical partners are located outside India, your information may be processed or stored in other jurisdictions, subject to reasonable safeguards and applicable law.' => 'यदि हमारे कोई सेवा प्रदाता, सॉफ्टवेयर टूल, सर्वर, समर्थन सिस्टम या तकनीकी भागीदार भारत के बाहर स्थित हैं, तो आपकी जानकारी उचित सुरक्षा उपायों और लागू कानून के अधीन अन्य न्यायालयों में संसाधित या संग्रहीत की जा सकती है।',

            '13. Consent' => '13. सहमति',
            'By using our website, app, or services, you consent to the collection, use, storage, and processing of your information as described in this Privacy Policy.' => 'हमारी वेबसाइट, ऐप या सेवाओं का उपयोग करके, आप इस गोपनीयता नीति में वर्णित अनुसार अपनी जानकारी के संग्रह, उपयोग, भंडारण और प्रसंस्करण के लिए सहमति देते हैं।',
            'Where required, consent requests and privacy-related communications may be provided clearly and plainly appropriate to the service being used.' => 'जहां आवश्यक हो, सहमति अनुरोध और गोपनीयता से संबंधित संचार स्पष्ट और उपयोग की जा रही सेवा के लिए उपयुक्त रूप से प्रदान किए जा सकते हैं।',

            '14. Changes to This Privacy Policy' => '14. इस गोपनीयता नीति में परिवर्तन',
            'We may update this Privacy Policy from time to time. The revised version will be posted on our website or app with the updated effective date. Continued use of our services after such changes means you accept the revised policy.' => 'हम समय-समय पर इस गोपनीयता नीति को अपडेट कर सकते हैं। संशोधित संस्करण अद्यतन प्रभावी तिथि के साथ हमारी वेबसाइट या ऐप पर पोस्ट किया जाएगा। ऐसे बदलावों के बाद हमारी सेवाओं का निरंतर उपयोग करने का अर्थ है कि आप संशोधित नीति को स्वीकार करते हैं।',

            '15. Contact Us' => '15. हमसे संपर्क करें',
            'If you have any questions regarding this Privacy Policy or wish to request access, correction, or deletion of your information, please contact:' => 'यदि इस गोपनीयता नीति के संबंध में आपके कोई प्रश्न हैं या अपनी जानकारी तक पहुंच, सुधार या हटाने का अनुरोध करना चाहते हैं, तो कृपया संपर्क करें:',
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
    <title><?= __('Privacy Policy') ?> - The Fortune Pathway</title>
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
            <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-6"><?= __('Privacy Policy') ?></h1>
            <p class="text-lg text-gray-600 leading-relaxed"><?= __('At The Fortune Pathway (TFP), we respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, store, process, disclose, and safeguard your information when you use our website, mobile app, or services.') ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-[3rem] p-8 lg:p-16 shadow-xl space-y-12">
            
            <!-- Section 1 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('1. Information We Collect') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We may collect the following categories of personal and usage information, depending on the services you use:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Name') ?></li>
                    <li><?= __('Phone number') ?></li>
                    <li><?= __('Email address') ?></li>
                    <li><?= __('Date of birth') ?></li>
                    <li><?= __('Time of birth') ?></li>
                    <li><?= __('Place of birth') ?></li>
                    <li><?= __('Gender, where relevant for the service') ?></li>
                    <li><?= __('Address, city, state, pin code, and delivery information where physical products are purchased') ?></li>
                    <li><?= __('Payment-related information, subject to payment gateway processing') ?></li>
                    <li><?= __('Booking details and appointment history') ?></li>
                    <li><?= __('Consultation details, client responses, preferences, and service history') ?></li>
                    <li><?= __('Uploaded documents, photos, screenshots, recordings, or other materials submitted by you') ?></li>
                    <li><?= __('Chat messages, emails, support requests, call details, and communication history') ?></li>
                    <li><?= __('Device information, IP address, app version, browser type, operating system, and general technical information') ?></li>
                    <li><?= __('Website and app usage data, analytics, crash logs, diagnostics, and interaction history') ?></li>
                    <li><?= __('Cookies, pixels, SDK data, and similar technologies, where applicable') ?></li>
                    <li><?= __('Subscription, membership, renewal, and transaction records, where relevant') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('We collect only such information as is reasonably necessary to provide, operate, improve, secure, and support our services.') ?></p>
            </div>

            <!-- Section 2 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('2. How We Collect Information') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We may collect information:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Directly from you when you fill forms, create an account, make a booking, place an order, upload information, or contact us') ?></li>
                    <li><?= __('Automatically through the website, app, analytics tools, cookies, SDKs, device permissions, or technical systems') ?></li>
                    <li><?= __('Through payment gateways, service providers, logistics partners, or other third-party integrations connected to our platform') ?></li>
                    <li><?= __('Through customer support, consultations, chats, calls, messages, or email interactions') ?></li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('3. How We Use Your Information') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Your information may be used for the following purposes:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Providing astrology, numerology, vastu, pooja, spiritual, and related consultations or services') ?></li>
                    <li><?= __('Preparing personalized analysis, reports, remedies, or recommendations') ?></li>
                    <li><?= __('Booking, scheduling, confirming, rescheduling, or managing appointments') ?></li>
                    <li><?= __('Processing orders, renewals, subscriptions, and payments') ?></li>
                    <li><?= __('Delivering physical products and digital services') ?></li>
                    <li><?= __('Customer support and communication') ?></li>
                    <li><?= __('Sending booking confirmations, reminders, updates, invoices, support responses, and service-related notifications') ?></li>
                    <li><?= __('Improving our website, app, content, product offerings, and customer experience') ?></li>
                    <li><?= __('Monitoring technical performance, app crashes, fraud prevention, misuse detection, and platform security') ?></li>
                    <li><?= __('Sending newsletters, promotional offers, or marketing communications where permitted by law or where you have consented') ?></li>
                    <li><?= __('Legal compliance, dispute handling, internal records, operational administration, and business management') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed font-semibold"><?= __('We do not sell your personal information to third parties.') ?></p>
            </div>

            <!-- Section 4 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('4. Sharing of Information') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We may share limited information with trusted service providers only to the extent reasonably necessary to operate our services, including:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Payment gateways and payment processors') ?></li>
                    <li><?= __('Website hosting and cloud service providers') ?></li>
                    <li><?= __('App infrastructure providers') ?></li>
                    <li><?= __('Analytics and crash reporting providers') ?></li>
                    <li><?= __('Communication and customer support tools') ?></li>
                    <li><?= __('Delivery, shipping, logistics, or courier partners') ?></li>
                    <li><?= __('Account management, software, or technical service providers') ?></li>
                    <li><?= __('Legal, regulatory, tax, compliance, or law enforcement authorities, where required by law') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('These service providers are expected to process information appropriately in accordance with their own privacy policies and applicable law.') ?></p>
            </div>

            <!-- Section 5 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('5. Data Security') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We implement reasonable technical, organisational, and administrative safeguards to protect your personal information against unauthorised access, misuse, alteration, disclosure, loss, or destruction. However:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('No website, app, server, platform, or internet transmission can be guaranteed to be completely secure') ?></li>
                    <li><?= __('We cannot guarantee the absolute security of any information transmitted electronically or stored digitally') ?></li>
                </ul>
            </div>

            <!-- Section 6 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('6. Data Retention') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('We retain personal information only for as long as reasonably necessary for:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Service delivery') ?></li>
                    <li><?= __('Booking and transaction history') ?></li>
                    <li><?= __('Customer support') ?></li>
                    <li><?= __('Product delivery and return handling') ?></li>
                    <li><?= __('Legal, tax, accounting, and compliance requirements') ?></li>
                    <li><?= __('Fraud prevention and dispute resolution') ?></li>
                    <li><?= __('Internal record keeping and operational needs') ?></li>
                    <li><?= __('Improvement of services and platform performance') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('After this period, information may be deleted, anonymised, archived, or securely retained as legally required.') ?></p>
            </div>

            <!-- Section 7 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('7. Cookies and Similar Technologies') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Our website and app may use cookies, pixels, SDKs, and similar technologies to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Improve user experience') ?></li>
                    <li><?= __('Remember preferences and session details') ?></li>
                    <li><?= __('Analyse traffic, app usage, and customer interactions') ?></li>
                    <li><?= __('Support security and fraud prevention') ?></li>
                    <li><?= __('Measure feature performance, service quality, and campaign effectiveness') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('You may manage some cookie or device permissions through your browser, app, or device settings, subject to technical limitations.') ?></p>
            </div>

            <!-- Section 8 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('8. Third-Party Services') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Our platform may contain links, payment integrations, SDKs, tools, services, or plug-ins operated by third parties. These may include:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Payment providers') ?></li>
                    <li><?= __('App analytics tools') ?></li>
                    <li><?= __('Crash reporting tools') ?></li>
                    <li><?= __('Hosting providers') ?></li>
                    <li><?= __('Messaging and communication services') ?></li>
                    <li><?= __('Delivery or logistics providers') ?></li>
                    <li><?= __('Social media links or integrations') ?></li>
                    <li><?= __('App store or platform billing systems') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('We are not responsible for the privacy practices of independent third-party providers, and users should review their respective privacy policies where relevant.') ?></p>
            </div>

            <!-- Section 9 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('9. User Rights') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Subject to applicable law, you may have the right to:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Request access to your personal data') ?></li>
                    <li><?= __('Request correction of inaccurate or incomplete information') ?></li>
                    <li><?= __('Request deletion of your personal data') ?></li>
                    <li><?= __('Withdraw consent for certain communications where applicable') ?></li>
                    <li><?= __('Request that we stop using your information for specific purposes where legally permitted') ?></li>
                    <li><?= __('Request account deletion where accounts are offered') ?></li>
                    <li><?= __('Ask how your information is being collected, used, and processed') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('To exercise these rights, you may contact us using the details provided below.') ?></p>
            </div>

            <!-- Section 10 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('10. Account Deletion') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('If our website or app allows account creation:') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li><?= __('Users may request the deletion of their account and associated personal information by contacting us') ?></li>
                    <li><?= __('Where supported, users may also be able to delete their account through an in-app option or a linked account deletion page') ?></li>
                    <li><?= __('Certain records may still be retained for legal compliance, fraud prevention, dispute handling, accounting, tax, or security reasons') ?></li>
                </ul>
            </div>

            <!-- Section 11 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('11. Children’s Privacy') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('Our services are intended primarily for adults.') ?></p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600 mb-4">
                    <li><?= __('Users under 18 years of age should use the services only with the involvement and consent of a parent or legal guardian') ?></li>
                    <li><?= __('We do not knowingly collect personal data from children in violation of applicable law') ?></li>
                </ul>
                <p class="text-gray-600 leading-relaxed"><?= __('If you believe a child has submitted personal information improperly, please contact us so that we can review and take appropriate action.') ?></p>
            </div>

            <!-- Section 12 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('12. International Processing') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('If any of our service providers, software tools, servers, support systems, or technical partners are located outside India, your information may be processed or stored in other jurisdictions, subject to reasonable safeguards and applicable law.') ?></p>
            </div>

            <!-- Section 13 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('13. Consent') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?= __('By using our website, app, or services, you consent to the collection, use, storage, and processing of your information as described in this Privacy Policy.') ?></p>
                <p class="text-gray-600 leading-relaxed"><?= __('Where required, consent requests and privacy-related communications may be provided clearly and plainly appropriate to the service being used.') ?></p>
            </div>

            <!-- Section 14 -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('14. Changes to This Privacy Policy') ?></h2>
                <p class="text-gray-600 leading-relaxed"><?= __('We may update this Privacy Policy from time to time. The revised version will be posted on our website or app with the updated effective date. Continued use of our services after such changes means you accept the revised policy.') ?></p>
            </div>

            <!-- Section 15 - Contact (Emails/Numbers hardcoded for intelligent display) -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= __('15. Contact Us') ?></h2>
                <p class="text-gray-600 leading-relaxed mb-2"><?= __('If you have any questions regarding this Privacy Policy or wish to request access, correction, or deletion of your information, please contact:') ?></p>
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