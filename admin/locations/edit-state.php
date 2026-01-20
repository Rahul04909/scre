<?php
require_once '../../database/config.php';

$id = $_GET['id'] ?? 0;
if (!$id) { header("Location: manage-states.php"); exit; }

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT * FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) { die("DB Error"); }

// Fetch State
try {
    $stmt = $pdo->prepare("SELECT * FROM states WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $state = $stmt->fetch();
    if (!$state) { header("Location: manage-states.php"); exit; }
} catch (PDOException $e) { die("Error"); }

if (isset($_POST['update_state'])) {
    $country_id = intval($_POST['country_id']);
    $name = trim($_POST['name']);

    try {
        $sql = "UPDATE states SET name = :name, country_id = :country_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $name, ':country_id' => $country_id, ':id' => $id]);
        header("Location: manage-states.php?msg=updated");
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
    <title>Edit State - Admin</title>
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
                    <h2 class="mb-0">Edit State</h2>
                    <a href="manage-states.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Select Country</label>
                                    <select name="country_id" class="form-select" required>
                                        <?php foreach($countries as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $state['country_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($c['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">State Name</label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($state['name']); ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_state" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update State</button>
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
