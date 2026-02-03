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

// Calculate total fees
$total_fees = $course['course_fees'] + $course['admission_fees'] + ($course['exam_fees_enabled'] ? $course['exam_fees'] : 0);
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .center-details-wrapper {
        background-color: #fcefe633;
        font-family: 'Poppins', sans-serif;
    }

    /* Hero Banner Section (Reused CD classes for consistency) */
    .cd-hero {
        position: relative;
        height: 350px;
        background-color: #2c3e50;
        background-image: url('<?php echo !empty($course['course_image']) ? $course['course_image'] : "assets/img/default-course-banner.jpg"; ?>');
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

    /* Course Image/Logo style in Hero */
    .cd-logo {
        width: 120px;
        height: 120px;
        background: #fff;
        border-radius: 10px;
        padding: 5px;
        object-fit: contain;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin-right: 25px;
        float: left;
    }

    .cd-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .cd-meta {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cd-rating {
        background: #e67e22;
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9rem;
        margin-right: 10px;
    }

    .cd-badge {
        background: #27ae60;
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    /* Main Content */
    .cd-main {
        padding: 40px 0;
    }

    .cd-section-card {
        background: #fff;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 30px;
        border: 1px solid #eee;
    }

    .cd-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 25px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        display: inline-block;
    }

    /* Info Grid */
    .cd-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }

    .cd-info-item {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        transition: transform 0.2s;
    }
    
    .cd-info-item:hover {
        transform: translateY(-3px);
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .cd-info-icon {
        font-size: 1.8rem;
        color: #e67e22;
        margin-right: 15px;
        width: 40px;
        text-align: center;
    }

    .cd-info-label {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .cd-info-value {
        font-weight: 700;
        color: #2d2f31;
        font-size: 1.05rem;
    }

    /* Description Content */
    .course-description-content {
        line-height: 1.8;
        color: #444;
        font-size: 1rem;
    }
    
    .course-description-content h2, 
    .course-description-content h3 {
        margin-top: 25px;
        margin-bottom: 15px;
        color: #2c3e50;
        font-weight: 700;
    }
    
    .course-description-content ul {
        margin-bottom: 20px;
        padding-left: 20px;
    }
    
    .course-description-content li {
        margin-bottom: 8px;
    }

    /* Sidebar Sticky */
    .cd-sidebar {
        position: sticky;
        top: 20px;
    }

    .cd-contact-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08); /* Stronger shadow for floating feel */
        border: 1px solid #eee;
    }

    .cd-contact-header {
        background: #e67e22;
        color: #fff;
        padding: 20px;
        font-weight: 700;
        font-size: 1.2rem;
        text-align: center;
    }

    .cd-contact-body {
        padding: 25px;
    }
    
    .fee-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        color: #555;
        font-size: 0.95rem;
        border-bottom: 1px dashed #eee;
        padding-bottom: 12px;
    }
    
    .fee-row.total {
        border-bottom: none;
        color: #2d2f31;
        font-weight: 800;
        font-size: 1.3rem;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 2px solid #f0f0f0;
    }

    .cd-action-btn-primary {
        display: block;
        width: 100%;
        padding: 14px;
        text-align: center;
        background: #1a2c3f;
        color: #fff;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 20px;
        transition: all 0.2s;
        font-size: 1.1rem;
    }
    
    .cd-action-btn-primary:hover {
        background: #0f1c29;
        transform: translateY(-2px);
        color: #fff;
    }
    
    .cd-action-btn-secondary {
        display: block;
        width: 100%;
        padding: 12px;
        text-align: center;
        background: #fff;
        color: #333;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 10px;
        border: 2px solid #ddd;
        transition: all 0.2s;
    }
    
    .cd-action-btn-secondary:hover {
        border-color: #aaa;
        background: #f9f9f9;
        color: #333;
    }
</style>

