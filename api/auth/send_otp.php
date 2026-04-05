<?php
session_start();
require_once '../../includes/db.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$name = $data['name'] ?? '';

// Store name in session temporarily for user creation/update in verify_otp
$_SESSION['temp_name'] = $name;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

if (!empty($name) && !preg_match("/^[a-zA-Z\s]*$/", $name)) {
    echo json_encode(['status' => 'error', 'message' => 'Name: Only alphabets and spaces are allowed']);
    exit;
}

// Check if user exists and has a staff role
$stmt = $pdo->prepare("SELECT role FROM users WHERE email = ?");
$stmt->execute([$email]);
$userRole = $stmt->fetchColumn();

if ($userRole && $userRole !== 'customer') {
    $portal = ($userRole === 'delivery') ? 'Partner Portal (/delivery)' : 'Admin Panel (/admin)';
    echo json_encode([
        'status' => 'error', 
        'message' => "Staff detected. Please use the {$portal} to login."
    ]);
    exit;
}

try {
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));

    // Check for resend cooldown (1 minute)
    $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, last_sent_at, NOW()) as diff FROM otps WHERE email = ?");
    $stmt->execute([$email]);
    $diff = $stmt->fetchColumn();

    if ($diff !== false && $diff < 60) {
        $wait = 60 - $diff;
        echo json_encode(['status' => 'error', 'message' => "Please wait $wait seconds before resending."]);
        exit;
    }

    // Store/Update OTP
    $stmt = $pdo->prepare("DELETE FROM otps WHERE email = ?");
    $stmt->execute([$email]);
    $stmt = $pdo->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
    $stmt->execute([$email, $otp]);

    // Use require instead of require_once to get the array reliably
    $mailConfig = require '../../includes/mail_config.php';

    $mail = new PHPMailer(true);
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = $mailConfig['auth'];
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['secure'];
    $mail->Port       = $mailConfig['port'];

    // Recipients
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Village Foods Login OTP';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 500px;'>
            <h2 style='color: #27ae60;'>Village Foods</h2>
            <p>Hello" . ($name ? " " . htmlspecialchars($name) : "") . ",</p>
            <p>Your One-Time Password (OTP) for logging into Village Foods is:</p>
            <div style='font-size: 24px; font-weight: bold; color: #333; letter-spacing: 5px; margin: 20px 0;'>$otp</div>
            <p style='color: #666; font-size: 12px;'>This OTP is valid for 5 minutes. Please do not share this code with anyone.</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 10px; color: #999;'>Sent with ❤️ from Village Foods Team</p>
        </div>
    ";
    $mail->AltBody = "Your Village Foods OTP is: $otp. Valid for 5 minutes.";

    if($mail->send()) {
        echo json_encode(['status' => 'success', 'message' => 'OTP sent to your email!']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Mail error: " . $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "Database error: " . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['status' => 'error', 'message' => "System error: " . $e->getMessage()]);
}
?>
