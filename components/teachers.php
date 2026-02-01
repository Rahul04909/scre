<?php
// Teachers Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Teachers
$teachers_list = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM teachers ORDER BY display_order ASC, created_at DESC");
        $teachers_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $teachers_list = []; // Fail gracefully
    }
}
?>

<style>
    /* Section Wrappers */
    .teachers-section-wrapper {
        margin-top: 60px;
        margin-bottom: 60px;
        font-family: 'Poppins', sans-serif;
        max-width: 98%;
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .teachers-heading-area {
        margin-bottom: 50px;
    }

    .teachers-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .highlight-text {
        color: #ff9800;
        position: relative;
        display: inline-block;
    }
    
    .highlight-text::after {
        content: '';
        position: absolute;
        bottom: 2px;
        left: 0;
        width: 100%;
        height: 8px;
        background-color: #ffcc80;
        opacity: 0.4;
        z-index: -1;
        border-radius: 4px;
    }

    /* Features */
    .feature-row {
        background-color: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        align-items: center;
        gap: 20px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        max-width: 350px;
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background-color: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #0d47a1;
        flex-shrink: 0;
    }

    .feature-text {
        font-size: 0.9rem;
        color: #555;
        line-height: 1.4;
    }

    /* Slider Container */
    .teachers-slider-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        padding: 20px 5px 40px 5px; /* Bottom padding for shadow/hover space */
    }

    .teachers-track {
        display: flex;
        gap: 30px; /* Space between cards */
        overflow-x: auto;
        scroll-behavior: smooth;
        padding-bottom: 20px;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }
    
    .teachers-track::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Webkit */
    }

    /* Individual Card Wrapper in Slider */
    .teacher-slide {
        flex: 0 0 auto;
        width: 280px; /* Fixed width for consistent cards */
    }

    /* Teacher Cards */
    .teacher-card {
        border: none;
        background: transparent;
        position: relative;
        text-align: center;
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .teacher-card:hover {
        transform: translateY(-10px);
    }

    .teacher-image-wrapper {
        position: relative;
        z-index: 1;
        margin-bottom: -40px;
        height: 250px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .teacher-img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
    }

    .teacher-info-card {
        background-color: #1a2c3f;
        color: #fff;
        border-radius: 20px;
        padding: 50px 20px 30px;
        position: relative;
        z-index: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        min-height: 180px; /* Ensure uniform height for info box */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .exp-badge {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #fdd835; /* Yellow/Gold for visibility */
        color: #000;
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .teacher-name {
        font-size: 1.3rem;
        font-weight: 700;
        margin-top: 10px;
        margin-bottom: 5px;
        color: #fff;
    }

    .teacher-designation {
        font-size: 0.9rem;
        color: #cfd8dc;
        margin-bottom: 5px;
    }

    .teacher-college {
        font-size: 0.95rem;
        font-weight: 600;
        color: #ff9800;
    }

    /* Navigation Buttons */
    .nav-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #fff;
        color: #1a2c3f;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 20;
    }

    .nav-btn:hover {
        background-color: #1a2c3f;
        color: #fff;
        border-color: #1a2c3f;
    }

    /* Position Buttons relative to the heading area or absolutely positioned */
    .slider-nav-area {
        display: flex;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .teachers-title {
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .feature-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .teachers-heading-area {
            flex-direction: column;
            gap: 20px;
        }
        .slider-nav-area {
            width: 100%;
            justify-content: center;
            margin-bottom: 10px;
        }
    }
</style>

<div class="container-fluid teachers-section-wrapper">
    
    <!-- Heading & Navigation -->
    <div class="row align-items-center teachers-heading-area">
        <div class="col-md-8 text-center text-md-start">
            <h2 class="teachers-title">
                All teachers teach.<br>
                Greatest <span class="highlight-text">teachers</span> inspire
            </h2>
        </div>
        <div class="col-md-4 d-flex justify-content-center justify-content-md-end">
            <div class="slider-nav-area">
                <button class="nav-btn" id="scrollLeft"><i class="fas fa-chevron-left"></i></button>
                <button class="nav-btn" id="scrollRight"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="feature-row">
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="feature-text">From Top-tier colleges with many years of experience</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="feature-text">Specially-trained teachers to bring out the best in every student.</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="feature-text">Teaching experience of over 4.5 crore hours to students.</div>
        </div>
    </div>

    <!-- Horizontal Slider -->
    <div class="teachers-slider-container">
        <div class="teachers-track" id="teachersTrack">
            
            <?php if (!empty($teachers_list)): ?>
                <?php foreach ($teachers_list as $teacher): ?>
                    <div class="teacher-slide">
                        <div class="teacher-card">
                            <div class="teacher-image-wrapper">
                                <img src="<?php echo htmlspecialchars($teacher['image_path']); ?>" class="teacher-img" alt="<?php echo htmlspecialchars($teacher['name']); ?>">
                            </div>
                            <div class="teacher-info-card">
                                <?php if (!empty($teacher['experience_years'])): ?>
                                    <span class="exp-badge"><?php echo htmlspecialchars($teacher['experience_years']); ?> years exp</span>
                                <?php endif; ?>
                                
                                <h3 class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></h3>
                                
                                <?php if (!empty($teacher['designation'])): ?>
                                    <p class="teacher-designation"><?php echo htmlspecialchars($teacher['designation']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($teacher['college'])): ?>
                                    <p class="teacher-college"><?php echo htmlspecialchars($teacher['college']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <div class="col-12 text-center text-muted" style="min-width: 100%;">
                     <p>Our expert teachers will be listed here soon.</p>
                 </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('teachersTrack');
        const scrollLeftBtn = document.getElementById('scrollLeft');
        const scrollRightBtn = document.getElementById('scrollRight');

        if(track && scrollLeftBtn && scrollRightBtn) {
            scrollLeftBtn.addEventListener('click', () => {
                track.scrollBy({ left: -310, behavior: 'smooth' }); // Width of card + gap
            });

            scrollRightBtn.addEventListener('click', () => {
                track.scrollBy({ left: 310, behavior: 'smooth' }); // Width of card + gap
            });
        }
    });
</script>
