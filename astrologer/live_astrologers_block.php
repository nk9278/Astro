<?php
require_once __DIR__ . '/../db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Online' => 'ऑनलाइन',
            'Offline' => 'ऑफ़लाइन',
            'yrs' => 'वर्ष',
            '/min' => '/मिनट',
            'Call' => 'कॉल',
            'Chat' => 'चैट',
            'Call Off' => 'कॉल बंद',
            'Chat Off' => 'चैट बंद',
            'View All Astrologers' => 'सभी ज्योतिषियों को देखें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$isLoggedIn = isset($_SESSION['userid']);

/* ================= FETCH DATA ================= */

// ONLINE (Only Approved Astrologers)
$stmt_online = $conn->prepare("
SELECT id, astrologer_name, experience_years, profile_photo, online_status,
       profile_rating, rating_count, wait_time, price_per_minute,
       base_price_per_minute, skills, languages, is_chat_online, is_call_online
FROM users
WHERE role_id = 1 AND online_status = 1 AND approval_status = 'approved'
");
$stmt_online->execute();
$online_astrologers = $stmt_online->fetchAll(PDO::FETCH_ASSOC);

// OFFLINE (Only Approved Astrologers)
$stmt_offline = $conn->prepare("
SELECT id, astrologer_name, experience_years, profile_photo, online_status,
       profile_rating, rating_count, wait_time, price_per_minute,
       base_price_per_minute, skills, languages, is_chat_online, is_call_online
FROM users
WHERE role_id = 1 AND online_status = 0 AND approval_status = 'approved'
");
$stmt_offline->execute();
$offline_astrologers = $stmt_offline->fetchAll(PDO::FETCH_ASSOC);

/* ================= RANDOM ORDER ================= */

shuffle($online_astrologers);
shuffle($offline_astrologers);

/* ================= MIN 6 LOGIC ================= */

$display_limit = 6;
$display_astrologers = $online_astrologers;

if(count($display_astrologers) < $display_limit){
    $remaining = $display_limit - count($display_astrologers);
    $display_astrologers = array_merge(
        $display_astrologers,
        array_slice($offline_astrologers, 0, $remaining)
    );
}

/* If online > 6 then still show only 6 */
$display_astrologers = array_slice($display_astrologers, 0, $display_limit);

/* For View All condition */
$total_astrologers = count($online_astrologers) + count($offline_astrologers);
?>

<style>
/* ================= MOBILE CARD ================= */
.astro-wrapper{ padding-bottom:30px; }

.astro-card{
  width:92%;
  margin:0 auto 12px auto;
  display:flex;
  flex-direction: column;
  background:#fff;
  border:1px solid #e6e6e6;
  border-radius:16px;
  padding:12px;
  position:relative;
  transition: all 0.3s ease;
}

.astro-top-info {
  display: flex;
  width: 100%;
}

.astro-left{ width:90px; min-width:90px; position:relative; }

.astro-left img{
  width:90px; height:110px; border-radius:14px; object-fit:cover;
}

.astro-rating{
  position:absolute; left:8px; bottom:-10px;
  background:#222; color:#fff; padding:2px 8px; font-size:10px; border-radius:8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.astro-details{ flex:1; padding-left:12px; }

.astro-name{ font-size:15px; font-weight:700; color:#222; }

.astro-skills, .astro-exp, .astro-lang{ font-size:12px; margin-top:2px; color:#555; }

.astro-status{
  position:absolute; right:10px; top:10px;
  font-size:11px; padding:3px 8px; border-radius:7px;
  background:#ffe7c7; font-weight:600; color:#d97706;
}

.astro-status.offline{ background:#e8e8e8; color:#777; }

.astro-price-old{ font-size:11px; text-decoration:line-through; color:#d03535; }

.astro-price-new{ color:#0c9c45; font-size:15px; font-weight:700; }

/* ================= DESKTOP GRID ================= */
@media(min-width:768px){
  .astro-wrapper{
    max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(2,1fr); gap:18px;
  }
  .astro-card{ width:100%; margin:0; }
}

@media(min-width:1200px){
  .astro-wrapper{ grid-template-columns:repeat(3,1fr); }
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme .astro-card { background: #1f2937 !important; border-color: #374151 !important; }
body.dark-theme .astro-name { color: #ffffff !important; }
body.dark-theme .astro-skills, 
body.dark-theme .astro-exp, 
body.dark-theme .astro-lang { color: #d1d5db !important; }

body.dark-theme .astro-status { background: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }
body.dark-theme .astro-status.offline { background: #374151 !important; color: #9ca3af !important; }
</style>

<div class="astro-wrapper">

<?php foreach ($display_astrologers as $astro): ?>
<?php $online = $astro['online_status'] ? 1 : 0; ?>

<div class="astro-card" onclick="window.location.href='/astrologer/profile.php?id=<?= intval($astro['id']) ?>'">

    <span class="astro-status <?= !$online ? 'offline' : '' ?>">
        <?= $online ? __('Online') : __('Offline') ?>
    </span>

    <div class="astro-top-info">
        <div class="astro-left">
            <img src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>" alt="Profile">
            <div class="astro-rating">★ <?= number_format((float)$astro['profile_rating'], 1) ?></div>
        </div>

        <div class="astro-details">
            <div class="astro-name"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="astro-skills text-truncate" style="display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">
                <?= htmlspecialchars($astro['skills'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="astro-exp"><?= htmlspecialchars($astro['experience_years'], ENT_QUOTES, 'UTF-8') ?> <?= __('yrs') ?></div>
            <div class="astro-lang"><?= htmlspecialchars($astro['languages'], ENT_QUOTES, 'UTF-8') ?></div>
            
            <div style="margin-top:4px;">
                <?php if ($astro['base_price_per_minute'] != $astro['price_per_minute']): ?>
                    <span class="astro-price-old">₹<?= (float)$astro['base_price_per_minute'] ?><?= __('/min') ?></span>
                <?php endif; ?>
                <span class="astro-price-new">₹<?= (float)$astro['price_per_minute'] ?><?= __('/min') ?></span>
            </div>
        </div>
    </div>

    <div class="flex gap-2 mt-4 z-10 relative" onclick="event.stopPropagation();">
        
        <?php if ($online && $astro['is_call_online']): ?>
            <form id="callForm_<?= intval($astro['id']) ?>" method="POST" action="/user/request_call.php" class="flex-1">
                <input type="hidden" name="astro_id" value="<?= intval($astro['id']) ?>">
                <button type="button" class="w-full bg-black dark:bg-gray-700 text-white py-2 rounded-lg text-sm font-bold transition shadow-sm hover:bg-gray-800" onclick="startCall(<?= intval($astro['id']) ?>)">
                    <i class="fas fa-phone mr-1 text-xs"></i> <?= __('Call') ?>
                </button>
            </form>
        <?php else: ?>
            <button class="w-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-2 rounded-lg text-sm font-bold flex-1 cursor-not-allowed border border-gray-200 dark:border-gray-700" disabled>
                <?= __('Call Off') ?>
            </button>
        <?php endif; ?>

        <?php if ($online && $astro['is_chat_online']): ?>
            <form id="chatForm_<?= intval($astro['id']) ?>" method="POST" action="/user/request_chat.php" class="flex-1">
                <input type="hidden" name="astro_id" value="<?= intval($astro['id']) ?>">
                <button type="button" class="w-full bg-emerald-600 text-white py-2 rounded-lg text-sm font-bold transition shadow-sm hover:bg-emerald-700" onclick="startChat(<?= intval($astro['id']) ?>)">
                    <i class="fas fa-comment mr-1 text-xs"></i> <?= __('Chat') ?>
                </button>
            </form>
        <?php else: ?>
            <button class="w-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-2 rounded-lg text-sm font-bold flex-1 cursor-not-allowed border border-gray-200 dark:border-gray-700" disabled>
                <?= __('Chat Off') ?>
            </button>
        <?php endif; ?>
        
    </div>

</div>
<?php endforeach; ?>

</div>

<?php if ($total_astrologers > $display_limit): ?>
<div style="text-align:center;margin-top:14px;">
    <a href="/astrologer/list.php" style="font-size:15px;font-weight:700;" class="text-gray-800 dark:text-gray-200 hover:text-indigo-600 transition">
        <?= __('View All Astrologers') ?>
    </a>
</div>
<?php endif; ?>

<script>
const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

async function startCall(id){
    if(!isLoggedIn){
        window.location.href="/auth/login.php";
        return;
    }
    const form = document.getElementById('callForm_'+id);
    const fd = new FormData(form);

    try {
        const res = await fetch(form.action, { method: "POST", body: fd });
        const data = await res.json();
        if(data.status === "success"){
            window.location.href = `/user/call_waiting.php?astro_id=${id}&call_id=${data.call_id}`;
        } else { alert(data.message); }
    } catch(err) { alert("Network error. Try again."); }
}

async function startChat(id) {
    if(!isLoggedIn){
        window.location.href="/auth/login.php";
        return;
    }
    const form = document.getElementById('chatForm_'+id);
    const fd = new FormData(form);

    try {
        const res = await fetch(form.action, { method: "POST", body: fd });
        const data = await res.json();
        if(data.status === "success"){
            window.location.href = `/user/chat_waiting.php?astro_id=${id}&session_id=${data.session_id}`;
        } else { alert(data.message); }
    } catch(err) { alert("Network error. Try again."); }
}
</script>