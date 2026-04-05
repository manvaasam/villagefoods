<?php

/**
 * Village Foods — Location Search API
 * GET /api/get_locations.php?q=thirupathur
 * Returns JSON array of matching locations in Thirupathur District
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/db.php';

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT id, name, area_type, district, pincode
        FROM locations
        WHERE name LIKE :q OR pincode LIKE :q2
        ORDER BY 
            CASE WHEN name LIKE :q3 THEN 0 ELSE 1 END,
            name ASC
        LIMIT 8
    ');

    $like = '%' . $q . '%';
    $starts = $q . '%';

    $stmt->bindValue(':q', $like);
    $stmt->bindValue(':q2', $like);
    $stmt->bindValue(':q3', $starts);

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
