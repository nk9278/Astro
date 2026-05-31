<?php
// Ensure session is started for authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Astrologer profile not found.' => 'ज्योतिषी प्रोफ़ाइल नहीं मिली।',
            'OFF' => 'छूट',
            'Online' => 'ऑनलाइन',
            'Offline' => 'ऑफ़लाइन',
            'Call' => 'कॉल',
            'Chat' => 'चैट',
            'Call Offline' => 'कॉल ऑफ़लाइन',
            'Chat Offline' => 'चैट ऑफ़लाइन',
            'Currently Offline' => 'वर्तमान में ऑफ़लाइन',
            '/min' => '/मिनट',
            'years' => 'वर्ष',
            'Years Experience' => 'वर्षों का अनुभव',
            'Reviews' => 'समीक्षाएँ',
            'Expertise & Skills' => 'विशेषज्ञता और कौशल',
            'About the Expert' => 'विशेषज्ञ के बारे में',
            'Consultation Details' => 'परामर्श विवरण',
            'Languages Spoken' => 'बोली जाने वाली भाषाएँ',
            'Session Availability' => 'सत्र की उपलब्धता',
            'Calls & Messaging' => 'कॉल और संदेश',
            'Pricing Plans' => 'मूल्य निर्धारण योजनाएँ',
            '/ PER MINUTE' => '/ प्रति मिनट',
            'Secure Consultation' => 'सुरक्षित परामर्श',
            'Exp:' => 'अनुभव:',
            'Lang:' => 'भाषाएँ:',
            'Skills:' => 'कौशल:'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$isLoggedIn = isset($_SESSION['userid']);
$astrologer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// NEW: Fetching is_chat_online and is_call_online flags
$stmt = $conn->prepare("SELECT astrologer_name, about, experience_years, skills, languages, price_per_minute, base_price_per_minute, offer_percent, profile_photo, profile_rating, rating_count, online_status, is_chat_online, is_call_online FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astrologer_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$astro) {
    echo htmlspecialchars(__("Astrologer profile not found."), ENT_QUOTES, 'UTF-8');
    exit;
}

$base_price = $astro['base_price_per_minute'] ?? 0;
$offer_price = $astro['price_per_minute'] ?? 0;
$offer_percent = $astro['offer_percent'] ?? 0;
$show_offer = $offer_percent > 0;

$rating = $astro['profile_rating'] ?? 0;
$rating_count = $astro['rating_count'] ?? 0;

$status_text = $astro['online_status'] ? __('Online') : __('Offline');
$status_color = $astro['online_status'] ? "#21ba4b" : "#db2525";
$status_bg = $astro['online_status'] ? "#e3f9ec" : "#fdeaea";

$skills = array_filter(array_map('trim', explode(',', $astro['skills'] ?? '')));
$languages = array_filter(array_map('trim', explode(',', $astro['languages'] ?? '')));
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?> - Fortune Parth</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">

