<?php
// Hardened Security: Sirf installation ke liye. Use ke baad delete karein.
echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
echo "<h3 style='color: #333;'>Installing Razorpay SDK... Please wait 10-20 seconds.</h3>";
echo "<pre style='background:#111; color:#0f0; padding:20px; border-radius: 8px; display: inline-block; text-align: left;'>";

// Composer ko run karne ka command
putenv('COMPOSER_HOME=' . __DIR__ . '/.composer');
$output = shell_exec('composer require razorpay/razorpay 2>&1');

echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
echo "</pre><h3 style='color: #10b981;'>Installation Complete! File Manager refresh karein.</h3>";
echo "</div>";
?>