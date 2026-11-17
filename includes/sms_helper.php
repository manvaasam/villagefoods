<?php
/**
 * Fast2SMS Helper for Village Foods
 */

if (!function_exists('send_sms_otp')) {
    /**
     * Send numeric OTP via Fast2SMS OTP Route
     * 
     * @param string $phone 10-digit mobile number
     * @param string $otp Numeric OTP code
     * @return array ['success' => bool, 'message' => string]
     */
    function send_sms_otp($phone, $otp) {
        // 1. Sanitize to strictly 10 digits
        $cleanedPhone = preg_replace('/[^0-9]/', '', (string)$phone);
        if (strlen($cleanedPhone) === 12 && str_starts_with($cleanedPhone, '91')) {
            $cleanedPhone = substr($cleanedPhone, 2);
        } elseif (strlen($cleanedPhone) === 11 && str_starts_with($cleanedPhone, '0')) {
            $cleanedPhone = substr($cleanedPhone, 1);
        }

        if (!preg_match('/^[6-9]\d{9}$/', $cleanedPhone)) {
            return [
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number'
            ];
        }

        // 2. Load configuration
        $configFile = __DIR__ . '/sms_config.php';
        if (!file_exists($configFile)) {
            return [
                'success' => false,
                'message' => 'SMS configuration file missing'
            ];
        }

        $config = require $configFile;
        if (empty($config['api_key'])) {
            return [
                'success' => false,
                'message' => 'SMS API Key not configured'
            ];
        }

        // 3. Prepare payload for Fast2SMS OTP route
        $payload = [
            'route'            => $config['route'] ?? 'otp',
            'variables_values' => (string)$otp,
            'numbers'          => $cleanedPhone
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $config['api_url'] ?? 'https://www.fast2sms.com/dev/bulkV2',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $config['api_key'],
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [
                'success' => false,
                'message' => 'SMS Gateway Error: ' . $curlErr
            ];
        }

        $result = json_decode($response, true);
        if (isset($result['return']) && $result['return'] === true) {
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your mobile number!'
            ];
        }

        $errMsg = 'Failed to send OTP via SMS';
        if (!empty($result['message'])) {
            $errMsg = is_array($result['message']) ? implode(' ', $result['message']) : (string)$result['message'];
        }

        return [
            'success' => false,
            'message' => $errMsg
        ];
    }
}
