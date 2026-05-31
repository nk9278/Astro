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
            'Update KYC Details' => 'केवाईसी विवरण अपडेट करें',
            'Bank & KYC Verification' => 'बैंक और केवाईसी सत्यापन',
            'Phone Number *' => 'फ़ोन नंबर *',
            'Aadhaar Card Number *' => 'आधार कार्ड नंबर *',
            'Upload Aadhaar (JPG/PNG/PDF) *' => 'आधार अपलोड करें (JPG/PNG/PDF) *',
            'Current Aadhaar Uploaded' => 'वर्तमान आधार अपलोड किया गया',
            'PAN Card Number *' => 'पैन कार्ड नंबर *',
            'Upload PAN (JPG/PNG/PDF) *' => 'पैन अपलोड करें (JPG/PNG/PDF) *',
            'Current PAN Uploaded' => 'वर्तमान पैन अपलोड किया गया',
            'Bank Account Number *' => 'बैंक खाता संख्या *',
            'Bank Name *' => 'बैंक का नाम *',
            'Bank IFSC Code *' => 'बैंक IFSC कोड *',
            'Bank Branch *' => 'बैंक शाखा *',
            'UPI ID *' => 'UPI आईडी *',
            'Submit Documents & Details for Approval' => 'अनुमोदन के लिए दस्तावेज़ और विवरण सबमिट करें',
            'All text fields are required.' => 'सभी टेक्स्ट फ़ील्ड अनिवार्य हैं।',
            'Invalid Aadhaar file format or size (Max 5MB).' => 'अमान्य आधार फ़ाइल प्रारूप या आकार (अधिकतम 5MB)।',
            'Invalid PAN file format or size (Max 5MB).' => 'अमान्य पैन फ़ाइल प्रारूप या आकार (अधिकतम 5MB)।',
            'KYC Details & Documents updated successfully. Awaiting Admin Approval.' => 'केवाईसी विवरण और दस्तावेज़ सफलतापूर्वक अपडेट किए गए। एडमिन की स्वीकृति की प्रतीक्षा है।',
            'Database error. Please try again.' => 'डेटाबेस त्रुटि। कृपया पुनः प्रयास करें।',
            'Status:' => 'स्थिति:',
            'pending' => 'लंबित',
            'approved' => 'स्वीकृत',
            'rejected' => 'अस्वीकृत'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header("Location: /login.php");
    exit;
}

$message = '';
$message_type = '';

