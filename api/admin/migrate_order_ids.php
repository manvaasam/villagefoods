<?php
require_once '../../includes/db.php';

try {
    // Get all orders sorted by ID to maintain historical sequence
    $stmt = $pdo->query("SELECT id FROM orders ORDER BY id ASC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $start = 1001;
    $count = 0;

    $pdo->beginTransaction();

    foreach ($orders as $order) {
        $newNumber = "VF-" . ($start++);
        $updateStmt = $pdo->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
        $updateStmt->execute([$newNumber, $order['id']]);
        $count++;
    }

    $pdo->commit();
    echo "Successfully migrated $count orders to the new VF-XXXX format.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error during migration: " . $e->getMessage();
}
?>
