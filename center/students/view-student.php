<?php
session_start();
require_once '../../database/config.php';

// Check Auth
if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$student_id = intval($_GET['id']);

// Fetch Student Details
try {
    $sql = "SELECT s.*, c.course_name, ses.session_name,
            co.name as country_name, st.name as state_name, ci.name as city_name
            FROM students s 
            LEFT JOIN courses c ON s.course_id = c.id 
            LEFT JOIN academic_sessions ses ON s.session_id = ses.id
            LEFT JOIN countries co ON s.country_id = co.id
            LEFT JOIN states st ON s.state_id = st.id
            LEFT JOIN cities ci ON s.city_id = ci.id
            WHERE s.id = ? AND s.center_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $center_id]);
    $student = $stmt->fetch();

    if (!$student) {
        die("Student not found or unauthorized access.");
    }
} catch (PDOException $e) {
    die("Db Error: " . $e->getMessage());
}

// Fetch Qualifications
try {
    $stmtQual = $pdo->prepare("SELECT * FROM student_qualifications WHERE student_id = ?");
    $stmtQual->execute([$student_id]);
    $qualifications = $stmtQual->fetchAll();
} catch (PDOException $e) { $qualifications = []; }

// Helper for safe output
function safe_output($value) {
    if ($value === null) return '-';
    $valStr = (string)$value;
    if (strpos($valStr, 'xdebug-error') !== false || stripos($valStr, '<br />') === 0) {
        return '-';
    }
    return htmlspecialchars($valStr) ?: '-';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student - <?php echo safe_output($student['first_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .details-label { font-weight: 600; color: #555; font-size: 0.9rem; }
        .details-value { color: #000; font-weight: 500; font-size: 0.95rem; word-break: break-word; }
        .student-photo { width: 130px; height: 130px; object-fit: cover; border: none; box-shadow: none; border-radius: 10px; }
        .section-title { border-left: 4px solid #115E59; padding-left: 10px; margin-bottom: 20px; color: #115E59; font-weight: 700; }
        .card { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px; width: 100%;">
            <?php include '../header.php'; ?>
            
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1 fw-bold text-dark">Student Profile</h2>
                        <p class="text-muted mb-0">Enrollment No: <span class="fw-bold text-primary"><?php echo safe_output($student['enrollment_no']); ?></span></p>
                    </div>
                    <div>
                        <a href="download-student-pdf.php?id=<?php echo $student_id; ?>" class="btn btn-success me-2"><i class="fas fa-file-pdf me-2"></i> Download Profile</a>
                        <!-- <a href="#" class="btn btn-danger me-2"><i class="fas fa-file-pdf me-2"></i> Download ID Card</a> -->
                        <a href="edit-student.php?id=<?php echo $student_id; ?>" class="btn btn-primary me-2"><i class="fas fa-edit me-2"></i> Edit Profile</a>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column: Details -->
                    <div class="col-lg-8">
                        <!-- Personal Info -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="section-title">Personal Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-3 d-flex flex-column align-items-center align-items-md-start mb-3 mb-md-0" style="background: none;">
                                        <?php 
                                            $img_url = !empty($student['student_image']) ? '../../'.$student['student_image'] : 'https://ui-avatars.com/api/?name='.urlencode($student['first_name']);
                                        ?>
                                        <img src="<?php echo $img_url; ?>" class="student-photo mb-2">
                                        <div class="mt-2">
                                            <span class="badge bg-<?php echo ($student['status'] == 'Active') ? 'success' : 'danger'; ?> px-3 py-2"><?php echo $student['status']; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="details-label">First Name</div>
                                                <div class="details-value"><?php echo safe_output($student['first_name']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Last Name</div>
                                                <div class="details-value"><?php echo safe_output($student['last_name']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Date of Birth</div>
                                                <div class="details-value"><?php echo safe_output($student['dob']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Gender</div>
                                                <div class="details-value"><?php echo safe_output($student['gender']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Father's Name</div>
                                                <div class="details-value"><?php echo safe_output($student['father_name']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Mother's Name</div>
                                                <div class="details-value"><?php echo safe_output($student['mother_name']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Category</div>
                                                <div class="details-value"><?php echo safe_output($student['category']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">Highest Qualification</div>
                                                <div class="details-value"><?php echo safe_output($student['qualification']); ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-label">National ID (<?php echo safe_output($student['national_id_type']); ?>)</div>
                                                <div class="details-value"><?php echo safe_output($student['national_id_no']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Info -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="section-title">Academic Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="details-label">Course</div>
                                        <div class="details-value text-primary fw-bold"><?php echo safe_output($student['course_name']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Session</div>
                                        <div class="details-value"><?php echo safe_output($student['session_name']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Admission Mode</div>
                                        <div class="details-value"><?php echo safe_output($student['admission_mode']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Enrollment Date</div>
                                        <div class="details-value"><?php echo safe_output($student['enrollment_date']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="section-title">Contact & Location</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="details-label">Mobile Number</div>
                                        <div class="details-value"><?php echo safe_output($student['mobile']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Email Address</div>
                                        <div class="details-value"><?php echo safe_output($student['email']); ?></div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="details-label">Address</div>
                                        <div class="details-value"><?php echo safe_output($student['address']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Location</div>
                                        <div class="details-value">
                                            <?php echo safe_output($student['city_name']); ?>, 
                                            <?php echo safe_output($student['state_name']); ?>, 
                                            <?php echo safe_output($student['country_name']); ?> - 
                                            <?php echo safe_output($student['pincode']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                         <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="section-title">Emergency Contact</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="details-label">Person Name</div>
                                        <div class="details-value"><?php echo safe_output($student['emergency_name']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Relation</div>
                                        <div class="details-value"><?php echo safe_output($student['emergency_relation']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Mobile No</div>
                                        <div class="details-value"><?php echo safe_output($student['emergency_mobile']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Documents -->
                    <div class="col-lg-4">
                         <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="section-title h6">ID / Documents</h5>
                                <p class="small text-muted mb-3">Attached files & proofs</p>
                                
                                <div class="d-grid gap-3">
                                    <?php if(!empty($student['student_image'])): ?>
                                        <a href="../../<?php echo $student['student_image']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm text-start py-2">
                                            <i class="fas fa-image me-2 text-primary"></i> Student Photo
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($student['student_signature'])): ?>
                                        <a href="../../<?php echo $student['student_signature']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm text-start py-2">
                                            <i class="fas fa-file-signature me-2 text-primary"></i> Student Signature
                                        </a>
                                    <?php endif; ?>

                                    <?php if(!empty($student['national_id_file'])): ?>
                                        <a href="../../<?php echo $student['national_id_file']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm text-start py-2">
                                            <i class="fas fa-id-card me-2 text-primary"></i> National ID (PDF)
                                        </a>
                                    <?php endif; ?>

                                     <?php foreach($qualifications as $q): ?>
                                        <?php if(!empty($q['file_path'])): ?>
                                            <a href="../../<?php echo $q['file_path']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm text-start py-2">
                                                <i class="fas fa-file-alt me-2 text-primary"></i> <?php echo safe_output($q['doc_name']); ?> (<?php echo safe_output($q['doc_number']); ?>)
                                            </a>
                                        <?php endif; ?>
                                     <?php endforeach; ?>
                                </div>
                                <?php if(empty($qualifications) && empty($student['national_id_file'])): ?>
                                    <small class="text-muted d-block text-center mt-3">No documents available.</small>
                                <?php endif; ?>

                            </div>
                         </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
