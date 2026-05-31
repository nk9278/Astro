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
            'Kundali Reports - Fortune Parth' => 'कुंडली रिपोर्ट्स - फॉर्च्यून पाथ',
            'Reports' => 'रिपोर्ट्स',
            'Personalized Insights' => 'व्यक्तिगत अंतर्दृष्टि',
            'Our detailed' => 'हमारी विस्तृत',
            '11-page Action Analysis report' => '11-पृष्ठ कार्रवाई विश्लेषण रिपोर्ट',
            'focuses on solving core issues using the Vimshottari Dasha system and Lal Kitab insights.' => 'विंशोत्तरी दशा प्रणाली और लाल किताब अंतर्दृष्टि का उपयोग करके मुख्य मुद्दों को हल करने पर केंद्रित है।',
            'Get immediate clarity and focused remedies designed for your long-term success.' => 'अपनी दीर्घकालिक सफलता के लिए डिज़ाइन किए गए तत्काल स्पष्टता और केंद्रित उपाय प्राप्त करें।',
            'Get Your Report' => 'अपनी रिपोर्ट प्राप्त करें',
            'Kundali' => 'कुंडली',
            'Comprehensive Life Maps' => 'व्यापक जीवन मानचित्र',
            'Digital Analysis Ready' => 'डिजिटल विश्लेषण तैयार',
            'Your Life Story,' => 'आपकी जीवन कहानी,',
            'Written in the Stars.' => 'सितारों में लिखी गई।',
            'focuses on solving core issues. We analyze life timings via the' => 'मुख्य मुद्दों को हल करने पर केंद्रित है। हम',
            'Vimshottari Dasha system' => 'विंशोत्तरी दशा प्रणाली',
            'to support your long-term success.' => 'के माध्यम से जीवन के समय का विश्लेषण करते हैं ताकि आपकी दीर्घकालिक सफलता का समर्थन किया जा सके।',
            '"We provide focused remedies, including insights from Lal Kitab, to ensure immediate clarity rather than overwhelming you with information."' => '"हम सूचनाओं से अभिभूत करने के बजाय तत्काल स्पष्टता सुनिश्चित करने के लिए लाल किताब की अंतर्दृष्टि सहित केंद्रित उपाय प्रदान करते हैं।"',
            'Generate Report' => 'रिपोर्ट जनरेट करें',
            'In-Depth' => 'गहन',
            'Personalized Action Steps' => 'व्यक्तिगत कार्रवाई कदम'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$user_id = $_SESSION['userid'] ?? 0;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title><?= __('Kundali Reports - Fortune Parth') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">

    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; color: #1a1a1a; overflow-x: hidden; transition: background 0.3s ease; }
        
        .bg-glow {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 70% 30%, #f4f6ff 0%, #ffffff 100%);
            z-index: -1;
            transition: background 0.3s ease;
        }

        @keyframes rotate-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .mystic-rotate { animation: rotate-slow 100s linear infinite; }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .floating-card { animation: float 6s ease-in-out infinite; transition: background 0.3s ease; }

        @media (min-width: 1024px) {
            .desktop-container { max-width: 80rem; margin: 0 auto; padding: 0 4rem; }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-glow { background: radial-gradient(circle at 70% 30%, #1f2937 0%, #121212 100%) !important; }
        
        body.dark-theme .text-black, body.dark-theme .text-gray-900, body.dark-theme .text-gray-800 { color: #ffffff !important; }
        body.dark-theme .text-gray-600, body.dark-theme .text-gray-500 { color: #d1d5db !important; }
        body.dark-theme .text-gray-400 { color: #9ca3af !important; }
        
        /* Dark Theme Buttons & Cards */
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; }
        body.dark-theme .hover\:bg-gray-800:hover { background-color: #059669 !important; }
        body.dark-theme .floating-card { background-color: #1f2937 !important; border: 1px solid #374151 !important; }
        
        /* Blockquotes / Sections */
        body.dark-theme .bg-gray-50 { background-color: #374151 !important; border-left-color: #10b981 !important; }
        body.dark-theme .border-black { border-color: #10b981 !important; }
        body.dark-theme .border-gray-200 { border-color: #4b5563 !important; }
    </style>
</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="bg-glow"></div>

<div class="block lg:hidden">
    <?php include '../user/mobile_header.php'; ?>
    
    <div class="flex flex-col items-center justify-center min-h-[85vh] px-8 pt-10 text-center">
        <div class="relative w-64 h-72 mb-8 bg-black rounded-[2.5rem] shadow-2xl flex flex-col items-center justify-center p-6 floating-card overflow-hidden">
            
            <div class="absolute inset-0 flex items-center justify-center opacity-40">
                <svg viewBox="0 0 200 200" class="mystic-rotate w-[130%] h-[130%] stroke-white fill-none">
                    <circle cx="100" cy="100" r="90" stroke-width="1.8" />
                    <rect x="60" y="60" width="80" height="80" stroke-width="1.5" transform="rotate(45 100 100)" />
                    <rect x="60" y="60" width="80" height="80" stroke-width="1.5" />
                    <circle cx="100" cy="100" r="15" stroke-width="1" stroke-dasharray="4 4" />
                </svg>
            </div>

            <div class="relative z-10 text-center">
                <h2 class="text-2xl font-bold tracking-[0.2em] uppercase text-white"><?= __('Reports') ?></h2>
                <p class="text-[9px] text-gray-400 tracking-[0.4em] uppercase mt-2"><?= __('Personalized Insights') ?></p>
            </div>
        </div>

        <div class="space-y-4 mb-10 px-2">
            <p class="text-gray-600 leading-relaxed text-sm">
                <?= __('Our detailed') ?> <strong><?= __('11-page Action Analysis report') ?></strong> <?= __('focuses on solving core issues using the Vimshottari Dasha system and Lal Kitab insights.') ?>
            </p>
            <p class="text-gray-500 text-xs italic">
                <?= __('Get immediate clarity and focused remedies designed for your long-term success.') ?>
            </p>
        </div>

        <a href="/store/reports.php" class="w-full py-4 bg-black text-white font-bold rounded-xl text-center shadow-xl mb-24">
            <?= __('Get Your Report') ?>
        </a>
    </div>

    <?php include '../user/footer_user.php'; ?>
</div>

<div class="hidden lg:flex min-h-screen flex-col">
    <?php include '../user/web_header.php'; ?>

    <main class="flex-grow flex items-center py-12">
        <div class="desktop-container w-full">
            <div class="grid grid-cols-12 gap-12 items-center">
                
                <div class="col-span-5 flex justify-center">
                    <div class="floating-card relative w-[85%] aspect-[4/5] bg-black rounded-[3.5rem] shadow-2xl flex flex-col items-center justify-center p-12 overflow-hidden">
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-30">
                            <svg viewBox="0 0 200 200" class="mystic-rotate w-[140%] h-[140%] stroke-white fill-none">
                                <circle cx="100" cy="100" r="95" stroke-width="1.5" />
                                <rect x="50" y="50" width="100" height="100" stroke-width="1.2" transform="rotate(45 100 100)" />
                                <rect x="50" y="50" width="100" height="100" stroke-width="1.2" />
                                <circle cx="100" cy="100" r="70" stroke-width="0.8" stroke-dasharray="10 10" />
                            </svg>
                        </div>

                        <div class="relative z-10 w-full flex flex-col items-center">
                            <h2 class="text-4xl font-black text-white uppercase tracking-[0.2em] mb-3"><?= __('Kundali') ?></h2>
                            <div class="h-[1px] w-20 bg-white/40 mb-4"></div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.4em]"><?= __('Comprehensive Life Maps') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-span-7 pl-6">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-[0.2em] mb-8">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                        <?= __('Digital Analysis Ready') ?>
                    </div>
                    
                    <h1 class="text-5xl font-black leading-tight tracking-tight text-black mb-8">
                        <?= __('Your Life Story,') ?> <br><span class="text-gray-300"><?= __('Written in the Stars.') ?></span>
                    </h1>
                    
                    <div class="space-y-6 max-w-lg mb-12">
                        <p class="text-lg text-gray-500 leading-relaxed">
                            <?= __('Our detailed') ?> <strong class="text-black font-bold"><?= __('11-page Action Analysis report') ?></strong> <?= __('focuses on solving core issues. We analyze life timings via the') ?> <span class="text-black"><?= __('Vimshottari Dasha system') ?></span> <?= __('to support your long-term success.') ?>
                        </p>
                        
                        <div class="p-6 border-l-4 border-black bg-gray-50 rounded-r-2xl shadow-sm">
                            <p class="text-base text-black font-medium leading-relaxed italic">
                                <?= __('"We provide focused remedies, including insights from Lal Kitab, to ensure immediate clarity rather than overwhelming you with information."') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <a href="/store/reports.php" class="px-10 py-5 bg-black text-white font-bold rounded-2xl transition-all hover:bg-gray-800 active:scale-95 shadow-lg text-sm">
                            <?= __('Generate Report') ?>
                        </a>
                        
                        <div class="flex flex-col border-l border-gray-200 pl-6">
                            <span class="text-xs font-bold text-black uppercase tracking-widest"><?= __('In-Depth') ?></span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-tight"><?= __('Personalized Action Steps') ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

</body>
</html>