<?php
require_once 'includes/db.php';
header('Content-Type: text/plain');

try {
    $stmt = $pdo->query("SELECT id, shop_name, shop_image FROM shops ORDER BY id DESC LIMIT 10");
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "ID | Shop Name | Image Path | Exists?\n";
    echo "---|-----------|------------|---------\n";
    foreach ($shops as $shop) {
        $exists = file_exists($shop['shop_image']) ? "YES" : "NO";
        echo "{$shop['id']} | {$shop['shop_name']} | {$shop['shop_image']} | $exists\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
