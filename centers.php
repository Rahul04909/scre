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

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --text-dark: #2d2f31;
    }

    body {
        background-color: var(--soft-ivory);
        font-family: 'Poppins', sans-serif;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--deep-brown), #3e2723);
        color: #fff;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .page-header::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: -10%;
        width: 120%;
        height: 60px;
        background: var(--soft-ivory);
        transform: rotate(-2deg);
    }

    .page-title {
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Sidebar */
    .course-sidebar {
        background: #fff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #eee;
        position: sticky;
        top: 20px;
    }

    .widget-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--muted-gold);
    }

    .form-control, .form-select {
        border-radius: 30px;
        padding: 10px 20px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }
    
    .btn-apply {
        width: 100%;
        background-color: var(--warm-brown);
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-apply:hover {
        background-color: var(--deep-brown);
        color: #fff;
    }

    .btn-reset {
        display: block;
        text-align: center;
        margin-top: 10px;
        color: #666;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .btn-reset:hover {
        color: var(--warm-brown);
    }

    /* Center Cards (Grid View) */
    .course-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #eee;
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .course-thumb {
        height: 180px;
        background-color: #f0f0f0;
        position: relative;
        overflow: hidden;
    }

    .course-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .course-card:hover .course-thumb img {
        transform: scale(1.05);
    }

    .course-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(40, 167, 69, 0.9);
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        backdrop-filter: blur(2px);
    }

    .center-code-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-family: monospace;
    }

    .course-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .course-cat {
        font-size: 0.8rem;
        color: var(--muted-gold);
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px;
        line-height: 1.4;
    }
    
    .course-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 15px;
    }
    
    .course-info i {
        color: var(--muted-gold);
        width: 20px;
        text-align: center;
        margin-right: 5px;
    }

    .feature-tag {
        font-size: 0.75rem;
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-right: 5px;
        margin-bottom: 5px;
        border: 1px solid #eee;
    }

    .course-footer {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #f5f5f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-view {
        background-color: var(--soft-ivory);
        color: var(--warm-brown);
        border: 1px solid var(--warm-brown);
        padding: 6px 15px;
        border-radius: 5px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        width: 100%;
        text-align: center;
    }

    .btn-view:hover {
        background-color: var(--warm-brown);
        color: #fff;
    }

    /* Filters Bar */
    .filters-bar {
        background: #fff;
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid #eee;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<!-- Main Page Content -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title">Find a Training Center</h1>
        <p class="mb-0 opacity-75">Locate verified institutes near you for the best technical education</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="course-sidebar">
                <h5 class="widget-title"><i class="fas fa-filter me-2"></i> Filter Results</h5>
                <form action="" method="GET" id="filterForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Search</label>
                        <input type="text" name="q" class="form-control" placeholder="Name or Center Code" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Country</label>
                        <select name="country" id="country" class="form-select">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['country']) && $_GET['country'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">State</label>
                        <select name="state" id="state" class="form-select" <?php echo empty($_GET['country']) ? 'disabled' : ''; ?>>
                            <option value="">All States</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">City</label>
                        <select name="city" id="city" class="form-select" <?php echo empty($_GET['state']) ? 'disabled' : ''; ?>>
                            <option value="">All Cities</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn-apply">Apply Filters</button>
                        <a href="centers.php" class="btn-reset">Reset All</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            
            <!-- Filters Bar -->
            <div class="filters-bar">
                <div class="text-muted">
                    Showing <strong><?php echo count($centers); ?></strong> Institutes Found
                </div>
            </div>

            <!-- Centers Grid -->
            <?php if (count($centers) > 0): ?>
            <div class="row g-4">
                <?php foreach ($centers as $center): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="course-card">
                        <div class="course-thumb">
                            <a href="center-details.php?id=<?php echo $center['id']; ?>">
                                <?php 
                                    $imgSrc = !empty($center['banner_image']) ? $center['banner_image'] : 'assets/logo/frontpage-logo.webp'; 
                                ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($center['center_name']); ?>">
                            </a>
                            <span class="center-code-badge"><?php echo htmlspecialchars($center['center_code']); ?></span>
                            <span class="course-badge"><i class="fas fa-check-circle"></i> Verified</span>
                        </div>
                        <div class="course-body">
                            <div class="course-cat"><?php echo htmlspecialchars($center['city_name'] ?? 'City'); ?>, <?php echo htmlspecialchars($center['state_name'] ?? 'State'); ?></div>
                            <h3 class="course-title">
                                <a href="center-details.php?id=<?php echo $center['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($center['center_name']); ?>
                                </a>
                            </h3>
                            
                            <div class="course-info">
                                <div><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($center['mobile']); ?></div>
                                <div class="mt-2">
                                    <?php if ($center['num_computers'] > 0): ?>
                                        <span class="feature-tag"><i class="fas fa-desktop"></i> <?php echo $center['num_computers']; ?> PCs</span>
                                    <?php endif; ?>
                                    <?php if ($center['internet_avail'] == 'Yes'): ?>
                                        <span class="feature-tag"><i class="fas fa-wifi"></i> WiFi</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="course-footer">
                                <a href="center-details.php?id=<?php echo $center['id']; ?>" class="btn-view">
                                    View Center Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-light text-center py-5 shadow-sm">
                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                <h5>No centers found</h5>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="centers.php" class="btn btn-primary mt-2">View All Centers</a>
            </div>
            <?php endif; ?>

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
