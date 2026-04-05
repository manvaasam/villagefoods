<?php
header('Content-Type: application/json');
require_once '../../../includes/db.php';

try {
    // Handle both JSON and Form Data
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : strtolower(str_replace(' ', '-', $name));

    if (!$name) {
        throw new Exception('Category name is required.');
    }

    $image_url = null;
    // Current image URL if editing
    if ($id) {
        $stmt = $pdo->prepare('SELECT image_url FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $image_url = $stmt->fetchColumn();
    }

    // Handle File Upload
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../assets/images/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'cat_' . ($slug ?: time()) . '_' . uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.');
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // If an old image exists and a new one was uploaded, delete the old one
            if ($image_url && file_exists('../../../' . $image_url)) {
                unlink('../../../' . $image_url);
            }
            // Success - update the URL
            $image_url = 'assets/images/categories/' . $newFileName;
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }

    if ($id) {
        // Update
        $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $image_url, $id]);
        $message = 'Category updated successfully';
    } else {
        // Create
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)');
        $stmt->execute([$name, $slug, $image_url]);
        $id = $pdo->lastInsertId();
        $message = 'Category added successfully';
    }

    echo json_encode(['success' => true, 'message' => $message, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
