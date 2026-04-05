<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['admin']);

try {
    // 1. Total Revenue (Paid Orders)
    $stmt = $pdo->query("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'Paid'");
    $revenue = $stmt->fetch()['total'] ?? 0;

    // 1b. Total Platform Profit
    $stmt = $pdo->query("SELECT SUM(platform_profit) as total FROM orders WHERE payment_status = 'Paid'");
    $platform_profit = $stmt->fetch()['total'] ?? 0;

    // 2. Total Orders
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM orders');
    $orders = $stmt->fetch()['total'] ?? 0;

    // 2b. Pending Orders (for sidebar notification)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
    $pending_orders = $stmt->fetch()['total'] ?? 0;

    // 3. Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND is_deleted = 0");
    $customers = $stmt->fetch()['total'] ?? 0;

    // 4. Online Delivery Boys
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'delivery'");
    $delivery_partners = $stmt->fetch()['total'] ?? 0;

    // 4a. Refund Pending Count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE payment_status = 'Refund Pending'");
    $refund_pending = $stmt->fetch()['total'] ?? 0;

    // 4b. Rapid Order Stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rapid_orders WHERE status = 'Requested'");
    $pending_rapid = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rapid_orders WHERE status = 'Completed'");
    $completed_rapid = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->query("SELECT SUM(price) as total FROM rapid_orders WHERE status = 'Completed'");
    $rapid_revenue = $stmt->fetch()['total'] ?? 0;

    // 5. Recent Orders (Last 5)
    $stmt = $pdo->query('SELECT o.*, u.name as customer_name,
                         COALESCE(s.shop_name, (
                             SELECT s2.shop_name 
                             FROM order_items oi2 
                             JOIN products p2 ON oi2.product_id = p2.id 
                             JOIN shops s2 ON p2.shop_id = s2.id 
                             WHERE oi2.order_id = o.id 
                             LIMIT 1
                         )) as shop_name
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         LEFT JOIN shops s ON o.shop_id = s.id
                         ORDER BY o.created_at DESC LIMIT 5');
    $recent_orders = $stmt->fetchAll();

    // 6. Revenue & Order Count by Category
    $stmt = $pdo->query("SELECT c.name, c.image_url, COUNT(DISTINCT oi.order_id) as order_count, SUM(oi.price * oi.quantity) as revenue
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         JOIN categories c ON p.category_id = c.id
                         JOIN orders o ON oi.order_id = o.id
                         WHERE o.payment_status = 'Paid'
                         GROUP BY c.id
                         ORDER BY revenue DESC");
    $category_stats = $stmt->fetchAll();

    // 7. Top Selling Products
    $stmt = $pdo->query("SELECT p.name, p.image_url, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as total_revenue
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         JOIN orders o ON oi.order_id = o.id
                         WHERE o.payment_status = 'Paid'
                         GROUP BY p.id
                         ORDER BY total_sold DESC LIMIT 5");
    $top_products = $stmt->fetchAll();

    // 8. Order Status Breakdown
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $status_breakdown = $stmt->fetchAll();

    // 9. Average Order Value (AOV)
    $stmt = $pdo->query("SELECT AVG(grand_total) as aov FROM orders WHERE payment_status = 'Paid'");
    $aov = $stmt->fetch()['aov'] ?? 0;

    // 10. Growth Comparison (This month vs Last month)
    $this_month = date('Y-m-01');
    $last_month = date('Y-m-01', strtotime('-1 month'));
    
    $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'Paid' AND created_at >= ?");
    $stmt->execute([$this_month]);
    $rev_this_month = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'Paid' AND created_at >= ? AND created_at < ?");
    $stmt->execute([$last_month, $this_month]);
    $rev_last_month = $stmt->fetch()['total'] ?? 0;
    
    $growth = 0;
    if ($rev_last_month > 0) {
        $growth = (($rev_this_month - $rev_last_month) / $rev_last_month) * 100;
    }

    // 11. Stats for Selected Range (Chart)
    $range = $_GET['range'] ?? '30d';
    $daily_stats = [];

    if ($range === '12m') {
        for ($i = 11; $i >= 0; $i--) {
            $start = date('Y-m-01', strtotime("-$i months"));
            $end = date('Y-m-t', strtotime("-$i months"));
            $month_name = date('M', strtotime($start));
            
            // Revenue
            $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'Paid' AND created_at >= ? AND created_at <= ?");
            $stmt->execute([$start . ' 00:00:00', $end . ' 23:59:59']);
            $rev = $stmt->fetch()['total'] ?? 0;
            
            // Orders
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE created_at >= ? AND created_at <= ?");
            $stmt->execute([$start . ' 00:00:00', $end . ' 23:59:59']);
            $ord = $stmt->fetch()['total'] ?? 0;

            $daily_stats[] = [
                'day' => $month_name,
                'date' => date('Y M', strtotime($start)),
                'amount' => $rev,
                'orders' => $ord
            ];
        }
    } else {
        $days = ($range === '7d') ? 6 : 29;
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $day_name = date('D', strtotime($date));
            
            // Revenue
            $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'Paid' AND DATE(created_at) = ?");
            $stmt->execute([$date]);
            $rev = $stmt->fetch()['total'] ?? 0;
            
            // Orders
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = ?");
            $stmt->execute([$date]);
            $ord = $stmt->fetch()['total'] ?? 0;

            $daily_stats[] = [
                'day' => $day_name,
                'date' => date('M d', strtotime($date)),
                'amount' => (float)$rev,
                'orders' => (int)$ord
            ];
        }
    }

    echo json_encode([
        'stats' => [
            'revenue' => number_format($revenue, 2),
            'orders' => $orders,
            'pending_orders' => $pending_orders,
            'customers' => $customers,
            'delivery_partners' => $delivery_partners,
            'aov' => number_format($aov, 2),
            'growth' => round($growth, 1),
            'pending_rapid' => $pending_rapid,
            'completed_rapid' => $completed_rapid,
            'rapid_revenue' => number_format($rapid_revenue, 2),
            'platform_profit' => number_format($platform_profit + (float)($pdo->query("SELECT SUM(platform_profit) FROM rapid_orders WHERE status = 'Completed'")->fetchColumn() ?: 0), 2),
            'refund_pending' => (int)$refund_pending
        ],
        'recent_orders' => $recent_orders,
        'category_stats' => $category_stats,
        'top_products' => $top_products,
        'status_breakdown' => $status_breakdown,
        'weekly_revenue' => $daily_stats
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
