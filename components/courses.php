<?php
// Courses Component
// Ensure DB connection
if (!isset($pdo)) {
    $configPath = __DIR__ . '/../database/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Fetch Courses
$courses_list = [];
if (isset($pdo)) {
    try {
        // Fetch courses with their category names
        $sql = "SELECT c.*, cat.category_name 
                FROM courses c 
                LEFT JOIN course_categories cat ON c.category_id = cat.id 
                ORDER BY c.id DESC LIMIT 8";
        $stmt = $pdo->query($sql);
        $courses_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail or log error
    }
}
?>

<style>
    .courses-section-wrapper {
        padding: 60px 0;
        background-color: #fdfdfd;
        font-family: 'Poppins', sans-serif;
    }

    .courses-container {
        max-width: 95%; /* Use wide layout like Hero/Teachers */
        margin: 0 auto;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2d2f31;
        margin-bottom: 10px;
    }
    
    .section-subtitle {
        color: #6a6f73;
        font-size: 1.1rem;
        margin-bottom: 40px;
    }

    /* Course Card Design */
    .course-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #d1d7dc;
    }

    .course-img-wrapper {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16/9;
    }

    .course-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .course-card:hover .course-img {
        transform: scale(1.05);
    }

    .course-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .course-category {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #a435f0; /* Purple accent */
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 10px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .course-meta {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .course-fees-wrapper {
        display: flex;
        flex-direction: column;
    }

    .fees-label {
        font-size: 0.75rem;
        color: #6a6f73;
    }

    .course-fees {
        font-size: 1.25rem;
        font-weight: 800;
        color: #2d2f31;
    }

    .btn-apply {
        background-color: #1a2c3f;
        color: #fff;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .btn-apply:hover {
        background-color: #0f1c29;
        color: #fff;
    }

    /* Badge */
    .badge-new {
        position: absolute;
        top: 15px;
        left: 15px;
        background-color: #ffc107;
        color: #000;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 4px;
        z-index: 2;
    }
</style>

<div class="courses-section-wrapper">
    <div class="container-fluid courses-container">
        
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="section-title">Explore Our <span style="color: #0d6efd;">Premium Courses</span></h2>
                <p class="section-subtitle">Get started with our most popular courses and kickstart your career.</p>
            </div>
            <div class="col-md-4 text-md-end align-self-center">
                <a href="courses.php" class="btn btn-outline-primary fw-bold">View All Courses <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($courses_list)): ?>
                <?php foreach ($courses_list as $course): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <div class="course-card">
                            <div class="course-img-wrapper">
                                <span class="badge-new">NEW</span>
                                <?php 
                                    $imgSrc = !empty($course['course_image']) ? $course['course_image'] : 'https://via.placeholder.com/400x225?text=Course+Image';
                                ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>" class="course-img">
                            </div>
                            <div class="course-body">
                                <div class="course-category"><?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?></div>
                                <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                
                                <div class="course-meta">
                                    <div class="course-fees-wrapper">
                                        <span class="fees-label">Course Fees</span>
                                        <span class="course-fees">₹<?php echo number_format($course['course_fees'], 2); ?></span>
                                    </div>
                                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-apply text-center w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-light border">
                        <p class="mb-0 text-muted fs-5">No courses available at the moment. Please check back later.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
