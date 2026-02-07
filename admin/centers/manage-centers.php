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

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) { $countries = []; }

// Helper function to get location name
function getLocationName($pdo, $table, $id) {
    if (!$id) return '';
    $stmt = $pdo->prepare("SELECT name FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() ?: '';
}

// Build Filter Query
$wherebox = [];
$params = [];

if (!empty($_GET['country'])) {
    $wherebox[] = "country = ?";
    $params[] = $_GET['country'];
}
if (!empty($_GET['state'])) {
    $wherebox[] = "state = ?";
    $params[] = $_GET['state'];
}
if (!empty($_GET['city'])) {
    $wherebox[] = "city = ?";
    $params[] = $_GET['city'];
}

// Search by name/code
if (!empty($_GET['q'])) {
    $wherebox[] = "(center_name LIKE ? OR center_code LIKE ?)";
    $params[] = "%".$_GET['q']."%";
    $params[] = "%".$_GET['q']."%";
}

$whereSQL = "";
if (!empty($wherebox)) {
    $whereSQL = "WHERE " . implode(" AND ", $wherebox);
}

// Fetch Centers with Filters
try {
    $sql = "SELECT * FROM centers $whereSQL ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $centers = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
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

                <!-- Filters -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-uppercase fw-bold text-muted">Search</label>
                                <input type="text" name="q" class="form-control" placeholder="Name or Code" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-uppercase fw-bold text-muted">Country</label>
                                <select name="country" id="country" class="form-select">
                                    <option value="">All</option>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['country']) && $_GET['country'] == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-uppercase fw-bold text-muted">State</label>
                                <select name="state" id="state" class="form-select" <?php echo empty($_GET['country']) ? 'disabled' : ''; ?>>
                                    <option value="">All</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-uppercase fw-bold text-muted">City</label>
                                <select name="city" id="city" class="form-select" <?php echo empty($_GET['state']) ? 'disabled' : ''; ?>>
                                    <option value="">All</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2 flex-grow-1"><i class="fas fa-filter me-1"></i> Filter</button>
                                <a href="manage-centers.php" class="btn btn-outline-secondary flex-grow-1">Reset</a>
                            </div>
                        </form>
                    </div>
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
                                        <th class="py-3">Registration Date</th>
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
                                                <td><small><?php echo date('d-m-Y', strtotime($c['created_at'])); ?></small></td>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');

            // Pre-select values if URL params exist
            const urlParams = new URLSearchParams(window.location.search);
            const selectedState = urlParams.get('state');
            const selectedCity = urlParams.get('city');

            // Function to load states
            function loadStates(countryId, callback) {
                if (!countryId) {
                    stateSelect.innerHTML = '<option value="">All</option>';
                    stateSelect.disabled = true;
                    citySelect.innerHTML = '<option value="">All</option>';
                    citySelect.disabled = true;
                    return;
                }

                stateSelect.disabled = false;
                // Use the existing API endpoint
                fetch(`../locations/get-location-data.php?type=get_states&country_id=${countryId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">All</option>';
                        data.forEach(item => {
                            const selected = (selectedState == item.id) ? 'selected' : '';
                            html += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        stateSelect.innerHTML = html;
                        if (callback) callback();
                    })
                    .catch(err => console.error('Error loading states:', err));
            }

            // Function to load cities
            function loadCities(stateId) {
                if (!stateId) {
                    citySelect.innerHTML = '<option value="">All</option>';
                    citySelect.disabled = true;
                    return;
                }

                citySelect.disabled = false;
                fetch(`../locations/get-location-data.php?type=get_cities&state_id=${stateId}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">All</option>';
                        data.forEach(item => {
                            const selected = (selectedCity == item.id) ? 'selected' : '';
                            html += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        citySelect.innerHTML = html;
                    })
                    .catch(err => console.error('Error loading cities:', err));
            }

            // Event Listeners
            countrySelect.addEventListener('change', function() {
                loadStates(this.value);
            });

            stateSelect.addEventListener('change', function() {
                loadCities(this.value);
            });

            // Initial Load for pre-selected values
            if (countrySelect.value) {
                loadStates(countrySelect.value, () => {
                    if (selectedState) {
                        loadCities(selectedState);
                    }
                });
            }
        });
    </script>
</body>
</html>
