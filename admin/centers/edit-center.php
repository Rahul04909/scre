<?php
require_once '../../database/config.php';

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-centers.php");
    exit;
}

$center_id = intval($_GET['id']);
$message = '';
$messageType = '';

// Helper function to safely output data and strip Xdebug error junk if present in DB
function safe_output($value) {
    if ($value === null) return '';
    $valStr = (string)$value;
    // Check if the value looks like an Xdebug error message (starts with <br />, contains xdebug-error)
    // or just generally if it starts with <br /> which is not valid for these inputs
    if (strpos($valStr, 'xdebug-error') !== false || stripos($valStr, '<br />') === 0) {
        return '';
    }
    return htmlspecialchars($valStr);
}

// Fetch Center Details
try {
    $stmt = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
    $stmt->execute([$center_id]);
    $center = $stmt->fetch();

    if (!$center) {
        die("Center not found.");
    }
} catch (PDOException $e) {
    die("Db Error: " . $e->getMessage());
}

// Fetch Active Courses
try {
    $stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) { die("Db Error"); }

// Fetch Allotted Courses
try {
    $stmtCtx = $pdo->prepare("SELECT course_id FROM center_course_allotment WHERE center_id = ?");
    $stmtCtx->execute([$center_id]);
    $allotted_courses = $stmtCtx->fetchAll(PDO::FETCH_COLUMN); // Array of course IDs
} catch (PDOException $e) { $allotted_courses = []; }

// Fetch Specific Location Data for Dropdowns
try {
    // Countries
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();

    // States (for selected country)
    $states = [];
    if($center['country']) {
        $stmtSt = $pdo->prepare("SELECT id, name FROM states WHERE country_id = ? ORDER BY name ASC");
        $stmtSt->execute([$center['country']]);
        $states = $stmtSt->fetchAll();
    }

    // Cities (for selected state)
    $cities = [];
    if($center['state']) {
        $stmtCt = $pdo->prepare("SELECT id, name FROM cities WHERE state_id = ? ORDER BY name ASC");
        $stmtCt->execute([$center['state']]);
        $cities = $stmtCt->fetchAll();
    }
} catch (PDOException $e) { die("Location DB Error"); }

// Fetch Center Documents
try {
    $stmtDocs = $pdo->prepare("SELECT * FROM center_documents WHERE center_id = ?");
    $stmtDocs->execute([$center_id]);
    $center_documents = $stmtDocs->fetchAll();
} catch (PDOException $e) { $center_documents = []; }

