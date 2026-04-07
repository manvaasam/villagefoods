<?php
class RapidHelper {
    /**
     * Recalculates and updates the availability status of a delivery boy.
     * Status is 'busy' if they have any active rapid orders, else 'available'.
     */
    public static function syncStatus($pdo, $delivery_boy_id) {
        if (!$delivery_boy_id) return;

        // Active statuses: assigned, accepted, picked
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rapid_orders 
                               WHERE delivery_boy_id = ? 
                               AND status IN ('assigned', 'accepted', 'picked')");
        $stmt->execute([$delivery_boy_id]);
        $activeCount = $stmt->fetchColumn();

        $newStatus = ($activeCount > 0) ? 'busy' : 'available';

        $stmt = $pdo->prepare("UPDATE delivery_partners SET status = ? WHERE user_id = ?");
        $stmt->execute([$newStatus, $delivery_boy_id]);
        
        return $newStatus;
    }

    /**
     * Handles timeouts for orders that were 'assigned' but not accepted within 60 seconds.
     * Reverts them to 'pending' and syncs the delivery boy's status.
     */
    public static function handleTimeouts($pdo) {
        $timeoutSeconds = 60;
        
        // Find assigned orders older than 60 seconds
        $stmt = $pdo->prepare("SELECT id, delivery_boy_id FROM rapid_orders 
                               WHERE status = 'assigned' 
                               AND updated_at < (NOW() - INTERVAL ? SECOND)");
        $stmt->execute([$timeoutSeconds]);
        $timedOutOrders = $stmt->fetchAll();

        foreach ($timedOutOrders as $order) {
            $pdo->beginTransaction();
            try {
                // Revert order to pending
                $updateStmt = $pdo->prepare("UPDATE rapid_orders SET status = 'pending', delivery_boy_id = NULL WHERE id = ?");
                $updateStmt->execute([$order['id']]);

                // Sync delivery boy status
                self::syncStatus($pdo, $order['delivery_boy_id']);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
        
        return count($timedOutOrders);
    }
}
?>
