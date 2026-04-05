<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

// Ensure user is logged in as delivery
checkPersistentLogin($pdo);
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'delivery') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;

    if ($amount <= 0) {
        throw new Exception('Invalid withdrawal amount');
    }

    // 1. Calculate Available Balance
    $stmt = $pdo->prepare("SELECT SUM(COALESCE(delivery_earning, 0)) FROM orders WHERE delivery_boy_id = ? AND status = 'Delivered'");
    $stmt->execute([$user_id]);
    $reg = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT SUM(COALESCE(delivery_earning, 0)) FROM rapid_orders WHERE delivery_boy_id = ? AND status = 'Completed'");
    $stmt->execute([$user_id]);
    $rap = $stmt->fetchColumn() ?: 0;

    $total_earnings = $reg + $rap;

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM withdrawal_requests WHERE user_id = ? AND status IN ('Approved', 'Pending')");
    $stmt->execute([$user_id]);
    $withdrawn_or_pending = $stmt->fetchColumn() ?: 0;

    $available = $total_earnings - $withdrawn_or_pending;

    if ($amount > $available) {
        throw new Exception('Insufficient balance. Available: ₹' . number_format($available, 2));
    }

    // 2. Fetch Bank Details for the request snapshot
    $stmt = $pdo->prepare("SELECT pbd.* FROM partner_bank_details pbd 
                           JOIN delivery_partners dp ON pbd.partner_id = dp.id 
                           WHERE dp.user_id = ?");
    $stmt->execute([$user_id]);
    $bank = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $bank_str = $bank ? json_encode($bank) : 'No bank details linked';

    // 3. Insert Request
    $stmt = $pdo->prepare("INSERT INTO withdrawal_requests (user_id, amount, status, bank_details) VALUES (?, ?, 'Pending', ?)");
    $stmt->execute([$user_id, $amount, $bank_str]);

    echo json_encode([
        'success' => true,
        'message' => 'Withdrawal request submitted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
