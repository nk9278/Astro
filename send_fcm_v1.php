<?php
// send_fcm_v1.php
// POST parameters: token, call_id, user_email, accept_url
// Returns FCM response JSON
header('Content-Type: application/json');

$serviceAccountPath = '/home/u824441453b96b88cba/fortune-parth-a404d-firebase-adminsdk-fbsvc-d82e8475bd.json';

if (!file_exists($serviceAccountPath)) {
    echo json_encode(['error' => 'service account json not found at: ' . $serviceAccountPath]);
    exit;
}

$token = $_POST['token'] ?? $_GET['token'] ?? '';
$call_id = $_POST['call_id'] ?? $_GET['call_id'] ?? '';
$user_email = $_POST['user_email'] ?? $_GET['user_email'] ?? '';
$accept_url = $_POST['accept_url'] ?? $_GET['accept_url'] ?? '';

if (!$token || !$call_id) {
    http_response_code(400);
    echo json_encode(['error'=>'missing token or call_id']);
    exit;
}

$sa = json_decode(file_get_contents($serviceAccountPath), true);
if (!$sa) {
    echo json_encode(['error'=>'invalid service account json']);
    exit;
}

// build JWT and exchange for access_token
$now = time();
$jwtHeader = ['alg'=>'RS256','typ'=>'JWT'];
$jwtClaim = [
    'iss' => $sa['client_email'],
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600
];

function base64url_encode($data){ return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }

$unsigned = base64url_encode(json_encode($jwtHeader)) . '.' . base64url_encode(json_encode($jwtClaim));

$private_key = $sa['private_key'];
openssl_sign($unsigned, $signature, $private_key, 'SHA256');
$jwt = $unsigned . '.' . base64url_encode($signature);

// request access token
$post = http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion'  => $jwt
]);

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$res = curl_exec($ch);
curl_close($ch);

$resObj = json_decode($res, true);
if (!isset($resObj['access_token'])) {
    echo json_encode(['error'=>'unable to obtain access token','raw'=>$res]);
    exit;
}
$accessToken = $resObj['access_token'];

// call FCM HTTP v1
$projectId = $sa['project_id'];
$fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

// ⭐⭐ FIX: Updated the message array to be "Data-Only" and matched Android keys ⭐⭐
$message = [
    'message' => [
        'token' => $token,
        'data' => [
            'call_id' => (string)$call_id,
            'caller_name' => $user_email ? $user_email : 'Incoming Call', 
            'join_url' => $accept_url 
        ],
        'android' => [
            'priority' => 'HIGH'
        ]
    ]
];

$ch = curl_init($fcmUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
$out = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) echo json_encode(['error'=>$err]); else echo $out;