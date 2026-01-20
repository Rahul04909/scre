<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM centers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-centers.php?msg=deleted");
        exit;
    } catch (PDOException $e) { $error = "Db Error: " . $e->getMessage(); }
}

// Fetch Centers
try {
    $stmt = $pdo->query("SELECT * FROM centers ORDER BY created_at DESC");
    $centers = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error"); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Centers - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>
        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Manage Centers</h2>
                    <a href="add-center.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Center</a>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        if($_GET['msg'] == 'added') echo "Center registered and email sent successfully!"; 
                        if($_GET['msg'] == 'deleted') echo "Center deleted successfully!"; 
                        if($_GET['msg'] == 'updated') echo "Center updated successfully!"; 
                        ?>
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Code</th>
                                        <th class="py-3">Center Name</th>
                                        <th class="py-3">Owner</th>
                                        <th class="py-3">Location</th>
                                        <th class="py-3">Contact</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($centers) > 0): ?>
                                        <?php foreach ($centers as $c): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($c['center_code']); ?></td>
                                                <td><span class="fw-bold"><?php echo htmlspecialchars($c['center_name']); ?></span></td>
                                                <td>
                                                    <?php if($c['owner_image']): ?><img src="../../<?php echo $c['owner_image']; ?>" class="rounded-circle me-1" width="25" height="25"><?php endif; ?>
                                                    <?php echo htmlspecialchars($c['owner_name']); ?>
                                                </td>
                                                <td><small><?php echo htmlspecialchars($c['city'].', '.$c['state']); ?></small></td>
                                                <td><small><?php echo htmlspecialchars($c['mobile']); ?></small></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $c['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $c['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <!-- Optional: Login as Center -->
                                                    <a href="view-center.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-info me-1" title="View Details"><i class="fas fa-eye"></i></a>
                                                    
                                                    <a href="edit-center.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-centers.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this center?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No centers found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
