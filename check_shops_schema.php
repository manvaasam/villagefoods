<?php
require_once 'includes/db.php';
$tables = ['shops', 'users'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        $null = $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        echo "{$col['Field']} - {$col['Type']} - $null\n";
    }
    echo "--- Indexes ---\n";
    $stmt = $pdo->query("SHOW INDEX FROM $table");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $idx) {
        echo "{$idx['Table']} - {$idx['Key_name']} - {$idx['Column_name']} - Uniq:" . ($idx['Non_unique'] == 0 ? 'YES' : 'NO') . "\n";
    }
}
