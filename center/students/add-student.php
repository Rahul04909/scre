<?php
session_start();
require_once '../../database/config.php';
// require_once '../../vendor/autoload.php'; // If using PHPMailer later

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$message = '';
$messageType = '';

// Helper to sanitize
function clean($str) { return htmlspecialchars(trim($str ?? '')); }

// Fetch Allotted Courses for Dropdown
$stmtCourses = $pdo->prepare("
    SELECT c.id, c.course_name, c.course_code, c.course_fees, c.admission_fees, c.exam_fees_enabled, c.exam_fees 
    FROM courses c 
    JOIN center_course_allotment cca ON c.id = cca.course_id 
    WHERE cca.center_id = ?
    ORDER BY c.course_name ASC
");
$stmtCourses->execute([$center_id]);
$courses = $stmtCourses->fetchAll();

// Fetch Countries & Phone Codes
try {
    $stmtCnt = $pdo->query("SELECT id, name, sortname, phonecode FROM countries ORDER BY name ASC");
    $country_list = $stmtCnt->fetchAll();
} catch (PDOException $e) { $country_list = []; }

// Handle Form Submission
if (isset($_POST['submit_admission'])) {
    try {
        // 1. Basic Inputs
        $first_name = clean($_POST['first_name']);
        $middle_name = clean($_POST['middle_name']);
        $last_name = clean($_POST['last_name']);
        $father_name = clean($_POST['father_name']);
        $mother_name = clean($_POST['mother_name']);
        $dob = $_POST['dob'];
        
        // Gender logic
        $gender = $_POST['gender'];
        if ($gender == 'Other' && !empty($_POST['gender_other'])) {
            $gender = clean($_POST['gender_other']);
        }

        $course_id = intval($_POST['course_id']);
        $session_id = intval($_POST['session_id']); // Assuming passed from frontend logic or static for now
        $admission_mode = $_POST['admission_mode'];
        $enrollment_date = $_POST['enrollment_date'];
        
        // Contact
        $c_code = clean($_POST['country_code']);
        $mob_num = clean($_POST['mobile']);
        $mobile = '+' . $c_code . '-' . $mob_num;
        
        $email = clean($_POST['email']);
        
        // Category logic
        $category = clean($_POST['category']);
        if ($category == 'Others' && !empty($_POST['category_other'])) {
            $category = clean($_POST['category_other']);
        }

        $is_indian = 1; // Defaulting to 1 since checkbox is removed, assuming mostly valid, or remove column logic if needed. keeping 1 for schema compatibility.
        
        // Location
        $country = isset($_POST['country_id']) ? intval($_POST['country_id']) : 101; 
        $state = intval($_POST['state']);
        $city = intval($_POST['city']);
        $pincode = clean($_POST['pincode']);
        $address = clean($_POST['address']);
        
        // Emergency
        $e_name = clean($_POST['emergency_name']);
        $e_rel = clean($_POST['emergency_relation']);
        $e_mob = clean($_POST['emergency_mobile']);
        
        // Academic
        $qualification = clean($_POST['qualification']);
        if ($qualification == 'Other' && !empty($_POST['qualification_other'])) {
            $qualification = clean($_POST['qualification_other']);
        }
        $nat_id_type = clean($_POST['national_id_type']);
        $nat_id_no = clean($_POST['national_id_no']);

        // 2. Generate Enrollment No & Password
        // Fetch Session Start Year
        $stmtSession = $pdo->prepare("SELECT start_year, session_name FROM academic_sessions WHERE id = ?");
        $stmtSession->execute([$session_id]);
        $sessData = $stmtSession->fetch();
        $sessionYear = $sessData ? $sessData['start_year'] : date('Y');
        $sessionName = $sessData['session_name'];
        
        // Logic: SCRE + SessionYear + Random(4)
        $rand4 = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $enrollment_no = "SCRE" . $sessionYear . $rand4;
        
        // Password
        $password_plain = "Pass@" . rand(100,999);
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
        
        // 3. File Uploads
        $uploadDir = '../../assets/uploads/students/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        // Helpers
        function uploadFile($fileInput, $prefix, $dir) {
            if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] != 0) return null;
            $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
            $fileName = $prefix . '_' . time() . '_' . rand(100,999) . '.' . $ext;
            if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $dir . $fileName)) {
                return 'assets/uploads/students/' . $fileName;
            }
            return null;
        }
        
        $img_path = uploadFile('student_image', 'IMG', $uploadDir);
        $sign_path = uploadFile('student_signature', 'SIGN', $uploadDir);
        $nid_path = uploadFile('national_id_file', 'NID', $uploadDir);
        
        $pdo->beginTransaction();
        
        // 4. Insert Student
        $sql = "INSERT INTO students (
            center_id, enrollment_no, password, course_id, session_id, admission_mode, enrollment_date,
            first_name, middle_name, last_name, father_name, mother_name,
            email, mobile, gender, dob, category, is_indian,
            country_id, state_id, city_id, pincode, address,
            emergency_name, emergency_relation, emergency_mobile,
            qualification, national_id_type, national_id_no, national_id_file,
            student_image, student_signature
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $center_id, $enrollment_no, $password_hash, $course_id, $session_id, $admission_mode, $enrollment_date,
            $first_name, $middle_name, $last_name, $father_name, $mother_name,
            $email, $mobile, $gender, $dob, $category, $is_indian,
            $country, $state, $city, $pincode, $address,
            $e_name, $e_rel, $e_mob,
            $qualification, $nat_id_type, $nat_id_no, $nid_path,
            $img_path, $sign_path
        ]);
        
        $student_id = $pdo->lastInsertId();
        
        // 5. Handle Extra Docs (student_qualifications)
        if (isset($_FILES['extra_docs']['name'][0])) {
            $count = count($_FILES['extra_docs']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['extra_docs']['error'][$i] == 0) {
                    $dName = clean($_POST['doc_names'][$i] ?? 'Document');
                    $dNum = clean($_POST['doc_numbers'][$i] ?? '');
                    
                    $ext = pathinfo($_FILES['extra_docs']['name'][$i], PATHINFO_EXTENSION);
                    $fName = 'DOC_' . $student_id . '_' . $i . '_' . time() . '.' . $ext;
                    
                    if (move_uploaded_file($_FILES['extra_docs']['tmp_name'][$i], $uploadDir . $fName)) {
                        $fullPath = 'assets/uploads/students/' . $fName;
                        $stmtQ = $pdo->prepare("INSERT INTO student_qualifications (student_id, doc_name, doc_number, file_path) VALUES (?, ?, ?, ?)");
                        $stmtQ->execute([$student_id, $dName, $dNum, $fullPath]);
                    }
                }
            }
        }
        
        $pdo->commit();
        
        // Send Welcome Email
        try {
            // Get Center Details
            $stmtCen = $pdo->prepare("SELECT center_name, email, mobile, address FROM centers WHERE id = ?");
            $stmtCen->execute([$center_id]);
            $centerDetails = $stmtCen->fetch();

            // Get Course Details
            $stmtCrs = $pdo->prepare("SELECT course_name, course_fees, admission_fees, exam_fees, exam_fees_enabled FROM courses WHERE id = ?");
            $stmtCrs->execute([$course_id]);
            $courseDetails = $stmtCrs->fetch();

            // Calculate Fees
            $cFee = number_format($courseDetails['course_fees'], 2);
            $aFee = number_format($courseDetails['admission_fees'], 2);
            $eFee = ($courseDetails['exam_fees_enabled']) ? number_format($courseDetails['exam_fees'], 2) : '0.00';
            $totalFeeVal = $courseDetails['course_fees'] + $courseDetails['admission_fees'] + ($courseDetails['exam_fees_enabled'] ? $courseDetails['exam_fees'] : 0);
            $tFee = number_format($totalFeeVal, 2);

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

                $mail->setFrom($smtp['from_email'], $centerDetails['center_name']); 
                $mail->addAddress($email, "$first_name $last_name");

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to ' . $centerDetails['center_name'] . ' - Admission Confirmed';
                
                // Professional HTML Template
                $mail->Body = "
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #115E59; color: #fff; padding: 20px; text-align: center;'>
                            <h2 style='margin: 0;'>Welcome to {$centerDetails['center_name']}</h2>
                        </div>
                        <div style='padding: 20px;'>
                            <p>Dear <strong>$first_name $last_name</strong>,</p>
                            <p>Congratulations! Your admission has been confirmed successfully. We are thrilled to have you on board.</p>
                            
                            <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                                <h3 style='margin-top: 0; color: #115E59; border-bottom: 2px solid #ddd; padding-bottom: 5px;'>Student Details</h3>
                                <table style='width: 100%; border-collapse: collapse;'>
                                    <tr><td style='padding: 5px 0; width: 40%;'><strong>Enrollment No:</strong></td><td>$enrollment_no</td></tr>
                                    <tr><td style='padding: 5px 0;'><strong>Course:</strong></td><td>{$courseDetails['course_name']}</td></tr>
                                    <tr><td style='padding: 5px 0;'><strong>Session:</strong></td><td>$sessionName</td></tr>
                                    <tr><td style='padding: 5px 0;'><strong>Mobile:</strong></td><td>$mobile</td></tr>
                                </table>
                            </div>

                            <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                                <h3 style='margin-top: 0; color: #115E59; border-bottom: 2px solid #ddd; padding-bottom: 5px;'>Fee Structure</h3>
                                <table style='width: 100%; border-collapse: collapse;'>
                                    <tr><td style='padding: 5px 0; width: 60%;'>Course Fee:</td><td style='text-align: right;'>₹$cFee</td></tr>
                                    <tr><td style='padding: 5px 0;'>Admission Fee:</td><td style='text-align: right;'>₹$aFee</td></tr>
                                    <tr><td style='padding: 5px 0;'>Exam Fee:</td><td style='text-align: right;'>₹$eFee</td></tr>
                                    <tr style='border-top: 1px solid #ccc; font-weight: bold;'>
                                        <td style='padding: 8px 0;'>Total Payable:</td>
                                        <td style='text-align: right; padding: 8px 0;'>₹$tFee</td>
                                    </tr>
                                </table>
                            </div>

                            <div style='background-color: #e6fffa; padding: 15px; border: 1px solid #b2f5ea; border-radius: 5px; text-align: center;'>
                                <h3 style='margin-top: 0; color: #047481;'>Student Portal Access</h3>
                                <p>You can login to your dashboard to check fees, results, and more.</p>
                                <p><strong>URL:</strong> <a href='http://localhost/pace-foundation/student/login.php' style='color: #047481;'>Student Login Here</a></p>
                                <p><strong>Enrollment No:</strong> $enrollment_no</p>
                                <p><strong>Password:</strong> $password_plain</p>
                                <small style='color: #d9534f;'>Note: Please change your password after your first login.</small>
                            </div>
                        </div>
                        <div style='background-color: #eee; padding: 15px; text-align: center; font-size: 12px; color: #777;'>
                            <p><strong>{$centerDetails['center_name']}</strong><br>
                            {$centerDetails['address']}<br>
                            Phone: {$centerDetails['mobile']} | Email: {$centerDetails['email']}</p>
                        </div>
                    </div>
                </body>";

                $mail->send();
            }
        } catch (Exception $e) {
            // Log error or ignore, don't stop the flow
            // error_log("Mail Error: " . $e->getMessage());
        }
        
        $message = "Student Admitted Successfully! Enrollment No: <strong>$enrollment_no</strong>";
        $messageType = "success";
        
    } catch (Exception $e) {
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
    <title>New Admission - PACE Center</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        .section-title { color: #115E59; font-weight: 700; border-bottom: 2px solid #FCD34D; padding-bottom: 5px; margin-bottom: 20px; display: inline-block; }
        .form-label { font-weight: 500; color: #374151; font-size: 0.95rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .btn-primary { background-color: #115E59; border-color: #115E59; }
        .btn-primary:hover { background-color: #0d4a46; }
        
        .fees-box { background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <?php include '../sidebar.php'; ?>
    
    <div id="page-content-wrapper" style="margin-left: 280px; width: 100%;">
        <?php include '../header.php'; ?>
        
        <div class="container-fluid px-4 py-5">
            <h2 class="fw-bold mb-4" style="color: #115E59;">New Admission</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- 1. Course & Session -->
                <div class="card p-4">
                    <h5 class="section-title">Course & Selection</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Select Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="course_select" class="form-select" required onchange="fetchSessions(this.value)">
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" 
                                        data-fees="<?php echo $c['course_fees']; ?>"
                                        data-adm="<?php echo $c['admission_fees']; ?>"
                                        data-exam="<?php echo $c['exam_fees_enabled'] ? $c['exam_fees'] : 0; ?>">
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Select Session <span class="text-danger">*</span></label>
                            <select name="session_id" id="session_id" class="form-select" required>
                                <option value="">Select Session</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Mode</label>
                            <select name="admission_mode" class="form-select">
                                <option value="Regular">Regular</option>
                                <option value="Online">Online</option>
                                <option value="Virtual">Virtual</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" name="enrollment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <!-- Fees Display -->
                        <div class="col-md-8">
                            <div class="fees-box d-flex justify-content-between align-items-center">
                                <div><strong>Course Fee:</strong> ₹<span id="d_course">0</span></div>
                                <div><strong>+ Admission:</strong> ₹<span id="d_adm">0</span></div>
                                <div><strong>+ Exam:</strong> ₹<span id="d_exam">0</span></div>
                                <div class="fs-5 text-success fw-bold">Total: ₹<span id="d_total">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Basic Details -->
                <div class="card p-4">
                    <h5 class="section-title">Basic Details</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="country_code" class="form-select country-select" required>
                                    <?php foreach ($country_list as $cl): ?>
                                        <option value="<?php echo htmlspecialchars($cl['phonecode']); ?>" <?php echo ($cl['phonecode'] == 91) ? 'selected' : ''; ?>>
                                            +<?php echo htmlspecialchars($cl['phonecode'] . ' (' . $cl['sortname'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="mobile" class="form-control" required pattern="[0-9]{10}" placeholder="9876543210">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email ID <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required onchange="checkOtherGender(this)">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="gender_other" class="form-control mt-2 d-none" placeholder="Enter Gender">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" onchange="checkOtherCategory(this)">
                                <option value="General">General</option>
                                <option value="SC">SC</option>
                                <option value="ST">ST</option>
                                <option value="OBC">OBC</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" name="category_other" class="form-control mt-2 d-none" placeholder="Enter Category">
                        </div>
                    </div>
                </div>

                <!-- 3. Location -->
                <div class="card p-4">
                    <h5 class="section-title">Location Details</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <select name="country_id" id="country_id" class="form-select select2" onchange="fetchStates(this.value)">
                                <option value="">Select Country</option>
                                <?php foreach ($country_list as $cl): ?>
                                    <option value="<?php echo $cl['id']; ?>" <?php echo ($cl['id'] == 101) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cl['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <select name="state" id="state_id" class="form-select select2" onchange="fetchCities(this.value)">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <select name="city" id="city_id" class="form-select select2">
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Full Address</label>
                            <textarea name="address" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- 4. Emergency -->
                <div class="card p-4">
                    <h5 class="section-title">Emergency Contact</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Contact Person Name</label>
                            <input type="text" name="emergency_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Relation</label>
                            <input type="text" name="emergency_relation" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="emergency_mobile" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- 5. Academic & Docs -->
                <div class="card p-4">
                    <h5 class="section-title">Academic & Documents</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Highest Qualification</label>
                            <select name="qualification" class="form-select" onchange="checkOtherQualification(this)">
                                <option value="10th">10th Standard</option>
                                <option value="12th">12th Standard</option>
                                <option value="Graduate">Graduate</option>
                                <option value="Post Graduate">Post Graduate</option>
                                <option value="PhD">PhD</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="qualification_other" class="form-control mt-2 d-none" placeholder="Enter Highest Qualification">
                        </div>
                        
                        <div class="col-md-6"></div> <!-- Spacer -->
                        
                        <div class="col-md-4">
                            <label class="form-label">National ID Type</label>
                            <input type="text" name="national_id_type" class="form-control" placeholder="e.g. Aadhar Card" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">National ID Number</label>
                            <input type="text" name="national_id_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Upload ID (PDF)</label>
                            <input type="file" name="national_id_file" class="form-control" accept="application/pdf">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Student Photo (Image)</label>
                            <input type="file" name="student_image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student Signature (Image)</label>
                            <input type="file" name="student_signature" class="form-control" accept="image/*" required>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">Additional Documents</label>
                            <div id="docs_container">
                                <div class="row g-2 mb-2 doc-row">
                                    <div class="col-md-4">
                                        <input type="text" name="doc_names[]" class="form-control" placeholder="Document Name (e.g. 10th Marksheet)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="doc_numbers[]" class="form-control" placeholder="Document Number">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="file" name="extra_docs[]" class="form-control" accept="application/pdf">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeDoc(this)"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addDocRow()"><i class="fas fa-plus"></i> Add More Document</button>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 my-4">
                    <button type="submit" name="submit_admission" class="btn btn-primary btn-lg shadow">Confirm Admission & Generate Enrollment</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sidebar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 with Bootstrap theme
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        
        // Specific width for Country Code to be compact
        $('.country-select').select2({
            theme: 'bootstrap-5',
            width: '10px',
            containerCssClass: 'country-select-container', // Add class to target with CSS
            dropdownAutoWidth: true
        });

        // Trigger State fetch for default country (India) on load if selected
        var defaultCountry = $('#country_id').val();
        if(defaultCountry) {
            fetchStates(defaultCountry);
        }
    });

    function fetchStates(countryId) {
        if(!countryId) { 
            $('#state_id').html('<option value="">Select State</option>'); 
            $('#city_id').html('<option value="">Select City</option>');
            return; 
        }
        
        $.ajax({
            url: '../get-locations.php',
            type: 'GET',
            data: { type: 'get_states', id: countryId },
            dataType: 'json',
            success: function(response) {
                var options = '<option value="">Select State</option>';
                $.each(response, function(index, item) {
                    options += '<option value="' + item.id + '">' + item.name + '</option>';
                });
                $('#state_id').html(options);
                $('#city_id').html('<option value="">Select City</option>'); // Reset City
            }
        });
    }

    function fetchCities(stateId) {
        if(!stateId) { 
            $('#city_id').html('<option value="">Select City</option>'); 
            return; 
        }
        
        $.ajax({
            url: '../get-locations.php',
            type: 'GET',
            data: { type: 'get_cities', id: stateId },
            dataType: 'json',
            success: function(response) {
                var options = '<option value="">Select City</option>';
                $.each(response, function(index, item) {
                    options += '<option value="' + item.id + '">' + item.name + '</option>';
                });
                $('#city_id').html(options);
            }
        });
    }
    
    function updateFees() {
        var option = $('#course_select').find(':selected');
        var fees = parseFloat(option.data('fees')) || 0;
        var adm = parseFloat(option.data('adm')) || 0;
        var exam = parseFloat(option.data('exam')) || 0;
        var total = fees + adm + exam;
        
        $('#d_course').text(fees.toFixed(2));
        $('#d_adm').text(adm.toFixed(2));
        $('#d_exam').text(exam.toFixed(2));
        $('#d_total').text(total.toFixed(2));
    }
    
    function fetchSessions(courseId) {
        updateFees(); // Call fee update
        
        if(!courseId) {
            $('#session_id').html('<option value="">Select Session</option>');
            return;
        }

        $.ajax({
            url: '../get-sessions.php',
            type: 'GET',
            data: { course_id: courseId },
            dataType: 'json',
            success: function(response) {
                var options = '<option value="">Select Session</option>';
                $.each(response, function(index, item) {
                    options += '<option value="' + item.id + '">' + item.session_name + '</option>';
                });
                $('#session_id').html(options);
            }
        });
    }

    function checkOtherCategory(select) {
        if(select.value === 'Others') {
            $('input[name="category_other"]').removeClass('d-none').focus();
        } else {
            $('input[name="category_other"]').addClass('d-none');
        }
    }

    function checkOtherGender(select) {
        if(select.value === 'Other') {
            $('input[name="gender_other"]').removeClass('d-none').focus();
        } else {
            $('input[name="gender_other"]').addClass('d-none');
        }
    }

    function checkOtherQualification(select) {
        if(select.value === 'Other') {
            $('input[name="qualification_other"]').removeClass('d-none').focus();
        } else {
            $('input[name="qualification_other"]').addClass('d-none');
        }
    }
    
    function addDocRow() {
        var html = `
            <div class="row g-2 mb-2 doc-row">
                <div class="col-md-4">
                    <input type="text" name="doc_names[]" class="form-control" placeholder="Document Name">
                </div>
                <div class="col-md-4">
                    <input type="text" name="doc_numbers[]" class="form-control" placeholder="Document Number">
                </div>
                <div class="col-md-3">
                    <input type="file" name="extra_docs[]" class="form-control" accept="application/pdf">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeDoc(this)"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
        $('#docs_container').append(html);
    }
    
    function removeDoc(btn) {
        $(btn).closest('.doc-row').remove();
    }
</script>

</body>
</html>
