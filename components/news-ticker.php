<?php
// News Ticker Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Active News
$news_list = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM news_updates WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10");
        $news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $news_list = [];
    }
}
?>
<style>
    .news-ticker-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #1a2c3f; /* Dark Premium Blue */
        color: #fff;
        z-index: 1000; /* High z-index to stay on top */
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
        display: flex;
        height: 50px;
        align-items: center;
    }

    .ticker-label {
        background-color: #dc3545; /* Red Attention Color */
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0 20px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        width: auto;
        min-width: 140px;
        font-size: 0.9rem;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }
    
    .ticker-label::after {
        content: '';
        position: absolute;
        top: 0;
        right: -15px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 50px 0 0 15px; /* Angle effect */
        border-color: transparent transparent transparent #dc3545;
        z-index: 2;
    }

    .ticker-content-wrapper {
        flex: 1;
        overflow: hidden;
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .ticker-content {
        display: inline-block;
        white-space: nowrap;
        animation: scroll-text 30s linear infinite; /* Adjusted speed */
        padding-left: 100%;
    }

    @keyframes scroll-text {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
    
    .ticker-item {
        margin-right: 50px;
        display: inline-flex;
        align-items: center;
    }
    
    .ticker-item a {
        color: #fff;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    
    .ticker-item a:hover {
        color: #ffc107; /* Yellow on hover */
        text-decoration: underline;
    }
    
    .ticker-item i {
        margin-right: 8px;
        color: #ffc107;
        font-size: 0.8rem;
    }

    .ticker-content-wrapper:hover .ticker-content {
        animation-play-state: paused;
    }

    @media (max-width: 576px) {
        .ticker-label {
            min-width: 100px;
            font-size: 0.8rem;
            padding: 0 10px;
        }
        .ticker-item a {
            font-size: 0.85rem;
        }
        .news-ticker-fixed {
            height: 40px;
        }
        .ticker-label::after {
            border-width: 40px 0 0 15px;
        }
    }
</style>

<div class="news-ticker-fixed">
    <div class="ticker-label">
        <i class="fas fa-bullhorn me-2"></i> LATEST
    </div>
    <div class="ticker-content-wrapper">
        <div class="ticker-content">
            <?php if (!empty($news_list)): ?>
                <?php foreach ($news_list as $news): ?>
                    <span class="ticker-item">
                        <i class="fas fa-circle"></i> 
                        <?php if (!empty($news['link_url'])): ?>
                            <a href="<?php echo htmlspecialchars($news['link_url']); ?>"><?php echo htmlspecialchars($news['message']); ?></a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($news['message']); ?></span>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
                
                <!-- Duplicate for smoother loop if few items -->
                <?php if (count($news_list) < 5): ?>
                    <?php foreach ($news_list as $news): ?>
                        <span class="ticker-item">
                            <i class="fas fa-circle"></i> 
                            <?php if (!empty($news['link_url'])): ?>
                                <a href="<?php echo htmlspecialchars($news['link_url']); ?>"><?php echo htmlspecialchars($news['message']); ?></a>
                            <?php else: ?>
                                <span><?php echo htmlspecialchars($news['message']); ?></span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php else: ?>
                <span class="ticker-item">
                    <i class="fas fa-info-circle"></i> 
                    <span>Welcome to PACE Foundation. Check back for latest updates and news.</span>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
