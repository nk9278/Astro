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
            'Palmistry (Hast-Rekha) - Fortune Parth' => 'हस्तरेखा - फॉर्च्यून पाथ',
            'Hast-Rekha' => 'हस्तरेखा',
            'Palm Reading' => 'हस्तरेखा पठन',
            'Our palmistry experts interpret hand lines, mounts, and' => 'हमारे हस्तरेखा विशेषज्ञ हाथ की रेखाओं, पर्वतों और',
            'dermatoglyphics' => 'डर्माटोग्लिफिक्स (हथेली की छाप)',
            'to provide insights into your health, wealth, career, and relationships.' => 'की व्याख्या करके आपके स्वास्थ्य, धन, करियर और संबंधों के बारे में अंतर्दृष्टि प्रदान करते हैं।',
            'Receive clear guidance with practical actions and precautions to help you navigate the months ahead with confidence.' => 'आत्मविश्वास के साथ आने वाले महीनों को नेविगेट करने में मदद के लिए व्यावहारिक कार्यों और सावधानियों के साथ स्पष्ट मार्गदर्शन प्राप्त करें।',
            'Start Reading' => 'पठन शुरू करें',
            'Palmistry' => 'हस्तरेखा',
            'Destiny in your hands' => 'आपके हाथों में नियति',
            'Top Palmists Online' => 'शीर्ष हस्तरेखाविद ऑनलाइन',
            'Lines of Life' => 'जीवन की रेखाएं',
            'Written in Time.' => 'समय में लिखी गई।',
            'Our palmistry experts interpret hand lines, mounts, and' => 'हमारे हस्तरेखा विशेषज्ञ हाथ की रेखाओं, पर्वतों और',
            'to provide precise insights into your health, wealth, career, and relationships.' => 'की व्याख्या करके आपके स्वास्थ्य, धन, करियर और संबंधों के बारे में सटीक अंतर्दृष्टि प्रदान करते हैं।',
            '"Get clear, concise guidance with practical actions and precautions to help you navigate your future with absolute confidence."' => '"अपने भविष्य को पूर्ण आत्मविश्वास के साथ नेविगेट करने में मदद के लिए व्यावहारिक कार्यों और सावधानियों के साथ स्पष्ट, संक्षिप्त मार्गदर्शन प्राप्त करें।"',
            'Consult a Palmist' => 'हस्तरेखाविद से परामर्श लें',
            'Confidential' => 'गोपनीय',
            'Image Based Analysis' => 'छवि आधारित विश्लेषण'
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
    <title><?= __('Palmistry (Hast-Rekha) - Fortune Parth') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">

    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; color: #1a1a1a; overflow-x: hidden; transition: background 0.3s ease; }
        
        .bg-glow {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 70% 30%, #f0f2ff 0%, #ffffff 100%);
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
                <svg viewBox="0 0 200 200" class="mystic-rotate w-[120%] h-[120%] stroke-white fill-none">
                    <circle cx="100" cy="100" r="90" stroke-width="1.8" />
                    <path d="M100 5 L120 40 L160 40 L140 70 L170 100 L140 130 L160 160 L120 160 L100 195 L80 160 L40 160 L60 130 L30 100 L60 70 L40 40 L80 40 Z" stroke-width="2.2" />
                    <circle cx="100" cy="100" r="15" stroke-width="1" stroke-dasharray="4 4" />
                </svg>
            </div>

            <div class="relative z-10 text-center">
                <h2 class="text-2xl font-bold tracking-[0.2em] uppercase text-white"><?= __('Hast-Rekha') ?></h2>
                <p class="text-[9px] text-gray-400 tracking-[0.4em] uppercase mt-2"><?= __('Palm Reading') ?></p>
            </div>
        </div>

        <div class="space-y-4 mb-10 px-2">
            <p class="text-gray-600 leading-relaxed text-sm">
                <?= __('Our palmistry experts interpret hand lines, mounts, and') ?> <strong><?= __('dermatoglyphics') ?></strong> <?= __('to provide insights into your health, wealth, career, and relationships.') ?>
            </p>
            <p class="text-gray-500 text-xs italic">
                <?= __('Receive clear guidance with practical actions and precautions to help you navigate the months ahead with confidence.') ?>
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
                    <div class="floating-card relative w-[85%] aspect-[4/5] bg-black rounded-[3.5rem] shadow-2xl flex flex-col items-center justify-center p-12 overflow-hidden">
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-30">
                            <svg viewBox="0 0 200 200" class="mystic-rotate w-[140%] h-[140%] stroke-white fill-none">
                                <circle cx="100" cy="100" r="95" stroke-width="1.5" />
                                <path d="M100 5 L120 40 L160 40 L140 70 L170 100 L140 130 L160 160 L120 160 L100 195 L80 160 L40 160 L60 130 L30 100 L60 70 L40 40 L80 40 Z" stroke-width="2" />
                                <circle cx="100" cy="100" r="70" stroke-width="0.8" stroke-dasharray="10 10" />
                            </svg>
                        </div>

                        <div class="relative z-10 w-full flex flex-col items-center">
                            <h2 class="text-4xl font-black text-white uppercase tracking-[0.2em] mb-3"><?= __('Palmistry') ?></h2>
                            <div class="h-[1px] w-20 bg-white/40 mb-4"></div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.4em]"><?= __('Destiny in your hands') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-span-7 pl-6">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black text-white text-[9px] font-bold uppercase tracking-[0.2em] mb-8">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        <?= __('Top Palmists Online') ?>
                    </div>
                    
                    <h1 class="text-5xl font-black leading-tight tracking-tight text-black mb-8">
                        <?= __('Lines of Life') ?> <br><span class="text-gray-300"><?= __('Written in Time.') ?></span>
                    </h1>
                    
                    <div class="space-y-6 max-w-lg mb-12">
                        <p class="text-lg text-gray-500 leading-relaxed">
                            <?= __('Our palmistry experts interpret hand lines, mounts, and') ?> <strong class="text-black font-bold"><?= __('dermatoglyphics') ?></strong> <?= __('to provide precise insights into your health, wealth, career, and relationships.') ?>
                        </p>
                        
                        <div class="p-6 border-l-4 border-black bg-gray-50 rounded-r-2xl shadow-sm">
                            <p class="text-base text-black font-medium leading-relaxed italic">
                                <?= __('"Get clear, concise guidance with practical actions and precautions to help you navigate your future with absolute confidence."') ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <a href="/astrologer/list.php" class="px-10 py-5 bg-black text-white font-bold rounded-2xl transition-all hover:bg-gray-800 active:scale-95 shadow-lg text-sm">
                            <?= __('Consult a Palmist') ?>
                        </a>
                        
                        <div class="flex flex-col border-l border-gray-200 pl-6">
                            <span class="text-xs font-bold text-black uppercase tracking-widest"><?= __('Confidential') ?></span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-tight"><?= __('Image Based Analysis') ?></span>
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