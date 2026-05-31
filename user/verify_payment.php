<?php
session_start();
require '../db.php';

header('Content-Type: application/json');
date_default_timezone_set("Asia/Kolkata");

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Unauthorized' => 'अनधिकृत',
            'Missing payment details. Process aborted.' => 'भुगतान विवरण गायब है। प्रक्रिया निरस्त कर दी गई।',
            'Added money via Razorpay' => 'रेज़रपे के माध्यम से पैसे जोड़े गए',
            'Internal server error during database update.' => 'डेटाबेस अपडेट के दौरान आंतरिक सर्वर त्रुटि।',
            'Invalid payment signature. Process aborted.' => 'अमान्य भुगतान हस्ताक्षर। प्रक्रिया निरस्त कर दी गई।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Hardened Security: Session check
if (empty($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => __('Unauthorized')]);
    exit;
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

// Hardened Security: Validate all incoming parameters tightly
if (!is_array($data) || empty($data['razorpay_order_id']) || empty($data['razorpay_payment_id']) || empty($data['razorpay_signature']) || empty($data['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => __('Missing payment details. Process aborted.')]);
    exit;
}

$userId = intval($_SESSION['userid']);
$amountAdded = floatval($data['amount']);

// SECURED: Replace with your new generated secret key
$keySecret = 'YOUR_RAZORPAY_KEY_SECRET';

$razorpay_order_id = $data['razorpay_order_id'];
$razorpay_payment_id = $data['razorpay_payment_id'];
$razorpay_signature = $data['razorpay_signature'];

// Generate matching signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $keySecret);

if (hash_equals($generated_signature, $razorpay_signature)) {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :id");
        $stmt->execute([':amount' => $amountAdded, ':id' => $userId]);

        $stmtTx = $conn->prepare("
            INSERT INTO wallet_transactions 
            (user_id, amount, type, description, icon, created_at) 
            VALUES (:user_id, :amount, 'wallet_recharge', :desc, 'account_balance_wallet', NOW())
        ");
        $stmtTx->execute([
            ':user_id' => $userId, 
            ':amount' => $amountAdded,
            ':desc' => __('Added money via Razorpay')
        ]);

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch(PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Wallet Recharge DB Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => __('Internal server error during database update.')]);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => __('Invalid payment signature. Process aborted.')]);
}
?>