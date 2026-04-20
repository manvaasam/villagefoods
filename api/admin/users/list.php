<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $role = $_GET['role'] ?? 'customer';
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? 'all';

    $where = ["u.role = ?", "u.is_deleted = 0"];
    $params = [$role];

    if (!empty($search)) {
        $where[] = "(u.name LIKE ? OR u.phone LIKE ? OR u.email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($filter === 'blocked') {
        // Find blocked regardless of is_deleted if we want to show them? 
        // Actually the prompt says "Soft delete only", so is_deleted=1 are hidden.
        // Blocked are status='Blocked'
        $where[count($where)-1] = "u.status = 'Blocked'"; // Override is_deleted=0 check if needed? No, prompt says soft delete hides them.
        $where[] = "u.is_deleted = 0";
    } elseif ($filter === 'new') {
        $where[] = "u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($filter === 'verified' && $role === 'delivery') {
        $where[] = "dp.verification_status = 'Verified'";
    } elseif ($filter === 'pending' && $role === 'delivery') {
        $where[] = "dp.verification_status = 'Verification Pending'";
    }

    $orderBy = "u.created_at DESC";
    if ($filter === 'top') {
        $orderBy = "total_spent DESC";
    }

    $whereClause = implode(" AND ", $where);

    if ($role === 'delivery') {
        $query = "SELECT u.*, u.created_at AS joined_at,
                         u.name as name, dp.city, dp.area,
                         COALESCE(NULLIF(u.phone, ''), (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as phone,
                         (SELECT COUNT(*) FROM orders WHERE delivery_boy_id = u.id AND status = 'Delivered') as total_deliveries,
                         (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE delivery_boy_id = u.id AND status = 'Delivered') as total_earned,
                         (SELECT COUNT(*) FROM orders WHERE delivery_boy_id = u.id AND status NOT IN ('Delivered', 'Cancelled')) as active_orders,
                         (SELECT COUNT(*) FROM rapid_orders WHERE delivery_boy_id = u.id AND status NOT IN ('Completed', 'Cancelled')) as active_rapid_orders,
                         dp.verification_status,
                         COALESCE(dd.is_online, 0) as is_online

                  FROM users u 
                  LEFT JOIN delivery_partners dp ON u.id = dp.user_id
                  LEFT JOIN delivery_details dd ON u.id = dd.user_id
                  WHERE $whereClause 
                  ORDER BY $orderBy
                  LIMIT 100";
    } else {

        $query = "SELECT u.*, u.created_at AS joined_at,
                         u.name as name,
                         COALESCE(NULLIF(u.phone, ''), (SELECT contact_number FROM user_addresses WHERE user_id = u.id ORDER BY id DESC LIMIT 1)) as phone,
                         COUNT(o.id) as total_orders, 
                         COALESCE(SUM(o.total_amount), 0) as total_spent 
                  FROM users u 
                  LEFT JOIN orders o ON u.id = o.user_id 
                  WHERE $whereClause 
                  GROUP BY u.id 
                  ORDER BY $orderBy
                  LIMIT 100";
    }
              
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
