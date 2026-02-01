<?php
require_once 'database/config.php';

// Fetch Countries for Filter
try {
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll();
} catch (PDOException $e) {
    $countries = [];
}

// Build Filter Query
$wherebox = [];
$params = [];

if (!empty($_GET['country'])) {
    $wherebox[] = "c.country = ?";
    $params[] = $_GET['country'];
}
if (!empty($_GET['state'])) {
    $wherebox[] = "c.state = ?";
    $params[] = $_GET['state'];
}
if (!empty($_GET['city'])) {
    $wherebox[] = "c.city = ?";
    $params[] = $_GET['city'];
}

// Search by name
if (!empty($_GET['q'])) {
    $wherebox[] = "(c.center_name LIKE ? OR c.center_code LIKE ?)";
    $params[] = "%".$_GET['q']."%";
    $params[] = "%".$_GET['q']."%";
}

$whereSQL = "";
if (!empty($wherebox)) {
    $whereSQL = "WHERE " . implode(" AND ", $wherebox);
}

// Fetch Centers
try {
    $sql = "SELECT c.*, 
                   cn.name as country_name, 
                   s.name as state_name, 
                   ct.name as city_name 
            FROM centers c
            LEFT JOIN countries cn ON c.country = cn.id
            LEFT JOIN states s ON c.state = s.id
            LEFT JOIN cities ct ON c.city = ct.id
            $whereSQL
            ORDER BY c.id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $centers = $stmt->fetchAll();
} catch (PDOException $e) {
    $centers = [];
}
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<style>
    .centers-page-wrapper {
        background-color: #f8f9fa;
        padding: 40px 0;
        min-height: 80vh;
    }
    
    /* Sidebar Filter Styles */
    .filter-sidebar {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 20px;
        position: sticky;
        top: 20px;
    }
    
    .filter-title {
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    /* Center Card Styles */
    .center-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border: 1px solid #eee;
        display: flex;
        flex-direction: column;
    }
    
    .center-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .center-img-wrapper {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    
    .center-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .center-card:hover .center-img {
        transform: scale(1.05);
    }
    
    .verified-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #28a745;
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: 600;
        z-index: 1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .center-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .center-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
        text-decoration: none;
        display: block;
    }
    
    .center-name:hover {
        color: #e67e22;
    }

    .center-location {
        color: #7f8c8d;
        font-size: 0.85rem;
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }
    
    .center-location i {
        color: #e67e22;
        margin-right: 5px;
        margin-top: 3px;
    }

    .center-features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .feature-tag {
        background: #ecf0f1;
        color: #555;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
    }

    .card-footer-custom {
        margin-top: auto;
        border-top: 1px solid #eee;
        padding-top: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .btn-details {
        background: #2c3e50;
        color: #fff;
        font-size: 0.85rem;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        transition: background 0.2s;
    }
    
    .btn-details:hover {
        background: #34495e;
        color: #fff;
    }
    
    .contact-info {
        font-size: 0.85rem;
        color: #333;
    }

    /* Mobile Responsive Sidebar */
    @media (max-width: 991px) {
        .filter-sidebar {
            position: relative;
            top: 0;
            margin-bottom: 30px;
        }
    }
</style>

<div class="centers-page-wrapper">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5 class="filter-title"><i class="fas fa-filter me-2"></i> Filter Centers</h5>
                    <form action="" method="GET" id="filterForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Search Name/Code</label>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="e.g. SCRE2025" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Country</label>
                            <select name="country" id="country" class="form-select form-select-sm">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['country']) && $_GET['country'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">State</label>
                            <select name="state" id="state" class="form-select form-select-sm" <?php echo empty($_GET['country']) ? 'disabled' : ''; ?>>
                                <option value="">All States</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">City/District</label>
                            <select name="city" id="city" class="form-select form-select-sm" <?php echo empty($_GET['state']) ? 'disabled' : ''; ?>>
                                <option value="">All Cities</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                            <a href="centers.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Centers Grid -->
            <div class="col-lg-9">
                <h3 class="mb-4 text-dark fw-bold border-bottom pb-2">
                    Our Training Centers 
                    <span class="badge bg-primary fs-6 align-middle ms-2"><?php echo count($centers); ?> Found</span>
                </h3>
                
                <?php if (count($centers) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($centers as $center): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="center-card">
                                    <div class="center-img-wrapper">
                                        <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                                        <?php 
                                        $imgSrc = !empty($center['banner_image']) ? $center['banner_image'] : 'assets/logo/frontpage-logo.webp'; // Fallback
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($center['center_name']); ?>" class="center-img">
                                    </div>
                                    <div class="center-content">
                                        <div class="mb-1 text-muted small"><?php echo htmlspecialchars($center['center_code']); ?></div>
                                        <a href="center-details.php?id=<?php echo $center['id']; ?>" class="center-name">
                                            <?php echo htmlspecialchars($center['center_name']); ?>
                                        </a>
                                        <div class="center-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <div>
                                                <?php echo htmlspecialchars($center['city_name'] ?? ''); ?>, 
                                                <?php echo htmlspecialchars($center['state_name'] ?? ''); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="center-features">
                                            <?php if ($center['num_computers'] > 0): ?>
                                                <span class="feature-tag"><i class="fas fa-desktop"></i> <?php echo $center['num_computers']; ?> PCs</span>
                                            <?php endif; ?>
                                            <?php if ($center['internet_avail'] == 'Yes'): ?>
                                                <span class="feature-tag"><i class="fas fa-wifi"></i> WiFi</span>
                                            <?php endif; ?>
                                            <?php if ($center['power_backup'] == 'Yes'): ?>
                                                <span class="feature-tag"><i class="fas fa-bolt"></i> Backup</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-footer-custom">
                                            <div class="contact-info">
                                                <i class="fas fa-phone-alt text-primary"></i> <?php echo htmlspecialchars($center['mobile']); ?>
                                            </div>
                                            <a href="center-details.php?id=<?php echo $center['id']; ?>" class="btn-details">
                                                View Details <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="assets/img/no-results.svg" alt="" style="width: 150px; opacity: 0.5;">
                        <h4 class="mt-3 text-muted">No centers found matching your criteria.</h4>
                        <a href="centers.php" class="btn btn-primary mt-2">View All Centers</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Filter Script -->
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
                stateSelect.innerHTML = '<option value="">All States</option>';
                stateSelect.disabled = true;
                citySelect.innerHTML = '<option value="">All Cities</option>';
                citySelect.disabled = true;
                return;
            }

            stateSelect.disabled = false;
            fetch(`admin/locations/get-location-data.php?type=get_states&country_id=${countryId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">All States</option>';
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
                citySelect.innerHTML = '<option value="">All Cities</option>';
                citySelect.disabled = true;
                return;
            }

            citySelect.disabled = false;
            fetch(`admin/locations/get-location-data.php?type=get_cities&state_id=${stateId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">All Cities</option>';
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

        // Initial Load
        if (countrySelect.value) {
            loadStates(countrySelect.value, () => {
                if (selectedState) {
                    loadCities(selectedState);
                }
            });
        }
    });
</script>
