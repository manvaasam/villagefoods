<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';
safe_session_start();
header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$category_slug = isset($_GET['category_slug']) ? $_GET['category_slug'] : null;
$params = [];

// Security: Only admins can see vendor emails or inactive shops
$is_admin = (isset($_GET['admin']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

$select = "s.*";
if ($is_admin) {
    $select .= ", u.email as vendor_email";
}

$query = "SELECT $select FROM shops s";
if ($is_admin) {
    $query .= " LEFT JOIN users u ON s.user_id = u.id";
}

if ($category_id) {
    $query .= " JOIN shop_categories sc ON s.id = sc.shop_id WHERE sc.category_id = ?";
    if (!$is_admin) $query .= " AND s.status IN ('active', 'inactive')";
    $params[] = $category_id;
} elseif ($category_slug) {
    $query .= " JOIN shop_categories sc ON s.id = sc.shop_id JOIN categories c ON sc.category_id = c.id WHERE c.slug = ?";
    if (!$is_admin) $query .= " AND s.status IN ('active', 'inactive')";
    $params[] = $category_slug;
} else {
    $query .= " WHERE 1=1";
    if (!$is_admin) $query .= " AND s.status IN ('active', 'inactive')";
}

$query .= " ORDER BY s.shop_name ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default image logic
    foreach ($shops as &$shop) {
        if (empty($shop['shop_image'])) {
            $shop['shop_image'] = 'assets/images/placeholder.png'; // Placeholder if missing
        } else {
            // Keep existing path if present
        }
        $shop['rating'] = 4.5; // Dummy rating for now
        $shop['is_open'] = ($shop['status'] === 'active'); 

        // Fetch categories mapped to this shop
        $catStmt = $pdo->prepare('SELECT category_id as id FROM shop_categories WHERE shop_id = ?');
        $catStmt->execute([$shop['id']]);
        $shop['categories'] = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['status' => 'success', 'data' => $shops]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
