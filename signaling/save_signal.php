<?php
session_start();
require '../db.php';
header("Content-Type: application/json");

$call_id = intval($_POST['call_id'] ?? 0);
$sender  = $_POST['sender'] ?? '';
$type    = $_POST['type'] ?? '';
$payload = $_POST['payload'] ?? '';

$stmt=$conn->prepare("
INSERT INTO call_signals (call_id,sender_role,type,payload)
VALUES (?,?,?,?)
");
$stmt->execute([$call_id,$sender,$type,$payload]);

echo json_encode(["status"=>"ok"]);
