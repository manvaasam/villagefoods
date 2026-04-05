<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NULL");
    echo "Column 'email' in 'users' table is now NULLABLE.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
