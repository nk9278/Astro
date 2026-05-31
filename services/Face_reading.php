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
            'Face & Palm Reading - Fortune Parth' => 'चेहरा और हस्तरेखा पठन - फॉर्च्यून पाथ',
            'Analysis' => 'विश्लेषण',
            'Face & Palm' => 'चेहरा और हथेली',
            'Through the study of' => 'के अध्ययन के माध्यम से',
            'facial features, structure, and expressions' => 'चेहरे की विशेषताओं, संरचना और भाव',
            ', we reveal insights about your temperament, strengths, stress areas, and leadership potential.' => ', हम आपके स्वभाव, शक्तियों, तनाव क्षेत्रों और नेतृत्व क्षमता के बारे में अंतर्दृष्टि प्रकट करते हैं।',
            'Our experts provide practical recommendations for behavioral and lifestyle adjustments to improve your personal and professional outcomes.' => 'हमारे विशेषज्ञ आपके व्यक्तिगत और व्यावसायिक परिणामों को बेहतर बनाने के लिए व्यवहारिक और जीवन शैली समायोजन के लिए व्यावहारिक सिफारिशें प्रदान करते हैं।',
            'Start Reading' => 'पठन शुरू करें',
            'Expert Readers Online' => 'विशेषज्ञ पाठक ऑनलाइन',
            'Ancient Portraits' => 'प्राचीन चित्र',
            'Decoded with Care.' => 'सावधानी से डिकोड किया गया।',
            '"These observations are translated into practical recommendations for behavioral and lifestyle adjustments to improve personal and professional outcomes."' => '"इन टिप्पणियों को व्यक्तिगत और व्यावसायिक परिणामों को बेहतर बनाने के लिए व्यवहारिक और जीवन शैली समायोजन के लिए व्यावहारिक सिफारिशों में अनुवादित किया गया है।"',
            'Consult a Reader' => 'पाठक से परामर्श करें',
            'Confidential' => 'गोपनीय',
            'Private Analysis' => 'निजी विश्लेषण'
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
    <title><?= __('Face & Palm Reading - Fortune Parth') ?></title>
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

        /* Continuous rotation for the brighter mandala */
        @keyframes rotate-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .mystic-rotate { animation: rotate-slow 100s linear infinite; }
        
        /* Floating effect for the black box */
        @keyframes float-box { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .floating-box { animation: float-box 6s ease-in-out infinite; transition: background 0.3s ease; }

        @media (min-width: 1024px) {
            .desktop-container { max-width: 80rem; margin: 0 auto; padding: 0 4rem; }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-glow { background: radial-gradient(circle at 70% 30%, #1f2937 0%, #121212 100%) !important; }
        
        body.dark-theme .text-black, body.dark-theme .text-gray-900 { color: #ffffff !important; }
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
    
    <div class="flex flex-col items-center justify-center min-h-[85vh] px-8 text-center pt-8">
        <div class="relative w-64 h-72 mb-8 bg-black rounded-[2rem] shadow-2xl flex flex-col items-center justify-center p-6 floating-box overflow-hidden">
            
            <div class="absolute inset-0 flex items-center justify-center opacity-40">
                <svg viewBox="0 0 200 200" class="mystic-rotate w-[120%] h-[120%] stroke-white fill-none">
                    <circle cx="100" cy="100" r="90" stroke-width="1.5"/>
                    <path d="M100 5 L105 95 L195 100 L105 105 L100 195 L95 105 L5 100 L95 95 Z" stroke-width="2"/>
                </svg>
            </div>

            <div class="relative z-10 text-center">
                <h2 class="text-xl font-bold tracking-[0.2em] uppercase text-white"><?= __('Analysis') ?></h2>
                <p class="text-[9px] text-gray-300 tracking-[0.4em] uppercase mt-2"><?= __('Face & Palm') ?></p>
            </div>
        </div>

        <div class="space-y-4 mb-10 px-2">
            <p class="text-gray-600 leading-relaxed text-sm">
                <?= __('Through the study of') ?> <strong><?= __('facial features, structure, and expressions') ?></strong><?= __(', we reveal insights about your temperament, strengths, stress areas, and leadership potential.') ?>
            </p>
            <p class="text-gray-500 text-xs italic">
                <?= __('Our experts provide practical recommendations for behavioral and lifestyle adjustments to improve your personal and professional outcomes.') ?>
            </p>
        </div>

        <a href="/astrologer/list.php" class="w-full py-4 bg-black text-white font-bold rounded-xl text-center shadow-xl mb-24">
            <?= __('Start Reading') ?>
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
                    <div class="floating-box relative w-[85%] aspect-[4/5] bg-black rounded-[3rem] shadow-2xl flex flex-col items-center justify-center p-10 overflow-hidden">
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-30">
                            <svg viewBox="0 0 200 200" class="mystic-rotate w-[130%] h-[130%] stroke-white fill-none">
                                <circle cx="100" cy="100" r="90" stroke-width="1.2"/>
                                <circle cx="100" cy="100" r="70" stroke-width="0.8" stroke-dasharray="4 8"/>
                                <path d="M100 5 L105 95 L195 100 L105 105 L100 195 L95 105 L5 100 L95 95 Z" stroke-width="1.8"/>
                            </svg>
                        </div>

                        <div class="relative z-10 text-center">
                            <h2 class="text-3xl font-black text-white uppercase tracking-[0.2em] mb-3"><?= __('Analysis') ?></h2>
                            <div class="h-[1px] w-20 bg-white/40 mx-auto mb-4"></div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.4em]"><?= __('Face & Palm') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-span-7 pl-6">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-[0.2em] mb-8">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        <?= __('Expert Readers Online') ?>
                    </div>
                    
                    <h1 class="text-5xl font-black leading-tight tracking-tight text-black mb-8">
                        <?= __('Ancient Portraits') ?> <br><span class="text-gray-300"><?= __('Decoded with Care.') ?></span>
                    </h1>
                    
                    <div class="space-y-6 max-w-lg mb-12">
                        <p class="text-lg text-gray-500 leading-relaxed">
                            <?= __('Through the study of') ?> <strong class="text-black font-bold"><?= __('facial features, structure, and expressions') ?></strong><?= __(', we reveal insights about your temperament, strengths, stress areas, and leadership potential.') ?>
                        </p>
                        
                        <div class="p-6 border-l-4 border-black bg-gray-50 rounded-r-2xl shadow-sm">
                            <p class="text-base text-black italic font-medium leading-relaxed">
                                <?= __('"These observations are translated into practical recommendations for behavioral and lifestyle adjustments to improve personal and professional outcomes."') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <a href="/astrologer/list.php" class="px-10 py-5 bg-black text-white font-bold rounded-2xl transition-all hover:bg-gray-800 active:scale-95 shadow-lg text-sm">
                            <?= __('Consult a Reader') ?>
                        </a>
                        
                        <div class="flex flex-col border-l border-gray-200 pl-6">
                            <span class="text-xs font-bold text-black uppercase tracking-wider"><?= __('Confidential') ?></span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-tight"><?= __('Private Analysis') ?></span>
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