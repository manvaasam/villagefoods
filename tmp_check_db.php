<?php
include 'includes/db.php';
echo "--- TABLES ---\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo $table . "\n";
}

echo "\n--- rapid_orders SCHEMA ---\n";
try {
    $stmt = $pdo->query("DESCRIBE rapid_orders");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) { echo "rapid_orders not found\n"; }

echo "\n--- delivery_partners SCHEMA ---\n";
try {
    $stmt = $pdo->query("DESCRIBE delivery_partners");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) { echo "delivery_partners not found\n"; }
?>
