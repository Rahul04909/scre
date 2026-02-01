<?php
require_once 'database/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: centers.php");
    exit;
}

$id = intval($_GET['id']);
$center = null;

try {
    // Fetch Center Details with Location Names
    $sql = "SELECT c.*, 
                   cn.name as country_name, 
                   s.name as state_name, 
                   ct.name as city_name 
            FROM centers c
            LEFT JOIN countries cn ON c.country = cn.id
            LEFT JOIN states s ON c.state = s.id
            LEFT JOIN cities ct ON c.city = ct.id
            WHERE c.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $center = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch Gallery
    $gallery = [];
    if (!empty($center['gallery_images'])) {
        $gallery = json_decode($center['gallery_images'], true);
    }

} catch (PDOException $e) {
    die("Error fetching center details");
}

if (!$center) {
    die("Center not found");
}
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<style>
    .center-details-wrapper {
        background-color: #fcefe633;
        font-family: 'Poppins', sans-serif;
    }

    /* Hero Banner Section */
    .cd-hero {
        position: relative;
        height: 350px;
        background-color: #2c3e50;
        background-image: url('<?php echo !empty($center['banner_image']) ? $center['banner_image'] : "assets/img/default-banner.jpg"; ?>');
        background-size: cover;
        background-position: center;
    }

    .cd-hero-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%);
        display: flex;
        align-items: flex-end;
        padding-bottom: 40px;
    }

    .cd-header-content {
        color: #fff;
    }

    .cd-logo {
        width: 100px;
        height: 100px;
        background: #fff;
        border-radius: 10px;
        padding: 5px;
        object-fit: contain;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin-right: 20px;
        float: left;
    }

    .cd-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .cd-meta {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 10px;
    }

    .cd-rating {
        background: #e67e22;
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9rem;
        margin-right: 10px;
    }

    .cd-badge {
        background: #27ae60;
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Main Content */
    .cd-main {
        padding: 40px 0;
    }

    .cd-section-card {
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        border: 1px solid #eee;
    }

    .cd-heading {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        display: inline-block;
    }

    .cd-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .cd-info-item {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .cd-info-icon {
        font-size: 1.5rem;
        color: #e67e22;
        margin-right: 15px;
        width: 30px;
        text-align: center;
    }

    .cd-info-label {
        font-size: 0.8rem;
        color: #777;
        margin-bottom: 2px;
    }

    .cd-info-value {
        font-weight: 600;
        color: #333;
    }

    /* Gallery */
    .cd-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }

    .cd-gallery-item {
        height: 120px;
        overflow: hidden;
        border-radius: 6px;
        cursor: pointer;
        position: relative;
    }

    .cd-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .cd-gallery-item:hover img {
        transform: scale(1.1);
    }

    /* Sidebar Sticky */
    .cd-sidebar {
        position: sticky;
        top: 20px;
    }

    .cd-contact-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: 1px solid #eee;
    }

    .cd-contact-header {
        background: #e67e22;
        color: #fff;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .cd-contact-body {
        padding: 20px;
    }

    .cd-contact-row {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }

    .cd-contact-row i {
        color: #e67e22;
        width: 25px;
        margin-top: 4px;
    }

    .cd-action-btn {
        display: block;
        width: 100%;
        padding: 12px;
        text-align: center;
        background: #25D366; /* WhatsApp Green */
        color: #fff;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        margin-top: 15px;
        transition: opacity 0.2s;
    }
    
    .cd-action-btn:hover {
        opacity: 0.9;
        color: #fff;
    }

    .cd-map-container {
        height: 250px;
        background: #eee;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    .cd-map-container iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }
</style>

<div class="center-details-wrapper">
    <!-- Hero Section -->
    <div class="cd-hero">
        <div class="cd-hero-overlay">
            <div class="container">
                <div class="d-flex align-items-end">
                    <img src="<?php echo !empty($center['logo_image']) ? $center['logo_image'] : 'assets/logo/frontpage-logo.webp'; ?>" alt="Logo" class="cd-logo d-none d-md-block">
                    <div class="cd-header-content">
                        <h1 class="cd-title"><?php echo htmlspecialchars($center['center_name']); ?></h1>
                        <div class="cd-meta">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($center['city_name']); ?>, <?php echo htmlspecialchars($center['state_name']); ?>
                            <span class="mx-2">|</span>
                            Code: <strong><?php echo htmlspecialchars($center['center_code']); ?></strong>
                        </div>
                        <div>
                            <span class="cd-rating">4.8 <i class="fas fa-star text-white"></i></span>
                            <span class="cd-badge"><i class="fas fa-check-circle"></i> Verified Center</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="cd-main">
        <div class="container">
            <div class="row">
                <!-- Left Content -->
                <div class="col-lg-8">
                    
                    <!-- Overview -->
                    <div class="cd-section-card">
                        <h2 class="cd-heading">Overview</h2>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="cd-info-item">
                                    <div class="cd-info-icon"><i class="fas fa-user-tie"></i></div>
                                    <div>
                                        <div class="cd-info-label">Center Director</div>
                                        <div class="cd-info-value"><?php echo htmlspecialchars($center['owner_name']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="cd-info-item">
                                    <div class="cd-info-icon"><i class="fas fa-calendar-check"></i></div>
                                    <div>
                                        <div class="cd-info-label">Working Days</div>
                                        <div class="cd-info-value"><?php echo htmlspecialchars($center['weekdays'] ?: 'Mon - Sat'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">About Center</h5>
                        <p class="text-muted">
                            <?php echo htmlspecialchars($center['center_name']); ?> is a premier computer training institute located in <?php echo htmlspecialchars($center['city_name']); ?>. 
                            We are committed to providing high-quality technical education and skill development.
                            Our center is equipped with modern facilities and experienced faculty to ensure the best learning environment for our students.
                        </p>
                    </div>

                    <!-- Infrastructure -->
                    <div class="cd-section-card">
                        <h2 class="cd-heading">Infrastructure & Facilities</h2>
                        <div class="cd-info-grid">
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-desktop"></i></div>
                                <div>
                                    <div class="cd-info-label">Computers</div>
                                    <div class="cd-info-value"><?php echo $center['num_computers']; ?> Systems</div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-chalkboard"></i></div>
                                <div>
                                    <div class="cd-info-label">Classrooms</div>
                                    <div class="cd-info-value"><?php echo $center['num_classrooms']; ?> Rooms</div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-users"></i></div>
                                <div>
                                    <div class="cd-info-label">Staff</div>
                                    <div class="cd-info-value"><?php echo $center['num_staff']; ?> Members</div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-wifi"></i></div>
                                <div>
                                    <div class="cd-info-label">Internet</div>
                                    <div class="cd-info-value"><?php echo $center['internet_avail']; ?></div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-bolt"></i></div>
                                <div>
                                    <div class="cd-info-label">Power Backup</div>
                                    <div class="cd-info-value"><?php echo $center['power_backup']; ?></div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-flask"></i></div>
                                <div>
                                    <div class="cd-info-label">Lab Type</div>
                                    <div class="cd-info-value"><?php echo htmlspecialchars($center['lab_type']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery -->
                    <?php if (!empty($gallery)): ?>
                    <div class="cd-section-card">
                        <h2 class="cd-heading">Center Gallery</h2>
                        <div class="cd-gallery-grid">
                            <?php foreach ($gallery as $img): ?>
                                <div class="cd-gallery-item">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Gallery Image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <div class="cd-sidebar">
                        <div class="cd-contact-card">
                            <div class="cd-contact-header">
                                <i class="fas fa-address-card me-2"></i> Contact Details
                            </div>
                            <div class="cd-contact-body">
                                <div class="cd-contact-row">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <div>
                                        <strong>Address:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($center['address'])); ?><br>
                                        <?php echo htmlspecialchars($center['city_name'] . ', ' . $center['state_name'] . ' - ' . $center['pincode']); ?>
                                    </div>
                                </div>
                                <div class="cd-contact-row">
                                    <i class="fas fa-phone-volume"></i>
                                    <div>
                                        <strong>Phone:</strong><br>
                                        <a href="tel:<?php echo htmlspecialchars($center['mobile']); ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($center['mobile']); ?></a>
                                    </div>
                                </div>
                                <div class="cd-contact-row">
                                    <i class="fas fa-envelope"></i>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <a href="mailto:<?php echo htmlspecialchars($center['email']); ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($center['email']); ?></a>
                                    </div>
                                </div>
                                
                                <div class="cd-contact-row mt-3">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <strong>Opening Hours:</strong><br>
                                        <?php echo htmlspecialchars($center['opening_time'] . ' - ' . $center['closing_time']); ?>
                                    </div>
                                </div>

                                <a href="https://wa.me/91<?php echo htmlspecialchars($center['mobile']); ?>?text=I%20am%20interested%20in%20your%20courses" target="_blank" class="cd-action-btn">
                                    <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
                                </a>
                                
                                <?php if(!empty($center['map_url'])): ?>
                                    <div class="cd-map-container">
                                        <?php echo $center['map_url']; // Embedding Map directly ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
