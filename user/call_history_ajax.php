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
            'Unauthorized' => 'अनधिकृत',
            'Attended' => 'उपस्थित',
            'Cancelled' => 'रद्द',
            'Missed' => 'छूट गया',
            'Ringing' => 'बज रहा है',
            'No history available.' => 'कोई इतिहास उपलब्ध नहीं है।',
            'Astrologer:' => 'ज्योतिषी:',
            'Status:' => 'स्थिति:'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$user_id = intval($_GET['uid'] ?? 0);

// Security
if (!isset($_SESSION['userid']) || $_SESSION['userid'] != $user_id) {
    die("<div style='padding:15px;color:red;'>" . __('Unauthorized') . "</div>");
}

$stmt = $conn->prepare("
    SELECT * FROM (
        SELECT cs.id, cs.status, cs.duration_minutes, cs.start_time, a.astrologer_name, 'Call' as session_type
        FROM call_sessions cs
        JOIN users a ON a.id = cs.astrologer_id
        WHERE cs.user_id = ?
        
        UNION ALL
        
        SELECT ch.id, ch.status, ch.duration_minutes, ch.start_time, a.astrologer_name, 'Chat' as session_type
        FROM chat_sessions ch
        JOIN users a ON a.id = ch.astrologer_id
        WHERE ch.user_id = ?
    ) AS combined_history
    ORDER BY start_time DESC
    LIMIT 15
");
$stmt->execute([$user_id, $user_id]);
$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statusText($row) {
    if ($row['duration_minutes'] > 0) return "<span class='status-attended text-emerald-600 font-bold'>" . __('Attended') . "</span>";
    if ($row['status'] === 'user_rejected') return "<span class='status-cancelled text-amber-600 font-bold'>" . __('Cancelled') . "</span>";
    if ($row['status'] === 'rejected' || $row['status'] === 'ended') return "<span class='status-missed text-rose-600 font-bold'>" . __('Missed') . "</span>";
    return "<span class='status-ongoing text-blue-600 font-bold'>" . __('Ringing') . "</span>";
}

if (!$calls) {
    echo "<div style='padding:15px;color:#777;text-align:center;'>" . __('No history available.') . "</div>";
    exit;
}

foreach ($calls as $c) {
    $icon = $c['session_type'] === 'Chat' ? '<i class="fas fa-comment-dots text-blue-500 mr-1"></i>' : '<i class="fas fa-phone-alt text-emerald-500 mr-1"></i>';
    echo "
    <div class='history-item p-3 border-b border-gray-100 dark:border-gray-700 flex flex-col gap-1'>
        <div class='flex justify-between items-center'>
            <span class='font-bold text-gray-800 dark:text-gray-200 text-sm'>{$icon} " . htmlspecialchars($c['astrologer_name'], ENT_QUOTES, 'UTF-8') . "</span>
            <span class='text-xs'>" . statusText($c) . "</span>
        </div>
        <div class='text-xs text-gray-500 dark:text-gray-400'>".date('d M Y, h:i A', strtotime($c['start_time']))."</div>
    </div>
    ";
}
?>