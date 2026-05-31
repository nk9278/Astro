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
            'My Wallet' => 'मेरा वॉलेट',
            'My Wallet - Fortune Parth' => 'मेरा वॉलेट - फॉर्च्यून पाथ',
            'Current Balance' => 'वर्तमान बैलेंस',
            '+ Add Money' => '+ पैसे जोड़ें',
            'Recent Transactions' => 'हाल के लेनदेन',
            'Astrologer:' => 'ज्योतिषी:',
            'User:' => 'उपयोगकर्ता:',
            'No recent transactions.' => 'कोई हालिया लेनदेन नहीं।',
            'Total Balance' => 'कुल बैलेंस',
            '+ Add Money to Wallet' => '+ वॉलेट में पैसे जोड़ें',
            'Secure encrypted transactions' => 'सुरक्षित एन्क्रिप्टेड लेनदेन',
            'Status' => 'स्थिति',
            'Active Account' => 'सक्रिय खाता',
            'Transactions' => 'लेनदेन',
            'Recent' => 'हाल की',
            'Activity' => 'गतिविधि',
            'Download Statement' => 'विवरण डाउनलोड करें',
            'No transactions found in your history.' => 'आपके इतिहास में कोई लेनदेन नहीं मिला।',
            'Recharge Wallet' => 'वॉलेट रिचार्ज करें',
            'Enter the amount you wish to add.' => 'वह राशि दर्ज करें जिसे आप जोड़ना चाहते हैं।',
            'Cancel' => 'रद्द करें',
            'Proceed' => 'आगे बढ़ें',
            'Please enter a valid amount.' => 'कृपया एक वैध राशि दर्ज करें।',
            'Processing...' => 'प्रक्रिया हो रही है...',
            'Payment verification failed due to a server error. Check console.' => 'सर्वर त्रुटि के कारण भुगतान सत्यापन विफल रहा। कंसोल की जाँच करें।',
            'Error from server: ' => 'सर्वर से त्रुटि: ',
            'Payment Successful! Amount added to your wallet.' => 'भुगतान सफल! राशि आपके वॉलेट में जोड़ दी गई है।',
            'Payment verification failed: ' => 'भुगतान सत्यापन विफल: ',
            'Payment Failed: ' => 'भुगतान विफल: ',
            'Error: ' => 'त्रुटि: ',
            'SERVER ERROR! The server did not return valid JSON. Check Developer Console for full error.' => 'सर्वर त्रुटि! सर्वर ने मान्य JSON नहीं लौटाया। पूर्ण त्रुटि के लिए डेवलपर कंसोल देखें।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

date_default_timezone_set("Asia/Kolkata");

// Hardened Security: Strict session and role validation
if (empty($_SESSION['userid']) || !isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    session_unset();
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}

$user_id = intval($_SESSION['userid']);

// Safe default initializations
$wallet_balance = 0;
$user_name = 'Fortune Parth User';
$user_email = 'user@fortunepathway.com';
$user_phone = '9999999999';
$transactions = [];

// Secure User Fetch with Exception Handling
try {
    $stmt = $conn->prepare("SELECT wallet_balance, name, email, phone FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $wallet_balance = (float)($user_data['wallet_balance'] ?? 0);
        $user_name = !empty($user_data['name']) ? htmlspecialchars($user_data['name'], ENT_QUOTES, 'UTF-8') : $user_name;
        $user_email = !empty($user_data['email']) ? filter_var($user_data['email'], FILTER_SANITIZE_EMAIL) : $user_email;
        $user_phone = !empty($user_data['phone']) ? preg_replace('/[^0-9]/', '', $user_data['phone']) : $user_phone;
    }
} catch (PDOException $e) {
    error_log("Wallet User Fetch Error: " . $e->getMessage());
}

