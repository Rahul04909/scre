<?php
require_once '../../database/config.php';

$id = $_GET['id'] ?? 0;
if (!$id) { header("Location: manage-cities.php"); exit; }

// Fetch City
try {
    $stmt = $pdo->prepare("SELECT * FROM cities WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $city = $stmt->fetch();
    if (!$city) { header("Location: manage-cities.php"); exit; }

    // Fetch Current State and Country
    $stmt = $pdo->prepare("SELECT * FROM states WHERE id = :sid");
    $stmt->execute([':sid' => $city['state_id']]);
    $currentState = $stmt->fetch();
    $currentCountryId = $currentState['country_id'];

} catch (PDOException $e) { die("Error"); }

// Fetch Countries
try {
    $countries = $pdo->query("SELECT * FROM countries ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) { die("DB Error"); }

// Fetch States for Current Country
try {
    $stmt = $pdo->prepare("SELECT * FROM states WHERE country_id = :cid ORDER BY name ASC");
    $stmt->execute([':cid' => $currentCountryId]);
    $states = $stmt->fetchAll();
} catch (PDOException $e) { die("DB Error"); }


if (isset($_POST['update_city'])) {
    $state_id = intval($_POST['state_id']);
    $name = trim($_POST['name']);

    try {
        $sql = "UPDATE cities SET name = :name, state_id = :state_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $name, ':state_id' => $state_id, ':id' => $id]);
        header("Location: manage-cities.php?msg=updated");
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
    <title>Edit City - Admin</title>
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
                    <h2 class="mb-0">Edit City</h2>
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
                                        <?php foreach($countries as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $currentCountryId) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($c['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select State</label>
                                    <select name="state_id" id="state" class="form-select" required>
                                        <?php foreach($states as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" <?php if($s['id'] == $city['state_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($s['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City Name</label>
                                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($city['name']); ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_city" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update City</button>
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
