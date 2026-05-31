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
            'Call History - Fortune Parth' => 'कॉल इतिहास - फॉर्च्यून पाथ',
            'My Call History' => 'मेरा कॉल इतिहास',
            'From' => 'से',
            'To' => 'तक',
            'Apply' => 'लागू करें',
            'No call records found.' => 'कोई कॉल रिकॉर्ड नहीं मिला।',
            'Duration:' => 'अवधि:',
            'min' => 'मिनट',
            'Call' => 'कॉल',
            'Logs.' => 'लॉग्स।',
            'Review your spiritual consultations and durations.' => 'अपने आध्यात्मिक परामर्शों और अवधियों की समीक्षा करें।',
            'From Date' => 'आरंभ तिथि',
            'To Date' => 'अंतिम तिथि',
            'Filter Results' => 'परिणाम फ़िल्टर करें',
            'Clear' => 'साफ़ करें',
            'No conversation records found for this period.' => 'इस अवधि के लिए कोई बातचीत रिकॉर्ड नहीं मिला।',
            'Attended' => 'उपस्थित',
            'Astrologer Rejected' => 'ज्योतिषी ने अस्वीकार किया',
            'Cancelled by You' => 'आपके द्वारा रद्द किया गया',
            'Missed' => 'छूट गया',
            'Ringing' => 'बज रहा है'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// =========================
