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
    $validity_date = !empty($_POST['validity_date']) ? $_POST['validity_date'] : null;
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
            franchise_fee, royalty_percentage, validity_date,
            banner_image, logo_image, gallery_images,
            weekdays, weekend_off, opening_time, closing_time,
            bank_name, account_no, ifsc_code, account_holder, branch_address,
            razorpay_key, razorpay_secret, qr_code_1, qr_code_2
        ) VALUES (
            :cc, :cn, :em, :pw, :mob, :on,
            :oimg, :osig, :cst, :auth,
            :pin, :ctr, :st, :ct, :addr, :map,
            :nc, :ncl, :ns, :ia, :pb, :lt,
            :ff, :rp, :vd,
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
            ':ff' => $franchise_fee, ':rp' => $royalty_percentage, ':vd' => $validity_date,
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
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper">
            <div class="container-fluid py-4 px-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 text-dark fw-bold">Add New Center</h2>
                        <div>
                            <a href="manage-centers.php" class="btn btn-secondary me-2">Back</a>
                            <button type="submit" name="add_center" class="btn btn-primary"><i class="fas fa-save me-1"></i> Register Center</button>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Main Content (Left Column) -->
                        <div class="col-lg-9">
                            
                            <!-- 1. Basic Information -->
                            <div class="card mb-4">
                                <div class="card-header">Basic Information</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Center Name <span class="text-danger">*</span></label>
                                            <input type="text" name="center_name" class="form-control" required placeholder="Enter center full name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Owner Name <span class="text-danger">*</span></label>
                                            <input type="text" name="owner_name" class="form-control" required placeholder="Center owner name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required placeholder="Will be used as username">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" name="mobile" class="form-control" required placeholder="10-digit mobile number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Location -->
                            <div class="card mb-4">
                                <div class="card-header">Location Details</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Country</label>
                                            <select name="country" id="country" class="form-select select2" required>
                                                <option value="">-- Select Country --</option>
                                                <?php foreach ($countries as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">State</label>
                                            <select name="state" id="state" class="form-select select2" required>
                                                <option value="">-- Select Country First --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">City/District</label>
                                            <select name="city" id="city" class="form-select select2" required>
                                                <option value="">-- Select State First --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Address</label>
                                            <textarea name="address" class="form-control" rows="3" placeholder="Street address, landmark..."></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Google Maps Embed URL</label>
                                            <textarea name="map_url" class="form-control" rows="3" placeholder='<iframe src="..." ...></iframe>'></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="pincode" class="form-control" required maxlength="6" placeholder="Area pincode">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Infrastructure & Facilities -->
                            <div class="card mb-4">
                                <div class="card-header">Infrastructure & Facilities</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">No. of Computers</label>
                                            <input type="number" name="num_computers" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">No. of Classrooms</label>
                                            <input type="number" name="num_classrooms" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">No. of Staff</label>
                                            <input type="number" name="num_staff" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Lab Type</label>
                                            <input type="text" name="lab_type" class="form-control" placeholder="e.g. Modern">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Internet Availability</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="internet_avail" id="internet_yes" value="Yes" checked>
                                                    <label class="form-check-label" for="internet_yes">Yes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="internet_avail" id="internet_no" value="No">
                                                    <label class="form-check-label" for="internet_no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Power Backup</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="power_backup" id="power_yes" value="Yes" checked>
                                                    <label class="form-check-label" for="power_yes">Yes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="power_backup" id="power_no" value="No">
                                                    <label class="form-check-label" for="power_no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Course Allotment -->
                            <div class="card mb-4">
                                <div class="card-header">Course Allotment</div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($courses as $c): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="courses[]" value="<?php echo $c['id']; ?>" id="c_<?php echo $c['id']; ?>">
                                                    <label class="form-check-label" for="c_<?php echo $c['id']; ?>">
                                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Bank Details -->
                            <div class="card mb-4">
                                <div class="card-header">Bank Details</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Account Number</label>
                                            <input type="text" name="account_no" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">IFSC Code</label>
                                            <input type="text" name="ifsc_code" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Account Holder</label>
                                            <input type="text" name="account_holder" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Branch Address</label>
                                            <input type="text" name="branch_address" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 6. Legal & Documents (Table) -->
                            <div class="card mb-4">
                                <div class="card-header">Center Documents</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0" id="docTable">
                                            <thead class="table-light">
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
                            </div>

                        </div>

                        <!-- Sidebar (Right Column) -->
                        <div class="col-lg-3">
                            <!-- Financials & Validity -->
                             <div class="card mb-4">
                                <div class="card-header">Config & Validity</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Validity Date</label>
                                        <!-- Validity Date Field -->
                                        <input type="date" name="validity_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+3 years')); ?>"> 
                                        <div class="form-text">Default: 3 Years from today</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Franchise Fee</label>
                                        <input type="number" step="0.01" name="franchise_fee" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Royalty (%)</label>
                                        <input type="number" step="0.01" name="royalty_percentage" class="form-control" value="0">
                                    </div>
                                </div>
                            </div>

                            <!-- key Uploads -->
                            <div class="card mb-4">
                                <div class="card-header">Key Documents</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Auth Letter (PDF) <span class="text-danger">*</span></label>
                                        <input type="file" name="auth_letter" class="form-control" accept="application/pdf" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Owner Photo</label>
                                        <input type="file" name="owner_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Owner Signature</label>
                                        <input type="file" name="owner_sign" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Center Stamp</label>
                                        <input type="file" name="center_stamp" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <!-- Media -->
                            <div class="card mb-4">
                                <div class="card-header">Branding & Media</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Center Logo</label>
                                        <input type="file" name="logo_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Banner Image</label>
                                        <input type="file" name="banner_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gallery (Multiple)</label>
                                        <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">QR Code 1</label>
                                        <input type="file" name="qr_code_1" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">QR Code 2</label>
                                        <input type="file" name="qr_code_2" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <!-- Working Hours -->
                            <div class="card mb-4">
                                <div class="card-header">Working Hours</div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label">Opening Time</label>
                                        <input type="time" name="opening_time" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Closing Time</label>
                                        <input type="time" name="closing_time" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold d-block">Working Days</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Monday" checked> <label class="form-check-label">Mon</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Tuesday" checked> <label class="form-check-label">Tue</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Wednesday" checked> <label class="form-check-label">Wed</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Thursday" checked> <label class="form-check-label">Thu</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Friday" checked> <label class="form-check-label">Fri</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_days[]" value="Saturday" checked> <label class="form-check-label">Sat</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                        });
                } else {
                    $('#state').html('<option value="">-- Select Country First --</option>');
                }
            });

            // Cascading Dropdowns: State -> City
            $('#state').on('change', function() {
                var stateId = $(this).val();
                $('#city').empty().append('<option value="">Loading...</option>');

                if(stateId) {
                    fetch(`../locations/get-location-data.php?type=get_cities&state_id=${stateId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#city').empty().append('<option value="">-- Select City --</option>');
                            data.forEach(item => {
                                $('#city').append(`<option value="${item.id}">${item.name}</option>`);
                            });
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
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button></td>
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
