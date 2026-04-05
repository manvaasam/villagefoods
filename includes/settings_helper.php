<?php
class Settings {
    private static $settings = null;

    public static function load($pdo) {
        if (self::$settings === null) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM store_settings");
            self::$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    }

    public static function get($key, $default = null) {
        return self::$settings[$key] ?? $default;
    }

    public static function isEnabled($key) {
        $val = self::get($key, '0');
        return $val === '1' || $val === 'true';
    }
}
?>
