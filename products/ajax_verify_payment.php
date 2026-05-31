<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (empty($_SESSION['userid'])) {
    echo json_encode(['success' => false]); exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (empty($data['razorpay_order_id']) || empty($data['razorpay_payment_id']) || empty($data['razorpay_signature']) || empty($data['db_order_id'])) {
    echo json_encode(['success' => false]); exit;
}

$keySecret = 'I2CEtCSbnSb1QLmrEz50I5Ey';

$generated_signature = hash_hmac('sha256', $data['razorpay_order_id'] . "|" . $data['razorpay_payment_id'], $keySecret);

if (hash_equals($generated_signature, $data['razorpay_signature'])) {
    // Payment is valid, update DB
    $stmt = $conn->prepare("UPDATE product_orders SET razorpay_payment_id = ?, payment_status = 'successful' WHERE id = ? AND razorpay_order_id = ?");
    $stmt->execute([$data['razorpay_payment_id'], $data['db_order_id'], $data['razorpay_order_id']]);
    
    echo json_encode(['success' => true]);
} else {
    // Invalid signature
    $stmt = $conn->prepare("UPDATE product_orders SET payment_status = 'failed' WHERE id = ?");
    $stmt->execute([$data['db_order_id']]);
    
    echo json_encode(['success' => false]);
}
?>