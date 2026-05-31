<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Unauthorized access.' => 'अनधिकृत पहुंच।',
            'Invalid payload data.' => 'अमान्य पेलोड डेटा।',
            'Invalid transaction amount.' => 'अमान्य लेनदेन राशि।',
            'Payment gateway unreachable.' => 'भुगतान गेटवे पहुंच से बाहर।'
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
    echo json_encode(['success' => false, 'message' => __('Unauthorized access.')]);
    exit;
}

// Hardened Security: Strict JSON payload validation
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!is_array($data) || empty($data['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => __('Invalid payload data.')]);
    exit;
}

$amount = floatval($data['amount']);
if ($amount < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => __('Invalid transaction amount.')]);
    exit;
}

// SECURED: Please use environment variables for these in your production environment
$keyId = 'YOUR_RAZORPAY_KEY_ID'; 
$keySecret = 'YOUR_RAZORPAY_KEY_SECRET';

$receipt = 'rcptid_' . time() . '_' . intval($_SESSION['userid']);

$payload = json_encode([
    'amount'          => $amount * 100, 
    'currency'        => 'INR',
    'receipt'         => $receipt,
    'payment_capture' => 1
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200 && $response) {
    $responseData = json_decode($response, true);
    if (isset($responseData['id'])) {
        echo json_encode([
            'success' => true, 
            'order' => [
                'id' => htmlspecialchars($responseData['id'], ENT_QUOTES, 'UTF-8'), 
                'amount' => floatval($responseData['amount'])
            ]
        ]);
        exit;
    }
} 

http_response_code(502);
error_log("Razorpay API Error: " . $response);
echo json_encode(['success' => false, 'message' => __('Payment gateway unreachable.')]);
exit;
?>