// --- Handle Form Submission ---
if (isset($_POST['update_center'])) {
    // 1. Basic Details
    $center_name = trim($_POST['center_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $owner_name = trim($_POST['owner_name']);
    
    // Password update (optional)
    $password_sql_part = "";
    $params_pw = [];
    if (!empty($_POST['new_password'])) {
        $hashed_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $password_sql_part = ", password = :pw";
    }

    // 2. Location
    $pincode = $_POST['pincode'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $map_url = $_POST['map_url'];

    // 3. Infra
    $num_computers = intval($_POST['num_computers']);
    $num_classrooms = intval($_POST['num_classrooms']);
    $num_staff = intval($_POST['num_staff']);
    $internet_avail = $_POST['internet_avail'];
    $power_backup = $_POST['power_backup'];
    $lab_type = $_POST['lab_type'];

    // 5. Fees & Working
    $franchise_fee = floatval($_POST['franchise_fee']);
    $royalty_percentage = floatval($_POST['royalty_percentage']);
    $weekdays = isset($_POST['working_days']) ? implode(', ', $_POST['working_days']) : '';
    $weekend_off = isset($_POST['weekend_off_days']) ? implode(', ', $_POST['weekend_off_days']) : '';
    $opening_time = $_POST['opening_time'];
    $closing_time = $_POST['closing_time'];

    // 6. Bank
    $bank_name = $_POST['bank_name'];
    $account_no = $_POST['account_no'];
    $ifsc_code = $_POST['ifsc_code'];
    $account_holder = $_POST['account_holder'];
    $branch_address = $_POST['branch_address'];

    // 7. API
    $razorpay_key = $_POST['razorpay_key'];
    $razorpay_secret = $_POST['razorpay_secret'];

    // --- File Upload Logic ---
    $uploadDir = '../../assets/uploads/centers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    function handleUpload($inputName, $existingPath, $dir, $prefix) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
            $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
            $newName = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dir . $newName)) {
                return 'assets/uploads/centers/' . $newName;
            }
        }
        return $existingPath;
    }

    $owner_image = handleUpload('owner_image', $center['owner_image'], $uploadDir, 'owner');
    $owner_sign = handleUpload('owner_sign', $center['owner_sign'], $uploadDir, 'sign');
    $center_stamp = handleUpload('center_stamp', $center['center_stamp'], $uploadDir, 'stamp');
    $auth_letter = handleUpload('auth_letter', $center['auth_letter'], $uploadDir, 'auth');
    $banner_image = handleUpload('banner_image', $center['banner_image'], $uploadDir, 'banner');
    $logo_image = handleUpload('logo_image', $center['logo_image'], $uploadDir, 'logo');
    $qr_code_1 = handleUpload('qr_code_1', $center['qr_code_1'], $uploadDir, 'qr1');
    $qr_code_2 = handleUpload('qr_code_2', $center['qr_code_2'], $uploadDir, 'qr2');
    
    // Gallery Logic
    $current_gallery = json_decode($center['gallery_images'], true) ?: [];
    // Here: Append new uploads.
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] == 0) {
                $ext = pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION);
                $newName = 'gallery_' . time() . '_' . $key . '.' . $ext;
                if (move_uploaded_file($tmp_name, $uploadDir . $newName)) {
                    $current_gallery[] = 'assets/uploads/centers/' . $newName;
                }
            }
        }
    }
    $gallery_json = json_encode($current_gallery);


    try {
        $pdo->beginTransaction();

        $sql = "UPDATE centers SET 
            center_name = :cn, email = :em, mobile = :mob, owner_name = :on,
            owner_image = :oimg, owner_sign = :osig, center_stamp = :cst, auth_letter = :auth,
            pincode = :pin, country = :ctr, state = :st, city = :ct, address = :addr, map_url = :map,
            num_computers = :nc, num_classrooms = :ncl, num_staff = :ns, internet_avail = :ia, power_backup = :pb, lab_type = :lt,
            franchise_fee = :ff, royalty_percentage = :rp,
            banner_image = :bi, logo_image = :li, gallery_images = :gi,
            weekdays = :wd, weekend_off = :weo, opening_time = :ot, closing_time = :ctm,
            bank_name = :bn, account_no = :an, ifsc_code = :ic, account_holder = :ah, branch_address = :ba,
            razorpay_key = :rk, razorpay_secret = :rs, qr_code_1 = :q1, qr_code_2 = :q2
            $password_sql_part
            WHERE id = :id";
        
        $params = [
            ':cn' => $center_name, ':em' => $email, ':mob' => $mobile, ':on' => $owner_name,
            ':oimg' => $owner_image, ':osig' => $owner_sign, ':cst' => $center_stamp, ':auth' => $auth_letter,
            ':pin' => $pincode, ':ctr' => $country, ':st' => $state, ':ct' => $city, ':addr' => $address, ':map' => $map_url,
            ':nc' => $num_computers, ':ncl' => $num_classrooms, ':ns' => $num_staff, ':ia' => $internet_avail, ':pb' => $power_backup, ':lt' => $lab_type,
            ':ff' => $franchise_fee, ':rp' => $royalty_percentage,
            ':bi' => $banner_image, ':li' => $logo_image, ':gi' => $gallery_json,
            ':wd' => $weekdays, ':weo' => $weekend_off, ':ot' => $opening_time, ':ctm' => $closing_time,
            ':bn' => $bank_name, ':an' => $account_no, ':ic' => $ifsc_code, ':ah' => $account_holder, ':ba' => $branch_address,
            ':rk' => $razorpay_key, ':rs' => $razorpay_secret, ':q1' => $qr_code_1, ':q2' => $qr_code_2,
            ':id' => $center_id
        ];

        if (!empty($_POST['new_password'])) {
            $params[':pw'] = $hashed_password;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // --- Update Documents ---
        // 1. Delete requested
        if (isset($_POST['delete_doc_ids'])) {
            $delIds = explode(',', $_POST['delete_doc_ids']);
            foreach ($delIds as $did) {
                if(intval($did) > 0) {
                     $stmtDel = $pdo->prepare("DELETE FROM center_documents WHERE id = ?");
                     $stmtDel->execute([$did]);
                     // Optionally unlink file
                }
            }
        }
        // 2. Add new
        if (isset($_POST['doc_name'])) {
            $sqlDoc = "INSERT INTO center_documents (center_id, document_name, document_number, file_path) VALUES (:cid, :dname, :dno, :dpath)";
            $stmtDoc = $pdo->prepare($sqlDoc);
            
            foreach ($_POST['doc_name'] as $key => $dName) {
                if (!empty($dName) && isset($_FILES['doc_file']['name'][$key]) && $_FILES['doc_file']['error'][$key] == 0) {
                    $dNo = $_POST['doc_number'][$key];
                    $ext = pathinfo($_FILES['doc_file']['name'][$key], PATHINFO_EXTENSION);
                    $newDocName = 'doc_' . time() . '_' . $key . '.' . $ext;
                    if (move_uploaded_file($_FILES['doc_file']['tmp_name'][$key], $uploadDir . $newDocName)) {
                        $stmtDoc->execute([
                            ':cid' => $center_id,
                            ':dname' => $dName,
                            ':dno' => $dNo,
                            ':dpath' => 'assets/uploads/centers/' . $newDocName
                        ]);
                    }
                }
            }
        }

        // --- Update Course Allotment ---
        // Strategy: Delete all for this center, re-insert
        $pdo->prepare("DELETE FROM center_course_allotment WHERE center_id = ?")->execute([$center_id]);
        
        if (isset($_POST['courses'])) {
            $sqlAllot = "INSERT INTO center_course_allotment (center_id, course_id) VALUES (:cid, :course_id)";
            $stmtAllot = $pdo->prepare($sqlAllot);
            foreach ($_POST['courses'] as $course_id) {
                $stmtAllot->execute([':cid' => $center_id, ':course_id' => $course_id]);
            }
        }

        $pdo->commit();
        $message = "Center updated successfully!";
        $messageType = "success";
        
        // Refresh Data
        $stmt = $pdo->prepare("SELECT * FROM centers WHERE id = ?");
        $stmt->execute([$center_id]);
        $center = $stmt->fetch();
        
        // Refetch Docs
        $stmtDocs->execute([$center_id]);
        $center_documents = $stmtDocs->fetchAll();

        // Refetch Allotment
        $stmtCtx->execute([$center_id]);
        $allotted_courses = $stmtCtx->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Center - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .nav-tabs .nav-link { color: #495057; font-weight: 500; }
        .select2-container .select2-selection--single { height: 38px !important; }
        .select2-container--bootstrap-5 .select2-selection { border: 1px solid #dee2e6; }
        .img-preview { max-height: 100px; margin-top: 5px; border: 1px solid #ddd; padding: 2px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Edit Center: <?php echo safe_output($center['center_name'] ?? ''); ?></h2>
                    <a href="manage-centers.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="delete_doc_ids" id="delete_doc_ids" value="">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item"><button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">Basic Info</button></li>
                                <li class="nav-item"><button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button">Location</button></li>
                                <li class="nav-item"><button class="nav-link" id="infra-tab" data-bs-toggle="tab" data-bs-target="#infra" type="button">Infrastructure</button></li>
                                <li class="nav-item"><button class="nav-link" id="legal-tab" data-bs-toggle="tab" data-bs-target="#legal" type="button">Legal & Docs</button></li>
                                <li class="nav-item"><button class="nav-link" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button">Config & Fees</button></li>
                                <li class="nav-item"><button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button">Media</button></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="myTabContent">
                                
                                <!-- 1. Basic Info -->
                                <div class="tab-pane fade show active" id="basic">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Center Code (Read Only)</label>
                                            <input type="text" class="form-control" value="<?php echo safe_output($center['center_code'] ?? ''); ?>" readonly disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Center Name <span class="text-danger">*</span></label>
                                            <input type="text" name="center_name" class="form-control" required value="<?php echo safe_output($center['center_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                            <input type="text" name="owner_name" class="form-control" required value="<?php echo safe_output($center['owner_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required value="<?php echo safe_output($center['email'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" class="form-control" required value="<?php echo safe_output($center['mobile'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Change Password (Leave blank to keep current)</label>
                                            <input type="password" name="new_password" class="form-control" placeholder="New Password">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Owner Image</label>
                                            <input type="file" name="owner_image" class="form-control" accept="image/*">
                                            <?php if($center['owner_image'] ?? false): ?>
                                                <img src="../../<?php echo $center['owner_image']; ?>" class="img-preview">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Owner Sign</label>
                                            <input type="file" name="owner_sign" class="form-control" accept="image/*">
                                            <?php if($center['owner_sign'] ?? false): ?>
                                                <img src="../../<?php echo $center['owner_sign']; ?>" class="img-preview">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Center Stamp</label>
                                            <input type="file" name="center_stamp" class="form-control" accept="image/*">
                                            <?php if($center['center_stamp'] ?? false): ?>
                                                <img src="../../<?php echo $center['center_stamp']; ?>" class="img-preview">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Location -->
                                <div class="tab-pane fade" id="location">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Country</label>
                                            <select name="country" id="country" class="form-select select2" required>
                                                <option value="">-- Select Country --</option>
                                                <?php foreach ($countries as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == ($center['country'] ?? '')) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">State</label>
                                            <select name="state" id="state" class="form-select select2" required>
                                                <option value="">-- Select State --</option>
                                                <?php foreach ($states as $s): ?>
                                                    <option value="<?php echo $s['id']; ?>" <?php echo ($s['id'] == ($center['state'] ?? '')) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($s['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">City/District</label>
                                            <select name="city" id="city" class="form-select select2" required>
                                                <option value="">-- Select City --</option>
                                                <?php foreach ($cities as $ct): ?>
                                                    <option value="<?php echo $ct['id']; ?>" <?php echo ($ct['id'] == ($center['city'] ?? '')) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($ct['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="pincode" id="pincode" class="form-control" required maxlength="6" value="<?php echo safe_output($center['pincode'] ?? ''); ?>">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Full Address</label>
                                            <textarea name="address" class="form-control" rows="2"><?php echo safe_output($center['address'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Google Maps Embed URL</label>
                                            <textarea name="map_url" class="form-control" rows="2"><?php echo htmlspecialchars($center['map_url'] ?? ''); ?></textarea>
                                        </div>
                                        <?php if($center['map_url'] ?? false): ?>
                                            <div class="col-12 mb-3">
                                                <h6>Map Preview:</h6>
                                                <div style="max-width: 400px;"><?php echo $center['map_url']; ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- 3. Infrastructure -->
                                <div class="tab-pane fade" id="infra">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Computers</label>
                                            <input type="number" name="num_computers" class="form-control" value="<?php echo safe_output($center['num_computers'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Classrooms</label>
                                            <input type="number" name="num_classrooms" class="form-control" value="<?php echo safe_output($center['num_classrooms'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Staff</label>
                                            <input type="number" name="num_staff" class="form-control" value="<?php echo safe_output($center['num_staff'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Internet Availability</label>
                                            <select name="internet_avail" class="form-select">
                                                <option value="Yes" <?php echo (($center['internet_avail'] ?? '') == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                <option value="No" <?php echo (($center['internet_avail'] ?? '') == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Power Backup</label>
                                            <select name="power_backup" class="form-select">
                                                <option value="Yes" <?php echo (($center['power_backup'] ?? '') == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                <option value="No" <?php echo (($center['power_backup'] ?? '') == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Lab Type</label>
                                            <input type="text" name="lab_type" class="form-control" value="<?php echo safe_output($center['lab_type'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Legal & Docs -->
                                <div class="tab-pane fade" id="legal">
                                    <h6 class="text-primary mb-3">Authorization</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Authorization Letter (PDF)</label>
                                        <input type="file" name="auth_letter" class="form-control" accept="application/pdf">
                                        <?php if($center['auth_letter'] ?? false): ?>
                                            <div class="mt-2">
                                                Current: <a href="../../<?php echo $center['auth_letter']; ?>" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-danger"></i> View Letter</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <hr>
                                    
                                    <h6 class="text-primary mb-3">Center Documents</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle" id="docTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Document Name</th>
                                                    <th>Document Number</th>
                                                    <th>File</th>
                                                    <th width="50px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($center_documents as $doc): ?>
                                                    <tr id="doc_row_<?php echo $doc['id']; ?>">
                                                        <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($doc['document_number']); ?></td>
                                                        <td>
                                                            <a href="../../<?php echo $doc['file_path']; ?>" target="_blank">View File</a>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger remove-existing-doc" data-id="<?php echo $doc['id']; ?>"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <!-- New Row Template -->
                                                <tr class="add-new-row-placeholder">
                                                    <td colspan="4" class="text-center bg-light">
                                                        <button type="button" class="btn btn-sm btn-success add-row"><i class="fas fa-plus me-1"></i> Add New Document</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 5. Config, Fees, Courses, Working, Bank -->
                                <div class="tab-pane fade" id="config">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success fw-bold">Financials</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Franchise Fee</label>
                                                <input type="number" step="0.01" name="franchise_fee" class="form-control" value="<?php echo safe_output($center['franchise_fee'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Royalty Percentage (%)</label>
                                                <input type="number" step="0.01" name="royalty_percentage" class="form-control" value="<?php echo safe_output($center['royalty_percentage'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success fw-bold">Working Hours</h6>
                                            <?php 
                                            // Arrays from DB string
                                            $db_weekdays = array_map('trim', explode(',', $center['weekdays'] ?? ''));
                                            $db_weekend_off = array_map('trim', explode(',', $center['weekend_off'] ?? ''));
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            ?>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label d-block">Working Days</label>
                                                    <?php foreach($days as $day): ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="<?php echo $day; ?>" id="wd_<?php echo $day; ?>" <?php echo in_array($day, $db_weekdays) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="wd_<?php echo $day; ?>"><?php echo $day; ?></label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label d-block">Weekend Off</label>
                                                    <?php foreach($days as $day): ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="weekend_off_days[]" value="<?php echo $day; ?>" id="we_<?php echo $day; ?>" <?php echo in_array($day, $db_weekend_off) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="we_<?php echo $day; ?>"><?php echo $day; ?></label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Opening</label>
                                                    <input type="time" name="opening_time" class="form-control" value="<?php echo safe_output($center['opening_time'] ?? ''); ?>">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Closing</label>
                                                    <input type="time" name="closing_time" class="form-control" value="<?php echo safe_output($center['closing_time'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">Course Allotment</h6>
                                    <div class="mb-3 p-3 bg-light rounded" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($courses as $c): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="courses[]" value="<?php echo $c['id']; ?>" id="c_<?php echo $c['id']; ?>" 
                                                <?php echo in_array($c['id'], $allotted_courses) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="c_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">Bank Details</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control" value="<?php echo safe_output($center['bank_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account No</label>
                                            <input type="text" name="account_no" class="form-control" value="<?php echo safe_output($center['account_no'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">IFSC Code</label>
                                            <input type="text" name="ifsc_code" class="form-control" value="<?php echo safe_output($center['ifsc_code'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Holder Name</label>
                                            <input type="text" name="account_holder" class="form-control" value="<?php echo safe_output($center['account_holder'] ?? ''); ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Branch Address</label>
                                            <input type="text" name="branch_address" class="form-control" value="<?php echo safe_output($center['branch_address'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">API & Payment</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Key</label>
                                            <input type="text" name="razorpay_key" class="form-control" value="<?php echo safe_output($center['razorpay_key'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Secret</label>
                                            <input type="text" name="razorpay_secret" class="form-control" value="<?php echo safe_output($center['razorpay_secret'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">QR Code 1</label>
                                            <input type="file" name="qr_code_1" class="form-control" accept="image/*">
                                            <?php if($center['qr_code_1'] ?? false): ?>
                                                <img src="../../<?php echo $center['qr_code_1']; ?>" class="img-preview">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">QR Code 2</label>
                                            <input type="file" name="qr_code_2" class="form-control" accept="image/*">
                                            <?php if($center['qr_code_2'] ?? false): ?>
                                                <img src="../../<?php echo $center['qr_code_2']; ?>" class="img-preview">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Media -->
                                <div class="tab-pane fade" id="media">
                                    <div class="mb-3">
                                        <label class="form-label">Background Banner</label>
                                        <input type="file" name="banner_image" class="form-control" accept="image/*">
                                        <?php if($center['banner_image'] ?? false): ?>
                                            <img src="../../<?php echo $center['banner_image']; ?>" class="img-preview w-100" style="max-height: 200px; object-fit:cover;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Logo</label>
                                        <input type="file" name="logo_image" class="form-control" accept="image/*">
                                        <?php if($center['logo_image'] ?? false): ?>
                                            <img src="../../<?php echo $center['logo_image']; ?>" class="img-preview">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Add Gallery Images (Appends to existing)</label>
                                        <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                                    </div>
                                    <div class="mb-3 row">
                                        <h6>Existing Gallery:</h6>
                                        <?php 
                                            $gal = json_decode($center['gallery_images'] ?? '[]', true);
                                            if($gal && is_array($gal)):
                                                foreach($gal as $g):
                                        ?>
                                            <div class="col-md-2 mb-2">
                                                <img src="../../<?php echo $g; ?>" class="img-thumbnail w-100">
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4">
                            <button type="submit" name="update_center" class="btn btn-primary w-100 btn-lg"><i class="fas fa-save me-2"></i> Update Center Details</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

            // Location Change
            $('#country').on('change', function() {
                var countryId = $(this).val();
                $('#state').empty().append('<option value="">Loading...</option>');
                $('#city').empty().append('<option value="">-- Select State First --</option>');
                if(countryId) {
                    fetch(`../locations/get-location-data.php?type=get_states&country_id=${countryId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#state').empty().append('<option value="">-- Select State --</option>');
                            data.forEach(item => { $('#state').append(`<option value="${item.id}">${item.name}</option>`); });
                        });
                }
            });

            $('#state').on('change', function() {
                var stateId = $(this).val();
                $('#city').empty().append('<option value="">Loading...</option>');
                if(stateId) {
                    fetch(`../locations/get-location-data.php?type=get_cities&state_id=${stateId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#city').empty().append('<option value="">-- Select City --</option>');
                            data.forEach(item => { $('#city').append(`<option value="${item.id}">${item.name}</option>`); });
                        });
                }
            });

            // Add New Doc Row
            $(document).on('click', '.add-row', function() {
                var html = `<tr>
                                <td><input type="text" name="doc_name[]" class="form-control" placeholder="Name"></td>
                                <td><input type="text" name="doc_number[]" class="form-control" placeholder="Num"></td>
                                <td><input type="file" name="doc_file[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
                            </tr>`;
                // Insert before the LAST row (placeholder)
                $(this).closest('tr').before(html);
            });

            // Remove New Row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Remove Existing Doc Logic
            $(document).on('click', '.remove-existing-doc', function() {
                if(confirm('Are you sure? This will delete the document upon saving.')) {
                    var id = $(this).data('id');
                    var currentIds = $('#delete_doc_ids').val();
                    var newIds = currentIds ? currentIds + ',' + id : id;
                    $('#delete_doc_ids').val(newIds);
                    $(this).closest('tr').hide(); // Visually hide
                }
            });
        });
    </script>
</body>
</html>
