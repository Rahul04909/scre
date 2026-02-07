<?php
// gallery.php
require_once 'database/config.php';

// Fetch Categories
try {
    $stmt = $pdo->query("SELECT * FROM gallery_categories WHERE status='active' ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Fetch Images
$cat_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$params = [];
$sql = "SELECT * FROM gallery_images ";

if ($cat_id > 0) {
    $sql .= "WHERE category_id = ? ";
    $params[] = $cat_id;
}
$sql .= "ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $images = $stmt->fetchAll();
} catch (PDOException $e) { $images = []; }

// Helper to get category name
$current_category = 'All Photos';
if ($cat_id > 0) {
    foreach ($categories as $c) {
        if ($c['id'] == $cat_id) {
            $current_category = $c['name'];
            break;
        }
    }
}
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --border-beige: #e0d0b8;
    }

    .gallery-page-wrapper {
        background-color: #fff;
        min-height: 90vh;
        padding: 60px 0;
        font-family: 'Poppins', sans-serif;
    }

    .page-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 40px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        position: relative;
        padding-bottom: 15px;
    }
    
    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--muted-gold);
        border-radius: 2px;
    }

    /* Sidebar */
    .gallery-sidebar {
        background: var(--soft-ivory);
        border-radius: 12px;
        padding: 25px;
        border: 1px solid var(--border-beige);
        position: sticky;
        top: 20px;
    }

    .sidebar-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--muted-gold);
    }

    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        margin-bottom: 10px;
    }

    .category-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        color: #5d4037;
        text-decoration: none;
        background: #fff;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        font-weight: 500;
    }

    .category-link:hover {
        background: #fff;
        border-color: var(--muted-gold);
        color: var(--deep-brown);
        transform: translateX(5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .category-link.active {
        background: var(--deep-brown);
        color: #fff;
        border-color: var(--deep-brown);
    }

    .category-link.active i {
        color: var(--muted-gold);
    }

    /* Image Grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .gallery-item {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        position: relative;
        cursor: pointer;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(109, 76, 65, 0.15);
    }

    .gallery-img-container {
        position: relative;
        padding-top: 75%; /* 4:3 Aspect Ratio */
        overflow: hidden;
    }

    .gallery-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .gallery-item:hover .gallery-img {
        transform: scale(1.08);
    }

    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        padding: 20px;
        color: #fff;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .gallery-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    /* Lightbox Modal */
    .lightbox-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .lightbox-content {
        max-width: 90%;
        max-height: 90vh;
        border-radius: 5px;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    .close-lightbox {
        position: absolute;
        top: 20px;
        right: 30px;
        color: #fff;
        font-size: 40px;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close-lightbox:hover {
        color: var(--muted-gold);
    }

    @media (max-width: 768px) {
        .gallery-sidebar {
            margin-bottom: 30px;
            position: static;
        }
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
        }
    }
</style>

<div class="gallery-page-wrapper">
    <div class="container">
        <h1 class="page-title">Photo Gallery</h1>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="gallery-sidebar shadow-sm">
                    <h5 class="sidebar-title">Categories</h5>
                    <ul class="category-list">
                        <li class="category-item">
                            <a href="gallery.php" class="category-link <?php echo $cat_id == 0 ? 'active' : ''; ?>">
                                <span>All Photos</span>
                                <i class="fas fa-th-large"></i>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): 
                            $isActive = ($cat_id == $cat['id']) ? 'active' : '';
                        ?>
                        <li class="category-item">
                            <a href="gallery.php?category=<?php echo $cat['id']; ?>" class="category-link <?php echo $isActive; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                <i class="fas fa-folder"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-dark fw-bold"><?php echo htmlspecialchars($current_category); ?></h4>
                    <span class="text-muted small"><?php echo count($images); ?> photos found</span>
                </div>

                <?php if (count($images) > 0): ?>
                    <div class="gallery-grid">
                        <?php foreach ($images as $img): ?>
                        <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($img['image_path']); ?>')">
                            <div class="gallery-img-container">
                                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" class="gallery-img">
                                <div class="gallery-overlay">
                                    <h6 class="gallery-title"><?php echo htmlspecialchars($img['title']); ?></h6>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-5 border">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <p class="mb-0 text-muted">No photos found in this category yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox-modal">
    <span class="close-lightbox" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" class="lightbox-content" src="">
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto'; // Enable scrolling
    }

    // Close lightbox when clicking outside the image
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
</script>
