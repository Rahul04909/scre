<?php
// courses.php
require_once 'database/config.php';

// Pagination Setup
$limit = 9; // Courses per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter Parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// Build Query
$where_clauses = ["status = 'Active'"];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(course_name LIKE ? OR course_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter > 0) {
    $where_clauses[] = "category_id = ?";
    $params[] = $category_filter;
}

$where_sql = "WHERE " . implode(' AND ', $where_clauses);

// Fetch Total Count for Pagination
$count_sql = "SELECT COUNT(*) FROM courses $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_courses = $stmt->fetchColumn();
$total_pages = ceil($total_courses / $limit);

// Fetch Courses
$sql = "SELECT c.*, cat.category_name 
        FROM courses c 
        LEFT JOIN course_categories cat ON c.category_id = cat.id 
        $where_sql 
        ORDER BY c.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Categories for Sidebar
$cat_sql = "SELECT * FROM course_categories ORDER BY category_name ASC";
$categories = $pdo->query($cat_sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --text-dark: #2d2f31;
    }

    body {
        background-color: var(--soft-ivory);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--deep-brown), #3e2723);
        color: #fff;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .page-header::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: -10%;
        width: 120%;
        height: 60px;
        background: var(--soft-ivory);
        transform: rotate(-2deg);
    }

    .page-title {
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Sidebar */
    .course-sidebar {
        background: #fff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #eee;
        position: sticky;
        top: 20px;
    }

    .widget-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--muted-gold);
    }

    .search-form .form-control {
        border-radius: 30px;
        padding: 10px 20px;
        border: 1px solid #ddd;
    }
    
    .search-form .btn {
        border-radius: 30px;
        margin-left: -40px;
        z-index: 10;
        background: none;
        border: none;
        color: var(--warm-brown);
    }

    .cat-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cat-list li {
        margin-bottom: 10px;
    }

    .cat-list a {
        display: flex;
        justify-content: space-between;
        color: #666;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .cat-list a:hover, .cat-list a.active {
        background-color: var(--soft-ivory);
        color: var(--warm-brown);
        font-weight: 600;
    }
    
    .cat-count {
        background: #eee;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 10px;
        color: #555;
    }

    /* Course Cards (Grid View) */
    .course-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #eee;
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .course-thumb {
        height: 180px;
        background-color: #f0f0f0;
        position: relative;
        overflow: hidden;
    }

    .course-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .course-card:hover .course-thumb img {
        transform: scale(1.05);
    }

    .course-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        backdrop-filter: blur(2px);
    }

    .course-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .course-cat {
        font-size: 0.8rem;
        color: var(--muted-gold);
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px;
        line-height: 1.4;
    }
    
    .course-info {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 15px;
    }
    
    .course-footer {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #f5f5f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .course-price {
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--deep-brown);
    }

    .btn-view {
        background-color: var(--soft-ivory);
        color: var(--warm-brown);
        border: 1px solid var(--warm-brown);
        padding: 6px 15px;
        border-radius: 5px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view:hover {
        background-color: var(--warm-brown);
        color: #fff;
    }

    /* List View Styles */
    .courses-list-view .course-card {
        flex-direction: row;
        height: auto;
        min-height: 200px;
    }
    
    .courses-list-view .course-thumb {
        width: 300px;
        height: auto;
        flex-shrink: 0;
    }
    
    .courses-list-view .course-body {
        padding: 25px;
    }

    /* Filters Bar */
    .filters-bar {
        background: #fff;
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid #eee;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .view-toggles .btn {
        color: #888;
        border: 1px solid #ddd;
    }
    
    .view-toggles .btn.active {
        background-color: var(--warm-brown);
        color: #fff;
        border-color: var(--warm-brown);
    }

    @media (max-width: 768px) {
        .courses-list-view .course-card {
            flex-direction: column;
        }
        .courses-list-view .course-thumb {
            width: 100%;
            height: 200px;
        }
    }
</style>

<!-- Main Page Content -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title">Explore Our Courses</h1>
        <p class="mb-0 opacity-75">Enhance your skills with our professional certification programs</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="course-sidebar">
                
                <!-- Search Widget -->
                <div class="mb-4">
                    <h5 class="widget-title">Search</h5>
                    <form action="" method="GET" class="search-form d-flex align-items-center">
                        <?php if($category_filter): ?>
                        <input type="hidden" name="cat" value="<?php echo $category_filter; ?>">
                        <?php endif; ?>
                        <input type="text" name="q" class="form-control" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Categories Widget -->
                <div>
                    <h5 class="widget-title">Categories</h5>
                    <ul class="cat-list">
                        <li>
                            <a href="courses.php" class="<?php echo $category_filter == 0 ? 'active' : ''; ?>">
                                <span>All Courses</span>
                                <span class="cat-count"><?php echo $pdo->query("SELECT COUNT(*) FROM courses WHERE status='Active'")->fetchColumn(); ?></span>
                            </a>
                        </li>
                        <?php foreach($categories as $cat): 
                            // Get count for this category
                            $stmtC = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE category_id = ? AND status='Active'");
                            $stmtC->execute([$cat['id']]);
                            $cCount = $stmtC->fetchColumn();
                        ?>
                        <li>
                            <a href="courses.php?cat=<?php echo $cat['id']; ?>" class="<?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                                <span><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                <span class="cat-count"><?php echo $cCount; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            
            <!-- Filters Bar -->
            <div class="filters-bar">
                <div class="text-muted">
                    Showing <strong><?php echo count($courses); ?></strong> of <strong><?php echo $total_courses; ?></strong> courses
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-none d-md-inline text-muted small me-2">View:</span>
                    <div class="btn-group view-toggles" role="group">
                        <button type="button" class="btn btn-sm active" id="grid-view-btn" title="Grid View">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-sm" id="list-view-btn" title="List View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Courses Container -->
            <?php if(count($courses) > 0): ?>
            <div id="courses-container" class="row g-4">
                <?php foreach($courses as $course): ?>
                <div class="col-md-6 col-lg-4 course-item-col">
                    <div class="course-card">
                        <div class="course-thumb">
                            <a href="course-details.php?id=<?php echo $course['id']; ?>">
                                <?php 
                                    $imgSrc = !empty($course['og_image']) ? $course['og_image'] : 'assets/logo/frontpage-logo.webp'; 
                                ?>
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                            </a>
                            <span class="course-badge"><?php echo ucfirst($course['duration_value'] . ' ' . $course['duration_type']); ?></span>
                        </div>
                        <div class="course-body">
                            <div class="course-cat"><?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?></div>
                            <h3 class="course-title">
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </a>
                            </h3>
                            <div class="course-info">
                                <span><i class="fas fa-code me-1"></i> <?php echo htmlspecialchars($course['course_code']); ?></span>
                                <span><i class="fas fa-signal me-1"></i> <?php echo ucwords(str_replace('_', ' ', $course['course_type'])); ?></span>
                            </div>
                            <!-- Short desc for List View (Hidden in Grid via CSS or line-clamp) -->
                            <p class="text-muted small mb-3 list-view-desc d-none">
                                <?php echo substr(strip_tags($course['description']), 0, 150) . '...'; ?>
                            </p>
                            
                            <div class="course-footer">
                                <div class="course-price">
                                    ₹<?php echo number_format($course['course_fees']); ?>
                                </div>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="mt-5 d-flex justify-content-center">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&q=<?php echo urlencode($search); ?>&cat=<?php echo $category_filter; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <li class="page-item <?php echo $i==$page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&cat=<?php echo $category_filter; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&q=<?php echo urlencode($search); ?>&cat=<?php echo $category_filter; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                <h5>No courses found</h5>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="courses.php" class="btn btn-primary mt-2">View All Courses</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gridBtn = document.getElementById('grid-view-btn');
        const listBtn = document.getElementById('list-view-btn');
        const container = document.getElementById('courses-container');
        const items = document.querySelectorAll('.course-item-col');
        const listDescs = document.querySelectorAll('.list-view-desc');

        listBtn.addEventListener('click', function() {
            // Activate List View
            container.classList.add('courses-list-view');
            gridBtn.classList.remove('active');
            listBtn.classList.add('active');
            
            // Adjust Columns for List View (Full width)
            items.forEach(item => {
                item.classList.remove('col-md-6', 'col-lg-4');
                item.classList.add('col-12');
            });

            // Show descriptions
            listDescs.forEach(desc => desc.classList.remove('d-none'));
        });

        gridBtn.addEventListener('click', function() {
            // Activate Grid View
            container.classList.remove('courses-list-view');
            listBtn.classList.remove('active');
            gridBtn.classList.add('active');
            
            // Adjust Columns for Grid View
            items.forEach(item => {
                item.classList.remove('col-12');
                item.classList.add('col-md-6', 'col-lg-4');
            });

            // Hide descriptions
            listDescs.forEach(desc => desc.classList.add('d-none'));
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
