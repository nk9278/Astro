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
            'Complete the Set' => 'सेट पूरा करें',
            'Buy Now' => 'अभी खरीदें',
            'Home' => 'होम',
            'Product Detail' => 'उत्पाद विवरण',
            'Login to Buy' => 'खरीदने के लिए लॉग इन करें',
            'Related' => 'संबंधित',
            'Items.' => 'सामग्री।',
            'Checkout Details' => 'चेकआउट विवरण',
            'Full Name' => 'पूरा नाम',
            'Phone Number' => 'फ़ोन नंबर',
            'Pincode' => 'पिनकोड',
            'Town/City' => 'शहर/कस्बा',
            'Flat, House no., Building, Apartment' => 'फ्लैट, मकान नंबर, बिल्डिंग, अपार्टमेंट',
            'Area, Street, Sector, Village' => 'क्षेत्र, गली, सेक्टर, गांव',
            'Landmark (Optional)' => 'सीमाचिह्न / लैंडमार्क (वैकल्पिक)',
            'e.g. near Apollo Hospital' => 'उदा. अपोलो अस्पताल के पास',
            'Pay Securely' => 'सुरक्षित भुगतान करें',
            'Processing...' => 'प्रोसेस हो रहा है...'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: /products/list.php');
    exit;
}

// Fetch product details
$stmt = $conn->prepare("SELECT id, name, description, price, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: /products/list.php');
    exit;
}

// Fetch related products
$stmtRelated = $conn->prepare("SELECT id, name, price, image FROM products WHERE id != ? ORDER BY id DESC LIMIT 5");
$stmtRelated->execute([$id]);
$relatedProducts = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);

