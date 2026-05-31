<?php
session_start(); 
require '../db.php'; 
date_default_timezone_set('Asia/Kolkata');

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Earnings Report' => 'कमाई रिपोर्ट',
            'Filter Reports' => 'रिपोर्ट फ़िल्टर करें',
            'Date Range' => 'तिथि सीमा',
            'User Search' => 'उपयोगकर्ता खोज',
            'Name or Email...' => 'नाम या ईमेल...',
            'Sort Results' => 'परिणाम क्रमित करें',
            'Latest First' => 'नवीनतम पहले',
            'Highest Earnings' => 'उच्चतम आय',
            'Longest Duration' => 'सबसे लंबी अवधि',
            'Update Results' => 'परिणाम अपडेट करें',
            'Today' => 'आज',
            'Last 7d' => 'पिछले 7 दिन',
            'This Month' => 'इस महीने',
            'Total Calls' => 'कुल कॉल',
            'Minutes' => 'मिनट',
            'Net Earnings' => 'शुद्ध आय',
            'Platform Fee' => 'प्लेटफ़ॉर्म शुल्क',
            'Call Logs' => 'कॉल लॉग्स',
            'Export CSV' => 'CSV निर्यात करें',
            'No records found for this period.' => 'इस अवधि के लिए कोई रिकॉर्ड नहीं मिला।',
            'Guest User' => 'अतिथि उपयोगकर्ता',
            'ID #' => 'आईडी #',
            'MIN SESSION' => 'मिनट का सत्र',
            'Page' => 'पृष्ठ',
            'Previous' => 'पिछला',
            'Next' => 'अगला'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if(!isset($_SESSION['userid']) || $_SESSION['role'] != 1){ 
    header('Location:/login.php'); 
    exit; 
}

$astro_id = (int)$_SESSION['userid'];

/* ---------- Filters ---------- */
$today = date('Y-m-d'); 
$firstMonth = date('Y-m-01');
$from = $_GET['from'] ?? $firstMonth; 
$to = $_GET['to'] ?? $today;
$user_search = trim($_GET['user'] ?? ''); 
$min_duration = (int)($_GET['min_dur'] ?? 0); 
$min_earning = (float)($_GET['min_earn'] ?? 0);
$sort = $_GET['sort'] ?? 'end_desc';

/* ---------- WHERE Logic ---------- */
$where = ["cs.astrologer_id=:aid", "cs.status='ended'", "DATE(cs.end_time) BETWEEN :from_date AND :to_date"];
$params = [":aid" => $astro_id, ":from_date" => $from, ":to_date" => $to];

if($user_search !== ''){ 
    $where[] = "(u.name LIKE :q OR u.email LIKE :q)"; 
    $params[':q'] = "%{$user_search}%"; 
}
if($min_duration > 0){ 
    $where[] = "cs.duration_minutes>=:mind"; 
    $params[':mind'] = $min_duration; 
}
if($min_earning > 0){ 
    $where[] = "(cs.cost-(cs.cost*cs.commission_percent/100))>=:mine"; 
    $params[':mine'] = $min_earning; 
}

/* ---------- Sorting ---------- */
$orderBy = 'cs.end_time DESC';
switch($sort){
  case 'end_asc': $orderBy = 'cs.end_time ASC'; break;
  case 'dur_desc': $orderBy = 'cs.duration_minutes DESC'; break;
  case 'dur_asc': $orderBy = 'cs.duration_minutes ASC'; break;
  case 'earn_desc': $orderBy = '(cs.cost-(cs.cost*cs.commission_percent/100)) DESC'; break;
  case 'earn_asc': $orderBy = '(cs.cost-(cs.cost*cs.commission_percent/100)) ASC'; break;
}

/* ---------- Pagination ---------- */
$perPage = 10; 
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; 
$offset = ($page - 1) * $perPage;

/* ---------- Totals & Aggregations ---------- */
$countSql = "SELECT COUNT(*) FROM call_sessions cs JOIN users u ON u.id=cs.user_id WHERE ".implode(' AND ', $where);
$cst = $conn->prepare($countSql); $cst->execute($params); $totalRecords = (int)$cst->fetchColumn();
$totalPages = (int)ceil($totalRecords / $perPage);

$aggSql = "SELECT COALESCE(SUM(cs.duration_minutes),0) AS sum_minutes,
                COALESCE(SUM(cs.cost),0) AS sum_paid,
                COALESCE(SUM((cs.cost*cs.commission_percent)/100),0) AS sum_comm
         FROM call_sessions cs JOIN users u ON u.id=cs.user_id
         WHERE ".implode(' AND ', $where);
