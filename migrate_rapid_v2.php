<?php
include 'includes/db.php';

try {
    // 1. Rename delivery_partners.status to verification_status
    $stmt = $pdo->query("SHOW COLUMNS FROM delivery_partners LIKE 'verification_status'");
    if (!$stmt->fetch()) {
        echo "Renaming status to verification_status...\n";
        $pdo->exec("ALTER TABLE delivery_partners CHANGE COLUMN status verification_status ENUM('Profile Incomplete','Verification Pending','Verified','Rejected') DEFAULT 'Profile Incomplete'");
    }

    // 2. Add availability status column to delivery_partners
    $stmt = $pdo->query("SHOW COLUMNS FROM delivery_partners LIKE 'status'");
    if (!$stmt->fetch()) {
        echo "Adding new status column...\n";
        $pdo->exec("ALTER TABLE delivery_partners ADD COLUMN status ENUM('available', 'busy') DEFAULT 'available' AFTER verification_status");
    }

    // 3. Update rapid_orders status ENUM
    echo "Updating rapid_orders status flow...\n";
    $pdo->exec("ALTER TABLE rapid_orders MODIFY COLUMN status VARCHAR(20)");
    
    // Map existing values
    $pdo->exec("UPDATE rapid_orders SET status = 'pending' WHERE status = 'Requested'");
    $pdo->exec("UPDATE rapid_orders SET status = 'accepted' WHERE status = 'Accepted'");
    $pdo->exec("UPDATE rapid_orders SET status = 'picked' WHERE status IN ('Picked', 'Delivering')");
    $pdo->exec("UPDATE rapid_orders SET status = 'completed' WHERE status = 'Completed'");
    $pdo->exec("UPDATE rapid_orders SET status = 'rejected' WHERE status = 'Cancelled'");
    
    // Set anything else to pending
    $pdo->exec("UPDATE rapid_orders SET status = 'pending' WHERE status NOT IN ('pending', 'assigned', 'accepted', 'picked', 'completed', 'rejected')");

    // Finalize the ENUM
    $pdo->exec("ALTER TABLE rapid_orders MODIFY COLUMN status ENUM('pending', 'assigned', 'accepted', 'picked', 'completed', 'rejected') DEFAULT 'pending'");

    echo "Migration successful!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
