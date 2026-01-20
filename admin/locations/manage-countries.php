<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM countries WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: manage-countries.php?msg=deleted");
        exit;
    } catch (PDOException $e) { $error = "Db Error: " . $e->getMessage(); }
}

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT * FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error"); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Countries - Admin</title>
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
                    <h2 class="mb-0">Manage Countries</h2>
                    <a href="add-country.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add Country</a>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        if($_GET['msg'] == 'added') echo "Country added successfully!"; 
                        if($_GET['msg'] == 'deleted') echo "Country deleted successfully!"; 
                        if($_GET['msg'] == 'updated') echo "Country updated successfully!"; 
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
                                        <th class="py-3 ps-4">ID</th>
                                        <th class="py-3">Name</th>
                                        <th class="py-3">ISO Code</th>
                                        <th class="py-3">Phone Code</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($countries) > 0): ?>
                                        <?php foreach ($countries as $c): ?>
                                            <tr>
                                                <td class="ps-4"><?php echo $c['id']; ?></td>
                                                <td><span class="fw-bold"><?php echo htmlspecialchars($c['name']); ?></span></td>
                                                <td><?php echo htmlspecialchars($c['sortname']); ?></td>
                                                <td>+<?php echo htmlspecialchars($c['phonecode']); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-country.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-countries.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this country? This will delete all associated states and cities.');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No countries found.</td></tr>
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
