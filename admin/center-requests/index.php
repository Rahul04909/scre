<?php
session_start();
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../login.php");
//     exit;
// }
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmtDel = $pdo->prepare("DELETE FROM center_requests WHERE id = ?");
    $stmtDel->execute([$id]);
    header("Location: index.php?msg=deleted");
    exit;
}

// Handle Status Change
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status']; // 'approved' or 'rejected'
    if (in_array($status, ['approved', 'rejected', 'pending'])) {
        $stmtStatus = $pdo->prepare("UPDATE center_requests SET status = ? WHERE id = ?");
        $stmtStatus->execute([$status, $id]);
        header("Location: index.php?msg=updated");
        exit;
    }
}

// Fetch Requests
$sql = "SELECT cr.*, c.name as country_name, s.name as state_name, ci.name as city_name 
        FROM center_requests cr 
        LEFT JOIN countries c ON cr.country_id = c.id 
        LEFT JOIN states s ON cr.state_id = s.id 
        LEFT JOIN cities ci ON cr.city_id = ci.id 
        ORDER BY cr.created_at DESC";
$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Center Requests - PACE Admin</title>
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
        .request-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending { background-color: #ffeeba; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        
        /* Fix for DataTables Select Arrow Overlap */
        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px !important;
            background-position: right 10px center !important;
            min-width: 60px;
        }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>

            <div class="container-fluid px-4 py-5">
                <h2 class="fw-bold mb-4" style="color: #115E59;">Center Registration Requests</h2>

                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="request-card p-4">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Center Info</th>
                                    <th>Owner Contact</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($requests as $req): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($req['center_name']); ?></div>
                                            <small class="text-muted">Owner: <?php echo htmlspecialchars($req['owner_name']); ?></small>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-phone-alt me-1 text-muted"></i> <?php echo htmlspecialchars($req['mobile']); ?></div>
                                            <div><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($req['email']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($req['city_name'] . ', ' . $req['state_name']); ?>
                                            <div class="small text-muted"><?php echo htmlspecialchars($req['pincode']); ?></div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $req['status']; ?>">
                                                <?php echo ucfirst($req['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- View Details Modal Trigger -->
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $req['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Actions -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    Action
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item text-success" href="?id=<?php echo $req['id']; ?>&status=approved">Approve</a></li>
                                                    <li><a class="dropdown-item text-warning" href="?id=<?php echo $req['id']; ?>&status=rejected">Reject</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="?delete_id=<?php echo $req['id']; ?>" onclick="return confirm('Delete this request?');">Delete</a></li>
                                                </ul>
                                            </div>

                                            <!-- Modal -->
                                            <div class="modal fade" id="viewModal<?php echo $req['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Request Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Message:</strong></p>
                                                            <div class="bg-light p-3 rounded mb-3">
                                                                <?php echo nl2br(htmlspecialchars($req['message'] ?? 'No message')); ?>
                                                            </div>
                                                            <p><strong>Address:</strong><br><?php echo htmlspecialchars($req['address']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

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
            $('#requestsTable').DataTable({
                "order": [[ 0, "desc" ]] // Sort by date desc
            });
        });
    </script>
</body>
</html>