<style>
/* MOBILE STYLES */
.mobile-body { font-family: 'Space Grotesk', sans-serif; background: #fafafa; margin: 0; padding: 0 10px 80px; transition: background 0.3s; }
.profile-box { max-width: 420px; margin: 70px auto 30px auto; background: #fff; border-radius: 18px; padding: 20px 18px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.05); position: relative; transition: background 0.3s; }
.offer-tag { position: absolute; top: 10px; right: 14px; background: #b02676; color: #fff; padding: 3px 12px; border-radius: 12px; font-weight: 700; font-size: 14px; z-index: 10; }

.mobile-header-card { display: flex; gap: 16px; align-items: flex-start; text-align: left; margin-bottom: 20px; }
.mobile-photo-container { flex: 0 0 110px; position: relative; display: flex; flex-direction: column; align-items: center; }
.mobile-photo { width: 100%; height: 135px; border-radius: 16px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.15); margin-bottom: 8px; }
.rating-badge { padding: 4px 12px; font-size: 12px; border-radius: 12px; font-weight: 700; background: #37223b; color: #fff; width: 100%; text-align: center; }
.mobile-info { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.mobile-name { font-size: 20px; font-weight: 700; color: #222; margin-bottom: 6px; line-height: 1.2; word-wrap: break-word; }
.status-badge { display: inline-block; padding: 3px 10px; font-size: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 8px; width: max-content; }
.mobile-stat { font-size: 13px; color: #555; margin-bottom: 4px; line-height: 1.4; }
.mobile-stat strong { font-weight: 700; color: #222; }
.mobile-price { font-size: 18px; font-weight: 700; color: #21ba4b; margin-top: 6px; }
.mobile-old-price { font-size: 13px; text-decoration: line-through; color: #d03535; font-weight: 400; margin-right: 6px; }

.about-section { margin-top: 24px; text-align: left; }
.about-text { text-align: justify; font-size: 14px; color: #444; line-height: 1.6; }

/* DESKTOP STYLES */
@media (min-width: 1024px) {
    body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; transition: background 0.3s; }
    .desktop-max-width { max-width: 80rem; margin: 0 auto; padding: 0 3rem; }
    .floating-sidebar { position: sticky; top: 100px; }
    .skill-pill { padding: 6px 16px; background: #f3f4f6; border-radius: 99px; font-weight: 600; font-size: 14px; color: #374151; display: inline-block; margin: 4px; }
}

/* DARK MODE */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme .mobile-body { background: #121212 !important; }
body.dark-theme .profile-box { background: #1f2937 !important; color: #f9fafb !important; box-shadow: 0 4px 18px rgba(0,0,0,0.3) !important; }
body.dark-theme .mobile-name { color: #ffffff !important; }
body.dark-theme .mobile-stat { color: #d1d5db !important; }
body.dark-theme .mobile-stat strong { color: #ffffff !important; }
body.dark-theme .about-text { color: #d1d5db !important; }
body.dark-theme .status-badge { background: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }

body.dark-theme .bg-white { background: #121212 !important; } 
body.dark-theme .text-slate-900, body.dark-theme .text-slate-800 { color: #ffffff !important; }
body.dark-theme .text-slate-600, body.dark-theme .text-slate-400 { color: #d1d5db !important; }
body.dark-theme .skill-pill { background: #374151 !important; color: #f9fafb !important; }
body.dark-theme .bg-slate-50 { background-color: #1f2937 !important; } 
body.dark-theme .border-slate-950 { border-color: #374151 !important; }
body.dark-theme .floating-sidebar > div { background: #1f2937 !important; } 
body.dark-theme .bg-slate-950 { background-color: #374151 !important; color: white !important; border: 1px solid #4b5563 !important; }
body.dark-theme .bg-slate-100 { background-color: #374151 !important; color: #9ca3af !important; border: none !important; }
</style>
</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-theme');
</script>

<div class="lg:hidden mobile-body">
    <?php include '../user/mobile_header.php'; ?>
    
    <div class="profile-box border border-gray-100 dark:border-gray-700">
        <?php if ($show_offer): ?>
            <div class="offer-tag"><?= htmlspecialchars($offer_percent, ENT_QUOTES, 'UTF-8') ?>% <?= __('OFF') ?></div>
        <?php endif; ?>

        <div class="mobile-header-card">
            <div class="mobile-photo-container">
                <img src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>" class="mobile-photo" alt="Profile">
                <div class="rating-badge">★ <?= number_format($rating, 1) ?> <span style="font-weight:400;font-size:10px;">(<?= htmlspecialchars($rating_count, ENT_QUOTES, 'UTF-8') ?>)</span></div>
            </div>
            
            <div class="mobile-info">
                <h1 class="mobile-name"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="status-badge" style="background: <?= $status_bg ?>; color: <?= $status_color ?>;"><?= $status_text ?></div>
                
                <div class="mobile-stat"><strong><?= __('Exp:') ?></strong> <?= htmlspecialchars($astro['experience_years'], ENT_QUOTES, 'UTF-8') ?> <?= __('years') ?></div>
                <div class="mobile-stat"><strong><?= __('Lang:') ?></strong> <?= htmlspecialchars(implode(', ', $languages), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mobile-stat text-truncate" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                    <strong><?= __('Skills:') ?></strong> <?= htmlspecialchars(implode(', ', $skills), ENT_QUOTES, 'UTF-8') ?>
                </div>
                
                <div class="mobile-price">
                    <?php if ($base_price != $offer_price): ?>
                        <span class="mobile-old-price">₹<?= (float)$base_price ?></span>
                    <?php endif; ?>
                    ₹<?= (float)$offer_price ?><span style="font-size:13px;font-weight:600;color:#555;"><?= __('/min') ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <?php if ($astro['online_status'] && $astro['is_call_online']): ?>
                <form id="callForm_mobile_<?= intval($astrologer_id) ?>" method="POST" action="/user/request_call.php" onsubmit="event.preventDefault();">
                    <input type="hidden" name="astro_id" value="<?= intval($astrologer_id) ?>">
                    <button type="button" onclick="startCallProfile(<?= intval($astrologer_id) ?>, 'callForm_mobile_<?= intval($astrologer_id) ?>')" class="w-full py-3.5 bg-slate-950 dark:bg-gray-700 text-white rounded-xl font-bold text-sm shadow-md hover:bg-slate-800 transition">
                        <i class="fas fa-phone mr-1"></i> <?= __('Call') ?>
                    </button>
                </form>
            <?php else: ?>
                <button class="w-full py-3.5 bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 rounded-xl font-bold text-sm cursor-not-allowed border border-slate-200 dark:border-gray-700">
                    <?= __('Call Offline') ?>
                </button>
            <?php endif; ?>

            <?php if ($astro['online_status'] && $astro['is_chat_online']): ?>
                <form id="chatForm_mobile_<?= intval($astrologer_id) ?>" method="POST" action="/user/request_chat.php" onsubmit="event.preventDefault();">
                    <input type="hidden" name="astro_id" value="<?= intval($astrologer_id) ?>">
                    <button type="button" onclick="startChatProfile(<?= intval($astrologer_id) ?>, 'chatForm_mobile_<?= intval($astrologer_id) ?>')" class="w-full py-3.5 bg-emerald-600 text-white rounded-xl font-bold text-sm shadow-md hover:bg-emerald-700 transition">
                        <i class="fas fa-comment mr-1"></i> <?= __('Chat') ?>
                    </button>
                </form>
            <?php else: ?>
                <button class="w-full py-3.5 bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 rounded-xl font-bold text-sm cursor-not-allowed border border-slate-200 dark:border-gray-700">
                    <?= __('Chat Offline') ?>
                </button>
            <?php endif; ?>
        </div>

        <div class="about-section">
            <h3 class="font-bold text-lg mb-2 dark:text-gray-200"><?= __('About the Expert') ?></h3>
            <div class="about-text"><?= nl2br(htmlspecialchars($astro['about'], ENT_QUOTES, 'UTF-8')) ?></div>
        </div>
    </div>
    
    <?php include '../user/footer_user.php'; ?>
</div>

<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>

    <div class="w-full bg-slate-950 py-24 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-indigo-600/10 blur-[120px] rounded-full"></div>
        <div class="desktop-max-width relative z-10">
            <div class="flex items-center gap-12">
                <div class="relative">
                    <img src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>" 
                         class="w-48 h-56 object-cover rounded-[2.5rem] shadow-2xl border-4 border-white/10" alt="Profile">
                    <?php if ($show_offer): ?>
                        <div class="absolute -top-4 -right-4 bg-rose-600 text-white font-bold px-4 py-2 rounded-2xl shadow-lg border-2 border-slate-900">
                            <?= htmlspecialchars($offer_percent, ENT_QUOTES, 'UTF-8') ?>% <?= __('OFF') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-white">
                    <div class="flex items-center gap-4 mb-3">
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest" style="background: <?= $status_bg ?>; color: <?= $status_color ?>;">
                             <?= $status_text ?>
                        </span>
                        <span class="bg-white/10 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-white/70">
                            <?= htmlspecialchars($astro['experience_years'], ENT_QUOTES, 'UTF-8') ?> <?= __('Years Experience') ?>
                        </span>
                    </div>
                    <h1 class="text-6xl font-black mb-4 tracking-tighter"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="flex items-center gap-2 text-yellow-400 text-xl font-black">
                        ★ <?= number_format($rating, 1) ?> <span class="text-white/40 text-sm font-medium">(<?= htmlspecialchars($rating_count, ENT_QUOTES, 'UTF-8') ?>+ <?= __('Reviews') ?>)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="py-20">
        <div class="desktop-max-width">
            <div class="grid grid-cols-12 gap-16 items-start">
                
                <div class="col-span-8 space-y-12">
                    <section>
                        <h2 class="text-3xl font-black mb-6 text-slate-900"><?= __('Expertise & Skills') ?></h2>
                        <div class="flex flex-wrap -ml-1">
                            <?php foreach($skills as $sk): ?>
                                <span class="skill-pill"># <?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-3xl font-black mb-6 text-slate-900"><?= __('About the Expert') ?></h2>
                        <div class="text-xl leading-relaxed text-slate-600 space-y-4">
                            <?= nl2br(htmlspecialchars($astro['about'], ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </section>

                    <section class="p-10 bg-slate-50 rounded-[2.5rem]">
                        <h2 class="text-2xl font-black mb-6 text-slate-900"><?= __('Consultation Details') ?></h2>
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('Languages Spoken') ?></p>
                                <p class="text-lg font-bold text-slate-800"><?= htmlspecialchars(implode(', ', $languages), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('Session Availability') ?></p>
                                <p class="text-lg font-bold text-slate-800"><?= __('Calls & Messaging') ?></p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-span-4 floating-sidebar">
                    <div class="p-8 border-2 border-slate-950 rounded-[2.5rem] shadow-2xl bg-white">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4"><?= __('Pricing Plans') ?></p>
                        <div class="flex items-center gap-3 mb-8">
                            <span class="text-5xl font-black text-slate-950">₹<?= (float)$offer_price ?></span>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-400"><?= __('/ PER MINUTE') ?></span>
                                <?php if ($base_price != $offer_price): ?>
                                    <span class="text-sm font-bold text-rose-600 line-through">₹<?= (float)$base_price ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <?php if ($astro['online_status'] && $astro['is_call_online']): ?>
                                <form id="callForm_desktop_<?= intval($astrologer_id) ?>" method="POST" action="/user/request_call.php" onsubmit="event.preventDefault();">
                                    <input type="hidden" name="astro_id" value="<?= intval($astrologer_id) ?>">
                                    <button type="button" onclick="startCallProfile(<?= intval($astrologer_id) ?>, 'callForm_desktop_<?= intval($astrologer_id) ?>')" class="w-full py-5 bg-slate-950 dark:bg-gray-700 text-white rounded-2xl font-bold text-lg hover:bg-slate-800 transition-all shadow-xl flex items-center justify-center gap-3">
                                        <i class="fas fa-phone"></i> <?= __('Start Call') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="w-full py-5 bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 rounded-2xl font-bold text-lg cursor-not-allowed flex items-center justify-center gap-3 border border-slate-200 dark:border-gray-700" disabled>
                                    <i class="fas fa-phone-slash"></i> <?= __('Call Offline') ?>
                                </button>
                            <?php endif; ?>

                            <?php if ($astro['online_status'] && $astro['is_chat_online']): ?>
                                <form id="chatForm_desktop_<?= intval($astrologer_id) ?>" method="POST" action="/user/request_chat.php" onsubmit="event.preventDefault();">
                                    <input type="hidden" name="astro_id" value="<?= intval($astrologer_id) ?>">
                                    <button type="button" onclick="startChatProfile(<?= intval($astrologer_id) ?>, 'chatForm_desktop_<?= intval($astrologer_id) ?>')" class="w-full py-5 bg-emerald-600 text-white rounded-2xl font-bold text-lg hover:bg-emerald-700 transition-all shadow-xl flex items-center justify-center gap-3">
                                        <i class="fas fa-comment"></i> <?= __('Start Chat') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="w-full py-5 bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 rounded-2xl font-bold text-lg cursor-not-allowed flex items-center justify-center gap-3 border border-slate-200 dark:border-gray-700" disabled>
                                    <i class="fas fa-comment-slash"></i> <?= __('Chat Offline') ?>
                                </button>
                            <?php endif; ?>

                        </div>
                        
                        <div class="mt-6 flex items-center justify-center gap-2 text-slate-400 text-xs font-bold uppercase tracking-widest">
                            <i class="fas fa-shield-alt text-sm"></i>
                            <?= __('Secure Consultation') ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

<script>
const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

async function startCallProfile(id, formId){
    if(!isLoggedIn){
        window.location.href="/auth/login.php";
        return;
    }
    const form = document.getElementById(formId);
    if (!form) return;
    
    const fd = new FormData(form);
    try {
        const res = await fetch(form.action, { method: "POST", body: fd });
        const data = await res.json();
        if(data.status === "success"){
            window.location.href = `/user/call_waiting.php?astro_id=${id}&call_id=${data.call_id}`;
        } else {
            alert(data.message || "Unable to initiate call. Please try again later.");
        }
    } catch(err) { alert("Network error. Please try again."); }
}

async function startChatProfile(id, formId) {
    if(!isLoggedIn){
        window.location.href="/auth/login.php";
        return;
    }
    const form = document.getElementById(formId);
    if (!form) return;
    
    const fd = new FormData(form);
    try {
        const res = await fetch(form.action, { method: "POST", body: fd });
        const data = await res.json();
        if(data.status === "success"){
            window.location.href = `/user/chat_waiting.php?astro_id=${id}&session_id=${data.session_id}`;
        } else {
            alert(data.message || "Unable to initiate chat. Please try again later.");
        }
    } catch(err) { alert("Network error. Please try again."); }
}
</script>

</body>
</html>