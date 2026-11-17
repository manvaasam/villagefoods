<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/plain');
echo "=== Village Foods SMS OTP Migration ===\n\n";

try {
    // 1. Ensure phone column in otps table
    $stmt = $pdo->query("SHOW COLUMNS FROM otps LIKE 'phone'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE otps ADD COLUMN phone VARCHAR(20) NULL AFTER id");
        echo "[OK] Added 'phone' column to 'otps' table.\n";
    } else {
        echo "[INFO] 'phone' column already exists in 'otps'.\n";
    }

    // 2. Make email nullable in otps table
    $pdo->exec("ALTER TABLE otps MODIFY COLUMN email VARCHAR(191) NULL");
    echo "[OK] 'email' column in 'otps' is now NULLABLE.\n";

    // 3. Ensure index on otps(phone)
    try {
        $pdo->exec("CREATE INDEX idx_otps_phone ON otps(phone)");
        echo "[OK] Added index on otps(phone).\n";
    } catch (\Throwable $e) {
        // Index might already exist
        echo "[INFO] Index on otps(phone) already present or skipped.\n";
    }

    // 4. Ensure users table allows null email and has index on phone
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NULL");
        echo "[OK] 'email' column in 'users' is now NULLABLE.\n";
    } catch (\Throwable $e) {}

    try {
        $pdo->exec("CREATE INDEX idx_users_phone ON users(phone)");
        echo "[OK] Added index on users(phone).\n";
    } catch (\Throwable $e) {
        echo "[INFO] Index on users(phone) already present or skipped.\n";
    }

    echo "\n=== Migration Completed Successfully! ===\n";

} catch (PDOException $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
}
