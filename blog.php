<?php
// blog.php
require_once 'database/config.php';

// Fetch Categories
try {
    $stmt = $pdo->query("SELECT * FROM blog_categories WHERE status='active' ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Fetch Blogs
$cat_slug = isset($_GET['category']) ? $_GET['category'] : '';
$current_category_name = 'All Posts';

$sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
        FROM blogs b 
        LEFT JOIN blog_categories c ON b.category_id = c.id 
        WHERE b.status = 'published'";
$params = [];

if ($cat_slug) {
    // Find category ID by slug
    $stmtCat = $pdo->prepare("SELECT id, name FROM blog_categories WHERE slug = ?");
    $stmtCat->execute([$cat_slug]);
    $catRow = $stmtCat->fetch();
    
    if ($catRow) {
        $sql .= " AND b.category_id = ?";
        $params[] = $catRow['id'];
        $current_category_name = $catRow['name'];
    }
}

$sql .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) { $blogs = []; }
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --border-beige: #e0d0b8;
    }

    .blog-page-wrapper {
        background-color: #fff;
        min-height: 90vh;
        padding: 60px 0;
        font-family: 'Poppins', sans-serif;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 50px;
        text-align: center;
        letter-spacing: 1px;
    }

    /* Sidebar */
    .blog-sidebar {
        background: var(--soft-ivory);
        border-radius: 12px;
        padding: 30px;
        border: 1px solid var(--border-beige);
        position: sticky;
        top: 20px;
    }

    .sidebar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--muted-gold);
    }

    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        margin-bottom: 12px;
    }

    .category-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        color: #5d4037;
        text-decoration: none;
        background: #fff;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        font-weight: 500;
    }

    .category-link:hover, .category-link.active {
        background: #fff;
        border-color: var(--muted-gold);
        color: var(--deep-brown);
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    
    .category-link.active {
        background: var(--deep-brown);
        color: #fff;
        border-color: var(--deep-brown);
    }
    
    .category-link.active i { color: var(--muted-gold); }

    /* Blog Card */
    .blog-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid #f0f0f0;
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(109, 76, 65, 0.12);
    }

    .blog-img-container {
        position: relative;
        padding-top: 60%; /* 16:9 Aspect Ratio */
        overflow: hidden;
    }

    .blog-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .blog-card:hover .blog-img {
        transform: scale(1.08);
    }
    
    .blog-category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--deep-brown);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .blog-card-body {
        padding: 25px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .blog-date {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 10px;
        display: block;
    }
    
    .blog-date i { color: var(--muted-gold); margin-right: 5px; }

    .blog-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .blog-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .blog-title a:hover {
        color: var(--muted-gold);
    }

    .blog-excerpt {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .read-more-btn {
        color: var(--deep-brown);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s;
    }

    .read-more-btn i {
        margin-left: 8px;
        transition: transform 0.3s;
        color: var(--muted-gold);
    }

    .read-more-btn:hover {
        color: var(--muted-gold);
    }

    .read-more-btn:hover i {
        transform: translateX(5px);
    }

    @media (max-width: 991px) {
        .blog-sidebar { margin-bottom: 40px; position: static; }
    }
</style>

<div class="blog-page-wrapper">
    <div class="container">
        <h1 class="page-title">Latest Updates & Articles</h1>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="blog-sidebar shadow-sm">
                    <h5 class="sidebar-title">Categories</h5>
                    <ul class="category-list">
                        <li class="category-item">
                            <a href="blog.php" class="category-link <?php echo $cat_slug == '' ? 'active' : ''; ?>">
                                <span>All Posts</span>
                                <i class="fas fa-th-large"></i>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): 
                            $isActive = ($cat_slug == $cat['slug']) ? 'active' : '';
                        ?>
                        <li class="category-item">
                            <a href="blog.php?category=<?php echo htmlspecialchars($cat['slug']); ?>" class="category-link <?php echo $isActive; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                <i class="fas fa-folder"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="mb-4 d-flex justify-content-between align-items-center border-bottom pb-3">
                    <h4 class="mb-0 text-dark fw-bold"><?php echo htmlspecialchars($current_category_name); ?></h4>
                    <span class="text-muted small"><?php echo count($blogs); ?> articles found</span>
                </div>

                <?php if (count($blogs) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($blogs as $blog): 
                            // Strip HTML tags for excerpt
                            $excerpt = strip_tags($blog['content']);
                            if (strlen($excerpt) > 120) $excerpt = substr($excerpt, 0, 120) . '...';
                            
                            $detailUrl = "blog-details.php?slug=" . $blog['slug'];
                            $imageUrl = $blog['image_path'] ? $blog['image_path'] : 'assets/images/default-blog.jpg';
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="blog-card">
                                <div class="blog-img-container">
                                    <a href="<?php echo $detailUrl; ?>">
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="blog-img">
                                    </a>
                                    <span class="blog-category-badge"><?php echo htmlspecialchars($blog['category_name']); ?></span>
                                </div>
                                <div class="blog-card-body">
                                    <span class="blog-date">
                                        <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                    </span>
                                    <h5 class="blog-title">
                                        <a href="<?php echo $detailUrl; ?>"><?php echo htmlspecialchars($blog['title']); ?></a>
                                    </h5>
                                    <p class="blog-excerpt"><?php echo $excerpt; ?></p>
                                    <a href="<?php echo $detailUrl; ?>" class="read-more-btn">
                                        Read Article <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-5 border">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <p class="mb-0 text-muted">No blog posts found in this category yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
