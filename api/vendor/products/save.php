<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';
require_once '../../../includes/auth_helper.php';

checkPersistentLogin($pdo);
requireRole(['vendor']);

try {
    if (!isset($_SESSION['shop_id'])) {
        $stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['shop_id'] = $stmt->fetchColumn();
    }

    if (!isset($_SESSION['shop_id']) || !$_SESSION['shop_id']) {
        throw new Exception('No shop associated with this vendor account.');
    }

    $shop_id = $_SESSION['shop_id'];

    // Handle Form Data
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : 1;
    $price = !empty($_POST['price']) ? (float) $_POST['price'] : 0;
    $old_price = !empty($_POST['old_price']) ? (float) $_POST['old_price'] : null;
    $unit = !empty($_POST['unit']) ? trim($_POST['unit']) : '';
    $stock = !empty($_POST['stock']) ? (int) $_POST['stock'] : 100;
    $rating = isset($_POST['rating']) ? (float) $_POST['rating'] : 4.5;
    $is_bestseller = isset($_POST['is_bestseller']) ? (int) $_POST['is_bestseller'] : 0;

    // Clamp rating between 0 and 5
    $rating = max(0, min(5, $rating));

    if (!$name || $price < 0) {
        throw new Exception('Product name and a valid price (>= 0) are required.');
    }

    $image_url = null;
    if ($id) {
        // Verify ownership
        $stmt = $pdo->prepare('SELECT image_url, shop_id FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) throw new Exception('Product not found.');
        if ($existing['shop_id'] != $shop_id) throw new Exception('Unauthorized to edit this product.');
        
        $image_url = $existing['image_url'];
    }

    // Handle File Upload
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $slug = strtolower(str_replace(' ', '-', $name));
        $newFileName = 'prod_' . ($slug ?: time()) . '_' . uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.');
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            if ($image_url && file_exists('../../../' . $image_url)) {
                @unlink('../../../' . $image_url);
            }
            $image_url = 'assets/images/products/' . $newFileName;
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }

    if ($id) {
        $stmt = $pdo->prepare('UPDATE products SET 
            name = ?, category_id = ?, price = ?, old_price = ?, 
            unit = ?, stock = ?, rating = ?, image_url = ?, is_bestseller = ?
            WHERE id = ? AND shop_id = ?');
        $stmt->execute([$name, $category_id, $price, $old_price, $unit, $stock, $rating, $image_url, $is_bestseller, $id, $shop_id]);
        $message = 'Product updated successfully';
    } else {
        $stmt = $pdo->prepare('INSERT INTO products 
            (name, category_id, shop_id, price, old_price, unit, stock, rating, image_url, is_bestseller) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $category_id, $shop_id, $price, $old_price, $unit, $stock, $rating, $image_url, $is_bestseller]);
        $id = $pdo->lastInsertId();
        $message = 'Product added successfully';
    }

    echo json_encode(['success' => true, 'message' => $message, 'id' => (int) $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
