<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id == 0) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageType = '';

// Helper
function clean($str) { return htmlspecialchars(trim($str ?? '')); }

// Fetch Student Data
try {
    $stmt = $pdo->prepare("SELECT s.*, c.course_name, ses.session_name 
                           FROM students s
                           LEFT JOIN courses c ON s.course_id = c.id
                           LEFT JOIN academic_sessions ses ON s.session_id = ses.id
                           WHERE s.id = ? AND s.center_id = ?");
    $stmt->execute([$student_id, $center_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found or unauthorized.");
    }

    // Fetch Qualifications
    $stmtQ = $pdo->prepare("SELECT * FROM student_qualifications WHERE student_id = ?");
    $stmtQ->execute([$student_id]);
    $qualifications = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Fetch Countries (for dropdown)
try {
    $stmtCnt = $pdo->query("SELECT id, name, sortname, phonecode FROM countries ORDER BY name ASC");
    $country_list = $stmtCnt->fetchAll();
} catch (PDOException $e) { $country_list = []; }


// Handle Update
if (isset($_POST['update_student'])) {
    try {
        // Basic Fields
        $first_name = clean($_POST['first_name']);
        $middle_name = clean($_POST['middle_name']);
        $last_name = clean($_POST['last_name']);
        $father_name = clean($_POST['father_name']);
        $mother_name = clean($_POST['mother_name']);
        $dob = $_POST['dob'];
        
        $gender = $_POST['gender'];
        if ($gender == 'Other' && !empty($_POST['gender_other'])) {
            $gender = clean($_POST['gender_other']);
        }
        
        // Contact
        $c_code = clean($_POST['country_code']);
        $mob_num = clean($_POST['mobile']);
        $mobile = '+' . $c_code . '-' . $mob_num;
        
        $email = clean($_POST['email']);
        
        $category = $_POST['category'];
        if ($category == 'Others' && !empty($_POST['category_other'])) {
            $category = clean($_POST['category_other']);
        }
        
        // Location
        $country_id = intval($_POST['country_id']);
        $state_id = intval($_POST['state']);
        $city_id = intval($_POST['city']);
        $pincode = clean($_POST['pincode']);
        $address = clean($_POST['address']);
        
        // Emergency
        $e_name = clean($_POST['emergency_name']);
        $e_rel = clean($_POST['emergency_relation']);
        $e_mob = clean($_POST['emergency_mobile']);
        
        // Academic (Non-Course/Session)
        $qualification = clean($_POST['qualification']);
        if ($qualification == 'Other' && !empty($_POST['qualification_other'])) {
            $qualification = clean($_POST['qualification_other']);
        }
        $nat_id_type = clean($_POST['national_id_type']);
        $nat_id_no = clean($_POST['national_id_no']);
        
        $uploadDir = '../../assets/uploads/students/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // File Upload Helper
        function handleUpload($inputName, $prefix, $currentPath, $dir) {
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
                $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
                $fileName = $prefix . '_' . time() . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dir . $fileName)) {
                    return 'assets/uploads/students/' . $fileName;
                }
            }
            return $currentPath;
        }

        $img_path = handleUpload('student_image', 'IMG', $student['student_image'], $uploadDir);
        $sign_path = handleUpload('student_signature', 'SIGN', $student['student_signature'], $uploadDir);
        $nid_path = handleUpload('national_id_file', 'NID', $student['national_id_file'], $uploadDir);

        $session_id = intval($_POST['session_id']);
        $admission_mode = clean($_POST['admission_mode']);
        $enrollment_date = clean($_POST['enrollment_date']);

        $pdo->beginTransaction();

        $sql = "UPDATE students SET 
                session_id=?, admission_mode=?, enrollment_date=?,
                first_name=?, middle_name=?, last_name=?, father_name=?, mother_name=?,
                email=?, mobile=?, gender=?, dob=?, category=?,
                country_id=?, state_id=?, city_id=?, pincode=?, address=?,
                emergency_name=?, emergency_relation=?, emergency_mobile=?,
                qualification=?, national_id_type=?, national_id_no=?, national_id_file=?,
                student_image=?, student_signature=?
                WHERE id=? AND center_id=?";
        
        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute([
            $session_id, $admission_mode, $enrollment_date,
            $first_name, $middle_name, $last_name, $father_name, $mother_name,
            $email, $mobile, $gender, $dob, $category,
            $country_id, $state_id, $city_id, $pincode, $address,
            $e_name, $e_rel, $e_mob,
            $qualification, $nat_id_type, $nat_id_no, $nid_path,
            $img_path, $sign_path,
            $student_id, $center_id
        ]);

        // Handle New Extra Docs
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
        $message = "Student Details Updated Successfully!";
        $messageType = "success";
        
        // Refresh Data
        $stmt->execute([$student_id, $center_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Re-Fetch Qualifications
        $stmtQ = $pdo->prepare("SELECT * FROM student_qualifications WHERE student_id = ?");
        $stmtQ->execute([$student_id]);
        $qualifications = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Logic to parse mobile number
$mob_parts = explode('-', $student['mobile']);
$sc_code = '91'; 
$sc_num = '';
if (count($mob_parts) == 2) {
    $sc_code = str_replace('+', '', $mob_parts[0]);
    $sc_num = $mob_parts[1];
} else {
    $sc_num = $student['mobile']; 
}

// Logic for Gender/Category 'Other' check
$curr_gender = $student['gender'];
$gender_is_other = !in_array($curr_gender, ['Male', 'Female']);
$gender_select = $gender_is_other ? 'Other' : $curr_gender;
$gender_other_val = $gender_is_other ? $curr_gender : '';

$curr_cat = $student['category'];
$cat_is_other = !in_array($curr_cat, ['General', 'SC', 'ST', 'OBC']);
$cat_select = $cat_is_other ? 'Others' : $curr_cat;
$cat_other_val = $cat_is_other ? $curr_cat : '';

$curr_qual = $student['qualification'];
$qual_standards = ['10th', '12th', 'Graduate', 'Post Graduate', 'PhD'];
$qual_is_other = !in_array($curr_qual, $qual_standards);
$qual_select = $qual_is_other ? 'Other' : $curr_qual;
$qual_other_val = $qual_is_other ? $curr_qual : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - <?php echo htmlspecialchars($student['first_name']); ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        .section-title { color: #115E59; font-weight: 700; border-bottom: 2px solid #FCD34D; padding-bottom: 5px; margin-bottom: 20px; display: inline-block; }
        .form-label { font-weight: 500; color: #374151; font-size: 0.95rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .btn-primary { background-color: #115E59; border-color: #115E59; }
        .btn-primary:hover { background-color: #0d4a46; }
        .preview-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <?php include '../sidebar.php'; ?>
    
    <div id="page-content-wrapper" style="margin-left: 280px; width: 100%;">
        <?php include '../header.php'; ?>
        
        <div class="container-fluid px-4 py-5">
             <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold" style="color: #115E59;">Edit Student</h2>
                <a href="view-student.php?id=<?php echo $student_id; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to View</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- 1. Academic Info -->
                <div class="card p-4">
                    <h5 class="section-title">Academic Info</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Enrollment No</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['enrollment_no'] ?? ''); ?>" readonly disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['course_name'] ?? ''); ?>" readonly disabled>
                            <!-- Hidden input to keep course_id if needed, though we don't update it -->
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Session</label>
                            <select name="session_id" class="form-select" required>
                                <?php 
                                    // Fetch sessions for this course
                                    try {
                                        $stmtS = $pdo->prepare("SELECT id, session_name FROM academic_sessions WHERE course_id = ? AND is_active = 1");
                                        $stmtS->execute([$student['course_id']]);
                                        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);
                                        foreach($sessions as $sess) {
                                            $selected = ($sess['id'] == $student['session_id']) ? 'selected' : '';
                                            echo "<option value='{$sess['id']}' $selected>" . htmlspecialchars($sess['session_name']) . "</option>";
                                        }
                                        // Also add current session if it's inactive/hidden but currently assigned
                                        if (!$sessions && $student['session_id']) {
                                             echo "<option value='{$student['session_id']}' selected>" . htmlspecialchars($student['session_name']) . " (Current)</option>";
                                        }
                                    } catch(PDOException $e) {}
                                ?>
                            </select>
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Admission Mode</label>
                            <select name="admission_mode" class="form-select" required>
                                <?php 
                                    $modes = ['Regular', 'Online', 'Virtual', 'Offline'];
                                    foreach($modes as $m) {
                                        $sel = ($student['admission_mode'] == $m) ? 'selected' : '';
                                        echo "<option value='$m' $sel>$m</option>";
                                    }
                                ?>
                            </select>
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" name="enrollment_date" class="form-control" value="<?php echo htmlspecialchars($student['enrollment_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- 2. Basic Details -->
                <div class="card p-4">
                    <h5 class="section-title">Basic Details</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control" value="<?php echo htmlspecialchars($student['mother_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="country_code" class="form-select country-select" required>
                                    <?php foreach ($country_list as $cl): ?>
                                        <option value="<?php echo htmlspecialchars($cl['phonecode']); ?>" <?php echo ($cl['phonecode'] == $sc_code) ? 'selected' : ''; ?>>
                                            +<?php echo htmlspecialchars($cl['phonecode'] . ' (' . $cl['sortname'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="mobile" class="form-control" required pattern="[0-9]{10}" value="<?php echo htmlspecialchars($sc_num ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email ID <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required onchange="checkOtherGender(this)">
                                <option value="Male" <?php echo ($gender_select == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($gender_select == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($gender_select == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <input type="text" name="gender_other" class="form-control mt-2 <?php echo ($gender_select == 'Other') ? '' : 'd-none'; ?>" 
                                   placeholder="Enter Gender" value="<?php echo htmlspecialchars($gender_other_val); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($student['dob'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" onchange="checkOtherCategory(this)">
                                <option value="General" <?php echo ($cat_select == 'General') ? 'selected' : ''; ?>>General</option>
                                <option value="SC" <?php echo ($cat_select == 'SC') ? 'selected' : ''; ?>>SC</option>
                                <option value="ST" <?php echo ($cat_select == 'ST') ? 'selected' : ''; ?>>ST</option>
                                <option value="OBC" <?php echo ($cat_select == 'OBC') ? 'selected' : ''; ?>>OBC</option>
                                <option value="Others" <?php echo ($cat_select == 'Others') ? 'selected' : ''; ?>>Others</option>
                            </select>
                            <input type="text" name="category_other" class="form-control mt-2 <?php echo ($cat_select == 'Others') ? '' : 'd-none'; ?>" 
                                   placeholder="Enter Category" value="<?php echo htmlspecialchars($cat_other_val); ?>">
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
                                    <option value="<?php echo $cl['id']; ?>" <?php echo ((string)$cl['id'] === (string)$student['country_id']) ? 'selected' : ''; ?>>
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
                            <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($student['pincode'] ?? ''); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Full Address</label>
                            <textarea name="address" class="form-control" rows="1"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- 4. Emergency -->
                <div class="card p-4">
                    <h5 class="section-title">Emergency Contact</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Contact Person Name</label>
                            <input type="text" name="emergency_name" class="form-control" value="<?php echo htmlspecialchars($student['emergency_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Relation</label>
                            <input type="text" name="emergency_relation" class="form-control" value="<?php echo htmlspecialchars($student['emergency_relation'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile No</label>
                            <input type="text" name="emergency_mobile" class="form-control" value="<?php echo htmlspecialchars($student['emergency_mobile'] ?? ''); ?>">
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
                                <?php 
                                    $quals = ['10th', '12th', 'Graduate', 'Post Graduate', 'PhD', 'Other'];
                                    foreach($quals as $q) {
                                        $sel = ($qual_select == $q) ? 'selected' : '';
                                        echo "<option value='$q' $sel>$q</option>";
                                    }
                                ?>
                            </select>
                            <input type="text" name="qualification_other" class="form-control mt-2 <?php echo ($qual_select == 'Other') ? '' : 'd-none'; ?>" 
                                   placeholder="Enter Highest Qualification" value="<?php echo htmlspecialchars($qual_other_val); ?>">
                        </div>
                        
                        <div class="col-md-6"></div> 
                        
                        <div class="col-md-4">
                            <label class="form-label">National ID Type</label>
                            <input type="text" name="national_id_type" class="form-control" value="<?php echo htmlspecialchars($student['national_id_type'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">National ID Number</label>
                            <input type="text" name="national_id_no" class="form-control" value="<?php echo htmlspecialchars($student['national_id_no'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Create/Update ID (PDF)</label>
                            <?php if(!empty($student['national_id_file'])): ?>
                                <div class="mb-2"><a href="../../<?php echo $student['national_id_file']; ?>" target="_blank" class="text-primary small"><i class="fas fa-file-pdf"></i> Current File</a></div>
                            <?php endif; ?>
                            <input type="file" name="national_id_file" class="form-control" accept="application/pdf">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Student Photo (Image)</label>
                            <div class="d-flex align-items-center mb-2">
                                <?php if(!empty($student['student_image'])): ?>
                                    <img src="../../<?php echo $student['student_image']; ?>" class="preview-img me-3">
                                <?php endif; ?>
                            </div>
                            <input type="file" name="student_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student Signature (Image)</label>
                            <div class="d-flex align-items-center mb-2">
                                <?php if(!empty($student['student_signature'])): ?>
                                    <img src="../../<?php echo $student['student_signature']; ?>" class="preview-img me-3">
                                <?php endif; ?>
                            </div>
                            <input type="file" name="student_signature" class="form-control" accept="image/*">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">Additional Documents</label>
                            
                            <!-- Existing Docs -->
                            <?php if(!empty($qualifications)): ?>
                                <div class="mb-3">
                                    <h6>Existing Documents:</h6>
                                    <ul class="list-group">
                                        <?php foreach($qualifications as $q): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <strong><?php echo htmlspecialchars($q['doc_name']); ?></strong> 
                                                    (<?php echo htmlspecialchars($q['doc_number']); ?>)
                                                    <a href="../../<?php echo $q['file_path']; ?>" target="_blank" class="ms-2"><i class="fas fa-external-link-alt"></i></a>
                                                </span>
                                                <!-- Could add Delete button here later -->
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div id="docs_container">
                                <!-- JS will add inputs here -->
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addDocRow()"><i class="fas fa-plus"></i> Add New Document</button>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 my-4">
                    <button type="submit" name="update_student" class="btn btn-primary btn-lg shadow">Update Student Details</button>
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
    var loadedState = "<?php echo $student['state_id']; ?>";
    var loadedCity = "<?php echo $student['city_id']; ?>";

    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        
        $('.country-select').select2({
            theme: 'bootstrap-5',
            width: '100px',
            dropdownAutoWidth: true
        });

        // Trigger State fetch for current country
        var defaultCountry = $('#country_id').val();
        if(defaultCountry) {
            fetchStates(defaultCountry, loadedState, loadedCity);
        }
    });

    function fetchStates(countryId, preSelectedState = null, preSelectedCity = null) {
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
                    var selected = (preSelectedState && item.id == preSelectedState) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                });
                $('#state_id').html(options);
                
                // If we set a state, fetch cities too
                if(preSelectedState) {
                    fetchCities(preSelectedState, preSelectedCity);
                }
            }
        });
    }

    function fetchCities(stateId, preSelectedCity = null) {
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
                    var selected = (preSelectedCity && item.id == preSelectedCity) ? 'selected' : '';
                    options += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                });
                $('#city_id').html(options);
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
