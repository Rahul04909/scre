<?php
// Hero Section Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Slides
$hero_slides = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM hero_slides ORDER BY display_order ASC, created_at DESC");
        $hero_slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail or log error, fallback to static/empty
    }
}
?>
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom Styles for Hero -->
<style>
    .hero-section-wrapper {
        margin-top: 20px;
        margin-bottom: 40px;
        font-family: 'Poppins', sans-serif;
        max-width: 98%; /* Increased width to near full-width */
    }

    .hero-container {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
/* ... */
</style>

<div class="container-fluid hero-section-wrapper"> <!-- Changed to container-fluid -->
    <div class="hero-container">
        <?php if (!empty($hero_slides)): ?>
            <div id="heroCarousel" class="carousel slide hero-slider carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                
                <!-- Indicators -->
                <div class="carousel-indicators">
                    <?php foreach ($hero_slides as $index => $slide): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>

                <!-- Slides -->
                <div class="carousel-inner">
                    <?php foreach ($hero_slides as $index => $slide): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>">
                            <div class="hero-caption">
                                <div class="hero-content">
                                    <?php if (!empty($slide['title'])): ?>
                                        <h2 class="hero-title"><?php echo htmlspecialchars($slide['title']); ?></h2>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($slide['subtitle'])): ?>
                                        <p class="hero-subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="hero-btn"><?php echo htmlspecialchars($slide['button_text']); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <!-- Fallback Static Content if Info Not Found (prevents broken layout) -->
            <div class="alert alert-warning text-center m-5">
                No hero slides found. Please add slides from the Admin Panel.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
