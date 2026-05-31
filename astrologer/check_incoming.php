<?php
// check_incoming.php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    echo json_encode(['has_call' => false]);
    exit;
}

$astro_id = $_SESSION['userid'];

$stmt = $conn->prepare("
    SELECT cs.id, u.email, u.name
    FROM call_sessions cs
    JOIN users u ON u.id = cs.user_id
    WHERE cs.astrologer_id=? AND cs.status='requested'
    ORDER BY cs.start_time ASC
    LIMIT 1
");
$stmt->execute([$astro_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode([
        "has_call" => true,
        "call_id" => (int)$row["id"],
        "user_email" => htmlspecialchars($row["email"], ENT_QUOTES, 'UTF-8'),
        "user_name" => htmlspecialchars($row["name"] ?? 'Guest', ENT_QUOTES, 'UTF-8')
    ]);
} else {
    echo json_encode(["has_call" => false]);
}
?>