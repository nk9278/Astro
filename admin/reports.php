<?php
session_start();
require '../db.php';

date_default_timezone_set('Asia/Kolkata');

// Auth: only admin
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

$admin_id = (int)$_SESSION['userid'];

// Include Admin Header
include '../admin/admin_header.php';

/* ----------------------------
   Filters
----------------------------- */
$today      = date('Y-m-d');
$firstMonth = date('Y-m-01');

$section = $_GET['section'] ?? 'calls'; // calls | commission | wallet | referrals | inquiries
$from    = $_GET['from'] ?? $firstMonth;
$to      = $_GET['to']   ?? $today;

// Normalize dates
$from_date = date('Y-m-d', strtotime($from));
$to_date   = date('Y-m-d', strtotime($to));

// For export
$export = $_GET['export'] ?? ''; // csv | xlsx

/* ----------------------------
   Export helpers
----------------------------- */
function send_csv_download($filename, $header, $rows) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, $header);
    foreach ($rows as $r) { fputcsv($out, $r); }
    fclose($out);
    exit;
}

function send_excel_fallback_html($filename, $header, $rows) {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1'><tr>";
    foreach ($header as $h) echo "<th>".htmlspecialchars($h)."</th>";
    echo "</tr>";
    foreach ($rows as $r) {
        echo "<tr>";
        foreach ($r as $cell) echo "<td>".htmlspecialchars((string)$cell)."</td>";
        echo "</tr>";
    }
    echo "</table></body></html>";
    exit;
}

function send_xlsx_or_fallback($filenameBase, $header, $rows) {
    $xlsxFilename = $filenameBase.'.xlsx';
    $xlsFilename  = $filenameBase.'.xls';

    if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $col = 1;
        foreach ($header as $h) {
            $sheet->setCellValueByColumnAndRow($col, 1, $h);
            $sheet->getStyleByColumnAndRow($col, 1)->getFont()->setBold(true);
            $col++;
        }
        $rIdx = 2;
        foreach ($rows as $r) {
            $cIdx = 1;
            foreach ($r as $cell) {
                $sheet->setCellValueByColumnAndRow($cIdx, $rIdx, $cell);
                $cIdx++;
            }
            $rIdx++;
        }
        for ($i=1; $i<=count($header); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$xlsxFilename.'"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } else {
        send_excel_fallback_html($xlsFilename, $header, $rows);
    }
}

/* ----------------------------
   Data loaders per section
----------------------------- */
$kpis = [];         
$headers = [];      
$dataRows = [];     
$listRows = [];     
$title = '';        