// Secure Transactions Fetch with Exception Handling
try {
    $stmtTx = $conn->prepare("
        SELECT w.amount, w.type, w.description, w.icon, w.created_at,
               u.name AS other_name,
               u.astrologer_name AS astro_name
        FROM wallet_transactions w
        LEFT JOIN users u ON w.related_user_id = u.id
        WHERE w.user_id = :user_id
        ORDER BY w.created_at DESC
    ");
    $stmtTx->execute([':user_id' => $user_id]);
    $transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Wallet Tx Fetch Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html class="light" lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= __('My Wallet - Fortune Parth') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">

<style>
    body { font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
    @media (min-width: 1024px) {
        .desktop-container { max-width: 80rem; margin: 0 auto; padding: 0 3rem; }
        .glass-card { 
            background: #ffffff; 
            border: 1px solid #f0f0f0; 
            border-radius: 2.5rem; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }
    }
    .transaction-row:last-child { border-bottom: none; }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
    
    /* General Mobile Overrides */
    body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
    body.dark-theme .bg-gray-200 { background-color: #374151 !important; }
    body.dark-theme .text-gray-700, body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #d1d5db !important; }
    body.dark-theme .border-b { border-color: #374151 !important; }
    
    /* Mobile specific colors */
    body.dark-theme .text-green-600 { color: #34d399 !important; }
    body.dark-theme .text-red-600 { color: #f87171 !important; }
    body.dark-theme .text-blue-600 { color: #60a5fa !important; }

    /* Desktop Dark Overrides */
    body.dark-theme .glass-card { background-color: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
    body.dark-theme .bg-white { background-color: #1f2937 !important; color: #f9fafb !important; }
    body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2 { color: #ffffff !important; }
    
    /* Desktop Buttons */
    body.dark-theme button.bg-black { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
    body.dark-theme button.bg-black:hover { background-color: #059669 !important; }
    
    body.dark-theme button.bg-white { background-color: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
    body.dark-theme button.bg-white:hover { background-color: #4b5563 !important; }

    /* Desktop Black Card specific overrides */
    body.dark-theme .bg-black.text-white { background-color: #111827 !important; border-color: #374151 !important; }
    body.dark-theme .bg-black.text-white .bg-white.opacity-5 { opacity: 0.1 !important; background-color: #374151 !important; }

    /* Desktop Transaction Overrides */
    body.dark-theme .hover\:bg-gray-50:hover { background-color: #374151 !important; }
    body.dark-theme .divide-gray-50 > :not([hidden]) ~ :not([hidden]) { border-color: #374151 !important; }
    body.dark-theme .bg-gray-100 { background-color: #374151 !important; color: #f9fafb !important; }
    body.dark-theme .text-gray-800 { color: #f9fafb !important; }
    
    /* Transaction specific colors */
    body.dark-theme .bg-blue-50 { background-color: rgba(59, 130, 246, 0.1) !important; color: #60a5fa !important; }
    body.dark-theme .text-blue-600 { color: #60a5fa !important; }
    body.dark-theme .text-green-500 { color: #34d399 !important; }
    body.dark-theme .text-red-500 { color: #f87171 !important; }

    /* Modal Dark Styles */
    body.dark-theme #addMoneyModal .bg-white { background-color: #1f2937 !important; border: 1px solid #374151 !important; }
    body.dark-theme #addMoneyModal input { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
    body.dark-theme #addMoneyModal input:focus { border-color: #10b981 !important; }
    body.dark-theme #cancelRechargeBtn { background-color: #374151 !important; color: #d1d5db !important; }
    body.dark-theme #cancelRechargeBtn:hover { background-color: #4b5563 !important; color: #ffffff !important; }
</style>
</head>

<body class="bg-gray-50">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="block lg:hidden">
    <?php include '../user/mobile_header.php'; ?>
    <div class="p-4 pt-6">
        <h2 class="text-3xl font-bold"><?= __('My Wallet') ?></h2>
    </div>
    <div class="flex flex-col items-center py-6 px-4">
        <p class="text-sm text-gray-500"><?= __('Current Balance') ?></p>
        <p class="text-5xl font-extrabold">₹<?= number_format($wallet_balance, 2) ?></p>
    </div>
    <div class="px-4 mb-4">
        <button class="w-full h-12 bg-black text-white font-bold rounded-lg rzp-add-money-btn"><?= __('+ Add Money') ?></button>
    </div>
    <h2 class="px-4 pb-3 pt-4 text-xl font-bold"><?= __('Recent Transactions') ?></h2>
    <div class="px-4">
    <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $tx): ?>
        <div class="flex items-center gap-4 border-b py-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-200">
                <span class="material-symbols-outlined text-gray-700"><?= htmlspecialchars($tx['icon'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between">
                    <p class="font-semibold"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($tx['type'], ENT_QUOTES, 'UTF-8'))) ?></p>
                    <p class="font-bold <?= ($tx['amount'] < 0 ? 'text-red-600' : 'text-green-600') ?>">
                        <?= ($tx['amount'] < 0 ? '-' : '+') ?>₹<?= number_format(abs((float)$tx['amount']), 2) ?>
                    </p>
                </div>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($tx['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($tx['astro_name'])): ?>
                    <p class="text-sm text-blue-600 font-semibold"><?= __('Astrologer:') ?> <?= htmlspecialchars($tx['astro_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!empty($tx['other_name'])): ?>
                    <p class="text-sm text-blue-600 font-semibold"><?= __('User:') ?> <?= htmlspecialchars($tx['other_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <p class="text-sm text-gray-400 mt-1"><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-gray-500 py-6"><?= __('No recent transactions.') ?></p>
    <?php endif; ?>
    </div>
    <?php include '../user/footer_user.php'; ?>
</div>

<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>

    <main class="py-16">
        <div class="desktop-container">
            <div class="grid grid-cols-12 gap-12 items-start">
                
                <div class="col-span-5 sticky top-10">
                    <div class="glass-card p-10 bg-black text-white relative overflow-hidden">
                        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white opacity-5 rounded-full"></div>
                        
                        <h2 class="text-xl font-medium opacity-70 mb-2"><?= __('Total Balance') ?></h2>
                        <div class="text-7xl font-black mb-10 tracking-tighter">
                            ₹<?= number_format($wallet_balance, 2) ?>
                        </div>

                        <div class="space-y-4">
                            <button class="w-full py-5 bg-white text-black font-black rounded-2xl text-lg hover:bg-gray-100 transition shadow-xl rzp-add-money-btn">
                                <?= __('+ Add Money to Wallet') ?>
                            </button>
                            <p class="text-xs text-center opacity-50 font-bold uppercase tracking-widest"><?= __('Secure encrypted transactions') ?></p>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="glass-card p-6 bg-white">
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1"><?= __('Status') ?></p>
                            <p class="text-lg font-bold text-green-600 flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> <?= __('Active Account') ?>
                            </p>
                        </div>
                        <div class="glass-card p-6 bg-white text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1"><?= __('Transactions') ?></p>
                            <p class="text-lg font-bold"><?= count($transactions) ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-span-7">
                    <div class="flex items-end justify-between mb-8 px-2">
                        <h2 class="text-4xl font-black text-gray-900"><?= __('Recent') ?> <span class="text-gray-400"><?= __('Activity') ?></span></h2>
                        <button class="text-sm font-bold text-black border-b-2 border-black pb-1"><?= __('Download Statement') ?></button>
                    </div>

                    <div class="glass-card bg-white overflow-hidden">
                        <?php if (!empty($transactions)): ?>
                            <div class="divide-y divide-gray-50">
                                <?php foreach ($transactions as $tx): 
                                    $isDebit = ($tx['amount'] < 0);
                                ?>
                                <div class="p-8 hover:bg-gray-50 transition flex items-center gap-6 transaction-row">
                                    <div class="h-14 w-14 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-800">
                                        <span class="material-symbols-outlined !text-3xl"><?= htmlspecialchars($tx['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-1">
                                            <div>
                                                <p class="text-xl font-bold text-gray-900 leading-tight">
                                                    <?= ucfirst(str_replace('_', ' ', htmlspecialchars($tx['type'], ENT_QUOTES, 'UTF-8'))) ?>
                                                </p>
                                                <p class="text-gray-500 font-medium"><?= htmlspecialchars($tx['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-black <?= ($tx['amount'] < 0 ? 'text-red-500' : 'text-green-500') ?>">
                                                    <?= ($tx['amount'] < 0 ? '-' : '+') ?>₹<?= number_format(abs((float)$tx['amount']), 2) ?>
                                                </p>
                                                <p class="text-xs font-bold text-gray-300 uppercase mt-1">
                                                    <?= date('d M, h:i A', strtotime($tx['created_at'])) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <?php if (!empty($tx['astro_name'])): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold mt-2">
                                                <?= __('Astrologer:') ?> <?= htmlspecialchars($tx['astro_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php elseif (!empty($tx['other_name'])): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold mt-2">
                                                <?= __('User:') ?> <?= htmlspecialchars($tx['other_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-24 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-200 mb-4">history_toggle_off</span>
                                <p class="text-xl text-gray-400 font-medium"><?= __('No transactions found in your history.') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

<div id="addMoneyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden transition-opacity duration-300 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 w-11/12 max-w-sm shadow-2xl">
        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?= __('Recharge Wallet') ?></h3>
        <p class="text-sm text-gray-500 mb-6"><?= __('Enter the amount you wish to add.') ?></p>
        
        <div class="relative mb-6">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-xl font-bold text-gray-400">₹</span>
            <input type="number" id="rechargeAmount" min="1" class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-lg font-bold focus:border-black focus:ring-0 focus:outline-none transition-colors" placeholder="e.g. 500">
        </div>
        
        <div class="flex gap-3">
            <button id="cancelRechargeBtn" class="flex-1 py-3.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition"><?= __('Cancel') ?></button>
            <button id="proceedRechargeBtn" class="flex-1 py-3.5 bg-black text-white font-bold rounded-xl hover:bg-gray-800 transition"><?= __('Proceed') ?></button>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const addMoneyBtns = document.querySelectorAll('.rzp-add-money-btn');
    const modal = document.getElementById('addMoneyModal');
    const cancelBtn = document.getElementById('cancelRechargeBtn');
    const proceedBtn = document.getElementById('proceedRechargeBtn');
    const amountInput = document.getElementById('rechargeAmount');

    // Localization strings
    const txtValidAmt = "<?= __('Please enter a valid amount.') ?>";
    const txtProcessing = "<?= __('Processing...') ?>";
    const txtProceed = "<?= __('Proceed') ?>";
    const txtServerErr = "<?= __('SERVER ERROR! The server did not return valid JSON. Check Developer Console for full error.') ?>";
    const txtErrFromServer = "<?= __('Error from server: ') ?>";
    const txtPaymentSuccess = "<?= __('Payment Successful! Amount added to your wallet.') ?>";
    const txtVerifyFail = "<?= __('Payment verification failed: ') ?>";
    const txtVerifyFailServer = "<?= __('Payment verification failed due to a server error. Check console.') ?>";
    const txtPaymentFail = "<?= __('Payment Failed: ') ?>";
    const txtError = "<?= __('Error: ') ?>";

    // Open Modal
    addMoneyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            amountInput.value = '';
            amountInput.focus();
        });
    });

    // Close Modal
    cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

    // Proceed to Payment
    proceedBtn.addEventListener('click', async () => {
        const amount = parseFloat(amountInput.value);
        if (!amount || amount < 1) {
            alert(txtValidAmt);
            return;
        }

        proceedBtn.innerText = txtProcessing;
        proceedBtn.disabled = true;

        try {
            // 1. Ask backend to create Razorpay Order
            const orderRes = await fetch('create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: amount })
            });
            
            // Fetch raw text to catch hidden PHP errors
            const rawText = await orderRes.text();
            let orderData;
            
            try {
                orderData = JSON.parse(rawText);
            } catch (e) {
                console.error("Raw Server Response:", rawText);
                alert(txtServerErr);
                proceedBtn.innerText = txtProceed;
                proceedBtn.disabled = false;
                return;
            }

            if (!orderData.success) {
                alert(txtErrFromServer + orderData.message);
                proceedBtn.innerText = txtProceed;
                proceedBtn.disabled = false;
                return;
            }

            // 2. Initialize Razorpay Checkout
            const options = {
                key: 'YOUR_RAZORPAY_KEY_ID', // Replaced live key
                amount: orderData.order.amount,
                currency: "INR",
                name: "Fortune Parth",
                description: "Wallet Recharge",
                order_id: orderData.order.id,
                
                // FORCE WEBVIEW INTENT FOR UPI APPS
                webview_intent: true, 

                // Securely injected user details to bypass the contact screen
                prefill: {
                    name: "<?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?>",
                    email: "<?= htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') ?>",
                    contact: "<?= htmlspecialchars($user_phone, ENT_QUOTES, 'UTF-8') ?>"
                },

                // FORCE HIDE THE CONTACT & EMAIL FORM 
                hidden: {
                    contact: true,
                    email: true
                },
                
                handler: async function (response) {
                    // 3. Verify Payment Signature on Backend
                    const verifyRes = await fetch('verify_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature,
                            amount: amount
                        })
                    });
                    
                    const verifyText = await verifyRes.text();
                    let verifyData;
                    
                    try {
                        verifyData = JSON.parse(verifyText);
                    } catch (e) {
                        console.error("Verification Raw Response:", verifyText);
                        alert(txtVerifyFailServer);
                        return;
                    }

                    if (verifyData.success) {
                        alert(txtPaymentSuccess);
                        window.location.reload();
                    } else {
                        alert(txtVerifyFail + verifyData.message);
                    }
                },
                theme: { color: "#000000" }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response){
                alert(txtPaymentFail + response.error.description);
            });
            rzp.open();
            
            modal.classList.add('hidden'); // Hide modal once checkout opens
            
        } catch (error) {
            alert(txtError + error.message);
        } finally {
            proceedBtn.innerText = txtProceed;
            proceedBtn.disabled = false;
        }
    });
});
</script>

</body>
</html>