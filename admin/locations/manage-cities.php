<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM cities WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-cities.php?msg=deleted");
        exit;
    } catch (PDOException $e) { $error = "Db Error: " . $e->getMessage(); }
}

// Fetch Cities with State & Country
try {
    $sql = "SELECT ci.*, s.name as state_name, co.name as country_name 
            FROM cities ci 
            JOIN states s ON ci.state_id = s.id 
            JOIN countries co ON s.country_id = co.id 
            ORDER BY co.name ASC, s.name ASC, ci.name ASC";
    $stmt = $pdo->query($sql);
    $cities = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error"); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cities - Admin</title>
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
                    <h2 class="mb-0">Manage Cities</h2>
                    <a href="add-city.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add City</a>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        if($_GET['msg'] == 'added') echo "City added successfully!"; 
                        if($_GET['msg'] == 'deleted') echo "City deleted successfully!"; 
                        if($_GET['msg'] == 'updated') echo "City updated successfully!"; 
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
                                        <th class="py-3 ps-4">City Name</th>
                                        <th class="py-3">State</th>
                                        <th class="py-3">Country</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cities) > 0): ?>
                                        <?php foreach ($cities as $c): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($c['name']); ?></td>
                                                <td><?php echo htmlspecialchars($c['state_name']); ?></td>
                                                <td><?php echo htmlspecialchars($c['country_name']); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-city.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-cities.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this city?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No cities found.</td></tr>
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
