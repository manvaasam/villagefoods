<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $shop_name = !empty($_POST['shop_name']) ? trim($_POST['shop_name']) : '';
    $owner_name = !empty($_POST['owner_name']) ? trim($_POST['owner_name']) : '';
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : ''; // This is the Login Phone Number
    $email = !empty($_POST['email']) ? trim($_POST['email']) : ''; // This is the Optional Contact Email
    $address = !empty($_POST['address']) ? trim($_POST['address']) : '';
    $status = !empty($_POST['status']) ? $_POST['status'] : 'active';
    $categories = !empty($_POST['categories']) ? json_decode($_POST['categories'], true) : [];
    $latitude = !empty($_POST['latitude']) ? (float) $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float) $_POST['longitude'] : null;
    $user_id_assigned = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
    $create_vendor_account = !empty($_POST['create_vendor_account']) ? true : false;
    $vendor_password = !empty($_POST['vendor_password']) ? $_POST['vendor_password'] : '';

    if (!$shop_name) {
        throw new Exception('Shop name is required.');
    }

    if (!$phone) {
        throw new Exception('Phone number (Login) is required.');
    }

    // Check for duplicate phone in SHOPS table
    $query = "SELECT id FROM shops WHERE phone = ? " . ($id ? "AND id != ?" : "");
    $params = [$phone];
    if ($id) $params[] = $id;
    $chk = $pdo->prepare($query);
    $chk->execute($params);
    if ($chk->fetch()) {
        throw new Exception("This phone number ($phone) is already assigned to another shop.");
    }

    $image_url = null;
    if ($id) {
        $stmt = $pdo->prepare('SELECT shop_image FROM shops WHERE id = ?');
        $stmt->execute([$id]);
        $image_url = $stmt->fetchColumn();
    }

    // Handle File Upload
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../assets/images/shops/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $slug = strtolower(str_replace(' ', '-', $shop_name));
        $newFileName = 'shop_' . ($slug ?: time()) . '_' . uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.');
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            if ($image_url && file_exists('../../../' . $image_url)) {
                @unlink('../../../' . $image_url);
            }
            $image_url = 'assets/images/shops/' . $newFileName;
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }

    $pdo->beginTransaction();

    if ($create_vendor_account && $phone && $vendor_password) {
        // Check if phone already exists in USERS
        $chk = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $chk->execute([$phone]);
        $existingUser = $chk->fetch();
        if ($existingUser) {
            throw new Exception("A user with this phone number ($phone) already exists.");
        }

        $hashed = password_hash($vendor_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'vendor', ?)");
        $stmt->execute([$owner_name ?: $shop_name, $email, $hashed, $phone]);
        $user_id_assigned = $pdo->lastInsertId();
    } elseif ($id && $user_id_assigned && $vendor_password) {
        // Update existing vendor password if provided
        $hashed = password_hash($vendor_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id_assigned]);
        
        // Also update email in users table if changed
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id_assigned]);
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE shops SET shop_name = ?, owner_name = ?, phone = ?, email = ?, address = ?, status = ?, shop_image = ?, latitude = ?, longitude = ?, user_id = ? WHERE id = ?');
        $stmt->execute([$shop_name, $owner_name, $phone, $email, $address, $status, $image_url, $latitude, $longitude, $user_id_assigned, $id]);
        $message = 'Shop updated successfully';
    } else {
        $stmt = $pdo->prepare('INSERT INTO shops (shop_name, owner_name, phone, email, address, status, shop_image, latitude, longitude, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$shop_name, $owner_name, $phone, $email, $address, $status, $image_url, $latitude, $longitude, $user_id_assigned]);
        $id = $pdo->lastInsertId();
        $message = 'Shop created successfully';
    }

    // Handle Categories (Update Many-To-Many relationship)
    $stmt = $pdo->prepare('DELETE FROM shop_categories WHERE shop_id = ?');
    $stmt->execute([$id]);
    
    if (!empty($categories) && is_array($categories)) {
        $stmt = $pdo->prepare('INSERT INTO shop_categories (shop_id, category_id) VALUES (?, ?)');
        foreach ($categories as $catId) {
            $stmt->execute([$id, (int)$catId]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message, 'id' => (int) $id]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
