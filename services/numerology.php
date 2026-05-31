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
            'Numerology - Fortune Parth' => 'अंक ज्योतिष - फॉर्च्यून पाथ',
            'Numbers' => 'अंक',
            'Destiny Analysis' => 'भाग्य विश्लेषण',
            'Mathematical Life Patterns' => 'गणितीय जीवन स्वरूप',
            'We analyze the numbers connected to your' => 'हम आपके से जुड़े अंकों का विश्लेषण करते हैं',
            'name and date of birth' => 'नाम और जन्म तिथि',
            'to uncover patterns that influence your life. Our experts guide you in choosing' => 'ताकि आपके जीवन को प्रभावित करने वाले पैटर्न का पता चल सके। हमारे विशेषज्ञ आपको चुनने में मार्गदर्शन करते हैं',
            'favorable business names, mobile numbers, and key event dates.' => 'अनुकूल व्यावसायिक नाम, मोबाइल नंबर और प्रमुख घटना तिथियां।',
            'Using precise, minimal adjustments to create positive shifts and better outcomes.' => 'सकारात्मक बदलाव और बेहतर परिणाम बनाने के लिए सटीक, न्यूनतम समायोजन का उपयोग करना।',
            'Check Your Numbers' => 'अपने अंक जांचें',
            'Pythagorean Wisdom' => 'पाइथागोरस ज्ञान',
            'Expert Numerologists Online' => 'विशेषज्ञ अंक ज्योतिषी ऑनलाइन',
            'Your Life' => 'आपका जीवन',
            'In Numbers.' => 'अंकों में।',
            'We analyze the numbers connected to your' => 'हम आपके',
            'to uncover patterns that influence your life\'s trajectory.' => 'से जुड़े अंकों का विश्लेषण करते हैं ताकि आपके जीवन के प्रक्षेपवक्र को प्रभावित करने वाले पैटर्न का पता चल सके।',
            'Our experts guide you in choosing' => 'हमारे विशेषज्ञ आपको',
            'favorable business names, mobile numbers, and key event dates' => 'अनुकूल व्यावसायिक नाम, मोबाइल नंबर और प्रमुख घटना तिथियां',
            ', using precise, minimal adjustments to create positive shifts and better outcomes.' => 'चुनने में मार्गदर्शन करते हैं, ताकि सकारात्मक बदलाव और बेहतर परिणाम मिल सकें।',
            'Get Your Report' => 'अपनी रिपोर्ट प्राप्त करें',
            'Calculated' => 'गणना की गई',
            'Full Compatibility Sync' => 'पूर्ण संगतता सिंक'
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
    <title><?= __('Numerology - Fortune Parth') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">

    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; color: #1a1a1a; overflow-x: hidden; transition: background 0.3s ease; }
        
        /* Soft background depth */
        .bg-glow {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 70% 30%, #f0f2ff 0%, #ffffff 100%);
            z-index: -1;
            transition: background 0.3s ease;
        }

        /* Continuous rotation for the high-visibility mandala */
        @keyframes rotate-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .num-rotate { animation: rotate-slow 100s linear infinite; }
        
        /* Premium floating effect for the black box */
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .floating-box { animation: float 6s ease-in-out infinite; transition: background 0.3s ease; }

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
        body.dark-theme .floating-box { background-color: #1f2937 !important; border: 1px solid #374151 !important; }
        
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
        <div class="relative w-64 h-72 mb-8 bg-black rounded-[2.5rem] shadow-2xl flex flex-col items-center justify-center p-6 floating-box overflow-hidden">
            
            <div class="absolute inset-0 flex items-center justify-center opacity-40">
                <svg viewBox="0 0 200 200" class="num-rotate w-[120%] h-[120%] stroke-white fill-none">
                    <circle cx="100" cy="100" r="85" stroke-width="1.5" stroke-dasharray="10 15"/>
                    <path d="M100 10 L105 95 L190 100 L105 105 L100 190 L95 105 L10 100 L95 95 Z" stroke-width="2.5" />
                    <circle cx="100" cy="100" r="10" stroke-width="1.5"/>
                </svg>
            </div>

            <div class="relative z-10 text-center">
                <h2 class="text-2xl font-bold tracking-[0.2em] uppercase text-white"><?= __('Numbers') ?></h2>
                <p class="text-[9px] text-gray-400 tracking-[0.4em] uppercase mt-2"><?= __('Destiny Analysis') ?></p>
            </div>
        </div>

        <div class="space-y-4 mb-10 px-2">
            <p class="text-gray-800 font-semibold text-base"><?= __('Mathematical Life Patterns') ?></p>
            <p class="text-gray-600 leading-relaxed text-sm">
                <?= __('We analyze the numbers connected to your') ?> <strong><?= __('name and date of birth') ?></strong> <?= __('to uncover patterns that influence your life. Our experts guide you in choosing') ?> <strong><?= __('favorable business names, mobile numbers, and key event dates.') ?></strong>
            </p>
            <p class="text-gray-500 text-xs italic">
                <?= __('Using precise, minimal adjustments to create positive shifts and better outcomes.') ?>
            </p>
        </div>

        <a href="/astrologer/list.php" class="w-full py-4 bg-black text-white font-bold rounded-xl text-center shadow-xl mb-24">
            <?= __('Check Your Numbers') ?>
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
                    <div class="floating-box relative w-[85%] aspect-[4/5] bg-black rounded-[3.5rem] shadow-2xl flex flex-col items-center justify-center p-12 overflow-hidden">
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-30">
                            <svg viewBox="0 0 200 200" class="num-rotate w-[140%] h-[140%] stroke-white fill-none">
                                <circle cx="100" cy="100" r="95" stroke-width="1.2" />
                                <circle cx="100" cy="100" r="70" stroke-width="0.8" stroke-dasharray="6 12" />
                                <path d="M100 10 L105 95 L190 100 L105 105 L100 190 L95 105 L10 100 L95 95 Z" stroke-width="2" />
                                <circle cx="100" cy="100" r="15" stroke-width="1" />
                            </svg>
                        </div>

                        <div class="relative z-10 w-full flex flex-col items-center">
                            <h2 class="text-4xl font-black text-white uppercase tracking-[0.25em] mb-3"><?= __('Numbers') ?></h2>
                            <div class="h-[1px] w-20 bg-white/40 mb-4"></div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.5em]"><?= __('Pythagorean Wisdom') ?></p>
                        </div>

                        <div class="absolute top-12 left-12 w-8 h-8 border-t border-l border-white/10"></div>
                        <div class="absolute bottom-12 right-12 w-8 h-8 border-b border-r border-white/10"></div>
                    </div>
                </div>

                <div class="col-span-7 pl-6">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-[0.2em] mb-8">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        <?= __('Expert Numerologists Online') ?>
                    </div>
                    
                    <h1 class="text-5xl font-black leading-tight tracking-tight text-black mb-8">
                        <?= __('Your Life') ?> <br><span class="text-gray-300"><?= __('In Numbers.') ?></span>
                    </h1>
                    
                    <div class="space-y-6 max-w-xl mb-12">
                        <p class="text-lg text-gray-500 leading-relaxed">
                            <?= __('We analyze the numbers connected to your') ?> <strong class="text-black"><?= __('name and date of birth') ?></strong> <?= __('to uncover patterns that influence your life\'s trajectory.') ?> 
                        </p>
                        
                        <div class="p-6 border-l-4 border-black bg-gray-50 rounded-r-2xl shadow-sm">
                            <p class="text-base text-black font-medium leading-relaxed">
                                <?= __('Our experts guide you in choosing') ?> <span class="text-gray-600 font-bold"><?= __('favorable business names, mobile numbers, and key event dates') ?></span><?= __(', using precise, minimal adjustments to create positive shifts and better outcomes.') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <a href="/astrologer/list.php" class="px-10 py-5 bg-black text-white font-bold rounded-2xl transition-all hover:bg-gray-800 active:scale-95 shadow-lg text-sm">
                            <?= __('Get Your Report') ?>
                        </a>
                        
                        <div class="flex flex-col border-l border-gray-200 pl-6">
                            <span class="text-xs font-bold text-black uppercase tracking-widest"><?= __('Calculated') ?></span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-tight"><?= __('Full Compatibility Sync') ?></span>
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