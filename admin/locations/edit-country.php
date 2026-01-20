<?php
require_once '../../database/config.php';

$id = $_GET['id'] ?? 0;
if (!$id) { header("Location: manage-countries.php"); exit; }

// Fetch Country
try {
    $stmt = $pdo->prepare("SELECT * FROM countries WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $country = $stmt->fetch();
    if (!$country) { header("Location: manage-countries.php"); exit; }
} catch (PDOException $e) { die("Error"); }

if (isset($_POST['update_country'])) {
    $sortname = strtoupper(trim($_POST['sortname']));
    $name = trim($_POST['name']);
    $phonecode = intval($_POST['phonecode']);

    try {
        $sql = "UPDATE countries SET sortname = :sortname, name = :name, phonecode = :phonecode WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sortname' => $sortname, ':name' => $name, ':phonecode' => $phonecode, ':id' => $id]);
        header("Location: manage-countries.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Country - Admin</title>
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
                    <h2 class="mb-0">Edit Country</h2>
                    <a href="manage-countries.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country Name</label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($country['name']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ISO Code (2 Char)</label>
                                    <input type="text" name="sortname" class="form-control" required maxlength="3" value="<?php echo htmlspecialchars($country['sortname']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phone Code</label>
                                    <input type="number" name="phonecode" class="form-control" required value="<?php echo htmlspecialchars($country['phonecode']); ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_country" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update Country</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
