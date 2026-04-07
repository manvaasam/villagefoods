<?php
require_once 'includes/db.php';
header('Content-Type: text/plain');

echo "--- rapid_orders columns ---\n";
$s = $pdo->query("DESC rapid_orders");
while($r = $s->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " (" . $r['Type'] . ")\n";
}

echo "\n--- users columns ---\n";
$s = $pdo->query("DESC users");
while($r = $s->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " (" . $r['Type'] . ")\n";
}

echo "\n--- sample rapid_order row ---\n";
$s = $pdo->query("SELECT * FROM rapid_orders LIMIT 1");
print_r($s->fetch(PDO::FETCH_ASSOC));
?>
