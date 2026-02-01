<?php
// course-details.php
require_once 'database/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: courses.php");
    exit;
}

$course_id = intval($_GET['id']);
$course = null;

try {
    $stmt = $pdo->prepare("SELECT c.*, cat.category_name 
                           FROM courses c 
                           LEFT JOIN course_categories cat ON c.category_id = cat.id 
                           WHERE c.id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching course details");
}

if (!$course) {
    die("Course not found");
}

// Discount Logic (Fake 20% off for visual appeal if not set)
$original_fees = $course['course_fees'] * 1.25; 
$discount_percent = 20;
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    :root {
        --pw-blue: #1b2124; /* Dark slate */
        --pw-primary: #5a4bda; /* Primary Purple/Blue like Unacademy/PW */
        --pw-accent: #00b894; /* Success Green */
    }
    
    body {
        background-color: #f5f7f9;
        font-family: 'Poppins', sans-serif;
    }

    /* Hero Section */
    .course-hero {
        background-color: #1a1b1f;
        color: #fff;
        padding: 60px 0 80px; /* Extra padding bottom for overlap */
        position: relative;
    }

    .hero-breadcrumb a {
        color: #b3b3b3;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .hero-breadcrumb span {
        color: #777;
        margin: 0 8px;
    }

    .course-title {
        font-size: 2.8rem;
        font-weight: 700;
        margin-top: 15px;
        line-height: 1.2;
    }

    .course-meta-tags {
        margin: 20px 0;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .meta-tag {
        background: rgba(255,255,255,0.1);
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    
    .meta-tag i {
        margin-right: 8px;
        color: #ffd700;
    }

    .instructor-mini {
        display: flex;
        align-items: center;
        margin-top: 25px;
    }
    
    .instructor-mini img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid #fff;
    }

    /* Main Content Layout */
    .course-content-wrapper {
        margin-top: -40px; /* Overlap effect */
        padding-bottom: 60px;
    }

    /* Left Column */
    .content-card {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #eee;
    }

    .section-head {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d2f31;
        margin-bottom: 20px;
    }

    .what-learn-box {
        border: 1px solid #e0e0e0;
        padding: 24px;
        border-radius: 8px;
        background: #fff;
    }
    
    .check-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .check-list li {
        list-style: none;
        display: flex;
        align-items: flex-start;
        font-size: 0.95rem;
        color: #444;
    }
    
    .check-list li i {
        color: #27ae60;
        margin-right: 10px;
        margin-top: 4px;
    }

    /* Right Sidebar (Sticky) */
    .course-sidebar {
        position: sticky;
        top: 20px;
        z-index: 10;
    }

    .enroll-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .preview-video-box {
        position: relative;
        height: 200px;
        background: #000;
        cursor: pointer;
    }
    
    .preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.8;
    }
    
    .play-btn {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 60px; height: 60px;
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .enroll-body {
        padding: 24px;
    }

    .price-large {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2d2f31;
    }
    
    .price-original {
        text-decoration: line-through;
        color: #777;
        margin-left: 10px;
        font-size: 1.1rem;
    }
    
    .discount-badge {
        color: #d32f2f;
        font-weight: 600;
        font-size: 0.9rem;
        margin-left: 10px;
    }

    .btn-enroll-lg {
        background: var(--pw-primary);
        color: #fff;
        width: 100%;
        padding: 14px;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        margin-top: 15px;
        transition: all 0.2s;
    }
    
    .btn-enroll-lg:hover {
        background: #4839bd;
        transform: translateY(-2px);
    }
    
    .btn-enquire {
        background: #fff;
        color: #333;
        width: 100%;
        padding: 12px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        border: 2px solid #ddd;
        margin-top: 10px;
    }

    .course-includes {
        margin-top: 25px;
    }
    
    .course-includes h6 {
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .include-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #555;
    }
    
    .include-item i {
        width: 24px;
        color: #555;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .course-hero { padding: 40px 0; }
        .course-title { font-size: 2rem; }
        .course-content-wrapper { margin-top: 0; }
        .course-sidebar { position: relative; top: 0; margin-bottom: 40px; order: -1; }
        .enroll-card { margin-bottom: 30px; }
    }
</style>

<div class="course-hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="hero-breadcrumb">
                    <a href="index.php">Home</a> <span>/</span> 
                    <a href="courses.php">Courses</a> <span>/</span> 
                    <span class="text-light"><?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?></span>
                </div>
                
                <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
                
                <div class="course-meta-tags">
                    <div class="meta-tag" style="background: #ffd700; color: #000; font-weight: 600;">
                        Bestseller
                    </div>
                    <div class="meta-tag">
                        <i class="fas fa-star"></i> 4.8 (1,254 ratings)
                    </div>
                    <div class="meta-tag">
                        <i class="fas fa-user-graduate"></i> 5,800+ Students
                    </div>
                    <div class="meta-tag">
                        <i class="fas fa-globe"></i> English / Hindi
                    </div>
                </div>

                <div class="instructor-mini">
                    <img src="assets/logo/frontpage-logo.webp" alt="Instructor">
                    <div>
                        <div style="font-size: 0.85rem; opacity: 0.8;">Created by</div>
                        <div style="font-weight: 600;">Pace Foundation Expert Faculty</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="course-content-wrapper">
    <div class="container">
        <div class="row">
            <!-- Left Column: Details -->
            <div class="col-lg-8">
                
                <!-- What you'll learn (Static for now, can be dynamic later) -->
                <div class="content-card">
                    <h3 class="section-head">What you'll learn</h3>
                    <div class="what-learn-box">
                        <ul class="check-list">
                            <li><i class="fas fa-check"></i> Comprehensive understanding of <?php echo htmlspecialchars($course['course_name']); ?></li>
                            <li><i class="fas fa-check"></i> Practical skills and real-world applications</li>
                            <li><i class="fas fa-check"></i> Industry-standard tools and technologies</li>
                            <li><i class="fas fa-check"></i> Project-based learning approach</li>
                            <li><i class="fas fa-check"></i> Preparation for professional certification</li>
                            <li><i class="fas fa-check"></i> Career guidance and resume building tips</li>
                        </ul>
                    </div>
                </div>

                <!-- Course Description -->
                <div class="content-card">
                    <h3 class="section-head">Description</h3>
                    <div class="course-desc-body">
                        <?php echo $course['description']; // Assumed safe HTML from Summernote ?>
                    </div>
                </div>

                <!-- Curriculum / Structure -->
                <?php if ($course['has_units']): ?>
                <div class="content-card">
                    <h3 class="section-head">Course Structure</h3>
                    <p class="mb-4">This course is divided into <strong><?php echo $course['unit_count']; ?> <?php echo ucfirst($course['unit_type']); ?>s</strong>.</p>
                    
                    <div class="accordion" id="curriculumAccordion">
                        <?php for($i=1; $i<=$course['unit_count']; $i++): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                <button class="accordion-button <?php echo $i!==1 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>">
                                    <?php echo ucfirst($course['unit_type']); ?> <?php echo $i; ?> Module
                                </button>
                            </h2>
                            <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i===1 ? 'show' : ''; ?>" data-bs-parent="#curriculumAccordion">
                                <div class="accordion-body">
                                    <ul class="mb-0">
                                        <li>Introduction to Module <?php echo $i; ?></li>
                                        <li>Core Concepts and Theory</li>
                                        <li>Practical Lab Sessions</li>
                                        <li>Project Work and Assessment</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Enrollment Card -->
            <div class="col-lg-4">
                <div class="course-sidebar">
                    <div class="enroll-card">
                        <!-- Video Preview -->
                        <div class="preview-video-box">
                            <?php 
                            $imgSrc = !empty($course['course_image']) ? $course['course_image'] : 'assets/img/default-course.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="preview-img" alt="Course Preview">
                            <div class="play-btn">
                                <i class="fas fa-play text-dark ps-1"></i>
                            </div>
                        </div>

                        <div class="enroll-body">
                            <div class="d-flex align-items-end mb-3">
                                <div class="price-large">₹<?php echo number_format($course['course_fees'], 2); ?></div>
                                <div class="price-original">₹<?php echo number_format($original_fees, 2); ?></div>
                                <div class="discount-badge">20% OFF</div>
                            </div>

                            <div class="mb-2 text-danger"><i class="fas fa-clock"></i> <small><strong>Offer ends soon!</strong></small></div>

                            <button class="btn-enroll-lg">Go to Cart</button>
                            <button class="btn-enquire" data-bs-toggle="modal" data-bs-target="#enquireModal">Enquire Now</button>

                            <div class="course-includes">
                                <h6>This course includes:</h6>
                                <div class="include-item"><i class="fas fa-video"></i> <?php echo $course['duration_value'] . ' ' . $course['duration_type']; ?> duration</div>
                                <div class="include-item"><i class="fas fa-file-download"></i> Downloadable resources</div>
                                <div class="include-item"><i class="fas fa-mobile-alt"></i> Access on mobile and TV</div>
                                <div class="include-item"><i class="fas fa-certificate"></i> Certificate of completion</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enquire Modal -->
<div class="modal fade" id="enquireModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enquire About This Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" placeholder="+91 9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" placeholder="john@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="3">I am interested in <?php echo htmlspecialchars($course['course_name']); ?>.</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Enquiry</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
