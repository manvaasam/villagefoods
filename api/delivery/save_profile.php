<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth_helper.php';

header('Content-Type: application/json');

checkPersistentLogin($pdo);
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'delivery') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 0. Get Partner ID
    $stmt = $pdo->prepare("SELECT id FROM delivery_partners WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $partner = $stmt->fetch();
    if (!$partner) throw new Exception('Partner record not found');
    $partner_id = $partner['id'];

    // 1. Handle Section A: Personal Information
    if (isset($_POST['full_name'])) {
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $stmt = $pdo->prepare("UPDATE delivery_partners SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $partner_id]);
        
        // Also update users table for consistency
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);
        $_SESSION['user_name'] = $full_name;
    }

    // 2. Handle Section B: Vehicle Details
    if (isset($_POST['vehicle_type'])) {
        $v_type = $_POST['vehicle_type'];
        $v_num = trim($_POST['vehicle_number']);
        $l_num = trim($_POST['license_number']);

        $stmt = $pdo->prepare("SELECT id FROM partner_vehicle_details WHERE partner_id = ?");
        $stmt->execute([$partner_id]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE partner_vehicle_details SET vehicle_type = ?, vehicle_number = ?, license_number = ? WHERE partner_id = ?");
            $stmt->execute([$v_type, $v_num, $l_num, $partner_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO partner_vehicle_details (partner_id, vehicle_type, vehicle_number, license_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$partner_id, $v_type, $v_num, $l_num]);
        }
    }

    // 3. Handle Section C: Bank Details
    if (isset($_POST['bank_name'])) {
        $bank = trim($_POST['bank_name']);
        $holder = trim($_POST['holder_name']);
        $acc = trim($_POST['account_number']);
        $ifsc = trim($_POST['ifsc_code']);
        $upi = trim($_POST['upi_id']);

        $stmt = $pdo->prepare("SELECT id FROM partner_bank_details WHERE partner_id = ?");
        $stmt->execute([$partner_id]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE partner_bank_details SET bank_name = ?, holder_name = ?, account_number = ?, ifsc_code = ?, upi_id = ? WHERE partner_id = ?");
            $stmt->execute([$bank, $holder, $acc, $ifsc, $upi, $partner_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO partner_bank_details (partner_id, bank_name, holder_name, account_number, ifsc_code, upi_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$partner_id, $bank, $holder, $acc, $ifsc, $upi]);
        }
    }

    // 4. Handle Section D: Documents & Profile Image
    $doc_dir = '../../uploads/delivery_docs/';
    $profile_dir = '../../uploads/profile/';
    if (!is_dir($doc_dir)) mkdir($doc_dir, 0777, true);
    if (!is_dir($profile_dir)) mkdir($profile_dir, 0777, true);

    $allowed_image_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_doc_ext = array_merge($allowed_image_ext, ['pdf']);

    // Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_image_ext)) {
            throw new Exception('Invalid profile image type. Allowed: JPG, PNG, WEBP.');
        }
        $filename = "profile_{$user_id}_" . time() . ".{$ext}";
        $path = "uploads/profile/" . $filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_dir . $filename)) {
            $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
            $stmt->execute([$path, $user_id]);
        }
    }

    $doc_types = ['license_doc', 'aadhaar_doc', 'rc_doc'];
    foreach ($doc_types as $doc) {
        if (isset($_FILES[$doc]) && $_FILES[$doc]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES[$doc]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_doc_ext)) {
                throw new Exception("Invalid document type for $doc. Allowed: JPG, PNG, WEBP, PDF.");
            }
            $filename = "{$doc}_{$partner_id}_" . time() . ".{$ext}";
            $path = "uploads/delivery_docs/" . $filename;
            
            if (move_uploaded_file($_FILES[$doc]['tmp_name'], $doc_dir . $filename)) {
                $stmt = $pdo->prepare("SELECT id FROM partner_documents WHERE partner_id = ?");
                $stmt->execute([$partner_id]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE partner_documents SET {$doc} = ? WHERE partner_id = ?");
                    $stmt->execute([$path, $partner_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO partner_documents (partner_id, {$doc}) VALUES (?, ?)");
                    $stmt->execute([$partner_id, $path]);
                }
            }
        }
    }

    // 5. Automatic Status Transition Logic
    $stmt = $pdo->prepare("
        SELECT dp.full_name, dp.phone, 
               pvd.vehicle_type, pvd.vehicle_number, pvd.license_number,
               pbd.bank_name, pbd.account_number,
               pd.license_doc, pd.aadhaar_doc, pd.rc_doc
        FROM delivery_partners dp
        LEFT JOIN partner_vehicle_details pvd ON dp.id = pvd.partner_id
        LEFT JOIN partner_bank_details pbd ON dp.id = pbd.partner_id
        LEFT JOIN partner_documents pd ON dp.id = pd.partner_id
        WHERE dp.id = ?
    ");
    $stmt->execute([$partner_id]);
    $check = $stmt->fetch();

    $isComplete = true;
    $mandatory = ['full_name', 'phone', 'vehicle_type', 'vehicle_number', 'license_number', 'bank_name', 'account_number', 'license_doc', 'aadhaar_doc', 'rc_doc'];
    foreach ($mandatory as $field) {
        if (empty($check[$field])) {
            $isComplete = false;
            break;
        }
    }

    if ($isComplete) {
        $stmt = $pdo->prepare("UPDATE delivery_partners SET status = 'Verification Pending' WHERE id = ? AND status = 'Profile Incomplete'");
        $stmt->execute([$partner_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Information saved successfully', 'is_complete' => $isComplete]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
