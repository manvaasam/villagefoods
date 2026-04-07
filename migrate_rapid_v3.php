<?php
include 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE rapid_orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER status");
    echo "Column updated_at added to rapid_orders successfully!\n";
} catch (Exception $e) {
    echo "Execution failed: " . $e->getMessage() . "\n";
}
?>
