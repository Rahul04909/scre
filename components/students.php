<?php
// Students Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Students
$students_list = [];
if (isset($pdo)) {
    try {
        // Fetch students with course name
        // Limit to 10-15 recent students or featured ones
        $sql = "SELECT s.first_name, s.last_name, s.student_image, c.course_name 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id 
                ORDER BY s.id DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        $students_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $students_list = [];
    }
}
?>

<style>
    .students-section-wrapper {
        padding: 60px 0;
        background-color: #fff;
        font-family: 'Poppins', sans-serif;
        position: relative;
    }

    .students-container {
        max-width: 98%;
        margin: 0 auto;
    }

    .students-heading-area {
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .students-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    
    .students-title span {
        color: #dc3545; /* Reddish highlight from reference */
    }

    /* Slider Buttons */
    .student-nav-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff;
        border: 1px solid #ddd;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .student-nav-btn:hover {
        background-color: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }

    /* Slider Track */
    .students-slider-container {
        overflow: hidden;
        padding: 10px 5px 30px 5px; /* Bottom padding for shadow */
    }

    .students-track {
        display: flex;
        gap: 25px;
        transition: transform 0.5s ease-in-out;
    }

    .student-slide {
        flex: 0 0 auto;
        width: 260px; /* Card width */
    }

    /* Card Design */
    .student-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08); /* Soft shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
        border: 1px solid #f0f0f0;
    }

    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }

    .student-img-box {
        width: 100%;
        height: 280px; /* Portrait height */
        background-color: #e9ecef;
        overflow: hidden;
    }

    .student-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top center;
    }

    .student-info {
        padding: 20px 15px;
    }

    .student-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .student-course {
        font-size: 0.85rem;
        font-weight: 600;
        color: #e74c3c; /* Red text for designation/course */
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .students-heading-area {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<div class="students-section-wrapper">
    <div class="container-fluid students-container">
        
        <div class="students-heading-area">
            <h2 class="students-title">Meet Our <span>Students</span></h2>
            <div class="d-flex gap-2">
                <button class="student-nav-btn" id="stdPrev"><i class="fas fa-arrow-left"></i></button>
                <button class="student-nav-btn" id="stdNext"><i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <div class="students-slider-container">
            <div class="students-track" id="studentsTrack">
                <?php if (!empty($students_list)): ?>
                    <?php foreach ($students_list as $student): ?>
                        <div class="student-slide">
                            <div class="student-card">
                                <div class="student-img-box">
                                    <?php 
                                        $sImg = !empty($student['student_image']) ? $student['student_image'] : 'https://via.placeholder.com/300x350?text=Student';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($sImg); ?>" alt="<?php echo htmlspecialchars($student['first_name']); ?>" class="student-img">
                                </div>
                                <div class="student-info">
                                    <h5 class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h5>
                                    <p class="student-course"><?php echo htmlspecialchars($student['course_name'] ?? 'Student'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted w-100">
                        <p>No students enrolled yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('studentsTrack');
        const prevBtn = document.getElementById('stdPrev');
        const nextBtn = document.getElementById('stdNext');
        
        if (!track || !track.children.length) return;

        const cardWidth = 260 + 25; // Width + Gap
        let scrollAmount = 0;
        const maxScroll = track.scrollWidth - track.clientWidth;
        
        // Auto Play
        let autoPlayInterval = setInterval(() => {
            moveRight();
        }, 3000); // 3 seconds

        function moveRight() {
            if (scrollAmount + track.clientWidth >= track.scrollWidth - 10) { // Near end
                scrollAmount = 0; // Reset to start
                track.style.transition = 'none'; // Instant jump
                track.style.transform = `translateX(0px)`;
                setTimeout(() => { track.style.transition = 'transform 0.5s ease-in-out'; }, 50);
            } else {
                scrollAmount += cardWidth;
                if(scrollAmount > track.scrollWidth - track.clientWidth) {
                    scrollAmount = track.scrollWidth - track.clientWidth;
                }
                track.style.transform = `translateX(-${scrollAmount}px)`;
            }
        }

        function moveLeft() {
            scrollAmount -= cardWidth;
            if (scrollAmount < 0) scrollAmount = 0;
            track.style.transform = `translateX(-${scrollAmount}px)`;
        }

        nextBtn.addEventListener('click', () => {
            clearInterval(autoPlayInterval); // Stop auto on interaction
            moveRight();
            autoPlayInterval = setInterval(moveRight, 3000); // Restart
        });

        prevBtn.addEventListener('click', () => {
             clearInterval(autoPlayInterval);
             moveLeft();
             autoPlayInterval = setInterval(moveRight, 3000);
        });
        
        // Touch/Swipe Support
        let startX;
        track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            clearInterval(autoPlayInterval);
        });

        track.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            if (startX - endX > 50) moveRight();
            if (endX - startX > 50) moveLeft();
            autoPlayInterval = setInterval(moveRight, 3000);
        });
    });
</script>