switch ($section) {
    case 'calls':
        $title = 'Call Reports';
        $sql = "SELECT cs.id, cs.user_id, cs.astrologer_id, cs.duration_minutes, cs.cost, cs.price_per_minute, cs.commission_percent, cs.end_time, u.name AS user_name, u.email AS user_email, a.astrologer_name AS astro_name FROM call_sessions cs JOIN users u ON u.id = cs.user_id JOIN users a ON a.id = cs.astrologer_id WHERE cs.status = 'ended' AND DATE(cs.end_time) BETWEEN :from AND :to ORDER BY cs.end_time DESC";
        $st = $conn->prepare($sql);
        $st->execute([':from'=>$from_date, ':to'=>$to_date]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $totalCalls = 0; $totalMins=0; $totalPaid=0; $totalComm=0; $totalEarn=0;
        foreach ($rows as $r) {
            $totalCalls++;
            $mins  = (int)$r['duration_minutes'];
            $cost  = (float)$r['cost'];
            $cper  = (int)$r['commission_percent'];
            $commA = round($cost * $cper / 100, 2);
            $earn  = round($cost - $commA, 2);

            $totalMins += $mins; $totalPaid += $cost; $totalComm += $commA; $totalEarn += $earn;

            $listRows[] = [
                'id'         => (int)$r['id'],
                'when'       => date('d M Y, h:i A', strtotime($r['end_time'])),
                'user'       => $r['user_name'] ?: $r['user_email'],
                'astro'      => $r['astro_name'],
                'mins'       => $mins,
                'ppm'        => number_format((float)$r['price_per_minute'], 2),
                'paid'       => number_format($cost, 2),
                'commission' => $cper.'% (₹'.number_format($commA,2).')',
                'earned'     => number_format($earn,2),
            ];

            $dataRows[] = [
                $r['id'], $r['user_name'] ?: $r['user_email'], $r['astro_name'], date('Y-m-d', strtotime($r['end_time'])), date('H:i', strtotime($r['end_time'])), $mins, number_format((float)$r['price_per_minute'], 2), number_format($cost, 2), $cper, number_format($commA, 2), number_format($earn, 2),
            ];
        }

        $kpis = [
            'Total Calls'          => (int)$totalCalls,
            'Total Minutes'        => (int)$totalMins,
            'User Paid (₹)'        => '₹'.number_format($totalPaid,2),
            'Commission (₹)'       => '₹'.number_format($totalComm,2),
            'Astro Earned (₹)'     => '₹'.number_format($totalEarn,2),
        ];
        $headers = ['Call ID','User','Astrologer','Date','Time','Duration (min)','PPM (₹)','User Paid (₹)','Comm %','Commission (₹)','Astrologer Earned (₹)'];
    break;

    case 'commission':
        $title = 'Commission Summary (Admin)';
        $sql = "SELECT cs.id, cs.duration_minutes, cs.cost, cs.commission_percent, cs.end_time, u.name AS user_name, u.email AS user_email, a.astrologer_name AS astro_name FROM call_sessions cs JOIN users u ON u.id = cs.user_id JOIN users a ON a.id = cs.astrologer_id WHERE cs.status = 'ended' AND DATE(cs.end_time) BETWEEN :from AND :to ORDER BY cs.end_time DESC";
        $st = $conn->prepare($sql);
        $st->execute([':from'=>$from_date, ':to'=>$to_date]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $totalCalls = 0; $totalPaid=0; $totalComm=0;
        foreach ($rows as $r) {
            $totalCalls++;
            $cost  = (float)$r['cost'];
            $cper  = (int)$r['commission_percent'];
            $commA = round($cost * $cper / 100, 2);
            $totalPaid += $cost; $totalComm += $commA;

            $listRows[] = [
                'date'  => date('d M Y, h:i A', strtotime($r['end_time'])),
                'user'  => $r['user_name'] ?: $r['user_email'],
                'astro' => $r['astro_name'],
                'paid'  => number_format($cost,2),
                'cper'  => $cper.'%',
                'comm'  => number_format($commA,2),
            ];

            $dataRows[] = [
                $r['id'], $r['user_name'] ?: $r['user_email'], $r['astro_name'], date('Y-m-d H:i', strtotime($r['end_time'])), number_format($cost, 2), $cper, number_format($commA, 2),
            ];
        }

        $kpis = [
            'Total Calls'    => (int)$totalCalls,
            'User Paid (₹)'  => '₹'.number_format($totalPaid,2),
            'Commission (₹)' => '₹'.number_format($totalComm,2),
        ];
        $headers = ['Call ID','User','Astrologer','When','User Paid (₹)','Comm %','Commission (₹)'];
    break;

    case 'wallet':
        $title = 'Admin Wallet Transactions';
        $sql = "SELECT w.*, u.name AS user_name, u.astrologer_name AS astro_name FROM wallet_transactions w LEFT JOIN users u ON w.related_user_id = u.id WHERE w.user_id = :aid AND DATE(w.created_at) BETWEEN :from AND :to ORDER BY w.created_at DESC";
        $st = $conn->prepare($sql);
        $st->execute([':aid'=>$admin_id, ':from'=>$from_date, ':to'=>$to_date]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $sumCredit=0; $sumDebit=0;
        foreach ($rows as $tx) {
            $amt = (float)$tx['amount'];
            if ($amt >= 0) $sumCredit += $amt; else $sumDebit += abs($amt);

            $party = !empty($tx['astro_name']) ? ('Astrologer: '.$tx['astro_name']) : (!empty($tx['user_name']) ? ('User: '.$tx['user_name']) : '');

            $listRows[] = [
                'when'   => date('d M Y, h:i A', strtotime($tx['created_at'])),
                'type'   => ucfirst(str_replace('_',' ', $tx['type'])),
                'desc'   => $tx['description'] ?? '',
                'party'  => $party,
                'amount' => ($amt<0 ? '- ' : '+ ').'₹'.number_format(abs($amt),2),
            ];

            $dataRows[] = [
                date('Y-m-d H:i', strtotime($tx['created_at'])), $tx['type'], $tx['description'], $party, $amt
            ];
        }

        $stb = $conn->prepare("SELECT commission_balance FROM users WHERE id=? LIMIT 1");
        $stb->execute([$admin_id]);
        $balance = (float)$stb->fetchColumn();

        $kpis = [
            'Credits (₹)' => '₹'.number_format($sumCredit,2),
            'Debits (₹)'  => '₹'.number_format($sumDebit,2),
            'Balance (₹)' => '₹'.number_format($balance,2),
        ];
        $headers = ['When','Type','Description','Related','Amount'];
    break;

    case 'referrals':
        $title = 'Referral Signups & Bonuses';
        $st = $conn->prepare("SELECT u.id AS new_user_id, u.email AS new_user_email, u.created_at AS signup_date, u.referred_by AS referrer_id FROM users u WHERE u.referred_by IS NOT NULL AND DATE(u.created_at) BETWEEN :from AND :to ORDER BY u.created_at DESC");
        $st->execute([':from'=>$from_date, ':to'=>$to_date]);
        $users = $st->fetchAll(PDO::FETCH_ASSOC);

        $wtStmt = $conn->prepare("SELECT id, user_id, amount, description, created_at FROM wallet_transactions WHERE user_id = ? AND type = 'referral_bonus' AND created_at BETWEEN DATE_SUB(?, INTERVAL 10 MINUTE) AND DATE_ADD(?, INTERVAL 24 HOUR) ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) ASC LIMIT 1");

        $countSignups=0; $sumBonus=0.0;
        foreach ($users as $u) {
            $countSignups++;
            $refEmail = null;
            if (!empty($u['referrer_id'])) {
                $q = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
                $q->execute([$u['referrer_id']]);
                $refRow = $q->fetch(PDO::FETCH_ASSOC);
                $refEmail = $refRow['email'] ?? null;
            }

            $bonus_amount = null; $bonus_date = null;
            if (!empty($u['referrer_id'])) {
                $wtStmt->execute([$u['referrer_id'], $u['signup_date'], $u['signup_date'], $u['signup_date']]);
                $tx = $wtStmt->fetch(PDO::FETCH_ASSOC);
                if ($tx) {
                    $bonus_amount = (float)$tx['amount'];
                    $bonus_date   = $tx['created_at'];
                    $sumBonus    += (float)$tx['amount'];
                }
            }

            $listRows[] = [
                'new_email' => $u['new_user_email'],
                'ref_email' => $refEmail ?: '—',
                'signup'    => date('d M Y, h:i A', strtotime($u['signup_date'])),
                'bonus'     => $bonus_amount !== null ? '₹'.number_format($bonus_amount,2) : '—',
                'bonus_on'  => $bonus_date ? date('d M Y, h:i A', strtotime($bonus_date)) : '—',
            ];

            $dataRows[] = [
                $u['new_user_email'], $refEmail ?: '', date('Y-m-d H:i', strtotime($u['signup_date'])), $bonus_amount !== null ? number_format($bonus_amount,2) : '', $bonus_date ? date('Y-m-d H:i', strtotime($bonus_date)) : ''
            ];
        }

        $kpis = [
            'Signups'       => (int)$countSignups,
            'Bonus Total (₹)'=> '₹'.number_format($sumBonus,2),
        ];
        $headers = ['New User Email','Referrer Email','Signup Date','Bonus Amount (₹)','Bonus Credited On'];
    break;

    case 'inquiries':
    default:
        $title = 'Product Inquiries';
        $sql = "SELECT pi.id, pi.message, pi.created_at, u.email AS user_email, p.name AS product_name, p.price AS product_price FROM product_inquiries pi JOIN users u ON pi.user_id = u.id JOIN products p ON pi.product_id = p.id WHERE DATE(pi.created_at) BETWEEN :from AND :to ORDER BY pi.created_at DESC";
        $st = $conn->prepare($sql);
        $st->execute([':from'=>$from_date, ':to'=>$to_date]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $countInq = 0;
        foreach ($rows as $r) {
            $countInq++;
            $listRows[] = [
                'user'   => $r['user_email'],
                'prod'   => $r['product_name'],
                'price'  => number_format((float)$r['product_price'],2),
                'msg'    => $r['message'],
                'when'   => date('d M Y, h:i A', strtotime($r['created_at'])),
            ];

            $dataRows[] = [
                $r['user_email'], $r['product_name'], number_format((float)$r['product_price'],2), $r['message'], date('Y-m-d H:i', strtotime($r['created_at'])),
            ];
        }

        $kpis = [
            'Inquiries' => (int)$countInq,
        ];
        $headers = ['User Email','Product','Price (₹)','Message','Date'];
    break;
}

/* ----------------------------
   Handle Export
----------------------------- */
if ($export === 'csv') {
    $file = "report_{$section}_{$from_date}_to_{$to_date}.csv";
    send_csv_download($file, $headers, $dataRows);
}
if ($export === 'xlsx') {
    $fileBase = "report_{$section}_{$from_date}_to_{$to_date}";
    send_xlsx_or_fallback($fileBase, $headers, $dataRows);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    /* Mobile Responsive Table (Card View) */
    @media (max-width: 1024px) {
        table thead { display: none; }
        table, tbody, tr, td { display: block; width: 100%; }
        tr {
            background: #ffffff; border: 1px solid #f3f4f6; border-radius: 16px;
            padding: 16px; margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        td {
            border: none !important; padding: 8px 0 !important; text-align: left !important;
            display: flex; flex-direction: column; gap: 4px; align-items: flex-start !important;
            border-bottom: 1px dashed #e5e7eb !important;
        }
        td:last-child { border-bottom: none !important; }
        td::before {
            content: attr(data-label); font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em; margin-bottom: 2px;
        }
    }

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme tr { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme td[data-label] { border-bottom-color: #4b5563 !important; }
    
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-800 { color: #f3f4f6 !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-600 { color: #9ca3af !important; }
    body.dark-theme .text-gray-500 { color: #6b7280 !important; }
    
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme input[type="date"], body.dark-theme select { background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
    body.dark-theme input:focus, body.dark-theme select:focus { border-color: #ef4444 !important; }
    body.dark-theme ::-webkit-calendar-picker-indicator { filter: invert(1); }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 mt-8">
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">System Reports</h1>
            <p class="text-gray-500 font-medium mt-1">Generate analytics for calls, commissions, wallets, and referrals.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl shadow-sm border border-gray-200 mb-8">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 items-end" id="filterForm">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">From</label>
                <input type="date" name="from" value="<?= htmlspecialchars($from_date) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">To</label>
                <input type="date" name="to" value="<?= htmlspecialchars($to_date) ?>" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Report Section</label>
                <select name="section" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-bold text-gray-900">
                    <option value="calls"      <?= $section==='calls'?'selected':'' ?>>Call Reports</option>
                    <option value="commission" <?= $section==='commission'?'selected':'' ?>>Commission Summary</option>
                    <option value="wallet"     <?= $section==='wallet'?'selected':'' ?>>Admin Wallet</option>
                    <option value="referrals"  <?= $section==='referrals'?'selected':'' ?>>Referrals & Bonuses</option>
                    <option value="inquiries"  <?= $section==='inquiries'?'selected':'' ?>>Product Inquiries</option>
                </select>
            </div>

            <div class="sm:col-span-3 lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="flex-1 bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md active:scale-95">Apply</button>
                <a href="/admin/reports.php" class="flex-1 bg-gray-100 text-gray-700 border border-gray-200 px-6 py-3 rounded-xl font-bold text-center hover:bg-gray-200 transition-colors shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Reset</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
        <?php if (!empty($kpis)): ?>
            <?php foreach ($kpis as $k=>$v): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)]">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 line-clamp-1"><?= htmlspecialchars($k) ?></p>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 truncate"><?= htmlspecialchars($v) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full bg-white border border-gray-200 border-dashed rounded-2xl p-6 text-center shadow-sm">
                <p class="text-sm font-bold text-gray-500">No KPI data for selected filters.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-1 sm:p-0 overflow-hidden mb-6">
        
        <!-- Table Header & Export Buttons -->
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h3 class="text-xl font-black text-gray-900"><?= htmlspecialchars($title) ?></h3>
            
            <div class="flex gap-3">
                <?php
                  $qs = $_GET; 
                  $qs['export']  = 'csv';  $dlCsv  = '/admin/reports.php?'.http_build_query($qs);
                  $qs['export']  = 'xlsx'; $dlXlsx = '/admin/reports.php?'.http_build_query($qs);
                ?>
                <a href="<?= htmlspecialchars($dlCsv) ?>" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-black transition-colors shadow-sm active:scale-95 flex items-center gap-1.5 dark:bg-gray-700 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> CSV
                </a>
                <a href="<?= htmlspecialchars($dlXlsx) ?>" class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl text-xs font-bold hover:bg-emerald-100 transition-colors shadow-sm active:scale-95 flex items-center gap-1.5 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <?php if (empty($listRows)): ?>
                <div class="p-12 text-center">
                    <p class="text-sm font-bold text-gray-500">No data found for the selected dates and section.</p>
                </div>
            <?php else: ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <?php if ($section === 'calls'): ?>
                            <tr>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">When</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">User</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">Astrologer</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">Duration</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">PPM</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">User Paid</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">Commission</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-[10px]">Astro Earned</th>
                            </tr>
                        <?php elseif ($section === 'commission'): ?>
                            <tr>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">When</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">User</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Astrologer</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">User Paid</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Comm %</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Commission (₹)</th>
                            </tr>
                        <?php elseif ($section === 'wallet'): ?>
                            <tr>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">When</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Type</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Description</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Related</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Amount</th>
                            </tr>
                        <?php elseif ($section === 'referrals'): ?>
                            <tr>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">New User Email</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Referrer Email</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Signup Date</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Bonus (₹)</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Credited On</th>
                            </tr>
                        <?php else: /* inquiries */ ?>
                            <tr>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">User Email</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Product</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Price (₹)</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Message</th>
                                <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Date</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php foreach ($listRows as $r): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <?php if ($section === 'calls'): ?>
                                    <td data-label="When" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['when']) ?></td>
                                    <td data-label="User" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['user']) ?></td>
                                    <td data-label="Astrologer" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['astro']) ?></td>
                                    <td data-label="Duration" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm"><?= htmlspecialchars($r['mins']) ?>m</td>
                                    <td data-label="PPM" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm">₹<?= htmlspecialchars($r['ppm']) ?></td>
                                    <td data-label="User Paid" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm">₹<?= htmlspecialchars($r['paid']) ?></td>
                                    <td data-label="Commission" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['commission']) ?></td>
                                    <td data-label="Astro Earned" class="p-4 border-b border-gray-100 font-black text-emerald-600 text-base dark:text-emerald-400">₹<?= htmlspecialchars($r['earned']) ?></td>
                                
                                <?php elseif ($section === 'commission'): ?>
                                    <td data-label="When" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['date']) ?></td>
                                    <td data-label="User" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['user']) ?></td>
                                    <td data-label="Astrologer" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['astro']) ?></td>
                                    <td data-label="User Paid" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm">₹<?= htmlspecialchars($r['paid']) ?></td>
                                    <td data-label="Comm %" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm"><?= htmlspecialchars($r['cper']) ?></td>
                                    <td data-label="Commission" class="p-4 border-b border-gray-100 font-black text-emerald-600 text-base dark:text-emerald-400">₹<?= htmlspecialchars($r['comm']) ?></td>
                                
                                <?php elseif ($section === 'wallet'): ?>
                                    <td data-label="When" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['when']) ?></td>
                                    <td data-label="Type" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['type']) ?></td>
                                    <td data-label="Description" class="p-4 border-b border-gray-100 text-sm text-gray-600"><?= htmlspecialchars($r['desc']) ?></td>
                                    <td data-label="Related" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm"><?= htmlspecialchars($r['party']) ?></td>
                                    <td data-label="Amount" class="p-4 border-b border-gray-100 font-black text-base <?= strpos($r['amount'],'- ')===0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' ?>"><?= htmlspecialchars($r['amount']) ?></td>
                                
                                <?php elseif ($section === 'referrals'): ?>
                                    <td data-label="New User Email" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['new_email']) ?></td>
                                    <td data-label="Referrer Email" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm"><?= htmlspecialchars($r['ref_email']) ?></td>
                                    <td data-label="Signup Date" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['signup']) ?></td>
                                    <td data-label="Bonus Amount" class="p-4 border-b border-gray-100 font-black text-emerald-600 text-base dark:text-emerald-400"><?= htmlspecialchars($r['bonus']) ?></td>
                                    <td data-label="Bonus Credited On" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['bonus_on']) ?></td>
                                
                                <?php else: /* inquiries */ ?>
                                    <td data-label="User Email" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-sm"><?= htmlspecialchars($r['user']) ?></td>
                                    <td data-label="Product" class="p-4 border-b border-gray-100 font-semibold text-gray-700 text-sm"><?= htmlspecialchars($r['prod']) ?></td>
                                    <td data-label="Price" class="p-4 border-b border-gray-100 font-black text-gray-900 text-sm">₹<?= htmlspecialchars($r['price']) ?></td>
                                    <td data-label="Message" class="p-4 border-b border-gray-100 text-sm text-gray-600 max-w-[300px]"><?= nl2br(htmlspecialchars($r['msg'])) ?></td>
                                    <td data-label="Date" class="p-4 border-b border-gray-100 text-sm font-medium text-gray-600"><?= htmlspecialchars($r['when']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>