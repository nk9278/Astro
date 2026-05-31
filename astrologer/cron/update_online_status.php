<?php
// astrologer/cron/update_online_status.php

declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');

/*
 IMPORTANT:
 db.php is in /public_html/db.php
 but this cron file is in: /public_html/astrologer/cron/
 so we need ../.. to reach main root
*/

require __DIR__ . '/../../db.php';

/**
 * RULES:
 * - Works only for astrologers (role = 1)
 * - Auto timer must be enabled (auto_timer_enabled = 1)
 * - If day is off → offline
 * - If current time is within start/end → online
 * - Supports overnight timing (22:00 → 06:00)
 */

$weekday = (int)date('w'); // 0 = Sunday
$now     = date('H:i:s');

try {
    $sql = "
      SELECT u.id, s.start_time, s.end_time, s.off_day
      FROM users u
      LEFT JOIN astrologer_schedule s
        ON s.user_id = u.id AND s.day_of_week = :wd
      WHERE u.role = 1 AND u.auto_timer_enabled = 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':wd' => $weekday]);

    $upd = $conn->prepare("UPDATE users SET online_status = :st WHERE id = :uid");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $uid   = (int)$row['id'];
        $off   = (int)$row['off_day'] === 1;
        $start = $row['start_time'];
        $end   = $row['end_time'];

        $status = 0; // default offline

        if (!$off && $start && $end) {
            if ($start <= $end) {
                // Normal same-day window
                if ($now >= $start && $now < $end) {
                    $status = 1;
                }
            } else {
                // Overnight window (22:00 → 06:00)
                if ($now >= $start || $now < $end) {
                    $status = 1;
                }
            }
        }

        $upd->execute([
            ':st'  => $status,
            ':uid' => $uid
        ]);
    }
} catch (PDOException $e) {
    error_log("Cron Error (update_online_status): " . $e->getMessage());
}
?>