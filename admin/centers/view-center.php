<?php
require_once '../../database/config.php';

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-centers.php");
    exit;
}

$center_id = intval($_GET['id']);

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

// Fetch Location Names
try {
    $country_name = ''; $state_name = ''; $city_name = '';
    
    if($center['country']) {
        $stmt = $pdo->prepare("SELECT name FROM countries WHERE id = ?");
        $stmt->execute([$center['country']]);
        $country_name = $stmt->fetchColumn();
    }
    if($center['state']) {
        $stmt = $pdo->prepare("SELECT name FROM states WHERE id = ?");
        $stmt->execute([$center['state']]);
        $state_name = $stmt->fetchColumn();
    }
    if($center['city']) {
        $stmt = $pdo->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->execute([$center['city']]);
        $city_name = $stmt->fetchColumn();
    }
} catch (PDOException $e) {}

// Fetch Allotted Courses
try {
    $stmtCtx = $pdo->prepare("
        SELECT c.course_name 
        FROM center_course_allotment cca 
        JOIN courses c ON cca.course_id = c.id 
        WHERE cca.center_id = ?
    ");
    $stmtCtx->execute([$center_id]);
    $allotted_courses = $stmtCtx->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) { $allotted_courses = []; }

// Fetch Documents
try {
    $stmtDocs = $pdo->prepare("SELECT * FROM center_documents WHERE center_id = ?");
    $stmtDocs->execute([$center_id]);
    $center_documents = $stmtDocs->fetchAll();
} catch (PDOException $e) { $center_documents = []; }

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
    <title>View Center - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .details-label { font-weight: 600; color: #555; }
        .details-value { color: #000; font-weight: 500; }
        .img-preview { max-height: 150px; border: 1px solid #ddd; padding: 3px; border-radius: 4px; }
        .section-title { border-left: 4px solid #0d6efd; padding-left: 10px; margin-bottom: 20px; color: #0d6efd; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Center Details</h2>
                        <p class="text-muted mb-0">View complete profile for <?php echo safe_output($center['center_name']); ?></p>
                    </div>
                    <div>
                        <a href="download-center.php?id=<?php echo $center_id; ?>" class="btn btn-danger me-2"><i class="fas fa-file-pdf me-2"></i> Download PDF</a>
                        <a href="edit-center.php?id=<?php echo $center_id; ?>" class="btn btn-primary me-2"><i class="fas fa-edit me-2"></i> Edit Center</a>
                        <a href="manage-centers.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column: Basic & Location -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Basic Information</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="details-label">Center Code</div>
                                        <div class="details-value"><?php echo safe_output($center['center_code']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Center Name</div>
                                        <div class="details-value"><?php echo safe_output($center['center_name']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Owner Name</div>
                                        <div class="details-value"><?php echo safe_output($center['owner_name']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Email</div>
                                        <div class="details-value"><?php echo safe_output($center['email']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Mobile</div>
                                        <div class="details-value"><?php echo safe_output($center['mobile']); ?></div>
                                    </div>
                                </div>

                                <h5 class="section-title">Location Details</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="details-label">Country</div>
                                        <div class="details-value"><?php echo htmlspecialchars($country_name ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">State</div>
                                        <div class="details-value"><?php echo htmlspecialchars($state_name ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">City</div>
                                        <div class="details-value"><?php echo htmlspecialchars($city_name ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Pincode</div>
                                        <div class="details-value"><?php echo safe_output($center['pincode']); ?></div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="details-label">Address</div>
                                        <div class="details-value"><?php echo safe_output($center['address']); ?></div>
                                    </div>
                                </div>

                                <h5 class="section-title">Infrastructure</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="details-label">Computers</div>
                                        <div class="details-value"><?php echo safe_output($center['num_computers']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Classrooms</div>
                                        <div class="details-value"><?php echo safe_output($center['num_classrooms']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Staff</div>
                                        <div class="details-value"><?php echo safe_output($center['num_staff']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Internet</div>
                                        <div class="details-value"><?php echo safe_output($center['internet_avail']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Power Backup</div>
                                        <div class="details-value"><?php echo safe_output($center['power_backup']); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="details-label">Lab Type</div>
                                        <div class="details-value"><?php echo safe_output($center['lab_type']); ?></div>
                                    </div>
                                </div>

                                <h5 class="section-title">Financials & Working</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="details-label">Franchise Fee</div>
                                        <div class="details-value"><?php echo safe_output($center['franchise_fee']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Royalty %</div>
                                        <div class="details-value"><?php echo safe_output($center['royalty_percentage']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Working Days</div>
                                        <div class="details-value"><?php echo safe_output($center['weekdays']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="details-label">Weekend Off</div>
                                        <div class="details-value"><?php echo safe_output($center['weekend_off']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Allotted Courses</h5>
                                <?php if(empty($allotted_courses)): ?>
                                    <p class="text-muted">No courses allotted.</p>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach($allotted_courses as $cname): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2"><?php echo htmlspecialchars($cname); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Documents</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Number</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($center['auth_letter']): ?>
                                                <tr>
                                                    <td>Authorization Letter</td>
                                                    <td>-</td>
                                                    <td><a href="../../<?php echo $center['auth_letter']; ?>" target="_blank" class="text-decoration-none">View PDF</a></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach($center_documents as $doc): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($doc['document_number']); ?></td>
                                                    <td><a href="../../<?php echo $doc['file_path']; ?>" target="_blank" class="text-decoration-none">View File</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if(empty($center_documents) && !$center['auth_letter']): ?>
                                                <tr><td colspan="3" class="text-center text-muted">No documents uploaded</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Images & Bank -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Key Images</h5>
                                <div class="row g-3">
                                    <div class="col-6 mb-3 text-center">
                                        <p class="small text-muted mb-1">Owner Image</p>
                                        <?php if($center['owner_image']): ?>
                                            <img src="../../<?php echo $center['owner_image']; ?>" class="img-preview img-fluid">
                                        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                    </div>
                                    <div class="col-6 mb-3 text-center">
                                        <p class="small text-muted mb-1">Owner Sign</p>
                                        <?php if($center['owner_sign']): ?>
                                            <img src="../../<?php echo $center['owner_sign']; ?>" class="img-preview img-fluid">
                                        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                    </div>
                                    <div class="col-6 mb-3 text-center">
                                        <p class="small text-muted mb-1">Center Stamp</p>
                                        <?php if($center['center_stamp']): ?>
                                            <img src="../../<?php echo $center['center_stamp']; ?>" class="img-preview img-fluid">
                                        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                    </div>
                                    <div class="col-6 mb-3 text-center">
                                        <p class="small text-muted mb-1">Center Logo</p>
                                        <?php if($center['logo_image']): ?>
                                            <img src="../../<?php echo $center['logo_image']; ?>" class="img-preview img-fluid">
                                        <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Bank Details</h5>
                                <div class="details-label">Bank Name</div>
                                <div class="details-value mb-2"><?php echo safe_output($center['bank_name']); ?></div>
                                
                                <div class="details-label">Account No</div>
                                <div class="details-value mb-2"><?php echo safe_output($center['account_no']); ?></div>
                                
                                <div class="details-label">IFSC Code</div>
                                <div class="details-value mb-2"><?php echo safe_output($center['ifsc_code']); ?></div>
                                
                                <div class="details-label">Account Holder</div>
                                <div class="details-value mb-2"><?php echo safe_output($center['account_holder']); ?></div>
                                
                                <div class="details-label">Branch</div>
                                <div class="details-value"><?php echo safe_output($center['branch_address']); ?></div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="section-title">Gallery</h5>
                                <div class="row g-2">
                                    <?php 
                                        $gal = json_decode($center['gallery_images'] ?? '[]', true);
                                        if($gal && is_array($gal)):
                                            foreach($gal as $g):
                                    ?>
                                        <div class="col-4">
                                            <a href="../../<?php echo $g; ?>" target="_blank">
                                                <img src="../../<?php echo $g; ?>" class="img-thumbnail w-100" style="height: 60px; object-fit: cover;">
                                            </a>
                                        </div>
                                    <?php endforeach; else: echo '<p class="text-muted small">No images</p>'; endif; ?>
                                </div>
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
