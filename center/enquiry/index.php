<?php
session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../database/config.php';

$center_id = $_SESSION['center_id'];

// Handle Delete (Optional - or just hide)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Ensure this application belongs to this center
    $stmtDel = $pdo->prepare("DELETE FROM applications WHERE id = ? AND center_id = ?");
    $stmtDel->execute([$id, $center_id]);
    header("Location: index.php?msg=deleted");
    exit;
}

// Fetch Enquiries for this Center
$sql = "SELECT app.*, c.course_name, ctr.name as country_name, st.name as state_name, ct.name as city_name 
        FROM applications app 
        JOIN courses c ON app.course_id = c.id 
        LEFT JOIN countries ctr ON app.country_id = ctr.id 
        LEFT JOIN states st ON app.state_id = st.id 
        LEFT JOIN cities ct ON app.city_id = ct.id 
        WHERE app.center_id = ? 
        ORDER BY app.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$center_id]);
$enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Enquiries - Center</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .enquiry-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }
        .table thead th {
            font-weight: 600;
            color: #444;
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending { background-color: #ffeeba; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>

            <div class="container-fluid px-4 py-5">
                <h2 class="fw-bold mb-4" style="color: #115E59;">Admission Enquiries</h2>

                <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Enquiry deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="enquiry-card p-4">
                    <div class="table-responsive">
                        <table id="enquiryTable" class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Applicant Name</th>
                                    <th>Course Interested</th>
                                    <th>Contact Info</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($enquiries as $enq): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($enq['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($enq['name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($enq['qualification']); ?></small>
                                        </td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($enq['course_name']); ?></span></td>
                                        <td>
                                            <div><i class="fas fa-phone-alt me-1 text-muted"></i> <?php echo htmlspecialchars($enq['mobile']); ?></div>
                                            <div><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($enq['email']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($enq['city_name'] . ', ' . $enq['state_name']); ?>
                                        </td>
                                        <td>
                                            <a href="?delete_id=<?php echo $enq['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this enquiry?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="tel:<?php echo htmlspecialchars($enq['mobile']); ?>" class="btn btn-sm btn-outline-success" title="Call">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($enq['email']); ?>" class="btn btn-sm btn-outline-primary" title="Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#enquiryTable').DataTable({
                "order": [[ 0, "desc" ]] // Sort by date desc
            });
        });
    </script>
</body>
</html>