// Folder configuration
$target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/KYC/';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Fetch existing data including new bank fields
$stmt = $conn->prepare("SELECT phone, aadhaar_card, pan_card, bank_account, bank_name, bank_ifsc, bank_branch, upi_id, aadhaar_file, pan_file, approval_status FROM users WHERE id=?");
$stmt->execute([$_SESSION['userid']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // High-security sanitization
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $aadhaar = htmlspecialchars(trim($_POST['aadhaar'] ?? ''), ENT_QUOTES, 'UTF-8');
    $pan = htmlspecialchars(trim($_POST['pan'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // New Bank Fields
    $bank_account = htmlspecialchars(trim($_POST['bank_account'] ?? ''), ENT_QUOTES, 'UTF-8');
    $bank_name = htmlspecialchars(trim($_POST['bank_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $bank_ifsc = htmlspecialchars(trim($_POST['bank_ifsc'] ?? ''), ENT_QUOTES, 'UTF-8');
    $bank_branch = htmlspecialchars(trim($_POST['bank_branch'] ?? ''), ENT_QUOTES, 'UTF-8');
    $upi = htmlspecialchars(trim($_POST['upi'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($phone) || empty($aadhaar) || empty($pan) || empty($bank_account) || empty($bank_name) || empty($bank_ifsc) || empty($bank_branch) || empty($upi)) {
        $message = __('All text fields are required.');
        $message_type = "error";
    } else {
        $aadhaar_file_name = $user['aadhaar_file'];
        $pan_file_name = $user['pan_file'];
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];

        // Handle Aadhaar File Upload
        if (isset($_FILES['aadhaar_file']) && $_FILES['aadhaar_file']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['aadhaar_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_types) && $_FILES['aadhaar_file']['size'] < 5000000) { // 5MB limit
                $aadhaar_file_name = "aadhaar_" . $_SESSION['userid'] . "_" . time() . "." . $ext;
                move_uploaded_file($_FILES['aadhaar_file']['tmp_name'], $target_dir . $aadhaar_file_name);
            } else {
                $message = __('Invalid Aadhaar file format or size (Max 5MB).');
                $message_type = "error";
            }
        }

        // Handle PAN File Upload
        if (isset($_FILES['pan_file']) && $_FILES['pan_file']['error'] == 0 && $message_type !== "error") {
            $ext = strtolower(pathinfo($_FILES['pan_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_types) && $_FILES['pan_file']['size'] < 5000000) {
                $pan_file_name = "pan_" . $_SESSION['userid'] . "_" . time() . "." . $ext;
                move_uploaded_file($_FILES['pan_file']['tmp_name'], $target_dir . $pan_file_name);
            } else {
                $message = __('Invalid PAN file format or size (Max 5MB).');
                $message_type = "error";
            }
        }

        // Proceed if no file upload errors
        if ($message_type !== "error") {
            $update_stmt = $conn->prepare("UPDATE users SET phone=?, aadhaar_card=?, pan_card=?, bank_account=?, bank_name=?, bank_ifsc=?, bank_branch=?, upi_id=?, aadhaar_file=?, pan_file=?, approval_status='pending' WHERE id=?");
            if ($update_stmt->execute([$phone, $aadhaar, $pan, $bank_account, $bank_name, $bank_ifsc, $bank_branch, $upi, $aadhaar_file_name, $pan_file_name, $_SESSION['userid']])) {
                $message = __('KYC Details & Documents updated successfully. Awaiting Admin Approval.');
                $message_type = "success";
                
                // Refresh $user array
                $stmt->execute([$_SESSION['userid']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = __('Database error. Please try again.');
                $message_type = "error";
            }
        }
    }
}

$status_colors = [
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200'
];
$current_status_color = $status_colors[$user['approval_status']] ?? $status_colors['pending'];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content"/>
    <title><?= __('Update KYC Details') ?> | Fortune Path</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { background: #fdfdfd; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
        body.dark-theme .auth-card { background: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2 { color: #f9fafb !important; }
        body.dark-theme .text-gray-700 { color: #d1d5db !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
        
        body.dark-theme .input-field { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
        body.dark-theme .input-field:focus { border-color: #10b981 !important; }
        body.dark-theme .input-field::placeholder { color: #9ca3af !important; }
        
        body.dark-theme .file-upload-box { background-color: #374151 !important; border-color: #4b5563 !important; }
        
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; }
        body.dark-theme .bg-black:hover { background-color: #059669 !important; }

        body.dark-theme .status-badge-dark { background-color: #374151 !important; border-color: #4b5563 !important; color: #f9fafb !important; }
    </style>
</head>
<body class="overflow-x-hidden">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<!-- MOBILE & DESKTOP HEADERS -->
<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/header.php"; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . "/astrologer/web-astrologer-header.php"; ?></div>

<!-- MAIN CONTENT -->
<section class="min-h-screen flex items-center justify-center bg-gray-50 py-10 px-4">
    <div class="w-full max-w-4xl auth-card p-8 md:p-12 rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl border border-gray-100">
        
        <!-- Header Text & Status -->
        <div class="text-center mb-10">
            <div class="inline-block px-4 py-1.5 rounded-full bg-gray-200 text-xs font-bold uppercase tracking-wider mb-4 text-gray-800">✦ <?= __('KYC & Bank Verification') ?></div>
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-6"><?= __('Update KYC Details') ?></h2>
            
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-xl border <?= $current_status_color ?> font-bold text-sm uppercase tracking-wide status-badge-dark">
                <?= __('Status:') ?> <?= __(htmlspecialchars($user['approval_status'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-2xl text-sm font-bold text-center border <?= $message_type === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- KYC Form -->
        <form method="POST" action="" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- General Info -->
            <div class="md:col-span-2">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">1. General Information</h3>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Phone Number *') ?></label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. 9876543210" />
            </div>

            <!-- Aadhaar Section -->
            <div class="md:col-span-2 mt-4">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">2. Identity Proof (Aadhaar)</h3>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Aadhaar Card Number *') ?></label>
                <input type="text" name="aadhaar" value="<?= htmlspecialchars($user['aadhaar_card'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. 1234 5678 9012" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Upload Aadhaar (JPG/PNG/PDF) *') ?></label>
                <input type="file" name="aadhaar_file" accept=".jpg,.jpeg,.png,.pdf" class="file-upload-box w-full px-5 py-3.5 rounded-2xl bg-gray-50 text-sm outline-none border border-dashed border-gray-300 focus:border-black transition-all cursor-pointer" />
                <?php if (!empty($user['aadhaar_file'])): ?>
                    <a href="/assets/KYC/<?= htmlspecialchars($user['aadhaar_file'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="inline-block mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors">
                        ✓ <?= __('Current Aadhaar Uploaded') ?> 👁
                    </a>
                <?php endif; ?>
            </div>

            <!-- PAN Section -->
            <div class="md:col-span-2 mt-4">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">3. Tax Proof (PAN)</h3>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('PAN Card Number *') ?></label>
                <input type="text" name="pan" value="<?= htmlspecialchars($user['pan_card'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. ABCDE1234F" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Upload PAN (JPG/PNG/PDF) *') ?></label>
                <input type="file" name="pan_file" accept=".jpg,.jpeg,.png,.pdf" class="file-upload-box w-full px-5 py-3.5 rounded-2xl bg-gray-50 text-sm outline-none border border-dashed border-gray-300 focus:border-black transition-all cursor-pointer" />
                <?php if (!empty($user['pan_file'])): ?>
                    <a href="/assets/KYC/<?= htmlspecialchars($user['pan_file'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="inline-block mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors">
                        ✓ <?= __('Current PAN Uploaded') ?> 👁
                    </a>
                <?php endif; ?>
            </div>

            <!-- Bank Details Section -->
            <div class="md:col-span-2 mt-4">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">4. Bank & Payout Details</h3>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Bank Name *') ?></label>
                <input type="text" name="bank_name" value="<?= htmlspecialchars($user['bank_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. State Bank of India" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Bank Account Number *') ?></label>
                <input type="text" name="bank_account" value="<?= htmlspecialchars($user['bank_account'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. 000123456789" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Bank IFSC Code *') ?></label>
                <input type="text" name="bank_ifsc" value="<?= htmlspecialchars($user['bank_ifsc'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. SBIN0001234" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Bank Branch *') ?></label>
                <input type="text" name="bank_branch" value="<?= htmlspecialchars($user['bank_branch'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. Main Branch, Lucknow" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('UPI ID *') ?></label>
                <input type="text" name="upi" value="<?= htmlspecialchars($user['upi_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="input-field w-full px-5 py-4 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required placeholder="e.g. yourname@bank" />
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-2 mt-8">
                <button type="submit" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl active:scale-95">
                    <?= __('Submit Documents & Details for Approval') ?>
                </button>
            </div>

        </form>
    </div>
</section>

<!-- MOBILE & DESKTOP FOOTERS -->
<div class="lg:hidden"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/footer_astrologer.php'; ?></div>
<div class="hidden lg:block"><?php include $_SERVER['DOCUMENT_ROOT'] . '/astrologer/web-astrologer-footer.php'; ?></div>

</body>
</html>