// SECURITY
// =========================
if (!isset($_SESSION['userid'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = intval($_SESSION['userid']);

$stmtRole = $conn->prepare("SELECT role_id, name, email FROM users WHERE id=?");
$stmtRole->execute([$user_id]);
$me = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$me || intval($me['role_id']) !== 2) {
    header("Location: /login.php");
    exit;
}

// =========================
// HELPERS
// =========================
function safe($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function deriveOutcome($row) {
    $status = $row['status'];
    $dur = intval($row['duration_minutes']);

    if ($dur > 0) return __('Attended');
    if ($status === "rejected") return __('Astrologer Rejected');
    if ($status === "user_rejected") return __('Cancelled by You');
    if ($status === "ended") return __('Missed');
    if ($status === "requested") return __('Ringing');

    return __(ucfirst($status));
}

// =========================
// DATE FILTER
// =========================
$from = $_GET['from'] ?? "";
$to   = $_GET['to'] ?? "";

$where_call = "WHERE cs.user_id = ?";
$where_chat = "WHERE ch.user_id = ?";
$params = [$user_id, $user_id];

if ($from != "" && $to != "") {
    $where_call .= " AND DATE(cs.start_time) BETWEEN ? AND ?";
    $where_chat .= " AND DATE(ch.start_time) BETWEEN ? AND ?";
    $params = [$user_id, $from, $to, $user_id, $from, $to];
}

// =========================
// FETCH CALL LOGS (UNION FOR BOTH)
// =========================
$stmt = $conn->prepare("
    SELECT * FROM (
        SELECT cs.id, cs.status, cs.start_time, cs.end_time, cs.duration_minutes, u.astrologer_name, 'Call' AS session_type
        FROM call_sessions cs
        JOIN users u ON u.id = cs.astrologer_id
        $where_call
        
        UNION ALL
        
        SELECT ch.id, ch.status, ch.start_time, ch.end_time, ch.duration_minutes, u.astrologer_name, 'Chat' AS session_type
        FROM chat_sessions ch
        JOIN users u ON u.id = ch.astrologer_id
        $where_chat
    ) AS combined_history
    ORDER BY start_time DESC
");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= __('Call History - Fortune Parth') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* DESKTOP STYLING */
@media (min-width: 1024px) {
    body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; transition: background 0.3s ease; }
    .desktop-container { max-width: 80rem; margin: 0 auto; padding: 0 3rem; }
    .call-card {
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }
    .call-card:hover {
        border-color: #000;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.02);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-list { animation: fadeInUp 0.5s ease-out forwards; }
}

/* Status Colors */
.status-attended { color: #059669; background: #ecfdf5; }
.status-missed { color: #dc2626; background: #fef2f2; }
.status-cancelled { color: #d97706; background: #fffbeb; }
.status-default { color: #4b5563; background: #f3f4f6; }

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
body.dark-theme .bg-white, body.dark-theme .call-card { background: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
body.dark-theme .call-card:hover { border-color: #4b5563 !important; }

/* Text Colors */
body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2 { color: #ffffff !important; }
body.dark-theme .text-gray-700 { color: #d1d5db !important; }
body.dark-theme .text-gray-600, body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
body.dark-theme .text-gray-300 { color: #6b7280 !important; }

/* Inputs & Buttons */
body.dark-theme input[type="date"] { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
body.dark-theme input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }
body.dark-theme button.bg-black { background: #10b981 !important; color: #ffffff !important; border: none !important; }
body.dark-theme button.bg-black:hover { background: #059669 !important; }

/* Status Badge Overrides for Dark Mode */
body.dark-theme .status-attended { background-color: rgba(16, 185, 129, 0.2) !important; color: #34d399 !important; }
body.dark-theme .status-missed { background-color: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; }
body.dark-theme .status-cancelled { background-color: rgba(245, 158, 11, 0.2) !important; color: #fbbf24 !important; }
body.dark-theme .status-default { background-color: #374151 !important; color: #d1d5db !important; }

/* Mobile Specific Status Colors */
body.dark-theme .text-green-600 { color: #34d399 !important; }
body.dark-theme .text-red-600 { color: #f87171 !important; }
body.dark-theme .text-amber-600 { color: #fbbf24 !important; }
</style>
</head>
<body class="bg-gray-50 lg:bg-white">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="block lg:hidden">
    <?php include '../user/mobile_header.php'; ?>
    <div class="max-w-lg mx-auto p-4 mt-3">
        <h2 class="text-xl font-bold mb-3"><?= __('My Call History') ?></h2>
        
        <form method="GET" class="bg-white p-3 rounded-lg shadow mb-4">
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="text-[11px] font-semibold block mb-1"><?= __('From') ?></label>
                    <input type="date" name="from" value="<?= safe($from) ?>" class="w-full p-1.5 border rounded text-sm">
                </div>
                <div class="flex-1">
                    <label class="text-[11px] font-semibold block mb-1"><?= __('To') ?></label>
                    <input type="date" name="to" value="<?= safe($to) ?>" class="w-full p-1.5 border rounded text-sm">
                </div>
                <div>
                    <button class="bg-black text-white px-3 py-[9px] rounded text-sm font-semibold whitespace-nowrap" style="margin-top:18px;"><?= __('Apply') ?></button>
                </div>
            </div>
        </form>

        <div class="space-y-3">
            <?php if (!$rows): ?>
                <p class="text-center text-gray-500 bg-white p-4 rounded-lg shadow"><?= __('No call records found.') ?></p>
            <?php else: ?>
                <?php foreach ($rows as $r): 
                    $out = deriveOutcome($r);
                    $color = ($out === __('Attended')) ? "text-green-600" : (($out === __('Missed')) ? "text-red-600" : (($out === __('Cancelled by You')) ? "text-amber-600" : "text-gray-700"));
                    $icon = $r['session_type'] === 'Chat' ? '<i class="fas fa-comment-dots text-blue-500"></i>' : '<i class="fas fa-phone-alt text-emerald-500"></i>';
                ?>
                <div class="bg-white rounded-lg shadow p-3">
                    <div class="text-md font-semibold flex items-center gap-2">
                        <?= $icon ?> <?= safe($r['astrologer_name']) ?>
                    </div>
                    <div class="text-sm font-semibold <?= $color ?>"><?= safe($out) ?></div>
                    <div class="text-sm text-gray-600 mt-1"><?= $r['start_time'] ? date('d M Y, h:i A', strtotime($r['start_time'])) : '-' ?></div>
                    <div class="text-sm text-gray-700 mt-1"><?= __('Duration:') ?> <?= intval($r['duration_minutes']) ?> <?= __('min') ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div style="height:90px;"></div>
    </div>
    <?php include '../user/footer_user.php'; ?>
</div>

<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>

    <main class="py-16">
        <div class="desktop-container">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6 animate-list">
                <div>
                    <h1 class="text-5xl font-black text-gray-900 mb-2"><?= __('Call') ?> <span class="text-gray-400"><?= __('Logs.') ?></span></h1>
                    <p class="text-gray-500 font-medium italic"><?= __('Review your spiritual consultations and durations.') ?></p>
                </div>

                <form method="GET" class="flex items-center gap-4 bg-gray-50 p-2 rounded-2xl border border-gray-100">
                    <div class="px-4 border-r border-gray-200">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1"><?= __('From Date') ?></label>
                        <input type="date" name="from" value="<?= safe($from) ?>" class="bg-transparent font-bold text-sm focus:outline-none">
                    </div>
                    <div class="px-4 border-r border-gray-200">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1"><?= __('To Date') ?></label>
                        <input type="date" name="to" value="<?= safe($to) ?>" class="bg-transparent font-bold text-sm focus:outline-none">
                    </div>
                    <button class="bg-black text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-gray-800 transition">
                        <?= __('Filter Results') ?>
                    </button>
                    <?php if($from || $to): ?>
                        <a href="call_history.php" class="pr-4 text-xs font-bold text-gray-400 hover:text-red-500 transition"><?= __('Clear') ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-4 animate-list" style="animation-delay: 0.1s;">
                <?php if (!$rows): ?>
                    <div class="text-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200">
                        <p class="text-xl text-gray-400 font-bold"><?= __('No conversation records found for this period.') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rows as $r): 
                        $out = deriveOutcome($r);
                        $statusStyle = "status-default";
                        if ($out === __('Attended')) $statusStyle = "status-attended";
                        if ($out === __('Missed')) $statusStyle = "status-missed";
                        if ($out === __('Cancelled by You')) $statusStyle = "status-cancelled";
                        
                        $isChat = $r['session_type'] === 'Chat';
                    ?>
                    <div class="call-card p-6 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                                <?php if($isChat): ?>
                                    <i class="fas fa-comment-dots text-blue-500 text-xl"></i>
                                <?php else: ?>
                                    <i class="fas fa-phone-alt text-emerald-500 text-xl"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <?= safe($r['astrologer_name']) ?>
                                    <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded uppercase tracking-widest"><?= $r['session_type'] ?></span>
                                </h3>
                                <p class="text-sm text-gray-400 font-medium uppercase tracking-tighter">
                                    <?= $r['start_time'] ? date('l, d M Y — h:i A', strtotime($r['start_time'])) : '-' ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-12 text-right">
                            <div>
                                <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-1"><?= __('Duration') ?></p>
                                <p class="text-lg font-black text-gray-900"><?= intval($r['duration_minutes']) ?> <span class="text-sm font-normal text-gray-400"><?= __('min') ?></span></p>
                            </div>
                            <div class="min-w-[140px]">
                                <span class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest <?= $statusStyle ?>">
                                    <?= safe($out) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

</body>
</html>