<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM states WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-states.php?msg=deleted");
        exit;
    } catch (PDOException $e) { $error = "Db Error: " . $e->getMessage(); }
}

// Fetch States with Country Name
try {
    $sql = "SELECT s.*, c.name as country_name 
            FROM states s 
            JOIN countries c ON s.country_id = c.id 
            ORDER BY c.name ASC, s.name ASC";
    $stmt = $pdo->query($sql);
    $states = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error"); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage States - Admin</title>
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
                    <h2 class="mb-0">Manage States</h2>
                    <a href="add-state.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add State</a>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        if($_GET['msg'] == 'added') echo "State added successfully!"; 
                        if($_GET['msg'] == 'deleted') echo "State deleted successfully!"; 
                        if($_GET['msg'] == 'updated') echo "State updated successfully!"; 
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
                                        <th class="py-3 ps-4">State Name</th>
                                        <th class="py-3">Country</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($states) > 0): ?>
                                        <?php foreach ($states as $s): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($s['name']); ?></td>
                                                <td><?php echo htmlspecialchars($s['country_name']); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-state.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-states.php?delete_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this state?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No states found.</td></tr>
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