// Logged-in user (Fetching name, phone, and city for auto-population)
$user = null;
if (isset($_SESSION['userid'])) {
    $stmtUser = $conn->prepare("SELECT id, role_id, email, name, phone, city FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['userid']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> - Fortune Path</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; color: #1a1a1a; transition: background 0.3s ease; }
    
    .unified-frame {
        position: relative; width: 100%; aspect-ratio: 1 / 1; background: #f9f9f9;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
        border-radius: 2rem; border: 1px solid #f0f0f0; transition: background 0.3s ease;
    }
    .unified-frame img { width: 100%; height: 100%; object-fit: contain; padding: 15%; }

    .related-frame {
        aspect-ratio: 1 / 1; background: #f9f9f9; border-radius: 1.25rem; overflow: hidden;
        display: flex; align-items: center; justify-content: center; transition: background 0.3s ease;
    }
    .related-frame img { width: 80%; height: 80%; object-fit: contain; }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Modal Styles */
    #checkoutModal { display: none; z-index: 99999; }
    .modal-overlay { background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
    .modal-content { animation: slideUp 0.3s ease-out; }
    @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }

    @media (max-width: 1023px) {
        .mobile-safe-area { padding-bottom: 180px; }
        .sticky-buy-box {
            position: fixed; bottom: 70px; left: 0; right: 0;
            background: rgba(255,255,255,0.9); backdrop-filter: blur(10px);
            border-top: 1px solid #eee; padding: 12px 16px; z-index: 40;
        }
    }
    @media (min-width: 1024px) { .desktop-container { max-width: 85rem; margin: 0 auto; padding: 0 2rem; } }

    /* Dark Mode Overrides */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .unified-frame, body.dark-theme .related-frame { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900, body.dark-theme .text-black { color: #ffffff !important; }
    body.dark-theme .text-gray-600, body.dark-theme .text-gray-500 { color: #d1d5db !important; }
    body.dark-theme .bg-gray-100 { background: #374151 !important; }
    body.dark-theme .sticky-buy-box { background: rgba(31, 41, 55, 0.9) !important; border-color: #374151 !important; }
    body.dark-theme .modal-content { background: #1f2937 !important; border-color: #374151 !important; color: #fff; }
    body.dark-theme .modal-input { background: #374151 !important; color: #fff !important; border-color: #4b5563 !important; }
    body.dark-theme .bg-black { background: #10b981 !important; color: #ffffff !important; border: none !important; }
</style>
</head>

<body class="bg-white">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<!-- Mobile View -->
<div class="block lg:hidden mobile-safe-area">
    <?php include '../user/mobile_header.php'; ?>
    <div class="px-4 pt-4">
        <div class="unified-frame mb-6">
            <img src="/uploads/products/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 leading-tight mb-2"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-2xl font-black text-black mb-6">₹<?= number_format((float)$product['price'], 0) ?></p>
            <div class="h-[1px] w-full bg-gray-100 mb-6"></div>
            <div class="text-gray-600 leading-relaxed text-base mb-10">
                <?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
    </div>

    <div class="sticky-buy-box">
        <?php if ($user): ?>
            <button onclick="openCheckoutModal()" class="w-full py-4 bg-black text-white text-center rounded-xl font-black block shadow-lg text-lg active:scale-95 transition-transform">
                <?= __('Buy Now') ?>
            </button>
        <?php else: ?>
            <a href="/auth/login.php" class="w-full py-4 bg-black text-white text-center rounded-xl font-black block shadow-lg text-lg">
                <?= __('Login to Buy') ?>
            </a>
        <?php endif; ?>
    </div>
    
    <div class="fixed bottom-0 left-0 right-0 z-50">
        <?php include '../user/footer_user.php'; ?>
    </div>
</div>

<!-- Desktop View -->
<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>
    <main class="desktop-container py-16">
        <div class="grid grid-cols-12 gap-16">
            <div class="col-span-6">
                <div class="sticky top-24 unified-frame h-[600px]">
                    <img src="/uploads/products/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="col-span-6 pt-12">
                <nav class="flex mb-8 text-[11px] font-bold text-gray-400 gap-2 uppercase tracking-widest">
                    <a href="/index.php" class="hover:text-black"><?= __('Home') ?></a> / <span class="text-black"><?= __('Product Detail') ?></span>
                </nav>
                <h1 class="text-6xl font-black text-gray-900 mb-6 leading-[1.1]"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="text-4xl font-black text-black mb-10">₹<?= number_format((float)$product['price'], 0) ?></div>
                <div class="prose prose-lg text-gray-500 mb-12 leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
                <div class="border-t border-gray-100 pt-10">
                    <?php if ($user): ?>
                        <button onclick="openCheckoutModal()" class="w-full py-6 bg-black text-white text-center rounded-2xl font-black text-xl hover:bg-gray-800 transition-all shadow-2xl block">
                            <?= __('Buy Now') ?>
                        </button>
                    <?php else: ?>
                        <a href="/auth/login.php" class="w-full py-6 bg-black text-white text-center rounded-2xl font-black text-xl hover:bg-gray-800 transition-all shadow-2xl block">
                            <?= __('Login to Buy') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include '../user/web_footer.php'; ?>
</div>

<!-- CHECKOUT MODAL (Updated E-commerce Form) -->
<div id="checkoutModal" class="fixed inset-0 w-full h-full flex items-end md:items-center justify-center">
    <div class="absolute inset-0 modal-overlay cursor-pointer" onclick="closeCheckoutModal()"></div>
    <div class="modal-content bg-white w-full md:w-[600px] rounded-t-3xl md:rounded-3xl p-6 relative z-10 max-h-[90vh] overflow-y-auto shadow-2xl border border-gray-100">
        
        <button onclick="closeCheckoutModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200">
            ✕
        </button>
        
        <h2 class="text-2xl font-black mb-6 text-gray-900"><?= __('Checkout Details') ?></h2>
        
        <form id="checkoutForm" onsubmit="initiateRazorpay(event)" class="space-y-4">
            <input type="hidden" id="pid" value="<?= $product['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Full Name') ?></label>
                    <input type="text" id="s_name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Phone Number') ?></label>
                    <input type="tel" id="s_phone" pattern="[0-9]{10}" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Pincode') ?></label>
                    <input type="text" id="s_pin" pattern="[0-9]{6}" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Town/City') ?></label>
                    <input type="text" id="s_city" value="<?= htmlspecialchars($user['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Flat, House no., Building, Apartment') ?></label>
                <input type="text" id="s_addr1" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Area, Street, Sector, Village') ?></label>
                <input type="text" id="s_addr2" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"><?= __('Landmark (Optional)') ?></label>
                <input type="text" id="s_landmark" placeholder="<?= __('e.g. near Apollo Hospital') ?>" class="modal-input w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-black">
            </div>

            <button type="submit" id="payBtn" class="w-full py-4 mt-2 bg-black text-white rounded-xl font-black text-lg shadow-lg flex justify-center items-center gap-2">
                <span id="btnText"><?= __('Pay Securely') ?> - ₹<?= number_format((float)$product['price'], 0) ?></span>
            </button>
        </form>
    </div>
</div>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    const modal = document.getElementById('checkoutModal');
    const payBtn = document.getElementById('payBtn');
    const btnText = document.getElementById('btnText');

    function openCheckoutModal() { modal.style.display = 'flex'; }
    function closeCheckoutModal() { modal.style.display = 'none'; }

    async function initiateRazorpay(e) {
        e.preventDefault();
        payBtn.disabled = true;
        btnText.innerText = "<?= __('Processing...') ?>";

        // Construct a structured multi-line address from the individual fields
        const addr1 = document.getElementById('s_addr1').value.trim();
        const addr2 = document.getElementById('s_addr2').value.trim();
        const landmark = document.getElementById('s_landmark').value.trim();
        const city = document.getElementById('s_city').value.trim();
        
        let fullAddress = addr1 + ", " + addr2;
        if(landmark !== '') { fullAddress += ", Landmark: " + landmark; }
        fullAddress += ", City: " + city;

        const payload = {
            product_id: document.getElementById('pid').value,
            name: document.getElementById('s_name').value,
            phone: document.getElementById('s_phone').value,
            pincode: document.getElementById('s_pin').value,
            address: fullAddress
        };

        try {
            // Step 1: Create Order in Backend
            const res = await fetch('/products/ajax_create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message);
                payBtn.disabled = false;
                btnText.innerText = "<?= __('Pay Securely') ?> - ₹<?= number_format((float)$product['price'], 0) ?>";
                return;
            }

            // Step 2: Open Razorpay Checkout
            var options = {
                "key": "rzp_live_ShmpmrlHXQlUdC", 
                "amount": data.order.amount,
                "currency": "INR",
                "name": "Fortune Path",
                "description": "<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>",
                "order_id": data.order.id,
                "handler": async function (response) {
                    // Step 3: Verify Payment in Backend
                    const verifyRes = await fetch('/products/ajax_verify_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature,
                            db_order_id: data.db_order_id
                        })
                    });
                    const verifyData = await verifyRes.json();
                    
                    if (verifyData.success) {
                        // Redirect user to their order history or success page
                        window.location.href = '/user/call_history.php?purchase=success'; 
                    } else {
                        alert("Payment verification failed!");
                    }
                },
                "prefill": {
                    "name": payload.name,
                    "contact": payload.phone,
                    "email": "<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                },
                "theme": { "color": "#000000" }
            };
            
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response){
                alert("Payment Failed. Reason: " + response.error.description);
                payBtn.disabled = false;
                btnText.innerText = "<?= __('Pay Securely') ?> - ₹<?= number_format((float)$product['price'], 0) ?>";
            });
            rzp1.open();

        } catch (err) {
            alert("Network error occurred.");
            payBtn.disabled = false;
            btnText.innerText = "<?= __('Pay Securely') ?> - ₹<?= number_format((float)$product['price'], 0) ?>";
        }
    }
</script>

</body>
</html>