$ast = $conn->prepare($aggSql); $ast->execute($params);
$agg = $ast->fetch(PDO::FETCH_ASSOC);
$totalEarn = (float)$agg['sum_paid'] - (float)$agg['sum_comm'];

/* ---------- Paged List Fetch ---------- */
$baseSelect = "SELECT cs.id, cs.user_id, cs.duration_minutes, cs.cost, cs.price_per_minute, cs.commission_percent, cs.start_time, cs.end_time, u.name AS user_name, u.email AS user_email
               FROM call_sessions cs JOIN users u ON u.id=cs.user_id
               WHERE ".implode(' AND ', $where);
$sql = $baseSelect . " ORDER BY $orderBy LIMIT $offset, $perPage";
$stmt = $conn->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title><?= __('Earnings Report') ?> | 2nd Code</title>
    
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; transition: background 0.3s; }
        .glass-card { background: white; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: background 0.3s; }
        .filter-input { height: 46px; border-radius: 12px; border: 1px solid #e2e8f0; padding: 0 14px; background: #fff; width: 100%; transition: all 0.2s; font-size: 14px; font-weight: 500; }
        .filter-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }
        
        .btn-apply { background: #0f172a; color: white; border-radius: 14px; font-weight: 700; transition: 0.3s; width: 100%; padding: 12px; }
        .btn-apply:hover { transform: translateY(-1px); background: #000; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .chip { background: #f1f5f9; padding: 8px 14px; border-radius: 100px; font-size: 12px; font-weight: 700; color: #64748b; cursor: pointer; border: 1px solid transparent; transition: 0.2s; }
        .chip:hover { background: #e2e8f0; color: #1e293b; }
        
        .kpi-card { padding: 24px; text-align: center; border-radius: 24px; transition: transform 0.3s; }
        .kpi-card:hover { transform: translateY(-5px); }

        @media (max-width: 1024px) { body { padding-bottom: 120px; } }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        
        body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .filter-input { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
        body.dark-theme .filter-input::-webkit-calendar-picker-indicator { filter: invert(1); }
        
        body.dark-theme .btn-apply { background: #374151 !important; border: 1px solid #4b5563 !important; }
        body.dark-theme .btn-apply:hover { background: #4b5563 !important; }

        body.dark-theme .chip { background: #374151 !important; color: #d1d5db !important; border-color: #4b5563 !important; }
        body.dark-theme .chip:hover { background: #4b5563 !important; color: #ffffff !important; }

        body.dark-theme .text-slate-900, body.dark-theme .text-slate-800 { color: #ffffff !important; }
        body.dark-theme .text-slate-600, body.dark-theme .text-slate-500, body.dark-theme .text-slate-400, body.dark-theme .text-slate-300 { color: #d1d5db !important; }

        /* For the list background */
        body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .hover\:bg-slate-50:hover { background-color: #374151 !important; }
        body.dark-theme .bg-slate-100 { background-color: #374151 !important; color: #f9fafb !important; }
        body.dark-theme .divide-slate-50 > :not([hidden]) ~ :not([hidden]) { border-color: #4b5563 !important; }
        body.dark-theme .border-slate-50 { border-color: #4b5563 !important; }

        /* Pagination buttons */
        body.dark-theme .bg-slate-50 { background-color: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme a.bg-white { background-color: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
        body.dark-theme a.bg-white:hover { background-color: #4b5563 !important; }
    </style>
</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<div class="max-w-[1200px] mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <aside class="w-full lg:w-80">
            <div class="glass-card p-6 sticky top-6">
                <div class="flex items-center gap-2 mb-6 text-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    <h2 class="text-lg font-800"><?= __('Filter Reports') ?></h2>
                </div>
                
                <form method="GET" id="filterForm" class="space-y-5">
                    <div class="space-y-1">
                        <label class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Date Range') ?></label>
                        <div class="grid grid-cols-1 gap-2">
                            <input type="date" name="from" value="<?=htmlspecialchars($from, ENT_QUOTES, 'UTF-8')?>" class="filter-input">
                            <input type="date" name="to" value="<?=htmlspecialchars($to, ENT_QUOTES, 'UTF-8')?>" class="filter-input">
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('User Search') ?></label>
                        <input type="text" name="user" placeholder="<?= __('Name or Email...') ?>" value="<?=htmlspecialchars($user_search, ENT_QUOTES, 'UTF-8')?>" class="filter-input">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest"><?= __('Sort Results') ?></label>
                        <select name="sort" class="filter-input">
                            <option value="end_desc" <?=$sort==='end_desc'?'selected':''?>><?= __('Latest First') ?></option>
                            <option value="earn_desc" <?=$sort==='earn_desc'?'selected':''?>><?= __('Highest Earnings') ?></option>
                            <option value="dur_desc" <?=$sort==='dur_desc'?'selected':''?>><?= __('Longest Duration') ?></option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="btn-apply"><?= __('Update Results') ?></button>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-50">
                        <span class="chip" onclick="quickRange('today')"><?= __('Today') ?></span>
                        <span class="chip" onclick="quickRange('7')"><?= __('Last 7d') ?></span>
                        <span class="chip" onclick="quickRange('month')"><?= __('This Month') ?></span>
                    </div>
                </form>
            </div>
        </aside>

        <main class="flex-1 space-y-6">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="glass-card kpi-card">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('Total Calls') ?></div>
                    <div class="text-2xl font-800 text-slate-900"><?= (int)$totalRecords ?></div>
                </div>
                <div class="glass-card kpi-card">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('Minutes') ?></div>
                    <div class="text-2xl font-800 text-slate-900"><?= (int)$agg['sum_minutes'] ?></div>
                </div>
                <div class="glass-card kpi-card bg-indigo-600 border-none">
                    <div class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest"><?= __('Net Earnings') ?></div>
                    <div class="text-2xl font-800 text-white">₹<?= number_format($totalEarn, 0) ?></div>
                </div>
                <div class="glass-card kpi-card bg-slate-900 border-none">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('Platform Fee') ?></div>
                    <div class="text-2xl font-800 text-white">₹<?= number_format($agg['sum_comm'], 0) ?></div>
                </div>
            </div>

            <div class="glass-card overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center bg-white">
                    <h3 class="font-800 text-slate-800"><?= __('Call Logs') ?></h3>
                    <div class="flex gap-4 items-center">
                        <a href="?<?=http_build_query(array_merge($_GET, ['export'=>'detail_csv']))?>" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition"><?= __('Export CSV') ?></a>
                    </div>
                </div>

                <?php if(empty($rows)): ?>
                    <div class="p-20 text-center">
                        <div class="text-slate-300 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-slate-400 font-medium"><?= __('No records found for this period.') ?></p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-slate-50">
                        <?php foreach($rows as $r): 
                            $earned = $r['cost'] - ($r['cost'] * $r['commission_percent'] / 100);
                        ?>
                        <div class="p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 hover:bg-slate-50 transition">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center font-bold text-slate-500 text-lg">
                                    <?= strtoupper(substr($r['user_name']?:'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-800 text-slate-900"><?=htmlspecialchars($r['user_name']?:__('Guest User'), ENT_QUOTES, 'UTF-8')?></div>
                                    <div class="text-[11px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-2">
                                        <span><?= __('ID #') ?><?=$r['id']?></span>
                                        <span class="text-slate-200">|</span>
                                        <span><?=date('d M, h:i A', strtotime($r['end_time']))?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex md:text-right flex-col md:items-end w-full md:w-auto">
                                <div class="text-lg font-800 text-indigo-600">₹<?=number_format($earned, 2)?></div>
                                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-tighter"><?= (int)$r['duration_minutes'] ?> <?= __('MIN SESSION') ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if($totalPages > 1): ?>
                    <div class="px-6 py-4 bg-slate-50 flex justify-between items-center">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase"><?= __('Page') ?> <?=$page?> / <?=$totalPages?></span>
                        <div class="flex gap-2">
                            <?php if($page > 1): ?>
                                <a href="?<?=http_build_query(array_merge($_GET, ['page'=>$page-1]))?>" class="bg-white border border-slate-200 text-slate-600 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-100 transition"><?= __('Previous') ?></a>
                            <?php endif; ?>
                            <?php if($page < $totalPages): ?>
                                <a href="?<?=http_build_query(array_merge($_GET, ['page'=>$page+1]))?>" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100"><?= __('Next') ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<div class="lg:hidden"><?php include '../astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include '../astrologer/web-astrologer-footer.php'; ?></div>

<script>
function quickRange(type){
  const f=document.querySelector('input[name="from"]');
  const t=document.querySelector('input[name="to"]');
  const today=new Date(); const fmt=d=>d.toISOString().slice(0,10);
  if(type==='today'){ const d=fmt(today); f.value=d; t.value=d; }
  else if(type==='7'){ const from=new Date(today.getFullYear(),today.getMonth(),today.getDate()-6); f.value=fmt(from); t.value=fmt(today); }
  else if(type==='month'){ const from=new Date(today.getFullYear(),today.getMonth(),1); f.value=fmt(from); t.value=fmt(today); }
  document.getElementById('filterForm').submit();
}
</script>
</body>
</html>