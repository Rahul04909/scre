<?php
// Partners Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Partners
$partners_list = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM partners ORDER BY display_order ASC, created_at DESC");
        $partners_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $partners_list = [];
    }
}
?>
<style>
    .partners-section-wrapper {
        padding: 50px 0;
        background-color: #fff;
        overflow: hidden; /* Hide scrollbar for ticker */
        font-family: 'Poppins', sans-serif;
    }

    .partners-container {
        max-width: 98%;
        margin: 0 auto;
    }

    .partners-title {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .partners-title span {
        color: #0d6efd; /* Blue highlight */
        position: relative;
    }
    
    .partners-title span::after {
        content: '';
        display: block;
        width: 100%;
        height: 3px;
        background: #0d6efd;
        position: absolute;
        bottom: -5px;
        left: 0;
        border-radius: 2px;
    }

    /* Ticker Animation */
    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); } /* Move half (since we duplicate content) */
    }

    .partners-ticker-wrapper {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 20px 0;
    }
    
    .partners-ticker-wrapper::before,
    .partners-ticker-wrapper::after {
        content: "";
        position: absolute;
        top: 0;
        width: 100px;
        height: 100%;
        z-index: 2;
        pointer-events: none;
    }
    
    .partners-ticker-wrapper::before {
        left: 0;
        background: linear-gradient(to right, white, transparent);
    }
    
    .partners-ticker-wrapper::after {
        right: 0;
        background: linear-gradient(to left, white, transparent);
    }

    .partners-track {
        display: flex;
        width: max-content;
        animation: ticker 30s linear infinite;
    }
    
    .partners-track:hover {
        animation-play-state: paused;
    }

    .partner-logo-item {
        flex: 0 0 auto;
        width: 200px;
        padding: 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .partner-logo {
        max-width: 100%;
        max-height: 80px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .partner-logo:hover {
        transform: scale(1.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .partners-title { font-size: 1.5rem; }
        .partner-logo-item { width: 150px; padding: 0 20px; }
        .partners-track { animation-duration: 20s; }
    }
</style>

<div class="partners-section-wrapper">
    <div class="container-fluid partners-container">
        
        <h2 class="partners-title">Our Trusted <span>Partners</span></h2>

        <?php if (!empty($partners_list)): ?>
            <div class="partners-ticker-wrapper">
                <div class="partners-track">
                    <!-- Original Set -->
                    <?php foreach ($partners_list as $partner): ?>
                        <div class="partner-logo-item">
                            <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" class="partner-logo" alt="<?php echo htmlspecialchars($partner['name']); ?>">
                        </div>
                    <?php endforeach; ?>

                    <!-- Duplicate Set for Layout Continuity (if < 10 items, maybe tripicate) -->
                    <?php 
                        // Ensure we have enough length for smooth scroll
                        $repeat_count = count($partners_list) < 5 ? 4 : 1; 
                        for ($i = 0; $i < $repeat_count; $i++):
                            foreach ($partners_list as $partner): 
                    ?>
                            <div class="partner-logo-item">
                                <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" class="partner-logo" alt="<?php echo htmlspecialchars($partner['name']); ?>">
                            </div>
                    <?php 
                            endforeach;
                        endfor; 
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center text-muted">
                <p>We are partnering with top industry leaders. Logos coming soon.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
