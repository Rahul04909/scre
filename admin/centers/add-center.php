<?php
require_once '../../database/config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php';

$message = '';
$messageType = '';

// Fetch Active Courses for Allotment
try {
    $stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Db Error: " . $e->getMessage());
}

// Fetch Countries for Location Dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Db Error: Fetching Countries failed.");
}

if (isset($_POST['add_center'])) {
    // 1. Basic Details
    $center_name = trim($_POST['center_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $owner_name = trim($_POST['owner_name']);
    
    // Auto-generate Center Code (SCRE + Year + Unique Random 3 Digits)
    $year = date('Y');
    $prefix = "SCRE{$year}";
    $is_unique = false;
    
    do {
        // Generate random 3 digits (e.g., 001 to 999)
        $rand_suffix = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $center_code = $prefix . $rand_suffix;
        
        // Check uniqueness
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM centers WHERE center_code = ?");
        $stmt_check->execute([$center_code]);
        if ($stmt_check->fetchColumn() == 0) {
            $is_unique = true;
        }
    } while (!$is_unique);

    $raw_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*"), 0, 10);
    $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

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

    // 4. Legal
    // Documents will be handled separately via 'center_documents' table

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

    function uploadFile($fileInputName, $dir, $prefix) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $ext = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
            $newName = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $dir . $newName)) {
                return 'assets/uploads/centers/' . $newName;
            }
        }
        return '';
    }

    $owner_image = uploadFile('owner_image', $uploadDir, 'owner');
    $owner_sign = uploadFile('owner_sign', $uploadDir, 'sign');
    $center_stamp = uploadFile('center_stamp', $uploadDir, 'stamp');
    $auth_letter = uploadFile('auth_letter', $uploadDir, 'auth'); // PDF
    $banner_image = uploadFile('banner_image', $uploadDir, 'banner');
    $logo_image = uploadFile('logo_image', $uploadDir, 'logo');
    $qr_code_1 = uploadFile('qr_code_1', $uploadDir, 'qr1');
    $qr_code_2 = uploadFile('qr_code_2', $uploadDir, 'qr2');
    
    // Gallery Images (Multiple)
    $gallery_images = [];
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] == 0) {
                $ext = pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION);
                $newName = 'gallery_' . time() . '_' . $key . '.' . $ext;
                if (move_uploaded_file($tmp_name, $uploadDir . $newName)) {
                    $gallery_images[] = 'assets/uploads/centers/' . $newName;
                }
            }
        }
    }
    $gallery_json = json_encode($gallery_images);

    // --- Database Insert ---
    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO centers (
            center_code, center_name, email, password, mobile, owner_name,
            owner_image, owner_sign, center_stamp, auth_letter,
            pincode, country, state, city, address, map_url,
            num_computers, num_classrooms, num_staff, internet_avail, power_backup, lab_type,
            franchise_fee, royalty_percentage,
            banner_image, logo_image, gallery_images,
            weekdays, weekend_off, opening_time, closing_time,
            bank_name, account_no, ifsc_code, account_holder, branch_address,
            razorpay_key, razorpay_secret, qr_code_1, qr_code_2
        ) VALUES (
            :cc, :cn, :em, :pw, :mob, :on,
            :oimg, :osig, :cst, :auth,
            :pin, :ctr, :st, :ct, :addr, :map,
            :nc, :ncl, :ns, :ia, :pb, :lt,
            :ff, :rp,
            :bi, :li, :gi,
            :wd, :weo, :ot, :ctm,
            :bn, :an, :ic, :ah, :ba,
            :rk, :rs, :q1, :q2
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cc' => $center_code, ':cn' => $center_name, ':em' => $email, ':pw' => $hashed_password, ':mob' => $mobile, ':on' => $owner_name,
            ':oimg' => $owner_image, ':osig' => $owner_sign, ':cst' => $center_stamp, ':auth' => $auth_letter,
            ':pin' => $pincode, ':ctr' => $country, ':st' => $state, ':ct' => $city, ':addr' => $address, ':map' => $map_url,
            ':nc' => $num_computers, ':ncl' => $num_classrooms, ':ns' => $num_staff, ':ia' => $internet_avail, ':pb' => $power_backup, ':lt' => $lab_type,
            ':ff' => $franchise_fee, ':rp' => $royalty_percentage,
            ':bi' => $banner_image, ':li' => $logo_image, ':gi' => $gallery_json,
            ':wd' => $weekdays, ':weo' => $weekend_off, ':ot' => $opening_time, ':ctm' => $closing_time,
            ':bn' => $bank_name, ':an' => $account_no, ':ic' => $ifsc_code, ':ah' => $account_holder, ':ba' => $branch_address,
            ':rk' => $razorpay_key, ':rs' => $razorpay_secret, ':q1' => $qr_code_1, ':q2' => $qr_code_2
        ]);
        
        $center_id = $pdo->lastInsertId();

        // -------------------------
        // Save Multiple Documents
        // -------------------------
        if (isset($_POST['doc_name'])) {
            $sqlDoc = "INSERT INTO center_documents (center_id, document_name, document_number, file_path) VALUES (:cid, :dname, :dno, :dpath)";
            $stmtDoc = $pdo->prepare($sqlDoc);
            
            foreach ($_POST['doc_name'] as $key => $dName) {
                // Check if name is provided and file is uploaded without error
                if (!empty($dName) && isset($_FILES['doc_file']['name'][$key]) && $_FILES['doc_file']['error'][$key] == 0) {
                    $dNo = $_POST['doc_number'][$key];
                    
                    // Upload logic
                    $ext = pathinfo($_FILES['doc_file']['name'][$key], PATHINFO_EXTENSION);
                    $newDocName = 'doc_' . time() . '_' . $key . '.' . $ext;
                    $docPath = '';
                    
                    if (move_uploaded_file($_FILES['doc_file']['tmp_name'][$key], $uploadDir . $newDocName)) {
                        $docPath = 'assets/uploads/centers/' . $newDocName;
                    }

                    if($docPath) {
                        $stmtDoc->execute([
                            ':cid' => $center_id,
                            ':dname' => $dName,
                            ':dno' => $dNo,
                            ':dpath' => $docPath
                        ]);
                    }
                }
            }
        }

        // Save Course Allotment
        if (isset($_POST['courses'])) {
            $sqlAllot = "INSERT INTO center_course_allotment (center_id, course_id) VALUES (:cid, :course_id)";
            $stmtAllot = $pdo->prepare($sqlAllot);
            foreach ($_POST['courses'] as $course_id) {
                $stmtAllot->execute([':cid' => $center_id, ':course_id' => $course_id]);
            }
        }

        $pdo->commit();

        // --- Send Email ---
        try {
            // Get SMTP Settings
            $smtpStmt = $pdo->query("SELECT * FROM smtp_settings LIMIT 1");
            $smtp = $smtpStmt->fetch();

            if ($smtp) {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['username'];
                $mail->Password = $smtp['password'];
                $mail->SMTPSecure = $smtp['encryption'];
                $mail->Port = $smtp['port'];

                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                $mail->addAddress($email, $center_name);

                // Attach Auth Letter if exists
                if ($auth_letter && file_exists('../../' . $auth_letter)) {
                    $mail->addAttachment('../../' . $auth_letter);
                }

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to ' . $smtp['from_name'] . ' - Center Registration Successful';
                $mail->Body = "
                    <h3>Congratulations! Your Center is Registered.</h3>
                    <p>Dear $owner_name,</p>
                    <p>Your center <b>$center_name</b> has been successfully registered.</p>
                    <p>Here are your login credentials:</p>
                    <ul>
                        <li><b>URL:</b> <a href='http://localhost/pace-foundation/center/login.php'>Login Here</a></li>
                        <li><b>Email:</b> $email</li>
                        <li><b>Password:</b> $raw_password</li>
                        <li><b>Center Code:</b> $center_code</li>
                    </ul>
                    <p>Please find your Authorization Letter attached.</p>
                    <br>
                    <p>Regards,<br>Admin Team</p>
                ";

                $mail->send();
            }
        } catch (Exception $e) {
            // Email failed but user added, execute silence or log
        }

        header("Location: manage-centers.php?msg=added");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Center - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .nav-tabs .nav-link { color: #495057; font-weight: 500; }
        /* Fix Select2 Height */
        .select2-container .select2-selection--single { height: 38px !important; }
        .select2-container--bootstrap-5 .select2-selection { border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add New Center</h2>
                    <a href="manage-centers.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
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
                                            <label class="form-label">Center Name <span class="text-danger">*</span></label>
                                            <input type="text" name="center_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                            <input type="text" name="owner_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required placeholder="Will be username">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Owner Image</label>
                                            <input type="file" name="owner_image" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Owner Sign</label>
                                            <input type="file" name="owner_sign" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Center Stamp</label>
                                            <input type="file" name="center_stamp" class="form-control" accept="image/*">
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
                                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">State</label>
                                            <select name="state" id="state" class="form-select select2" required>
                                                <option value="">-- Select Country First --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">City/District</label>
                                            <select name="city" id="city" class="form-select select2" required>
                                                <option value="">-- Select State First --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="pincode" id="pincode" class="form-control" required maxlength="6" placeholder="Enter Pincode">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Full Address</label>
                                            <textarea name="address" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Google Maps Embed URL</label>
                                            <textarea name="map_url" class="form-control" rows="2" placeholder='<iframe src="..." ...></iframe>'></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Infrastructure -->
                                <div class="tab-pane fade" id="infra">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Computers</label>
                                            <input type="number" name="num_computers" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Classrooms</label>
                                            <input type="number" name="num_classrooms" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">No. of Staff</label>
                                            <input type="number" name="num_staff" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Internet Availability</label>
                                            <select name="internet_avail" class="form-select">
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Power Backup</label>
                                            <select name="power_backup" class="form-select">
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Lab Type</label>
                                            <input type="text" name="lab_type" class="form-control" placeholder="e.g. Modern">
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Legal & Docs -->
                                <div class="tab-pane fade" id="legal">
                                    <h6 class="text-primary mb-3">Authorization</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Upload Authorization Letter (PDF) <span class="text-danger">*</span></label>
                                        <input type="file" name="auth_letter" class="form-control" accept="application/pdf" required>
                                        <small class="text-muted">This will be emailed to the center.</small>
                                    </div>
                                    <hr>
                                    
                                    <h6 class="text-primary mb-3">Center Documents</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle" id="docTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Document Name</th>
                                                    <th>Document Number</th>
                                                    <th>Upload File (PDF/Img)</th>
                                                    <th width="50px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="doc_name[]" class="form-control" placeholder="e.g. Aadhar Card"></td>
                                                    <td><input type="text" name="doc_number[]" class="form-control" placeholder="Optional"></td>
                                                    <td><input type="file" name="doc_file[]" class="form-control"></td>
                                                    <td><button type="button" class="btn btn-sm btn-success add-row"><i class="fas fa-plus"></i></button></td>
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
                                                <input type="number" step="0.01" name="franchise_fee" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Royalty Percentage (%)</label>
                                                <input type="number" step="0.01" name="royalty_percentage" class="form-control" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success fw-bold">Working Hours</h6>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label d-block">Working Days</label>
                                                    <?php 
                                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                    foreach($days as $day) {
                                                        echo '<div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="checkbox" name="working_days[]" value="'.$day.'" id="wd_'.$day.'">
                                                                <label class="form-check-label" for="wd_'.$day.'">'.$day.'</label>
                                                              </div>';
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label d-block">Weekend Off</label>
                                                    <?php 
                                                    foreach($days as $day) {
                                                        echo '<div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="checkbox" name="weekend_off_days[]" value="'.$day.'" id="we_'.$day.'">
                                                                <label class="form-check-label" for="we_'.$day.'">'.$day.'</label>
                                                              </div>';
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Opening</label>
                                                    <input type="time" name="opening_time" class="form-control">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Closing</label>
                                                    <input type="time" name="closing_time" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">Course Allotment</h6>
                                    <div class="mb-3 p-3 bg-light rounded" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($courses as $c): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="courses[]" value="<?php echo $c['id']; ?>" id="c_<?php echo $c['id']; ?>">
                                                <label class="form-check-label" for="c_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">Bank Details</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account No</label>
                                            <input type="text" name="account_no" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">IFSC Code</label>
                                            <input type="text" name="ifsc_code" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Holder Name</label>
                                            <input type="text" name="account_holder" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Branch Address</label>
                                            <input type="text" name="branch_address" class="form-control">
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-success fw-bold">API & Payment</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Key</label>
                                            <input type="text" name="razorpay_key" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Secret</label>
                                            <input type="text" name="razorpay_secret" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">QR Code 1</label>
                                            <input type="file" name="qr_code_1" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">QR Code 2</label>
                                            <input type="file" name="qr_code_2" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Media -->
                                <div class="tab-pane fade" id="media">
                                    <div class="mb-3">
                                        <label class="form-label">Background Banner</label>
                                        <input type="file" name="banner_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Logo</label>
                                        <input type="file" name="logo_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gallery Images (Multiple)</label>
                                        <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4">
                            <button type="submit" name="add_center" class="btn btn-primary w-100 btn-lg"><i class="fas fa-save me-2"></i> Register Center & Send Email</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Cascading Dropdowns: Country -> State
            $('#country').on('change', function() {
                var countryId = $(this).val();
                
                // Reset State & City
                $('#state').empty().append('<option value="">Loading...</option>');
                $('#city').empty().append('<option value="">-- Select State First --</option>');

                if(countryId) {
                    fetch(`../locations/get-location-data.php?type=get_states&country_id=${countryId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#state').empty().append('<option value="">-- Select State --</option>');
                            data.forEach(item => {
                                $('#state').append(`<option value="${item.id}">${item.name}</option>`);
                            });
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            $('#state').html('<option value="">Error loading states</option>');
                        });
                } else {
                    $('#state').html('<option value="">-- Select Country First --</option>');
                }
            });

            // Cascading Dropdowns: State -> City
            $('#state').on('change', function() {
                var stateId = $(this).val();
                
                // Reset City
                $('#city').empty().append('<option value="">Loading...</option>');

                if(stateId) {
                    fetch(`../locations/get-location-data.php?type=get_cities&state_id=${stateId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#city').empty().append('<option value="">-- Select City --</option>');
                            data.forEach(item => {
                                $('#city').append(`<option value="${item.id}">${item.name}</option>`);
                            });
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            $('#city').html('<option value="">Error loading cities</option>');
                        });
                } else {
                    $('#city').html('<option value="">-- Select State First --</option>');
                }
            });

            // Dynamic Document Rows
            $(document).on('click', '.add-row', function() {
                var html = `<tr>
                                <td><input type="text" name="doc_name[]" class="form-control" placeholder="e.g. PAN Card"></td>
                                <td><input type="text" name="doc_number[]" class="form-control" placeholder="Optional"></td>
                                <td><input type="file" name="doc_file[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
                            </tr>`;
                $('#docTable tbody').append(html);
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>

</body>
</html>
