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
            'My Profile' => 'मेरी प्रोफ़ाइल',
            'Professional Identity' => 'पेशेवर पहचान',
            'View your professional profile details.' => 'अपना पेशेवर प्रोफ़ाइल विवरण देखें।',
            'Profile Locked' => 'प्रोफ़ाइल लॉक है',
            'Your profile details are locked for security reasons. To make changes, please contact the administrator.' => 'सुरक्षा कारणों से आपके प्रोफ़ाइल विवरण लॉक हैं। परिवर्तन करने के लिए, कृपया व्यवस्थापक से संपर्क करें।',
            'Profile Photo' => 'प्रोफ़ाइल फ़ोटो',
            'Display Name (English)' => 'प्रदर्शन नाम (अंग्रेज़ी)',
            'Display Name (Hindi)' => 'प्रदर्शन नाम (हिंदी)',
            'Phone Number' => 'फ़ोन नंबर',
            'About / Bio (English)' => 'परिचय / बायो (अंग्रेज़ी)',
            'About / Bio (Hindi)' => 'परिचय / बायो (हिंदी)',
            'Experience (Years)' => 'अनुभव (वर्ष)',
            'Original Price (₹/min)' => 'मूल मूल्य (₹/मिनट)',
            'Offer Price (₹/min)' => 'ऑफ़र मूल्य (₹/मिनट)',
            'Skills' => 'कौशल',
            'Languages' => 'भाषाएं'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Security Check
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$user_id = (int)$_SESSION['userid'];

// Fetch Current User Data (No POST updates allowed here)
$stmt = $conn->prepare("SELECT astrologer_name, astrologer_name_hi, phone, about, about_hi, experience_years, skills, languages, profile_photo, base_price_per_minute, price_per_minute FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= __('My Profile') ?> | Fortune Path</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --base-txt: 14px;
            --heading-txt: 22px;
        }

        @media (min-width: 1024px) {
            :root {
                --base-txt: 15px;
                --heading-txt: 30px;
            }
        }

        body { 
            font-family: 'Space Grotesk', sans-serif; 
            background: #fdfdfd; 
            color: #1e293b;
            font-size: var(--base-txt);
            transition: background 0.3s ease;
        }

        h1 { font-size: var(--heading-txt); font-weight: 900; tracking-tight; }
        
        .glass-card { background: white; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: background 0.3s; }
        
        .photo-box-wrapper {
            width: 100%;
            height: 280px;
            background: #f8fafc;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
        }

        @media (min-width: 1024px) { .photo-box-wrapper { height: 330px; } }

        .readonly-field {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: var(--base-txt);
            font-weight: 600;
            color: #475569;
            cursor: not-allowed;
        }

        .label-text { 
            font-size: 11px; 
            font-weight: 800; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 8px; 
            display: block; 
        }
        
        .alert-banner {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 16px;
            border-radius: 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        @media (min-width: 1024px) {
            .page-container { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; align-items: start; }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme h1 { color: #f9fafb !important; }
        body.dark-theme .text-slate-500 { color: #d1d5db !important; }
        
        body.dark-theme .photo-box-wrapper { background: #374151 !important; border-color: #4b5563 !important; }
        body.dark-theme .readonly-field { background: #374151 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
        body.dark-theme .label-text { color: #9ca3af !important; }

        body.dark-theme .alert-banner { background: rgba(245, 158, 11, 0.1) !important; border-color: #b45309 !important; color: #fcd34d !important; }
    </style>
</head>

<body class="antialiased overflow-x-hidden">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<div class="max-w-[1100px] mx-auto px-4 py-8 lg:py-12">
    
    <div class="mb-6 lg:mb-8 text-center lg:text-left">
        <div class="inline-block px-4 py-1.5 rounded-full bg-gray-200 text-xs font-bold uppercase tracking-wider mb-3 text-gray-800 dark:bg-gray-700 dark:text-gray-200">✦ <?= __('Professional Identity') ?></div>
        <h1><?= __('My Profile') ?></h1>
        <p class="text-slate-500 font-medium opacity-80 mt-1"><?= __('View your professional profile details.') ?></p>
    </div>

    <!-- Alert Banner (Tells user they cannot edit) -->
    <div class="alert-banner">
        <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V9a4 4 0 00-8 0v4h8z"></path></svg>
        <div>
            <h4 class="font-bold text-base mb-1"><?= __('Profile Locked') ?></h4>
            <p class="text-sm font-medium opacity-90"><?= __('Your profile details are locked for security reasons. To make changes, please contact the administrator.') ?></p>
        </div>
    </div>

    <div class="page-container">
        
        <!-- Photo Section -->
        <div class="glass-card p-5 lg:p-6 mb-6 lg:mb-0">
            <span class="label-text"><?= __('Profile Photo') ?></span>
            <div class="photo-box-wrapper">
                <img src="<?= htmlspecialchars($user['profile_photo'] ? '/assets/images/profiles/'.$user['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover rounded-xl">
            </div>
        </div>

        <!-- Details Section -->
        <div class="glass-card p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                
                <div>
                    <label class="label-text"><?= __('Display Name (English)') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['astrologer_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Display Name (Hindi)') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['astrologer_name_hi'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Phone Number') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Experience (Years)') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['experience_years'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Skills') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['skills'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Languages') ?></label>
                    <div class="readonly-field"><?= htmlspecialchars($user['languages'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Original Price (₹/min)') ?></label>
                    <div class="readonly-field">₹<?= htmlspecialchars($user['base_price_per_minute'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div>
                    <label class="label-text"><?= __('Offer Price (₹/min)') ?></label>
                    <div class="readonly-field text-emerald-600 dark:text-emerald-400">₹<?= htmlspecialchars($user['price_per_minute'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="md:col-span-2">
                    <label class="label-text"><?= __('About / Bio (English)') ?></label>
                    <div class="readonly-field min-h-[80px] whitespace-pre-wrap"><?= htmlspecialchars($user['about'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="md:col-span-2">
                    <label class="label-text"><?= __('About / Bio (Hindi)') ?></label>
                    <div class="readonly-field min-h-[80px] whitespace-pre-wrap"><?= htmlspecialchars($user['about_hi'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Extra space for mobile footer -->
<div class="h-24 lg:hidden"></div>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/web-astrologer-footer.php'; ?></div>

</body>
</html>