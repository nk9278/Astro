<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (empty($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (empty($data['product_id']) || empty($data['name']) || empty($data['phone']) || empty($data['pincode']) || empty($data['address'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']); exit;
}

// Get Product Price securely from DB
$stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
$stmt->execute([$data['product_id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']); exit;
}

$amount = floatval($product['price']);
$keyId = 'rzp_live_ShmpmrlHXQlUdC'; 
$keySecret = 'I2CEtCSbnSb1QLmrEz50I5Ey';
$receipt = 'prd_' . time() . '_' . $_SESSION['userid'];

// Create Razorpay Order
$payload = json_encode(['amount' => $amount * 100, 'currency' => 'INR', 'receipt' => $receipt]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

if (isset($responseData['id'])) {
    // Save to Database as Pending
    $stmtDb = $conn->prepare("INSERT INTO product_orders (user_id, product_id, amount, shipping_name, shipping_phone, shipping_pincode, shipping_address, razorpay_order_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmtDb->execute([$_SESSION['userid'], $data['product_id'], $amount, $data['name'], $data['phone'], $data['pincode'], $data['address'], $responseData['id']]);
    
    $db_order_id = $conn->lastInsertId();

    echo json_encode([
        'success' => true, 
        'order' => ['id' => $responseData['id'], 'amount' => $responseData['amount']],
        'db_order_id' => $db_order_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Payment gateway error.']);
}
?>