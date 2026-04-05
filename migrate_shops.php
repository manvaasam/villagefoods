<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE shops ADD COLUMN email VARCHAR(255) AFTER phone");
    echo "Column 'email' added to 'shops' table successfully.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}
