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
        $city = trim($_POST['city'] ?? '');
        $area = trim($_POST['area'] ?? '');

        if (empty($full_name)) throw new Exception('Full Name is required');
        if (!preg_match('/^[a-zA-Z\s]{3,50}$/', $full_name)) throw new Exception('Invalid Name. Only letters and spaces allowed (3-50 chars).');
        
        if (empty($phone)) throw new Exception('Phone Number is required');
        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) throw new Exception('Invalid Phone Number. Must be 10 digits starting with 6-9.');

        if (empty($city)) throw new Exception('City is required');
        if (empty($area)) throw new Exception('Area is required');

        $stmt = $pdo->prepare("UPDATE delivery_partners SET full_name = ?, phone = ?, city = ?, area = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $city, $area, $partner_id]);
        
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

        if (empty($v_type)) throw new Exception('Vehicle Type is required');
        if (empty($v_num)) throw new Exception('Vehicle Number is required');
        if (empty($l_num)) throw new Exception('License Number is required');

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

        if (empty($bank)) throw new Exception('Bank Name is required');
        if (!preg_match('/^[a-zA-Z\s]{3,100}$/', $bank)) throw new Exception('Invalid Bank Name format');
        
        if (empty($holder)) throw new Exception('Account Holder Name is required');
        if (!preg_match('/^[a-zA-Z\s]{3,50}$/', $holder)) throw new Exception('Invalid Account Holder Name');

        if (empty($acc)) throw new Exception('Account Number is required');
        if (!preg_match('/^[0-9]{9,18}$/', $acc)) throw new Exception('Invalid Account Number. Must be 9-18 digits.');

        if (empty($ifsc)) throw new Exception('IFSC Code is required');
        if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) throw new Exception('Invalid IFSC Code format (e.g. SBIN0123456)');

        if (empty($upi)) throw new Exception('UPI ID is required');
        if (!preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $upi)) throw new Exception('Invalid UPI ID format');

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
        SELECT dp.full_name, dp.phone, dp.city, dp.area, dp.verification_status,
               pvd.vehicle_type, pvd.vehicle_number, pvd.license_number,
               pbd.bank_name, pbd.holder_name, pbd.account_number, pbd.ifsc_code, pbd.upi_id,
               pd.license_doc, pd.aadhaar_doc, pd.rc_doc,
               u.image as profile_image
        FROM delivery_partners dp
        LEFT JOIN partner_vehicle_details pvd ON dp.id = pvd.partner_id
        LEFT JOIN partner_bank_details pbd ON dp.id = pbd.partner_id
        LEFT JOIN partner_documents pd ON dp.id = pd.partner_id
        LEFT JOIN users u ON dp.user_id = u.id
        WHERE dp.id = ?
    ");
    $stmt->execute([$partner_id]);
    $check = $stmt->fetch();

    $isComplete = true;
    $mandatory = ['full_name', 'phone', 'city', 'area', 'vehicle_type', 'vehicle_number', 'license_number', 'bank_name', 'account_number', 'upi_id', 'license_doc', 'aadhaar_doc', 'rc_doc', 'profile_image'];
    foreach ($mandatory as $field) {
        if (empty($check[$field])) {
            $isComplete = false;
            break;
        }
    }

    // Logic for State Change:
    // 1. If currently Verified -> Reset to Pending (Any change)
    // 2. If currently Incomplete and now Complete -> Move to Pending
    $currentStatus = $check['verification_status'];
    
    if ($currentStatus === 'Verified') {
        $stmt = $pdo->prepare("UPDATE delivery_partners SET verification_status = 'Verification Pending' WHERE id = ?");
        $stmt->execute([$partner_id]);
    } else if ($isComplete && $currentStatus === 'Profile Incomplete') {
        $stmt = $pdo->prepare("UPDATE delivery_partners SET verification_status = 'Verification Pending' WHERE id = ?");
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
