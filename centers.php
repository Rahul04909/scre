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
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    :root {
        --primary-blue: #0F2027; /* Deep professional blue */
        --accent-gold: #c94b4b; /* Subtle brand accent */
        --bg-gray: #f2f4f8;
        --card-shadow: 0 8px 30px rgba(0,0,0,0.06);
        --hover-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    body {
        background-color: var(--bg-gray);
        font-family: 'Poppins', sans-serif;
    }

    .centers-page-wrapper {
        padding: 60px 0;
        min-height: 80vh;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #203a43, #2c5364);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 0 0 20px 20px;
        margin-top: -60px; /* Offset wrapper padding for top placement */
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .page-title-main {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.8;
        margin-top: 10px;
    }
    
    /* Sidebar Filter Styles */
    .filter-sidebar {
        background: #fff;
        border-radius: 15px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        position: sticky;
        top: 20px;
        border: 1px solid rgba(0,0,0,0.03);
    }
    
    .filter-title {
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #eee;
        text-transform: uppercase;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
    }

    .form-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        box-shadow: none;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(15, 32, 39, 0.1);
    }

    .btn-apply {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    .btn-apply:hover {
        background: #2c5364;
        transform: translateY(-2px);
    }

    .btn-reset {
        border: 2px solid #ddd;
        color: #666;
        background: transparent;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .btn-reset:hover {
        background: #eee;
        color: #333;
    }

    /* Centers Header in Content */
    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .results-count {
        font-weight: 600;
        color: #555;
        background: #fff;
        padding: 8px 20px;
        border-radius: 50px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    /* Center Card Styles */
    .center-card {
        background: #fff;
        border-radius: 15px;
        border: none;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .center-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--hover-shadow);
    }
    
    .center-img-wrapper {
        height: 220px;
        overflow: hidden;
        position: relative;
        background: #eee;
    }
    
    .center-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    
    .center-card:hover .center-img {
        transform: scale(1.1);
    }
    
    .verified-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(40, 167, 69, 0.95);
        backdrop-filter: blur(5px);
        color: #fff;
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
    }

    .verified-badge i {
        margin-right: 5px;
    }

    .center-code-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        color: #fff;
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 6px;
        font-family: monospace;
        z-index: 2;
    }

    .center-content {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .center-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 8px;
        text-decoration: none;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .center-name:hover {
        color: var(--accent-gold);
    }

    .center-location {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .center-location i {
        color: #e67e22;
        margin-right: 8px;
    }

    .center-features {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .feature-tag {
        background: #f8f9fa;
        color: #555;
        font-size: 0.8rem;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        border: 1px solid #eee;
        display: flex;
        align-items: center;
    }

    .feature-tag i {
        margin-right: 6px;
        color: #888;
    }

    .card-footer-custom {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .btn-details {
        background: transparent;
        color: var(--primary-blue);
        font-size: 0.9rem;
        padding: 8px 16px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        border: 1px solid #ddd;
        transition: all 0.3s;
    }
    
    .btn-details:hover {
        background: var(--primary-blue);
        color: #fff;
        border-color: var(--primary-blue);
    }
    
    .contact-info {
        font-size: 0.9rem;
        color: #444;
        font-weight: 500;
    }

    /* Mobile Responsive Sidebar */
    @media (max-width: 991px) {
        .filter-sidebar {
            position: relative;
            top: 0;
            margin-bottom: 40px;
        }
        .page-header {
            border-radius: 0;
            margin-top: -60px;
        }
    }
</style>

<!-- Hero / Page Header -->
<div class="page-header">
    <div class="container text-center">
        <h1 class="page-title-main">Find a Training Center</h1>
        <p class="page-subtitle">Locate verified institutes near you for the best technical education.</p>
    </div>
</div>

<div class="centers-page-wrapper">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5 class="filter-title"><i class="fas fa-filter me-2"></i> Filter Results</h5>
                    <form action="" method="GET" id="filterForm">
                        <div class="mb-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="q" class="form-control" placeholder="Name or Center Code" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Country</label>
                            <select name="country" id="country" class="form-select">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['country']) && $_GET['country'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">State</label>
                            <select name="state" id="state" class="form-select" <?php echo empty($_GET['country']) ? 'disabled' : ''; ?>>
                                <option value="">All States</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">City</label>
                            <select name="city" id="city" class="form-select" <?php echo empty($_GET['state']) ? 'disabled' : ''; ?>>
                                <option value="">All Cities</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn-apply btn-block">Apply Filters</button>
                            <a href="centers.php" class="btn-reset text-center">Reset All</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Centers Grid -->
            <div class="col-lg-9">
                <div class="results-header">
                    <h4 class="mb-0 fw-bold text-dark">Our Training Centers</h4>
                    <div class="results-count">
                        <i class="fas fa-store-alt text-primary me-2"></i> 
                        <strong><?php echo count($centers); ?></strong> Institutes Found
                    </div>
                </div>
                
                <?php if (count($centers) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($centers as $center): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="center-card">
                                    <div class="center-img-wrapper">
                                        <div class="center-code-badge"><?php echo htmlspecialchars($center['center_code']); ?></div>
                                        <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                                        <?php 
                                        $imgSrc = !empty($center['banner_image']) ? $center['banner_image'] : 'assets/logo/frontpage-logo.webp'; // Fallback
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($center['center_name']); ?>" class="center-img">
                                    </div>
                                    <div class="center-content">
                                        <a href="center-details.php?id=<?php echo $center['id']; ?>" class="center-name">
                                            <?php echo htmlspecialchars($center['center_name']); ?>
                                        </a>
                                        <div class="center-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>
                                                <?php echo htmlspecialchars($center['city_name'] ?? ''); ?>, 
                                                <?php echo htmlspecialchars($center['state_name'] ?? ''); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="center-features">
                                            <?php if ($center['num_computers'] > 0): ?>
                                                <span class="feature-tag"><i class="fas fa-desktop"></i> <?php echo $center['num_computers']; ?></span>
                                            <?php endif; ?>
                                            <?php if ($center['internet_avail'] == 'Yes'): ?>
                                                <span class="feature-tag"><i class="fas fa-wifi"></i> WiFi</span>
                                            <?php endif; ?>
                                            <?php if ($center['power_backup'] == 'Yes'): ?>
                                                <span class="feature-tag"><i class="fas fa-bolt"></i> Power</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-footer-custom">
                                            <div class="contact-info">
                                                <i class="fas fa-phone-alt text-success me-2"></i> <?php echo htmlspecialchars($center['mobile']); ?>
                                            </div>
                                            <a href="center-details.php?id=<?php echo $center['id']; ?>" class="btn-details">
                                                View <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded shadow-sm">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No centers found matching your criteria.</h4>
                        <p class="text-secondary">Try adjusting your filters or search term.</p>
                        <a href="centers.php" class="btn btn-primary mt-3">View All Centers</a>
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
