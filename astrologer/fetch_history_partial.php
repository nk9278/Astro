<?php
// Note: This file is used for both direct include and AJAX fetch
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'No call logs found in your history.' => 'आपके इतिहास में कोई कॉल लॉग नहीं मिला।',
            'Attended' => 'अटेंड किया',
            'Calling...' => 'कॉलिंग...',
            'Missed Call' => 'मिस्ड कॉल',
            'No Answer' => 'कोई जवाब नहीं',
            'Duration' => 'अवधि',
            'min' => 'मिनट'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$astro_id = intval($_SESSION['userid']);

// Fetch latest 25 call sessions
$stmt = $conn->prepare("
    SELECT cs.id, cs.status, cs.start_time, cs.duration_minutes, u.name AS user_name
    FROM call_sessions cs
    JOIN users u ON u.id = cs.user_id
    WHERE cs.astrologer_id = ?
    ORDER BY cs.id DESC LIMIT 25
");
$stmt->execute([$astro_id]);
$listRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$listRows): ?>
    <!-- Notice: bg-white and border will inherit dark-theme overrides from parent call_history.php -->
    <div class="bg-white p-12 text-center rounded-[32px] border border-gray-100">
        <p class="text-gray-400 font-medium"><?= __('No call logs found in your history.') ?></p>
    </div>
<?php endif;

foreach ($listRows as $r): 
    $status = $r['status'];
    $isRinging = ($status === 'requested');
    $duration = intval($r['duration_minutes'] ?? 0);
    
    // --- Logic for Status Badges & Labels ---
    $status_label = __('Attended');
    $status_bg = "bg-emerald-50 text-emerald-700";

    if ($isRinging) {
        $status_label = __('Calling...');
        $status_bg = "bg-blue-50 text-blue-700"; 
    } elseif ($duration === 0) {
        // Identify Missed Calls
        if ($status === 'user_rejected' || $status === 'ended' || $status === 'rejected') {
            $status_label = __('Missed Call');
            $status_bg = "bg-red-50 text-red-700";
        } else {
            $status_label = __('No Answer');
            $status_bg = "bg-gray-100 text-gray-500";
        }
    }
?>
    <div class="bg-white p-5 lg:p-7 rounded-[32px] shadow-sm border border-gray-50 flex flex-col">
        
        <div class="flex justify-between items-center">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-lg font-bold tracking-tight text-gray-900"><?= htmlspecialchars($r['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-lg <?= $status_bg ?>">
                        <?= $status_label ?>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= date('d M Y, h:i A', strtotime($r['start_time'])) ?>
                </div>
            </div>

            <div class="text-right min-w-[70px]">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"><?= __('Duration') ?></div>
                <div class="text-xl font-bold text-gray-900">
                    <?= $duration ?> <span class="text-xs font-medium text-gray-400"><?= __('min') ?></span>
                </div>
            </div>
        </div>
        
    </div>
<?php endforeach; ?>