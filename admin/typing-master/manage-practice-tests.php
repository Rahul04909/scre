<?php
require_once '../../database/config.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    try {
        $sql = "DELETE FROM typing_practice_tests WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        header("Location: manage-practice-tests.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "DataBase Error: " . $e->getMessage();
    }
}

// Fetch Tests with Language Name
try {
    $sql = "SELECT t.*, lang.language_name, lang.language_code 
            FROM typing_practice_tests t
            JOIN typing_languages lang ON t.language_id = lang.id
            ORDER BY t.created_at DESC";
    $stmt = $pdo->query($sql);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Practice Tests - Admin</title>
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
                    <h2 class="mb-0">Manage Practice Tests</h2>
                    <a href="add-practice-test.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add New Test</a>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Test deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Test added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Test updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">ID</th>
                                        <th class="py-3">Language</th>
                                        <th class="py-3">Test Title</th>
                                        <th class="py-3">Duration</th>
                                        <th class="py-3">Created At</th>
                                        <th class="py-3 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($tests) > 0): ?>
                                        <?php foreach ($tests as $test): ?>
                                            <tr>
                                                <td class="ps-4">#<?php echo $test['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($test['language_name']); ?> 
                                                    <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($test['language_code']); ?></span>
                                                </td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($test['test_title']); ?></td>
                                                <td><?php echo $test['duration_minutes']; ?> mins</td>
                                                <td><?php echo date('d M Y', strtotime($test['created_at'])); ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-practice-test.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                                                    <a href="manage-practice-tests.php?delete_id=<?php echo $test['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this test?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No practice tests found.</td></tr>
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
