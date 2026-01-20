<?php
require_once '../../database/config.php';

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT * FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) { die("DB Error"); }

if (isset($_POST['add_city'])) {
    $state_id = intval($_POST['state_id']);
    $name = trim($_POST['name']);

    try {
        $sql = "INSERT INTO cities (name, state_id) VALUES (:name, :state_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $name, ':state_id' => $state_id]);
        header("Location: manage-cities.php?msg=added");
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
    <title>Add City - Admin</title>
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
                    <h2 class="mb-0">Add City</h2>
                    <a href="manage-cities.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select Country</label>
                                    <select id="country" class="form-select">
                                        <option value="">-- Select Country --</option>
                                        <?php foreach($countries as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select State</label>
                                    <select name="state_id" id="state" class="form-select" required>
                                        <option value="">-- Select Country First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City Name</label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. San Francisco">
                                </div>
                            </div>
                            <button type="submit" name="add_city" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save City</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        const countrySelect = document.getElementById('country');
        const stateSelect = document.getElementById('state');

        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            stateSelect.innerHTML = '<option value="">Loading...</option>';
            
            if(countryId) {
                fetch(`get-location-data.php?type=get_states&country_id=${countryId}`)
                    .then(response => response.json())
                    .then(data => {
                        stateSelect.innerHTML = '<option value="">-- Select State --</option>';
                        data.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                    });
            } else {
                stateSelect.innerHTML = '<option value="">-- Select Country First --</option>';
            }
        });
    </script>
</body>
</html>
