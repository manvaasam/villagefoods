<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit;
}

$name = htmlspecialchars(strip_tags($data['name'] ?? ''));
$email = htmlspecialchars(strip_tags($data['email'] ?? ''));
$subject = htmlspecialchars(strip_tags($data['subject'] ?? 'General Inquiry'));
$message = htmlspecialchars(strip_tags($data['message'] ?? ''));

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, Email and Message are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Strict Regex Validation for Name
if (!preg_match("/^[a-zA-Z\s]*$/", $name)) {
    echo json_encode(['status' => 'error', 'message' => 'Name: Only alphabets and spaces are allowed']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