<div class="center-details-wrapper">
    <!-- Hero Section -->
    <div class="cd-hero">
        <div class="cd-hero-overlay">
            <div class="container">
                <div class="d-flex align-items-end">
                    <?php 
                        // Use course OG image or main image as "Logo" for the header if small logo isn't separate, 
                        // effectively mimicking the Center logo style.
                        $logoSrc = !empty($course['og_image']) ? $course['og_image'] : 'assets/logo/frontpage-logo.webp';
                    ?>
                    <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Course Logo" class="cd-logo d-none d-md-block">
                    
                    <div class="cd-header-content">
                        <div class="d-flex align-items-center mb-2">
                             <span class="badge bg-warning text-dark me-2">BESTSELLER</span>
                             <span class="text-white-50 small text-uppercase"><?php echo htmlspecialchars($course['category_name'] ?? 'Professional Course'); ?></span>
                        </div>
                        
                        <h1 class="cd-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
                        
                        <div class="cd-meta">
                            <span><i class="fas fa-code me-2"></i> Code: <strong><?php echo htmlspecialchars($course['course_code']); ?></strong></span>
                            <span><i class="fas fa-clock me-2"></i> Duration: <strong><?php echo $course['duration_value'] . ' ' . ucfirst($course['duration_type']); ?></strong></span>
                        </div>
                        
                        <div>
                            <span class="cd-rating">4.9 <i class="fas fa-star text-white"></i></span>
                            <span class="cd-badge"><i class="fas fa-check-circle"></i> Verified Course</span>
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
                    
                    <!-- Quick Info Grid -->
                    <div class="cd-section-card">
                        <h2 class="cd-heading">Course Highlights</h2>
                        <div class="cd-info-grid">
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-graduation-cap"></i></div>
                                <div>
                                    <div class="cd-info-label">Qualification</div>
                                    <div class="cd-info-value"><?php echo ucwords(str_replace('_', ' ', $course['course_type'])); ?></div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <div class="cd-info-label">Duration</div>
                                    <div class="cd-info-value"><?php echo $course['duration_value'] . ' ' . ucfirst($course['duration_type']); ?></div>
                                </div>
                            </div>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-layer-group"></i></div>
                                <div>
                                    <div class="cd-info-label">Mode</div>
                                    <div class="cd-info-value"><?php echo $course['has_units'] ? ucfirst($course['unit_type']) . ' Based' : 'Direct'; ?></div>
                                </div>
                            </div>
                            <?php if ($course['exam_fees_enabled']): ?>
                            <div class="cd-info-item">
                                <div class="cd-info-icon"><i class="fas fa-file-signature"></i></div>
                                <div>
                                    <div class="cd-info-label">Exam</div>
                                    <div class="cd-info-value">Required</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="cd-section-card">
                        <h2 class="cd-heading">About This Course</h2>
                        <div class="course-description-content">
                            <?php if (!empty($course['description'])): ?>
                                <?php echo $course['description']; ?>
                            <?php else: ?>
                                <p>No detailed description available for this course yet.</p>
                                <p>Please contact the center administration for syllabus and brochure.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Syllabus / Units Placeholder -->
                    <?php if ($course['has_units']): ?>
                    <div class="cd-section-card">
                        <h2 class="cd-heading">Course Structure</h2>
                        <div class="alert alert-light border">
                            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Unit Information</h5>
                            <p>This course is divided into <strong><?php echo $course['unit_count']; ?> <?php echo ucfirst($course['unit_type']); ?>s</strong>.</p>
                            <hr>
                            <p class="mb-0">Please enroll or download the brochure to view the detailed syllabus for each <?php echo $course['unit_type']; ?>.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <div class="cd-sidebar">
                        <div class="cd-contact-card">
                            <div class="cd-contact-header">
                                <i class="fas fa-wallet me-2"></i> Fee Structure
                            </div>
                            <div class="cd-contact-body">
                                
                                <div class="fee-row">
                                    <span>Course Fee</span>
                                    <span>₹<?php echo number_format($course['course_fees'], 2); ?></span>
                                </div>
                                
                                <?php if($course['admission_fees'] > 0): ?>
                                <div class="fee-row">
                                    <span>Admission Fee</span>
                                    <span>₹<?php echo number_format($course['admission_fees'], 2); ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if($course['exam_fees_enabled']): ?>
                                <div class="fee-row">
                                    <span>Exam Fee</span>
                                    <span>₹<?php echo number_format($course['exam_fees'], 2); ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="fee-row total">
                                    <span>Total Amount</span>
                                    <span>₹<?php echo number_format($total_fees, 2); ?></span>
                                </div>

                                <div class="mt-3 text-success text-center small">
                                    <i class="fas fa-check-circle me-1"></i> EMI Options Available
                                </div>

                                <a href="apply.php?course_id=<?php echo $course['id']; ?>" class="cd-action-btn-primary">
                                    Apply Now <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                                
                                <button class="cd-action-btn-secondary" data-bs-toggle="modal" data-bs-target="#enquireModal">
                                    <i class="fas fa-envelope me-2"></i> Enquire Now
                                </button>
                                
                                <a href="https://wa.me/?text=I%20am%20interested%20in%20<?php echo urlencode($course['course_name']); ?>" target="_blank" class="btn btn-success w-100 mt-2 py-2 fw-bold" style="border-radius: 8px;">
                                    <i class="fab fa-whatsapp me-2"></i> Share on WhatsApp
                                </a>

                            </div>
                        </div>
                        
                        <!-- Extra Card: Why Join? -->
                        <div class="cd-contact-card mt-4">
                            <div class="cd-contact-header">
                                <i class="fas fa-thumbs-up me-2"></i> Why Join This Course?
                            </div>
                            <div class="cd-contact-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Expert Faculty</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Practical Training</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Job Assistance</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-success me-2"></i> Recognized Certificate</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Available Centers Card -->
                        <?php
                            // Fetch centers offering this course
                            try {
                                $sqlCenters = "SELECT c.id, c.center_name, c.center_code, ci.name as city_name, s.name as state_name 
                                               FROM centers c
                                               JOIN center_course_allotment cca ON c.id = cca.center_id
                                               LEFT JOIN cities ci ON c.city = ci.id
                                               LEFT JOIN states s ON c.state = s.id
                                               WHERE cca.course_id = :course_id
                                               ORDER BY c.center_name ASC LIMIT 5";
                                $stmtCenters = $pdo->prepare($sqlCenters);
                                $stmtCenters->execute([':course_id' => $course_id]);
                                $allocated_centers = $stmtCenters->fetchAll(PDO::FETCH_ASSOC);
                            } catch (PDOException $e) {
                                $allocated_centers = [];
                            }
                        ?>
                        
                        <?php if (!empty($allocated_centers)): ?>
                        <div class="cd-contact-card mt-4">
                            <div class="cd-contact-header">
                                <i class="fas fa-map-marker-alt me-2"></i> Available at Centers
                            </div>
                            <div class="cd-contact-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($allocated_centers as $ac): ?>
                                    <li class="list-group-item px-4 py-3">
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($ac['center_name']); ?></div>
                                        <div class="small text-muted">
                                            <i class="fas fa-map-pin me-1 text-danger"></i> 
                                            <?php echo htmlspecialchars($ac['city_name'] . ', ' . $ac['state_name']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-hashtag me-1 text-secondary"></i> 
                                            Code: <?php echo htmlspecialchars($ac['center_code']); ?>
                                        </div>
                                        <a href="center-details.php?id=<?php echo $ac['id']; ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                            View Center <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enquire Modal (Reused) -->
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
