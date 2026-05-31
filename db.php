<?php
// ✅ Set India Timezone Globally (MOST IMPORTANT)
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username   = "u750810714_FortuneParth";
$password   = "God@war4";
$dbname     = "u750810714_FortuneParth";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ SET MySQL connection timezone to India
    $conn->exec("SET time_zone = '+05:30'");

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
