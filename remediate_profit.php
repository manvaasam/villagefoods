<?php
require_once 'includes/db.php';
require_once 'includes/settings_helper.php';
Settings::load($pdo);

$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM orders WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$orders = $stmt->fetchAll();

echo "Auditing " . count($orders) . " orders from today...\n";

foreach ($orders as $o) {
    $commission = (float)$o['commission_amount'];
    $p_fee = (float)$o['platform_fee'];
    $h_fee = (float)$o['handling_fee'];
    $d_charge = (float)$o['delivery_charge'];
    $d_earning = (float)$o['delivery_earning'];
    
    $correct_profit = ($commission + $p_fee + $h_fee + $d_charge) - $d_earning;
    
    if (abs((float)$o['platform_profit'] - $correct_profit) > 0.01) {
        echo "Order {$o['order_number']}: Correcting Profit from {$o['platform_profit']} to {$correct_profit}\n";
        $upd = $pdo->prepare("UPDATE orders SET platform_profit = ? WHERE id = ?");
        $upd->execute([$correct_profit, $o['id']]);
    } else {
        echo "Order {$o['order_number']}: Profit is correct ({$o['platform_profit']})\n";
    }
}
echo "Remediation complete.\n";
