<?php
session_start();
require_once '../../database/config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch Allotted Courses
// Logic: Join center_course_allotment with courses
$sql = "SELECT c.* 
        FROM courses c 
        JOIN center_course_allotment cca ON c.id = cca.course_id 
        WHERE cca.center_id = :center_id 
        ORDER BY c.course_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':center_id' => $_SESSION['center_id']]);
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - PACE Center</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .card-header { background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0 !important; padding: 1.25rem 1.5rem; }
        
        .table thead th { 
            background-color: #f8fafc; 
            color: #64748b; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 0.05em; 
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem;
        }
        .table tbody td { 
            vertical-align: middle; 
            padding: 1rem; 
            color: #334155; 
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        
        .badge-soft { padding: 0.35em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
        .badge-soft-primary { background-color: #dbeafe; color: #1e40af; }
        .badge-soft-success { background-color: #dcfce7; color: #166534; }
        .badge-soft-info { background-color: #cffafe; color: #155e75; }
        
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <?php include '../sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
        <?php include '../header.php'; ?>

        <div class="container-fluid px-4 py-5">
            <h2 class="fw-bold mb-4" style="color: #115E59;">Allotted Courses</h2>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Course List</h5>
                    <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo count($courses); ?> Courses</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Course Name</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Fees</th>
                                <!-- Optional: Add actions if needed later -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($courses) > 0): ?>
                                <?php foreach ($courses as $index => $c): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded p-2 me-3 text-primary">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block"><?php echo htmlspecialchars($c['course_name']); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($c['course_code']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-info text-uppercase">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $c['course_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="far fa-clock me-1 text-muted"></i> 
                                            <?php echo $c['duration_value'] . ' ' . ucfirst($c['duration_type']); ?>
                                        </td>
                                        <td class="fw-bold">
                                            ₹<?php echo number_format($c['course_fees'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/folder-is-empty-4064360-3363921.png" width="120" alt="Empty" class="mb-3 opacity-50">
                                        <p class="text-muted mb-0">No courses allotted to this center yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sidebar.js"></script>

</body>
</html>
