<?php
// Student Reviews Component
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Reviews
$reviews_list = [];
if (isset($pdo)) {
    try {
        $sql = "SELECT s.first_name, s.last_name, s.student_image, r.rating, r.review_message, c.course_name
                FROM student_reviews r 
                JOIN students s ON r.student_id = s.id 
                LEFT JOIN courses c ON s.course_id = c.id
                ORDER BY r.created_at DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        $reviews_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $reviews_list = [];
    }
}
?>

<style>
    .reviews-section-wrapper {
        padding: 80px 0;
        background: #f8f9fa; /* Light background to differentiate */
        font-family: 'Poppins', sans-serif;
        position: relative;
    }

    .reviews-container {
        max-width: 98%;
        margin: 0 auto;
    }

    .reviews-heading-area {
        margin-bottom: 50px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .reviews-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        position: relative;
    }
    
    .reviews-title span {
        color: #ffc107; /* Gold/Yellow for reviews/stars vibe */
    }

    /* Slider Buttons */
    .review-nav-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #fff;
        border: 1px solid #eee;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    .review-nav-btn:hover {
        background-color: #ffc107;
        color: #fff;
        border-color: #ffc107;
        transform: translateY(-2px);
    }

    /* Slider Track */
    .reviews-slider-container {
        overflow: hidden;
        padding: 10px 5px 40px 5px; 
    }

    .reviews-track {
        display: flex;
        gap: 30px;
        transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .review-slide {
        flex: 0 0 auto;
        width: 350px; /* Wider card for text */
    }

    /* Review Card Design */
    .review-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.3s ease;
    }

    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    /* Header: Image & Name */
    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .reviewer-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
        border: 2px solid #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .reviewer-info h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: #333;
    }

    .reviewer-info span {
        font-size: 0.85rem;
        color: #888;
        display: block;
    }

    /* Stars */
    .review-rating {
        color: #ffc107;
        font-size: 1rem;
        margin-bottom: 15px;
    }

    /* Message */
    .review-text {
        color: #555;
        font-size: 0.95rem;
        line-height: 1.6;
        font-style: italic;
        position: relative;
    }
    
    .review-text::before {
        content: '"';
        font-size: 3rem;
        color: #f0f0f0;
        position: absolute;
        top: -20px;
        left: -10px;
        z-index: 0;
        font-family: Georgia, serif;
    }
    
    .review-message-content {
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .review-slide {
            width: 280px;
        }
        .reviews-title {
            font-size: 2rem;
        }
    }
</style>

<div class="reviews-section-wrapper">
    <div class="container-fluid reviews-container">
        
        <div class="reviews-heading-area">
            <h2 class="reviews-title">What Our <span>Students Say</span></h2>
            <div class="d-flex gap-2">
                <button class="review-nav-btn" id="revPrev"><i class="fas fa-arrow-left"></i></button>
                <button class="review-nav-btn" id="revNext"><i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <div class="reviews-slider-container">
            <div class="reviews-track" id="reviewsTrack">
                <?php if (!empty($reviews_list)): ?>
                    <?php foreach ($reviews_list as $review): ?>
                        <div class="review-slide">
                            <div class="review-card">
                                
                                <div class="review-rating">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <?php if($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>

                                <div class="review-text">
                                    <span class="review-message-content">
                                        <?php echo htmlspecialchars(substr($review['review_message'], 0, 150) . (strlen($review['review_message']) > 150 ? '...' : '')); ?>
                                    </span>
                                </div>

                                <div class="review-header mt-4 pt-3 border-top">
                                    <?php 
                                        $rImage = !empty($review['student_image']) ? 'student/' . $review['student_image'] : 'https://ui-avatars.com/api/?name='.urlencode($review['first_name']).'&background=random';
                                        
                                        // Handle absolute text path if needed or fix path
                                        if (strpos($rImage, 'student/') === 0 && !file_exists(__DIR__ . '/../' . $rImage)) {
                                             // fallback
                                             $rImage = 'https://ui-avatars.com/api/?name='.urlencode($review['first_name']).'&background=random';
                                        }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($rImage); ?>" alt="Student" class="reviewer-img">
                                    <div class="reviewer-info">
                                        <h5><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h5>
                                        <span><?php echo htmlspecialchars($review['course_name'] ?? 'Student'); ?></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted w-100 py-5">
                        <p>No reviews yet. Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('reviewsTrack');
        const prevBtn = document.getElementById('revPrev');
        const nextBtn = document.getElementById('revNext');
        
        if (!track || !track.children.length) return;

        const cardWidth = document.querySelector('.review-slide').offsetWidth + 30; // Width + Gap
        let scrollAmount = 0;
        
        // Auto Play
        let reviewAutoPlay = setInterval(() => {
            moveRight();
        }, 4000); // 4 seconds

        function moveRight() {
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (scrollAmount >= maxScroll - 10) { 
                // Reset to start
                scrollAmount = 0;
                track.style.transition = 'none';
                track.style.transform = `translateX(0px)`;
                setTimeout(() => { track.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1)'; }, 50);
            } else {
                scrollAmount += cardWidth;
                if(scrollAmount > maxScroll) scrollAmount = maxScroll;
                track.style.transform = `translateX(-${scrollAmount}px)`;
            }
        }

        function moveLeft() {
            scrollAmount -= cardWidth;
            if (scrollAmount < 0) scrollAmount = 0;
            track.style.transform = `translateX(-${scrollAmount}px)`;
        }

        if(nextBtn) {
            nextBtn.addEventListener('click', () => {
                clearInterval(reviewAutoPlay);
                moveRight();
                reviewAutoPlay = setInterval(moveRight, 4000);
            });
        }

        if(prevBtn) {
            prevBtn.addEventListener('click', () => {
                clearInterval(reviewAutoPlay);
                moveLeft();
                reviewAutoPlay = setInterval(moveRight, 4000);
            });
        }
        
        // Touch support
        let startX;
        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            clearInterval(reviewAutoPlay);
        });

        track.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            if (startX - endX > 50) moveRight();
            if (endX - startX > 50) moveLeft();
            reviewAutoPlay = setInterval(moveRight, 4000);
        });
    });
</script>
